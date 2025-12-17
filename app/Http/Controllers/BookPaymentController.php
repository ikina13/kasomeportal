<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookPurchase;
use App\Models\BookPayment;
use App\Models\app_user;
use App\Services\DpoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookPaymentController extends Controller
{
    protected $dpoService;

    public function __construct(DpoService $dpoService)
    {
        $this->dpoService = $dpoService;
    }

    /**
     * Create payment token for book purchase.
     */
    public function createPaymentToken(Request $request, $bookId)
    {
        try {
            $book = Book::findOrFail($bookId);
            $userId = $request->input('user_id');
            $purchaseType = $request->input('purchase_type', 'purchase'); // 'purchase' or 'donation'
            $requestFromPortal = filter_var($request->input('request_from_portal', false), FILTER_VALIDATE_BOOLEAN);

            if (!$userId) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'User ID is required',
                    'code' => '400',
                ], 400);
            }

            $user = app_user::findOrFail($userId);

            // Determine amount
            $amount = $book->price;
            if ($purchaseType === 'donation') {
                $donationAmount = $request->input('donation_amount');
                if ($donationAmount && $donationAmount >= ($book->donation_min_amount ?? 0)) {
                    $amount = $donationAmount;
                } elseif ($book->donation_min_amount > 0) {
                    $amount = $book->donation_min_amount;
                }
            }

            // Check if user already purchased
            $existingPurchase = BookPurchase::where('user_id', $userId)
                ->where('book_id', $bookId)
                ->where('status', 'completed')
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'You have already purchased this book',
                    'code' => '400',
                    'data' => [
                        'purchase_id' => $existingPurchase->id,
                        'existing_purchase' => true,
                    ],
                ], 400);
            }

            // Create payment record
            $payment = BookPayment::create([
                'user_id' => $userId,
                'book_id' => $bookId,
                'amount' => $amount,
                'status' => 'pending',
                'payment_type' => 'book',
                'donation_type' => $purchaseType === 'donation' ? 'book' : null,
                'donation_title' => $purchaseType === 'donation' ? $book->title : null,
                'is_anonymous' => $request->input('is_anonymous', false),
                'donor_name' => $request->input('donor_name'),
                'donor_message' => $request->input('donor_message'),
                'created_by' => $userId,
            ]);

            // Prepare DPO payment request
            $companyToken = "55B69320-7B2D-451F-9846-4790DA901616";
            $fullName = $user->name;
            $names = explode(' ', $fullName);
            $firstName = $names[0] ?? '';
            $lastName = isset($names[1]) ? $names[1] : '';

            // Redirect URLs
            if ($requestFromPortal) {
                $redirectURL = "https://kasome.com/payment-status";
                $backURL = "https://kasome.com/api/dpo-callback";
            } else {
                $redirectURL = "https://portal.kasome.com/payurl.php";
                $backURL = "https://portal.kasome.com/backurl.php";
            }

            $xmlData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n
            <API3G>\r\n
                <CompanyToken>" . $companyToken . "</CompanyToken>\r\n
                <Request>createToken</Request>\r\n
                <Transaction>\r\n
                    <PaymentAmount>" . $amount . "</PaymentAmount>\r\n
                    <PaymentCurrency>TZS</PaymentCurrency>\r\n
                    <CompanyRef>BOOK-" . $bookId . "-" . $userId . "</CompanyRef>\r\n
                    <RedirectURL>" . $redirectURL . "</RedirectURL>\r\n
                    <BackURL>" . $backURL . "</BackURL>\r\n
                    <CompanyRefUnique>0</CompanyRefUnique>\r\n
                    <PTL>96</PTL>\r\n
                    <PTLtype>hours</PTLtype>\r\n
                    <customerFirstName>" . $firstName . "</customerFirstName>\r\n
                    <customerLastName>" . $lastName . "</customerLastName>\r\n
                    <customerPhone>" . $user->phone . "</customerPhone>\r\n
                    <customerZip>255</customerZip>\r\n
                    <customerCity>" . ($user->region ?? 'Dar es Salaam') . "</customerCity>\r\n
                    <customerAddress>" . ($user->district ?? '') . "</customerAddress>\r\n
                    <DefaultPayment>MO</DefaultPayment>\r\n
                    <customerCountry>TZ</customerCountry>\r\n
                    <customerEmail>" . ($user->email ?? '') . "</customerEmail>\r\n
                    <FraudTimeLimit>60</FraudTimeLimit>\r\n
                </Transaction>\r\n
                <Services>\r\n
                    <Service>\r\n
                        <ServiceType>29617</ServiceType>\r\n
                        <ServiceDescription>Book: " . $book->title . "</ServiceDescription>\r\n
                        <ServiceDate>" . date("Y/m/d H:i") . "</ServiceDate>\r\n
                    </Service>\r\n
                </Services>\r\n
            </API3G>";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://secure.3gdirectpay.com/API/v6/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $xmlData,
                CURLOPT_HTTPHEADER => array(
                    "Cache-Control: no-cache",
                    "Content-Type: application/xml"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                Log::error('DPO cURL Error: ' . $err);
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Payment gateway error',
                    'error' => $err,
                ], 500);
            }

            // Parse XML response
            $xmlResponse = simplexml_load_string($response);
            $result = (string) $xmlResponse->Result;
            $resultExplanation = (string) $xmlResponse->ResultExplanation;
            $transactionToken = (string) $xmlResponse->TransToken ?? '';

            if ($result === '000' && $transactionToken) {
                // Update payment with transaction token
                $payment->update([
                    'transactiontoken' => $transactionToken,
                    'transref' => 'BOOK-' . $bookId . '-' . $userId,
                ]);

                // Build payment URL
                $paymentUrl = "https://secure.3gdirectpay.com/payv2.php?ID=" . $transactionToken;

                return response()->json([
                    'status' => 'SUCCESS',
                    'message' => 'Payment token created successfully',
                    'data' => [
                        'token' => $transactionToken,
                        'payment_url' => $paymentUrl,
                        'payment_id' => $payment->id,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Failed to create payment token: ' . $resultExplanation,
                    'code' => $result,
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error creating payment token: ' . $e->getMessage());
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Failed to create payment token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle DPO payment callback.
     */
    public function paymentCallback(Request $request)
    {
        try {
            $transactionToken = $request->input('TransactionToken');
            $pnrid = $request->input('PnrID');
            $ccdapproval = $request->input('CCDapproval');
            $transid = $request->input('TransID');

            if (!$transactionToken) {
                Log::error('Book payment callback: Missing TransactionToken');
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Missing transaction token',
                ], 400);
            }

            // Find payment by transaction token
            $payment = BookPayment::where('transactiontoken', $transactionToken)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                Log::error('Book payment callback: Payment not found for token ' . $transactionToken);
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Payment not found',
                ], 404);
            }

            // Verify payment with DPO
            $isPaid = $this->dpoService->verifyToken($transactionToken);

            if ($isPaid) {
                DB::beginTransaction();

                try {
                    // Update payment status
                    $payment->update([
                        'status' => 'settled',
                        'pnrid' => $pnrid,
                        'ccdapproval' => $ccdapproval,
                        'transid' => $transid,
                        'updated_at' => Carbon::now(),
                        'updated_by' => $payment->user_id,
                    ]);

                    // Create or update book purchase
                    $purchase = BookPurchase::firstOrCreate(
                        [
                            'user_id' => $payment->user_id,
                            'book_id' => $payment->book_id,
                            'status' => 'completed',
                        ],
                        [
                            'payment_id' => $payment->id,
                            'purchase_type' => $payment->donation_type ? 'donation' : 'purchase',
                            'delivery_method' => 'digital',
                            'download_count' => 0,
                            'max_downloads' => 5, // Default max downloads
                            'purchased_at' => Carbon::now(),
                            'created_by' => $payment->user_id,
                        ]
                    );

                    // Generate download token
                    $purchase->generateDownloadToken(24); // 24 hours expiry

                    // Link payment to purchase
                    $payment->update([
                        'book_purchase_id' => $purchase->id,
                    ]);

                    DB::commit();

                    Log::info('Book payment successful: Payment ID ' . $payment->id . ', Purchase ID ' . $purchase->id);

                    return response()->json([
                        'status' => 'SUCCESS',
                        'message' => 'Payment processed successfully',
                        'data' => [
                            'payment_id' => $payment->id,
                            'purchase_id' => $purchase->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }

            } else {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'updated_at' => Carbon::now(),
                ]);

                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Payment verification failed',
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Book payment callback error: ' . $e->getMessage());
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Error processing payment callback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check payment status.
     */
    public function paymentStatus($token)
    {
        try {
            $payment = BookPayment::where('transactiontoken', $token)->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Payment not found',
                ], 404);
            }

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Payment status retrieved',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'book_id' => $payment->book_id,
                    'book' => $payment->book,
                    'purchase_id' => $payment->book_purchase_id,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Failed to check payment status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

