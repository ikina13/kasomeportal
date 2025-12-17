<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\practical_video_model as Course;
use App\Models\practical_video_clips_model as Video;
use App\Models\app_user as AppUser;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\subject_model  as Subject;
use App\Models\payments_model as Payments;
use App\Services\DpoService;
use Carbon\Carbon;

class CourseController extends Controller
{
    public function getAllCourses(Request $request)
    { 

        $data = Course::with(['PracticleVideoClips' => function ($query) {
                  $query->orderBy('id', 'asc'); 
              }])->get();

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Courses retrieved successfully',
            'data' => $data,
        ], 200);
    }

    public function getPortalCoursesById($id)
    { 
        //$request->input('token');
        // Extract data from the JSON request body
        $userId = request()->input('user_id');
        $data = Course::with(['PracticleVideoClips', 'payment' => function ($query) use ($userId) {
        $query->where('user_id', $userId)->first();
               }])->where('id', $id)->orderBy('id', 'asc')->get();

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Courses videos retrieved successfully',
            'data' => $data,
        ], 200);
    }

    public function getCoursesById($id)
    {
        // Extract data from the JSON request body
        $userId = request()->input('user_id');
        $user = AppUser::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'User not found',
            ], 404);
        }

        // Verify pending payments
        $payment = Payments::where('user_id', $userId)
            ->where('video_id', $id)
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();

        if($payment){
             $dpoService = new DpoService();
             $isVerified = $dpoService->verifyToken($payment->transactiontoken);

            if($isVerified){
                 $payment->status = 'settled';
                 $payment->save();
               }
          }

        // Get course data
        $course = Course::with(['PracticleVideoClips', 'payment' => function ($query) use ($userId) {
        $query->where('user_id', $userId)->latest('created_at')->first();
        }])->where('id', $id)->first();

        if (!$course) {
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Course not found',
            ], 404);
        }

        // Check access via all subscription types
        $hasAccess = $user->hasCourseAccess($id);

        // Determine access type
        $accessInfo = [
            'has_access' => $hasAccess,
            'access_type' => null, // 'free', 'payment', 'class_subscription', 'all_courses', 'specific_courses'
            'subscription_details' => null,
        ];

        if ($hasAccess) {
            // Check if course is free
            if ($course->price == null || $course->price == 0) {
                $accessInfo['access_type'] = 'free';
            }
            // Check individual payment
            elseif ($user->payments()->where('video_id', $id)->where('status', 'settled')->exists()) {
                $accessInfo['access_type'] = 'payment';
            }
            // Check class subscription
            elseif ($course->class_id && $user->hasActiveClassSubscription($course->class_id)) {
                $accessInfo['access_type'] = 'class_subscription';
                $classSub = $user->getActiveClassSubscription($course->class_id);
                $accessInfo['subscription_details'] = [
                    'expires_at' => $classSub->end_date,
                ];
            }
            // Check general subscription
            else {
                $activeSubscription = $user->subscriptions()
                    ->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
                
                if ($activeSubscription) {
                    $accessInfo['access_type'] = $activeSubscription->subscription_type;
                    $accessInfo['subscription_details'] = [
                        'expires_at' => $activeSubscription->end_date,
                        'subscription_id' => $activeSubscription->id,
                    ];
                }
            }
        }

        // Convert single course to array for consistency with existing API
        $data = collect([$course]);

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Courses videos retrieved successfully',
            'data' => $data,
            'access' => $accessInfo,
        ], 200);
    }



    public function getCourseSubjects($id)
    { 
        //$request->input('token');
        // Extract data from the JSON request body
        $userId = request()->input('user_id');
        $data = Course::where("suject_id",$id)->get();
        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Subject videos retrieved successfully',
            'data' => $data,
        ], 200);
    }

   
   public function getSubjectsById($id)
    { 
        //$request->input('token');
        // Extract data from the JSON request body
        $userId = request()->input('user_id');
        $data = Course::with('subject')->where('class_id',$id)->distinct('subject_id')->get();

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Courses videos retrieved successfully',
            'data' => $data,
        ], 200);
    }



  public function getSubjectsByClassId($subjectId,$id)
    { 
        //$request->input('token');
        // Extract data from the JSON request body
        //$subject_id = request()->input('subject_id');
        $data = Course::where(['class_id'=>$id,'subject_id'=>$subjectId])->get();

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Courses videos retrieved successfully',
            'data' => $data,
        ], 200);
    }


   public function getCourseSubjectsVideos($id)
    { 
        //$request->input('token');
        // Extract data from the JSON request body
        $userId = request()->input('user_id');
        $data = Subject::where("video_id",$id)->get();
        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Subject videos retrieved successfully',
            'data' => $data,
        ], 200);
    }
 
    public function getVideoByToken($id)
    { 
         //$token=$request->input('token');
        // Extract data from the JSON request body
        $videoId = Payments::where(['transactiontoken'=>$id,'status'=>'settled'])->value('video_id');
       
        $clip = Video::where(["practical_video_id"=>$videoId])
         ->orderBy('id', 'asc') // Assuming 'id' is the primary key column
         ->skip(2) // Skip the first two records
         ->take(1) // Take only one record (the third one)
         ->first();

         // var_dump($token);
       
        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Video retrieved successfully',
            'video_id' => $clip->video_id,
            'playbackInfo' => $clip->playbackInfo,
            'otp' => $clip->otp,
        ], 200);
    }

    public function getVideoById($id)
    { 

        // Extract data from the JSON request body
           $data = Video::with('practical_video')->where('id',$id)->orderBy('id', 'asc')->get();

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Video retrieved successfully',
            'data' => $data,
        ], 200);
    }

   
    public function paymentSuccess(Request $request)
    { 
       $transactiontoken = request()->input('TransactionToken');
       $pnrid = request()->input('PnrID');
       $ccdapproval = request()->input('CCDapproval');
       $transid = request()->input('TransID');
       
       $userId = Payments::where("transactiontoken",$transactiontoken)->value('user_id');

       $update_payments = Payments::where("transactiontoken",$transactiontoken)->update([
                                 'pnrid'=>  $pnrid,
                                 'status'=> 'settled',
                                 'ccdapproval'=> $ccdapproval,
                                 'transid'=>  $transid,
                                 'updated_at'=> date('Y-m-d H:i:s'),
                                 'updated_by' => $userId,
                                 'expired_date' => Carbon::now()->addDays(30) // 📆 Adds 30 days to the current date
                                ]);       
       if(!$update_payments)
          return ['status'=> 'FAILED','code' => '200','data' => $userId,'message'=>"Payment failed to be updated"]; 
       

          return ['status'=> 'SUCCESS','code' => '200','data' =>$update_payments,'message'=>"Payment updated successfully"]; 
       
    }
   
    public function createPaymentToken(Request $request)
    { 

     // dd(Auth::User());
        $CompanyToken = "55B69320-7B2D-451F-9846-4790DA901616";
        $Request = "createToken";
        $request_from_portal = filter_var($request->input('request_from_portal'), FILTER_VALIDATE_BOOLEAN);
        $userId = request()->input('user_id');
        $userData = AppUser::find($userId);
        $fullName = $userData->name;
        $names = explode(' ', $fullName);

        // Now $names is an array containing first and last names
        $firstName = isset($names[0]) ? $names[0] : '';
        $lastName = isset($names[1]) ? $names[1] : '';

      
        $PaymentAmount =  $request->input('amount');
        $video_id =  $request->input('video_id');
        $PaymentCurrency = "TZS";
        $CompanyRef = "49FKEOA";

        //The redirect and back URL

        // --- CONDITIONAL REDIRECT URLS ---
        if ($request_from_portal) {
            $RedirectURL = "https://kasome.com/payment-status"; // Next.js success page
            $BackURL = "https://kasome.com/payment-failure"; // Next.js API route for server-to-server callback
             
        } else {
            $RedirectURL = "https://portal.kasome.com/payurl.php"; // Old PHP success page
            $BackURL = "https://portal.kasome.com/backurl.php"; // Old PHP callback page
        }
        // --- END CONDITIONAL REDIRECT URLS ---
        // $RedirectURL = "http://45.79.205.240/payurl.php";
        // $BackURL = "http://45.79.205.240/backurl.php";

        //$request->input('password');

        $CompanyRefUnique = "0";
        $PTL = "96";
        $PTLtype = "hours";
        $ServiceType = "29617";
        $ServiceDescription = "Videos";
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
            return -1;
        } else {
            //return the token
            $xml = simplexml_load_string($Response);
            $token= json_encode($xml,JSON_UNESCAPED_SLASHES);

            $TransToken = $xml->xpath('//API3G/TransToken')[0];
            $data = json_decode($token, true);
          
            $payment = Payments::create([
                                 'amount'=>  $PaymentAmount,
                                 'status'=> 'pending',
                                 'transactiontoken'=> $data['TransToken'],
                                 'transRef'=>  $data['TransRef'],
                                 'user_id'=> $userId,
                                 'video_id'=>$video_id,
                                 'created_at'=> date('Y-m-d H:i:s'),
                                 'created_by' => $userId,
                                 'updated_at'=> date('Y-m-d H:i:s'),
                                 'updated_by' => $userId
                                ]);
            
            return ['status'=> 'SUCCESS','code' => '200','token' =>$data['TransToken'],'message'=>"Token set successfully"];
        }     
    }


     public function getPayments(Request $request){
     $userId = request()->input('user_id');

     $payments = Payments::with(['video','users'])->get();
     return ['status'=> 'SUCCESS','code' => '200','data' =>$payments,'message'=>"User updated successfully"];
   }       
}
 
