<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\app_user as AppUser;

use App\Models\practical_video_model as Video;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use App\Models\clip_comments_model as ClipComments;

use App\Models\clip_comments_replies_model as ClipCommentsReplies;

use App\Models\clip_views_model as ClipViews;

use App\Models\UserSession; 

class UserController extends Controller
{
    public function login(Request $request) {

    $validator = Validator::make($request->all(), [
        'phone' => 'required',
        'password' => 'required'
    ], [
        'phone.required' => 'Phone number is required.',
        'password.required' => 'Password is required.'
    ]);

    if ($validator->fails()) {
        return [
            'status' => 'FAILURE',
            'code' => '400',
            'message' => $validator->errors()->first()
        ];
    }

    $user_exist = AppUser::where('phone', self::getformatted($request->input("phone")))->first();

    if (!$user_exist) {
        return ['status' => 'FAILURE', 'code' => '400', 'message' => "Invalid phone number or password"];
    }

    if ($user_exist->status == 'in-active') {
        return ['status' => 'FAILURE', 'code' => '400', 'message' => "Your account is not activated"];
    }

    if (!password_verify($request->input('password'), $user_exist->password)) {
        return ['status' => 'FAILURE', 'code' => '400', 'message' => "Invalid phone number or password"];
    }


    // --- NEW SESSION MANAGEMENT LOGIC ---

    // Check if the user is a school account
    if ($user_exist->user_type === 'school') {
        // Count existing sessions for this user
        $sessionCount = UserSession::where('user_id', $user_exist->id)->count();

        // If the user has 3 or more sessions, delete the oldest one (FIFO)
        if ($sessionCount >= 3) {
            UserSession::where('user_id', $user_exist->id)
                ->orderBy('created_at', 'asc')
                ->first()
                ->delete();
        }
    }

    // --- END OF NEW LOGIC ---


    // Create the new authentication token
    $token = $user_exist->createToken('authToken')->plainTextToken;

    // Log the new session in the tbl_user_sessions table
    UserSession::create([
        'user_id' => $user_exist->id,
        'token'   =>  $token, // Store a hash of the token for security
    ]);
    
    return ['status' => 'SUCCESS', 'code' => '200', 'data' => $user_exist, 'token' => $token, 'message' => "User login successfully"];
}

    public function register(Request $request){
        
      
        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                'unique:tbl_users',
                'regex:/^[0-9+]+$/',
                'min:10',
                'max:13',
            ]
          
        ], [
            'phone.unique' => 'The phone number already exists.',
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number should contain only numbers and an optional + sign.',
            'phone.min' => 'The phone number should be at least 10 characters long.',
            'phone.max' => 'The phone number should not exceed 13 characters.',
           
        ]);

       $existing_phone = AppUser::where('phone',self::getformatted($request->input('phone','')))->first();
       if( $existing_phone)
            return ['status' => 'FAILURE','code' => '400','message' => "Phone number already exist"];
        
        
        if ($validator->fails()) {
            return [
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->first()
            ];
        }

        $user = AppUser::create([
            "name"=>$request->input('fname','').' '.$request->input('lname',''),
            "phone"=> self::getformatted($request->input('phone','')),
            "email"=> $request->input('email',''),
            "password"=> bcrypt($request->input('password','')),
            "region"=> $request->input('region',''),
            "user_type"=> $request->input('user_type','student'),
            "district"=> $request->input('district',''),
            "created_at"=> date('Y-m-d'),
            "updated_at"=> date('Y-m-d'),
            "sex"=> $request->input('gender',''),
            "dob"=> $request->input('dob',''),
            "reset_password_token"=> 0,
            "activate_user_token"=> self::generateUniqueNumericOTP(),
        ]);

        if($user){
           $token = $user->createToken('authToken')->plainTextToken;
           $message = 'You have successfully registered in KASOME app .Please enter this token  '.$user->activate_user_token.'  to activate account';
           self::send($message,$user->phone);
        }
        return ['status'=> 'SUCCESS','code' => '200','data' =>$user,'token'=>$token,'message'=>"User created successfully"];

    }


    public function activateAccount(Request $request){
  
        $validator = Validator::make($request->all(), [
            'activate_user_token' => 'required',
        ], [
            'activate_user_token.required' => 'Activation code is required.',
             
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->first()
            ];
        }

        $code_exist = AppUser::where('activate_user_token',$request->input('activate_user_token'))->first();

        if(!$code_exist)
           return ['status' => 'FAILURE','code' => '400','message' => "Invalid activation code"];

        if($code_exist) {
            $code_exist->status = 'active';
            $code_exist->save();
        }

        return ['status'=> 'SUCCESS','code' => '200','data' =>$code_exist,'message'=>"User activated successfully"];
         
    }



    public function forgetPassword(Request $request){
       $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                'unique:tbl_users',
                'regex:/^[0-9+]+$/',
                'min:10',
                'max:13',
            ]
           
        ], [
            'phone.unique' => 'The phone number already exists.',
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number should contain only numbers and an optional + sign.',
            'phone.min' => 'The phone number should be at least 10 characters long.',
            'phone.max' => 'The phone number should not exceed 13 characters.'
           
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->first()
            ];
        }
       $user_exist = AppUser::where('phone',self::getformatted($request->input('phone')))->first();
                   
       
       if(!$user_exist)
          return ['status'=> 'FAILURE','code' => '400','message'=>"User with a phone number not found"];

       $code = self::generatePasswordCode();
       $user = AppUser::where('phone',self::getformatted($request->input('phone')))->update(["reset_password_token"=>$code]);
       $message = "Enter ".$code." to reset password";
       if($user)
            self::send($message,self::getformatted($request->input('phone')));
    
        return ['status'=> 'SUCCESS','password_token'=>$code,'code' => '200','data' =>$user,'message'=>"Code successfully sent"];
        
    }


    public function resetPassword(Request $request){
      

        $validator = Validator::make($request->all(), [
            'reset_password_token' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ], [
            'reset_password_token.required' => 'Reset code is required.',
            'confirm_password.required' => 'Confirm Password is required.',
            'password.required' => 'Password is required.',
            'confirm_password.same' => 'Passwords must match.',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->all()
            ];
        }

        $code_exist = AppUser::where('reset_password_token',$request->input('reset_password_token'))->first();

        if(!$code_exist)
           return ['status' => 'FAILURE','code' => '400','message' => "Invalid reset code"];

        if($code_exist) {
            $code_exist->password = bcrypt($request->input('password'));
            $code_exist->save();
        }

        return ['status'=> 'SUCCESS','code' => '200','data' =>$code_exist,'message'=>"Password reset successfully"];
        
    }


    public function logout(Request $request){

         // Revoke the current user's token(s)
         $request->user()->tokens()->delete();

         return ['status'=> 'SUCCESS','code' => '200','message'=>"User logged out successfully"];
        
    }

    /**
     * Check for the phone and format
     *
     * @param string $phone
     * @return string $formatted
     */
    public static function getformatted(string $phone): string
    {
        $initial = substr($phone, 0, 1);
        switch ($initial) {
            case '+':
                return $phone;

            case '2':
                return '+' . $phone;

            case '0':
                return "+255" . substr($phone, 1);

            default:
                return "+255" . $phone;
        }
    }

    /**
     * Check for the phone and format
     *
     * @param string $phone
     * @return string $formatted
     */
    public static function getformattedBeem(string $phone): string
    {
        $initial = substr($phone, 0, 1);
        switch ($initial) {
            case '+':
                return  substr($phone, 1);
 
            default:
                return "+255" . $phone;
        }
    }


      /**
     * Generate a unique numeric OTP with a specified length.
     *
     * @param int $length The length of the OTP.
     * @return string
     */
    public static function generateUniqueNumericOTP($length = 6)
    {
        $otp = '';

        // Generate a random numeric OTP with the specified length
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9); // Append a random digit (0-9)
        }

        // Check if the OTP already exists in the database
        while (AppUser::where('activate_user_token', $otp)->exists()) {
            // Generate a new OTP if it exists
            $otp = '';
            for ($i = 0; $i < $length; $i++) {
                $otp .= rand(0, 9);
            }
        }

        return $otp;
    }

       /**
     * Generate a unique numeric OTP with a specified length.
     *
     * @param int $length The length of the OTP.
     * @return string
     */
    public static function generatePasswordCode($length = 6)
    {
        $otp = '';

        // Generate a random numeric OTP with the specified length
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9); // Append a random digit (0-9)
        }

        // Check if the OTP already exists in the database
        while (AppUser::where('reset_password_token', $otp)->exists()) {
            // Generate a new OTP if it exists
            $otp = '';
            for ($i = 0; $i < $length; $i++) {
                $otp .= rand(0, 9);
            }
        }

        return $otp;
    }

    public static function send($message,$phone){
        $api_key='7e18e640b4693884';
        $secret_key = 'OWYwNDI0MzhiZDdmM2RmY2JmZWRlNzEyMzkzODg1OTFlMDIyZTRkM2EwOWFhOTdmNWI3ZjVjYzVhOTQwMDk5MQ==';

        $activate_user = AppUser::where('phone',$phone)->first();


        $postData = array(
            'source_addr' => 'KASOME',
            'encoding'=>0,
            'schedule_time' => '',
            'message' =>$message,
            'recipients' => [array('recipient_id' => $activate_user->id,'dest_addr'=>self::getformattedBeem($activate_user->phone))]
        );

            $Url ='https://apisms.beem.africa/v1/send';

            $ch = curl_init($Url);
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array(
                    'Authorization:Basic ' . base64_encode("$api_key:$secret_key"),
                    'Content-Type: application/json'
                ),
                CURLOPT_POSTFIELDS => json_encode($postData)
            ));

            $response = curl_exec($ch);

            if($response === FALSE){
                    echo $response;

                die(curl_error($ch));
            }
           // var_dump($response."=id".$activate_user->id."=no ".self::getformattedBeem($activate_user->phone));
        }
   

    public static function createPaymentToken(){
    

        // dd(Auth::User());
        $CompanyToken = "55B69320-7B2D-451F-9846-4790DA901616";
        $Request = "createToken";
        
        $userId = request()->input('user_id');
        $userData = AppUser::find($userId);
        $fullName = $userData->name;
        $names = explode(' ', $fullName);

        // Now $names is an array containing first and last names
        $firstName = isset($names[0]) ? $names[0] : '';
        $lastName = isset($names[1]) ? $names[1] : '';

        $PaymentAmount = $request->input('amount');
        $PaymentCurrency = "TZS";
        $CompanyRef = "49FKEOA";

        //The redirect and back URL

        $RedirectURL = "https://portal.kasome.com/payurl.php";
        $BackURL = "https://portal.kasome.com/backurl.php";


        $CompanyRefUnique = "0";
        $PTL = "96";
        $PTLtype = "hours";
        $ServiceType = "29617";
        $ServiceDescription = "Videos";
        $FraudTimeLimit = "60";
        $ServiceDate = date("Y/m/d H:i");
        $DefaultPayment = "MO";
        $customerFirstName = $firstName;
        $customerLastName =$lastName;
        $customerPhone = $userData->phone;
        $customerEmail = $userData->email;
        $customerCity = $userData->region;
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
            $TransToken = $xml->xpath('//API3G/TransToken')[0];
            return ['status'=> 'SUCCESS','code' => '200','data' =>$TransToken,'message'=>"Token set successfully"];
        }     
    }

   public function getProfile(Request $request){

     $userId = request()->input('user_id');
     $user = AppUser::find($userId);
      return ['status'=> 'SUCCESS','code' => '200','data' =>$user,'message'=>"Profile request successfully"];
   }


  public function userSubscription(Request $request){

     $userId = request()->input('user_id');
     $user = AppUser::find($userId);

     $status = $user->getSubscriptionStatus();

     return ['status'=> 'SUCCESS','code' => '200','data' =>$user,'subscription'=>$status,'message'=>"Subscription request successfully"];
   }


  public function editProfile(Request $request){


        /*$validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                
                'regex:/^[0-9+]+$/',
                'min:10',
                'max:13',
            ]

        ], [
            
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number should contain only numbers and an optional + sign.',
            'phone.min' => 'The phone number should be at least 10 characters long.',
            'phone.max' => 'The phone number should not exceed 13 characters.',
           
        ]);*/

      // $existing_phone = AppUser::where('phone',self::getformatted($request->input('phone','')))->first();
    //   if( $existing_phone)
        //    return ['status' => 'FAILURE','code' => '400','message' => "Phone number already exist"];


        /*if ($validator->fails()) {
            return [
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->first()
            ];
        }*/

        $userId = request()->input('user_id');

        $existing_phone = AppUser::where('phone',self::getformatted($request->input('phone','')))->first();
        if($existing_phone){
         $user = AppUser::where("id",$userId)->update([
            "name"=>$request->input('name',''),
            //"phone"=> self::getformatted($request->input('phone','')),
            "email"=> $request->input('email',''),
            "created_at"=> date('Y-m-d'),
            "updated_at"=> date('Y-m-d')

        ]);


        $status = ['status'=> 'SUCCESS','code' => '200','data' =>$user,'message'=>"User updated successfully"];

        }else{
        $user = AppUser::where("id",$userId)->update([
            "name"=>$request->input('name',''),
            "phone"=> self::getformatted($request->input('phone','')),
            "email"=> $request->input('email',''),
            "created_at"=> date('Y-m-d'),
            "updated_at"=> date('Y-m-d')
             
        ]);

           $status =['status'=> 'SUCCESS','code' => '200','data' =>$user,'message'=>"User updated successfully"];
        }
        
        return $status;

    }


   public function getUserPayments(Request $request){
     $userId = request()->input('user_id');

     $user = AppUser::
            with(['payments' => function ($query) {
                 // Filter payments with status 'settled'
                 $query->where('status', 'settled');
             },'payments.video'])
             ->where('id',$userId)->first();
     return ['status'=> 'SUCCESS','code' => '200','data' =>$user,'message'=>"User updated successfully"];
   } 

  public function getUserVideos(Request $request){
     $userId = request()->input('user_id');

     /*$videos = Video::
            with(['payment' => function ($query) use($userId) {
                 // Filter payments with status 'settled'
                 $query->where(['user_id'=> $userId,'status'=>'settled']);
             }])
             ->get();*/

    $videos = Video::with(['payment' => function ($query) use ($userId) {
        // Filter payments with status 'settled'
        $query->where(['user_id' => $userId, 'status' => 'settled']);
    }])
        ->whereHas('payment', function ($query) use ($userId) {
            // Additional constraint to get only videos with settled payments
            $query->where(['user_id' => $userId, 'status' => 'settled']);
        })
        ->get();
     return ['status'=> 'SUCCESS','code' => '200','data' =>$videos,'message'=>"User updated successfully"];
   }

   public function registerGoogle(Request $request){


        

       $existing_user = AppUser::where('email',$request->input('email',''))->first();
       if( $existing_user){
             $token = $existing_user->createToken('authToken')->plainTextToken;
            return ['status' => 'SUCCESS','code' => '200','data' =>$existing_user,'token'=>$token,'message' => "User number already exist"];
       }


        $user = AppUser::create([
            "name"=>$request->input('name',''),
            "email"=> $request->input('email','')
            
        ]);

        if($user){
           $token = $user->createToken('authToken')->plainTextToken;
           //$message = 'You have successfully registered in KASOME app .Please enter this token  '.$user->activate_user_token.'  to activate account';
           //self::send($message,$user->phone);
        }
        return ['status'=> 'SUCCESS','code' => '200','data' =>$user,'token'=>$token,'message'=>"User created successfully"];

    }       

   public function registerFacebook(Request $request){


        

       $existing_user = AppUser::whereRaw('LOWER(name) = ?', strtolower($request->input('name', '')))
    ->first();
       if( $existing_user){
             $token = $existing_user->createToken('authToken')->plainTextToken;
            return ['status' => 'SUCCESS','code' => '200','data' =>$existing_user,'token'=>$token,'message' => "User number already exist"];
       }


        $user = AppUser::create([
            "name"=>$request->input('name','')
       

        ]);

        if($user){
           $token = $user->createToken('authToken')->plainTextToken;
           //$message = 'You have successfully registered in KASOME app .Please enter this token  '.$user->activate_user_token.'  to activate account';
           //self::send($message,$user->phone);
        }
        return ['status'=> 'SUCCESS','code' => '200','data' =>$user,'token'=>$token,'message'=>"User created successfully"];

    }

    public function createClipComments(Request $request,$id){
      // Validate the incoming request
        $userId = request()->input('user_id');

    // Create the comment or reply
    $commentId = DB::table('tbl_clip_comments')->insertGetId([

        'clip_id' =>$id,
        'user_id' => $userId,
        'content' => $request->input('content'),
        'created_at' => now(),
        'created_by' => $userId,
        'updated_at' => now(),
        'updated_by' => $userId
    ]);

    // Fetch the newly created comment
    $newComment = DB::table('tbl_clip_comments')->where('id', $commentId)->first();
    $viewCount = DB::table('tbl_practical_video_clips')->where('id', $id)->increment('comment_count');
    $viewCount = DB::table('tbl_practical_video_clips')->where('id', $id)->increment('view_count');
    return response()->json([
        'status' => 'SUCCESS',
        'code' => 200,
        'message' => 'Comment created successfully.',
        'data' => $newComment
    ]);
    }

   public function createClipCommentsReply(Request $request,$id){
      // Validate the incoming request
        $userId = request()->input('user_id');

    // Create the comment or reply
    $commentId = DB::table('tbl_clip_comments_replies')->insertGetId([
                 
        'clip_comment_id' =>$id,
        'user_id' => $userId,
        'content' => $request->input('content'),
        'created_at' => now(),
        'created_by' => $userId,
        'updated_at' => now(),
        'updated_by' => $userId
    ]);

    // Fetch the newly created comment
    $newComment = DB::table('tbl_clip_comments_replies')->where('id', $commentId)->first();
    $viewCount = DB::table('tbl_clip_comments_replies')->where('id', $commentId)->increment('clip_comment_count');
    return response()->json([
        'status' => 'SUCCESS',
        'code' => 200,
        'message' => 'Clip Comment replies  created successfully.',
        'data' => $newComment
    ]);
    }        

    public function getClipComments($id){

      $userId = request()->input('user_id');
    
      $clipcomments = ClipComments::with("reply.user","user")
                      ->where(["clip_id"=>$id,"visibility"=>"true"])
                      ->get(); 


       return response()->json([
        'status' => 'SUCCESS',
        'code' => 200,
        'message' => 'Clip Comment retrieved successfully.',
        'data' => $clipcomments
    ]);

    }


     public function createClipViews($id,$videoId){

      $userId = request()->input('user_id');


     // Create the comment or reply
      $viewId = DB::table('tbl_clip_views')->insertGetId([
        'clip_id' =>  $id,
        'user_id' =>  $userId,
        'video_id'=> $videoId,
        'created_by' => $userId,
        'updated_by' => $userId
      ]);

      // Fetch the newly created comment
       $newView = DB::table('tbl_clip_views')->where('id', $viewId)->first();
       $viewCount = DB::table('tbl_practical_video_clips')->where('id', $id)->increment('view_count');
       return response()->json([
        'status' => 'SUCCESS',
        'code' => 200,
        'message' => 'View created successfully.',
        'data' => $newView
       ]);

    }

     public function createVideosViews($id){

      $userId = request()->input('user_id');


     // Create the comment or reply
      $viewId = DB::table('tbl_video_views')->insertGetId([
        'video_id' =>  $id,
        'user_id' =>  $userId,
        'created_by' => $userId,
        'updated_by' => $userId
      ]);

      // Fetch the newly created comment
       $newView = DB::table('tbl_video_views')->where('id', $viewId)->first();
       $viewCount = DB::table('tbl_practical_video')->where('id', $id)->increment('view_count');
       return response()->json([
        'status' => 'SUCCESS',
        'code' => 200,
        'message' => 'View created successfully.',
        'data' => $newView
       ]);

    }
}

