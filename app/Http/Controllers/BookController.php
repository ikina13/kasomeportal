<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookPurchase;
use App\Models\app_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BookController extends Controller
{
    /**
     * List books with filters.
     */
    public function index(Request $request)
    {
        try {
            $query = Book::query();

            // Filter by language
            if ($request->has('language') && $request->language !== 'all') {
                $query->where('language', $request->language);
            }

            // Filter by level
            if ($request->has('level') && $request->level !== 'all') {
                $query->where('level', $request->level);
            }

             // Search
             if ($request->has('search') && $request->search) {
                 $search = $request->search;
                 $query->where(function($q) use ($search) {
                     $q->where('title', 'ilike', "%{$search}%")
                       ->orWhereHas('authorModel', function($q) use ($search) {
                           $q->where('name', 'ilike', "%{$search}%");
                       })
                       ->orWhere('description', 'ilike', "%{$search}%");
                 });
             }

            // Sort
            $sortBy = $request->input('sort', 'created_at');
            $order = $request->input('order', 'desc');
            $query->orderBy($sortBy, $order);

            // Only show active books for public
            $query->where('is_active', true);
            
            // Exclude donation books from regular listings (unless explicitly requested)
            // Donation books should only appear when donation_only=true or on donation page
            $includeDonations = filter_var($request->input('donation_only', false), FILTER_VALIDATE_BOOLEAN);
            
            // Check if is_donation_enabled column exists
            $hasDonationColumn = \Schema::hasColumn('tbl_books', 'is_donation_enabled');
            
            if ($hasDonationColumn) {
                if (!$includeDonations) {
                    // Exclude donation books from regular listings
                    $query->where(function($q) {
                        $q->where('is_donation_enabled', false)
                          ->orWhereNull('is_donation_enabled');
                    });
                } else {
                    // If donation_only=true, show ONLY donation books
                    $query->where('is_donation_enabled', true);
                }
            }
            // If column doesn't exist yet, all books will show (until migration is run)

            // Get user_id if authenticated (for purchase status)
            $userId = $request->input('user_id');
            $books = $query->with('authorModel')->get();

            // Add purchase status if user is authenticated
            if ($userId) {
                $books = $books->map(function($book) use ($userId) {
                    $purchase = $book->getUserPurchase($userId);
                    $book->has_purchased = $purchase ? true : false;
                    $book->purchase = $purchase;
                    if ($purchase) {
                        $book->download_count = $purchase->download_count;
                        $book->max_downloads = $purchase->max_downloads;
                        $book->remaining_downloads = $purchase->getRemainingDownloads();
                        $book->can_download = $purchase->canDownload();
                    }
                    return $book;
                });
            }

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Books retrieved successfully',
                'data' => $books,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching books: ' . $e->getMessage());
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Failed to retrieve books',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get book details.
     */
    public function show($id, Request $request)
    {
        try {
            $book = Book::with('authorModel')->findOrFail($id);

            // Check if user has access
            $userId = $request->input('user_id');
            $hasAccess = false;
            $purchase = null;
            $accessInfo = [
                'has_access' => false,
                'access_type' => null,
                'purchase_details' => null,
            ];

            if ($userId) {
                $purchase = $book->getUserPurchase($userId);
                if ($purchase) {
                    $hasAccess = true;
                    $accessInfo = [
                        'has_access' => true,
                        'access_type' => $purchase->purchase_type,
                        'purchase_details' => [
                            'purchase_id' => $purchase->id,
                            'purchased_at' => $purchase->purchased_at,
                            'download_count' => $purchase->download_count,
                            'max_downloads' => $purchase->max_downloads,
                            'remaining_downloads' => $purchase->getRemainingDownloads(),
                            'can_download' => $purchase->canDownload(),
                        ],
                    ];
                }
            }

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Book retrieved successfully',
                'data' => $book,
                'access' => $accessInfo,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching book: ' . $e->getMessage());
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Book not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Download book (secure download with token validation).
     */
    public function download($id, Request $request)
    {
        try {
            $book = Book::findOrFail($id);
            $userId = $request->input('user_id');
            $token = $request->input('token');
            $purchaseId = $request->input('purchase_id');

            // Validate user is authenticated
            if (!$userId) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Authentication required',
                    'code' => '401',
                ], 401);
            }

            // Find purchase
            $purchase = $purchaseId 
                ? BookPurchase::find($purchaseId)
                : $book->getUserPurchase($userId);

            if (!$purchase) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Book not purchased',
                    'code' => '403',
                ], 403);
            }

            // Validate user owns the purchase
            if ($purchase->user_id != $userId) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Unauthorized access',
                    'code' => '403',
                ], 403);
            }

            // Validate token
            if ($token && !$purchase->isTokenValid($token)) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Invalid or expired download token',
                    'code' => '403',
                ], 403);
            }

            // Check if user can download
            if (!$purchase->canDownload()) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Download limit reached or token expired',
                    'code' => '403',
                    'remaining_downloads' => $purchase->getRemainingDownloads(),
                ], 403);
            }

            // Check if book file exists
            if (!$book->download_url) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Book file not available',
                    'code' => '404',
                ], 404);
            }

            // Record download
            $purchase->recordDownload(
                $request->ip(),
                $request->userAgent(),
                $book->file_size
            );

            // Get file path
            $filePath = $book->download_url ?? $book->file_name;
            
            // Serve the file directly
            if (Storage::disk('local')->exists($filePath)) {
                // File is in private storage - serve it directly
                $fullPath = Storage::disk('local')->path($filePath);
                $fileName = $book->file_name ? basename($book->file_name) : ($book->title . '.' . ($book->file_type ?? 'pdf'));
                
                return response()->download($fullPath, $fileName, [
                    'Content-Type' => $book->file_type === 'pdf' ? 'application/pdf' : 'application/epub+zip',
                ]);
            } elseif (Storage::disk('public')->exists($filePath)) {
                // File is in public storage
                $fullPath = Storage::disk('public')->path($filePath);
                $fileName = $book->file_name ? basename($book->file_name) : ($book->title . '.' . ($book->file_type ?? 'pdf'));
                
                return response()->download($fullPath, $fileName, [
                    'Content-Type' => $book->file_type === 'pdf' ? 'application/pdf' : 'application/epub+zip',
                ]);
            } elseif (filter_var($filePath, FILTER_VALIDATE_URL)) {
                // External URL - return JSON with URL
                return response()->json([
                    'status' => 'SUCCESS',
                    'message' => 'Download link generated',
                    'data' => [
                        'download_url' => $filePath,
                        'file_name' => $book->file_name ?? $book->title . '.' . ($book->file_type ?? 'pdf'),
                        'file_size' => $book->file_size,
                        'remaining_downloads' => $purchase->getRemainingDownloads(),
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Book file not found in storage',
                    'code' => '404',
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Error downloading book: ' . $e->getMessage());
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Failed to generate download link',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's purchased books.
     */
    public function myPurchases(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            Log::info('BookController: myPurchases called', [
                'user_id' => $userId,
                'request_user_id' => $request->input('user_id'),
            ]);

            if (!$userId) {
                Log::warning('BookController: myPurchases - User ID missing');
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'User ID is required',
                    'code' => '400',
                ], 400);
            }

            // Get all purchases for the user with status completed
            $purchases = BookPurchase::where('user_id', $userId)
                ->where('status', 'completed')
                ->with(['book.authorModel', 'payment'])
                ->orderBy('purchased_at', 'desc')
                ->get();

            Log::info('BookController: myPurchases - Found purchases', [
                'user_id' => $userId,
                'count' => $purchases->count(),
            ]);

            // Filter out purchases where book is missing (soft delete or orphaned)
            $purchases = $purchases->filter(function($purchase) {
                return $purchase->book !== null;
            });

            $purchases = $purchases->map(function($purchase) {
                return [
                    'id' => $purchase->id,
                    'book' => $purchase->book,
                    'purchased_at' => $purchase->purchased_at,
                    'purchase_type' => $purchase->purchase_type,
                    'download_count' => $purchase->download_count ?? 0,
                    'max_downloads' => $purchase->max_downloads ?? 0,
                    'remaining_downloads' => $purchase->getRemainingDownloads(),
                    'can_download' => $purchase->canDownload(),
                    'last_downloaded_at' => $purchase->last_downloaded_at,
                    'payment' => $purchase->payment,
                ];
            });

            Log::info('BookController: myPurchases - Returning purchases', [
                'user_id' => $userId,
                'final_count' => $purchases->count(),
            ]);

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Purchased books retrieved successfully',
                'data' => $purchases->values()->all(), // Reset array keys
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching user purchases: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->input('user_id'),
            ]);
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Failed to retrieve purchased books',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user has access to a book.
     */
    public function checkAccess($id, Request $request)
    {
        try {
            $book = Book::findOrFail($id);
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'status' => 'SUCCESS',
                    'has_access' => false,
                    'message' => 'User not authenticated',
                ], 200);
            }

            $purchase = $book->getUserPurchase($userId);
            $hasAccess = $purchase ? true : false;

            return response()->json([
                'status' => 'SUCCESS',
                'has_access' => $hasAccess,
                'message' => $hasAccess ? 'User has access' : 'User does not have access',
                'data' => $purchase ? [
                    'purchase_id' => $purchase->id,
                    'download_count' => $purchase->download_count,
                    'max_downloads' => $purchase->max_downloads,
                    'remaining_downloads' => $purchase->getRemainingDownloads(),
                    'can_download' => $purchase->canDownload(),
                ] : null,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error checking access: ' . $e->getMessage());
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Failed to check access',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

