<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Subscription;
use App\Models\app_user as AppUser;
use App\Models\practical_video_model as Course;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Create a new subscription with optional course selection.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:tbl_users,id',
            'subscription_type' => 'required|in:all_courses,specific_courses',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'course_ids' => 'required_if:subscription_type,specific_courses|array',
            'course_ids.*' => 'exists:tbl_practical_video,id',
        ], [
            'subscription_type.in' => 'Subscription type must be either "all_courses" or "specific_courses"',
            'course_ids.required_if' => 'Course IDs are required when subscription_type is "specific_courses"',
            'end_date.after' => 'End date must be after start date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 400);
        }

        $userId = $request->input('user_id');
        $subscriptionType = $request->input('subscription_type');
        $courseIds = $request->input('course_ids', []);

        // Validate course_ids for specific_courses type
        if ($subscriptionType === 'specific_courses' && empty($courseIds)) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => 'At least one course must be selected for specific_courses subscription type',
            ], 400);
        }

        // Create subscription
        $subscription = Subscription::create([
            'user_id' => $userId,
            'subscription_type' => $subscriptionType,
            'amount' => $request->input('amount'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => 'active',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Attach courses if specific_courses type
        if ($subscriptionType === 'specific_courses' && !empty($courseIds)) {
            $subscription->courses()->attach($courseIds, [
                'created_at' => now(),
                'created_by' => $userId,
            ]);
        }

        // Load relationships
        $subscription->load('courses');

        return response()->json([
            'status' => 'SUCCESS',
            'code' => '200',
            'message' => 'Subscription created successfully',
            'data' => $subscription,
        ], 201);
    }

    /**
     * Get user's active subscriptions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserSubscriptions(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => 'User ID is required',
            ], 400);
        }

        $subscriptions = Subscription::with('courses')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('end_date', 'desc')
            ->get();

        // Filter by active date range
        $activeSubscriptions = $subscriptions->filter(function ($subscription) {
            return $subscription->isActive();
        })->values();

        return response()->json([
            'status' => 'SUCCESS',
            'code' => '200',
            'message' => 'Subscriptions retrieved successfully',
            'data' => $activeSubscriptions,
            'total' => $activeSubscriptions->count(),
        ], 200);
    }

    /**
     * Add courses to an existing subscription.
     *
     * @param Request $request
     * @param int $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCoursesToSubscription(Request $request, $subscriptionId)
    {
        $validator = Validator::make($request->all(), [
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'exists:tbl_practical_video,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '404',
                'message' => 'Subscription not found',
            ], 404);
        }

        if ($subscription->subscription_type !== 'specific_courses') {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => 'Can only add courses to subscriptions with type "specific_courses"',
            ], 400);
        }

        $courseIds = $request->input('course_ids');
        $userId = $request->input('user_id', $subscription->user_id);

        // Attach new courses (syncWithoutDetaching prevents duplicates)
        $subscription->courses()->syncWithoutDetaching(array_map(function ($courseId) use ($userId) {
            return [
                'course_id' => $courseId,
                'created_at' => now(),
                'created_by' => $userId,
            ];
        }, $courseIds));

        $subscription->load('courses');

        return response()->json([
            'status' => 'SUCCESS',
            'code' => '200',
            'message' => 'Courses added to subscription successfully',
            'data' => $subscription,
        ], 200);
    }

    /**
     * Remove courses from an existing subscription.
     *
     * @param Request $request
     * @param int $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeCoursesFromSubscription(Request $request, $subscriptionId)
    {
        $validator = Validator::make($request->all(), [
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'exists:tbl_practical_video,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '404',
                'message' => 'Subscription not found',
            ], 404);
        }

        if ($subscription->subscription_type !== 'specific_courses') {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => 'Can only remove courses from subscriptions with type "specific_courses"',
            ], 400);
        }

        $courseIds = $request->input('course_ids');
        $subscription->courses()->detach($courseIds);

        $subscription->load('courses');

        return response()->json([
            'status' => 'SUCCESS',
            'code' => '200',
            'message' => 'Courses removed from subscription successfully',
            'data' => $subscription,
        ], 200);
    }

    /**
     * Get all courses accessible to the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccessibleCourses(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => 'User ID is required',
            ], 400);
        }

        $user = AppUser::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '404',
                'message' => 'User not found',
            ], 404);
        }

        $accessibleCourseIds = $user->getAccessibleCourseIds();

        // Get course details
        $courses = Course::whereIn('id', $accessibleCourseIds)
            ->with(['PracticleVideoClips' => function ($query) {
                $query->orderBy('id', 'asc');
            }])
            ->get();

        return response()->json([
            'status' => 'SUCCESS',
            'code' => '200',
            'message' => 'Accessible courses retrieved successfully',
            'data' => $courses,
            'total' => $courses->count(),
        ], 200);
    }

    /**
     * Check if user has access to a specific course.
     *
     * @param Request $request
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCourseAccess(Request $request, $courseId)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '400',
                'message' => 'User ID is required',
            ], 400);
        }

        $user = AppUser::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '404',
                'message' => 'User not found',
            ], 404);
        }

        $course = Course::find($courseId);

        if (!$course) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '404',
                'message' => 'Course not found',
            ], 404);
        }

        $hasAccess = $user->hasCourseAccess($courseId);

        // Determine access type
        $accessInfo = [
            'has_access' => $hasAccess,
            'access_type' => null,
            'subscription_details' => null,
        ];

        if ($hasAccess) {
            // Check if course is free
            if ($course->price == null || $course->price == 0) {
                $accessInfo['access_type'] = 'free';
            }
            // Check individual payment
            elseif ($user->payments()->where('video_id', $courseId)->where('status', 'settled')->exists()) {
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

        return response()->json([
            'status' => 'SUCCESS',
            'code' => '200',
            'message' => 'Course access checked successfully',
            'course_id' => $courseId,
            'access' => $accessInfo,
        ], 200);
    }

    /**
     * Cancel/expire a subscription.
     *
     * @param Request $request
     * @param int $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSubscription(Request $request, $subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '404',
                'message' => 'Subscription not found',
            ], 404);
        }

        // Verify user owns this subscription
        $userId = $request->input('user_id');
        if ($subscription->user_id != $userId) {
            return response()->json([
                'status' => 'FAILURE',
                'code' => '403',
                'message' => 'Unauthorized to cancel this subscription',
            ], 403);
        }

        $subscription->status = 'cancelled';
        $subscription->updated_by = $userId;
        $subscription->save();

        return response()->json([
            'status' => 'SUCCESS',
            'code' => '200',
            'message' => 'Subscription cancelled successfully',
            'data' => $subscription,
        ], 200);
    }
}

