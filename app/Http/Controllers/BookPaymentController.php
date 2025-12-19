<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookPurchase;
use App\Models\BookPayment;
use App\Models\app_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BookPaymentController extends Controller
{
    /**
     * Create payment token for book purchase/donation.
     * This follows the exact same pattern as CourseController::createPaymentToken
     */
    public function createPaymentToken(Request $request, $bookId)
    {
        $CompanyToken = "55B69320-7B2D-451F-9846-4790DA901616";
        $Request = "createToken";
        $request_from_portal = filter_var($request->input('request_from_portal'), FILTER_VALIDATE_BOOLEAN);
        $userId = request()->input('user_id');
        $purchaseType = $request->input('purchase_type', 'purchase'); // 'purchase' or 'donation'
        
        $book = Book::findOrFail($bookId);
        $userData = app_user::findOrFail($userId);
        
        $fullName = $userData->name;
        $names = explode(' ', $fullName);
        $firstName = isset($names[0]) ? $names[0] : '';
        $lastName = isset($names[1]) ? $names[1] : '';

        // Determine amount
        $PaymentAmount = $book->price;
        if ($purchaseType === 'donation') {
            $donationAmount = $request->input('donation_amount');
            if ($donationAmount && $donationAmount >= ($book->donation_min_amount ?? 0)) {
                $PaymentAmount = $donationAmount;
            } elseif ($book->donation_min_amount > 0) {
                $PaymentAmount = $book->donation_min_amount;
            }
        }

        // Check if user already purchased this book
        $existingPurchase = BookPurchase::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->where('status', 'completed')
            ->first();

        if ($existingPurchase) {
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'You have already purchased this book',
                'code' => '400',
            ], 400);
        }

        $PaymentCurrency = "TZS";
        $CompanyRef = "BOOK-" . $bookId . "-" . $userId;

        // The redirect and back URL
        // --- CONDITIONAL REDIRECT URLS ---
        if ($request_from_portal) {
            $paymentTypeParam = $purchaseType === 'donation' ? 'type=book&donation=true' : 'type=book';
            $RedirectURL = "https://kasome.com/payment-status?" . $paymentTypeParam;
            $BackURL = "https://kasome.com/payment-status?" . $paymentTypeParam;
        } else {
            $RedirectURL = "https://portal.kasome.com/payurl.php";
            $BackURL = "https://portal.kasome.com/backurl.php";
        }
        // --- END CONDITIONAL REDIRECT URLS ---

        $CompanyRefUnique = "0";
        $PTL = "96";
        $PTLtype = "hours";
        $ServiceType = "29617";
        $ServiceDescription = $purchaseType === 'donation' ? "Book Donation: " . $book->title : "Book: " . $book->title;
        $FraudTimeLimit = "60";
        $ServiceDate = date("Y/m/d H:i");
        $DefaultPayment = "MO";
        $customerFirstName = $firstName;
        $customerLastName = $lastName;
        $customerPhone = $userData->phone;
        $customerEmail = $userData->email;
        $customerCity = $userData->region;
        $customerAddress = $userData->district;
        $customerCountry = "TZ";
        $customerZip = "255";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://secure.3gdirectpay.com/API/v6/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n
            <API3G>\r\n
                <CompanyToken>" . $CompanyToken . "</CompanyToken>\r\n
                <Request>" . $Request . "</Request>\r\n
                <Transaction>\r\n
                    <PaymentAmount>" . $PaymentAmount . "</PaymentAmount>\r\n
                    <PaymentCurrency>" . $PaymentCurrency . "</PaymentCurrency>\r\n
                    <CompanyRef>" . $CompanyRef . "</CompanyRef>\r\n
                    <RedirectURL>" . $RedirectURL . "</RedirectURL>\r\n
                    <BackURL>" . $BackURL . "</BackURL>\r\n
                    <CompanyRefUnique>" . $CompanyRefUnique . "</CompanyRefUnique>\r\n
                    <PTL>" . $PTL . "</PTL>\r\n
                    <PTLtype>" . $PTLtype . "</PTLtype>\r\n
                    <customerFirstName>" . $customerFirstName . "</customerFirstName>
                    <customerLastName>" . $customerLastName . "</customerLastName>
                    <customerPhone>" . $customerPhone . "</customerPhone>
                    <customerZip>" . $customerZip . "</customerZip>
                    <customerCity>" . $customerCity . "</customerCity>
                    <customerAddress>" . $customerAddress . "</customerAddress>
                    <DefaultPayment>" . $DefaultPayment . "</DefaultPayment>
                    <customerCountry>" . $customerCountry . "</customerCountry>
                    <customerEmail>" . $customerEmail . "</customerEmail>\r\n
                    <FraudTimeLimit>" . $FraudTimeLimit . "</FraudTimeLimit>\r\n
                </Transaction>\r\n
                <Services>\r\n
                    <Service>\r\n
                        <ServiceType>" . $ServiceType . "</ServiceType>\r\n
                        <ServiceDescription>" . $ServiceDescription . "</ServiceDescription>\r\n
                        <ServiceDate>" . $ServiceDate . "</ServiceDate>\r\n
                    </Service>\r\n
                </Services>\r\n
            </API3G>",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/xml"
            ),
        ));

        $Response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Payment gateway connection error',
                'code' => '500',
            ], 500);
        } else {
            // return the token (same as CourseController)
            $xml = simplexml_load_string($Response);
            $token = json_encode($xml, JSON_UNESCAPED_SLASHES);
            $TransToken = $xml->xpath('//API3G/TransToken')[0];
            $data = json_decode($token, true);

            // Determine payment_type for database
            $paymentType = $purchaseType === 'donation' ? 'donation' : 'book_purchase';

            // Create payment record (after getting token, like CourseController)
            $payment = BookPayment::create([
                'amount' => $PaymentAmount,
                'status' => 'pending',
                'transactiontoken' => $data['TransToken'],
                'transref' => $data['TransRef'] ?? $CompanyRef,
                'user_id' => $userId,
                'book_id' => $bookId,
                'payment_type' => $paymentType,
                'donation_type' => $purchaseType === 'donation' ? 'book' : null,
                'donation_title' => $purchaseType === 'donation' ? $book->title : null,
                'is_anonymous' => $request->input('is_anonymous', false),
                'donor_name' => $request->input('donor_name'),
                'donor_message' => $request->input('donor_message'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId
            ]);

            // Return same format as CourseController
            return response()->json([
                'status' => 'SUCCESS',
                'code' => '200',
                'token' => $data['TransToken'],
                'message' => 'Token set successfully'
            ]);
        }
    }

    /**
     * Handle DPO payment callback
     */
    public function paymentCallback(Request $request)
    {
        try {
            $transactiontoken = request()->input('TransactionToken');
            $pnrid = request()->input('PnrID');
            $ccdapproval = request()->input('CCDapproval');
            $transid = request()->input('TransID');

            Log::info('BookPaymentController: paymentCallback called', [
                'transactiontoken' => $transactiontoken,
                'pnrid' => $pnrid,
                'transid' => $transid,
            ]);

            if (!$transactiontoken) {
                Log::error('BookPaymentController: Missing TransactionToken');
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'TransactionToken is required',
                    'code' => '400',
                ], 400);
            }

            $payment = BookPayment::where("transactiontoken", $transactiontoken)->first();

            if (!$payment) {
                Log::error('BookPaymentController: Payment not found', [
                    'transactiontoken' => $transactiontoken,
                ]);
                return response()->json([
                    'status' => 'FAILURE',
                    'message' => 'Payment not found',
                    'code' => '404',
                ], 404);
            }

            // Update payment record (same pattern as CourseController)
            // Only update if payment is still pending (to avoid duplicate updates)
            if ($payment->status === 'pending') {
                $update_payments = BookPayment::where("transactiontoken", $transactiontoken)
                    ->where('status', 'pending')
                    ->update([
                        'pnrid' => $pnrid,
                        'status' => 'settled',
                        'ccdapproval' => $ccdapproval,
                        'transid' => $transid,
                        'updated_at' => Carbon::now(),
                        'updated_by' => $payment->user_id,
                    ]);

                if ($update_payments === false) {
                    Log::error('BookPaymentController: Database error updating payment', [
                        'payment_id' => $payment->id,
                        'transactiontoken' => $transactiontoken,
                    ]);
                    return response()->json([
                        'status' => 'FAILED',
                        'code' => '200',
                        'data' => $payment->user_id,
                        'message' => "Payment failed to be updated"
                    ], 200);
                }
            } else {
                // Payment already settled - log and continue
                Log::info('BookPaymentController: Payment already settled', [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                ]);
            }
            
            // Refresh payment model to get updated values
            $payment->refresh();

            // Create or update BookPurchase record
            try {
                $purchaseData = [
                    'status' => 'completed',
                    'purchased_at' => Carbon::now(),
                    'created_by' => $payment->user_id,
                    'updated_by' => $payment->user_id,
                ];
                
                // Only add purchase_type if column exists
                if (Schema::hasColumn('tbl_book_purchases', 'purchase_type') && $payment->payment_type) {
                    $purchaseData['purchase_type'] = $payment->payment_type === 'donation' ? 'donation' : 'purchase';
                }
                
                // Add payment_id if column exists
                if (Schema::hasColumn('tbl_book_purchases', 'payment_id')) {
                    $purchaseData['payment_id'] = $payment->id;
                }
                
                $purchase = BookPurchase::updateOrCreate(
                    [
                        'user_id' => $payment->user_id,
                        'book_id' => $payment->book_id,
                    ],
                    $purchaseData
                );

                // Link payment to purchase if book_purchase_id column exists
                if (Schema::hasColumn('tbl_books_payment', 'book_purchase_id') && !$payment->book_purchase_id) {
                    $payment->book_purchase_id = $purchase->id;
                    $payment->save();
                }

                Log::info('BookPaymentController: Payment updated successfully', [
                    'payment_id' => $payment->id,
                    'purchase_id' => $purchase->id,
                ]);
            } catch (\Exception $e) {
                Log::error('BookPaymentController: Error creating purchase', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continue even if purchase creation fails - payment is still updated
            }

            return response()->json([
                'status' => 'SUCCESS',
                'code' => '200',
                'data' => 1,
                'message' => "Payment updated successfully"
            ]);
        } catch (\Exception $e) {
            Log::error('BookPaymentController: Exception in paymentCallback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Error processing payment callback: ' . $e->getMessage(),
                'code' => '500',
            ], 500);
        }
    }

    /**
     * Check payment status by token
     */
    public function paymentStatus($token)
    {
        $payment = BookPayment::where('transactiontoken', $token)->first();

        if (!$payment) {
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Payment not found',
                'code' => '404',
            ], 404);
        }

        return response()->json([
            'status' => 'SUCCESS',
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'book_id' => $payment->book_id,
            ],
            'message' => 'Payment status retrieved successfully'
        ]);
    }
}
