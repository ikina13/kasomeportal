
<?php
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\TokenMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('users/register',[UserController::class,'register']);
Route::post('users/google',[UserController::class,'registerGoogle']);
Route::post('users/facebook',[UserController::class,'registerFacebook']);
Route::post('users/login',[UserController::class,'login']);
Route::post('users/activate',[UserController::class,'activateAccount']);
Route::post('users/payment/success',[CourseController::class,'paymentSuccess']);     
Route::post('users/forget/password',[UserController::class,'forgetPassword']);
Route::post('users/reset/password',[UserController::class,'resetPassword']);
Route::get('users/courses',[CourseController::class,'getAllCourses']);
Route::get('users/portal/courses/{id}',[CourseController::class,'getPortalCoursesById']);
Route::get('users/courses/video/{id}',[CourseController::class,'getVideoById']);
Route::get('users/video/{id}',[CourseController::class,'getVideoByToken']);
//Route::post('users/payment/token',[CourseController::class,'createPaymentToken']);
Route::group(['middleware'=>['auth:sanctum',TokenMiddleware::class]],function () {
     Route::post('users/logout',[UserController::class,'logout']);
     Route::get('users/profile',[UserController::class,'getProfile']);
     Route::post('users/profile',[UserController::class,'editProfile']);
     Route::get('users/payment',[UserController::class,'getUserPayments']);
     Route::get('users/payments',[CourseController::class,'getPayments']);
     Route::post('users/payment/token',[CourseController::class,'createPaymentToken']);  
     Route::get('users/courses/{id}',[CourseController::class,'getCoursesById']);
     Route::get('users/subjects/{id}',[CourseController::class,'getSubjectsById']);
     Route::get('users/subjects/{subjectId}/class/{id}',[CourseController::class,'getSubjectsByClassId']);  
     Route::get('users/videos',[UserController::class,'getUserVideos']);
     Route::post('users/videos/likes',[UserController::class,'createUpdateVideosLikes']);
     Route::get('users/videos/likes',[UserController::class,'getVideosLikes']);
     Route::post('users/clip/likes',[UserController::class,'createUpdateClipLikes']);
     Route::get('users/clip/likes',[UserController::class,'getClipLikes']);
     Route::post('users/videos/views/{id}',[UserController::class,'createVideosViews']);
     Route::post('users/clip/views/{id}/{videoId}',[UserController::class,'createClipViews']);
     Route::get('users/clip/views',[UserController::class,'getClipViews']);
     Route::post('users/videos/comments',[UserController::class,'createVideosComments']);
     Route::post('users/clip/comments/{id}',[UserController::class,'createClipComments']);
     Route::post('users/videos/comments/reply',[UserController::class,'createVideosCommentsReply']);
     Route::get('users/videos/comments',[UserController::class,'getVideosComments']);
     Route::get('users/clip/comments/{id}',[UserController::class,'getClipComments']);
     Route::post('users/clip/comments/reply/{id}',[UserController::class,'createClipCommentsReply']);
     Route::get('users/subscription',[UserController::class,'userSubscription']);
});
