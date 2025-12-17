# API Testing Guide - Flexible Subscription System

## 🧪 How to Test the APIs

### Prerequisites
1. ✅ Backend server running (`php artisan serve` or on portal.kasome.com)
2. ✅ User authentication token (get from login)
3. ✅ Database migration run (or test on local first)

---

## 🔧 Option 1: Using cURL (Command Line)

### Step 1: Get Authentication Token
```bash
# Login to get token
curl -X POST https://portal.kasome.com/api/users/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+255712345678",
    "password": "your_password"
  }'

# Response will include token:
# {
#   "status": "SUCCESS",
#   "token": "1|abc123xyz...",
#   ...
# }
```

### Step 2: Save Token
```bash
# Save token to variable
export AUTH_TOKEN="1|abc123xyz..."
```

---

## 📋 API Testing Examples

### 1. Check Course Access

**Endpoint:** `GET /api/users/courses/{courseId}`

```bash
curl -X GET "https://portal.kasome.com/api/users/courses/1?user_id=123" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "status": "SUCCESS",
  "message": "Courses videos retrieved successfully",
  "data": [...],
  "access": {
    "has_access": true,
    "access_type": "all_courses",
    "subscription_details": {
      "expires_at": "2025-12-31",
      "subscription_id": 1
    }
  }
}
```

---

### 2. Create Subscription (All Courses)

**Endpoint:** `POST /api/users/subscription/create`

```bash
curl -X POST "https://portal.kasome.com/api/users/subscription/create" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "subscription_type": "all_courses",
    "amount": 50000,
    "start_date": "2025-01-12",
    "end_date": "2025-12-31"
  }'
```

**Expected Response:**
```json
{
  "status": "SUCCESS",
  "code": "200",
  "message": "Subscription created successfully",
  "data": {
    "id": 13,
    "user_id": 123,
    "subscription_type": "all_courses",
    "amount": 50000,
    "status": "active",
    "courses": []
  }
}
```

---

### 3. Create Subscription (Specific Courses)

**Endpoint:** `POST /api/users/subscription/create`

```bash
curl -X POST "https://portal.kasome.com/api/users/subscription/create" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "subscription_type": "specific_courses",
    "amount": 30000,
    "start_date": "2025-01-12",
    "end_date": "2025-12-31",
    "course_ids": [1, 5, 10]
  }'
```

**Expected Response:**
```json
{
  "status": "SUCCESS",
  "code": "200",
  "message": "Subscription created successfully",
  "data": {
    "id": 14,
    "user_id": 123,
    "subscription_type": "specific_courses",
    "amount": 30000,
    "status": "active",
    "courses": [
      {"id": 1, "name": "Course 1"},
      {"id": 5, "name": "Course 5"},
      {"id": 10, "name": "Course 10"}
    ]
  }
}
```

---

### 4. Get User Subscriptions

**Endpoint:** `GET /api/users/subscriptions`

```bash
curl -X GET "https://portal.kasome.com/api/users/subscriptions?user_id=123" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "status": "SUCCESS",
  "code": "200",
  "message": "Subscriptions retrieved successfully",
  "data": [
    {
      "id": 13,
      "subscription_type": "all_courses",
      "status": "active",
      "start_date": "2025-01-12",
      "end_date": "2025-12-31",
      "courses": []
    }
  ],
  "total": 1
}
```

---

### 5. Add Courses to Subscription

**Endpoint:** `POST /api/users/subscription/{id}/courses/add`

```bash
curl -X POST "https://portal.kasome.com/api/users/subscription/14/courses/add" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "course_ids": [15, 20, 25]
  }'
```

---

### 6. Remove Courses from Subscription

**Endpoint:** `POST /api/users/subscription/{id}/courses/remove`

```bash
curl -X POST "https://portal.kasome.com/api/users/subscription/14/courses/remove" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "course_ids": [15]
  }'
```

---

### 7. Check Specific Course Access

**Endpoint:** `GET /api/users/courses/{courseId}/access`

```bash
curl -X GET "https://portal.kasome.com/api/users/courses/1/access?user_id=123" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "status": "SUCCESS",
  "code": "200",
  "message": "Course access checked successfully",
  "course_id": 1,
  "access": {
    "has_access": true,
    "access_type": "specific_courses",
    "subscription_details": {
      "expires_at": "2025-12-31",
      "subscription_id": 14
    }
  }
}
```

---

### 8. Get All Accessible Courses

**Endpoint:** `GET /api/users/courses/accessible`

```bash
curl -X GET "https://portal.kasome.com/api/users/courses/accessible?user_id=123" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json"
```

---

### 9. Cancel Subscription

**Endpoint:** `POST /api/users/subscription/{id}/cancel`

```bash
curl -X POST "https://portal.kasome.com/api/users/subscription/14/cancel" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123
  }'
```

---

## 🔧 Option 2: Using Postman

### Setup Postman Collection

1. **Create New Collection:** "Kasome Subscription APIs"

2. **Set Collection Variables:**
   - `base_url`: `https://portal.kasome.com/api`
   - `auth_token`: (will be set after login)
   - `user_id`: (your user ID)

3. **Add Pre-request Script (for auth):**
   ```javascript
   pm.request.headers.add({
     key: 'Authorization',
     value: 'Bearer ' + pm.collectionVariables.get('auth_token')
   });
   ```

### Test Flow:

1. **Login Request**
   - Method: POST
   - URL: `{{base_url}}/users/login`
   - Body: `{ "phone": "...", "password": "..." }`
   - Tests: Save token to collection variable

2. **Check Course Access**
   - Method: GET
   - URL: `{{base_url}}/users/courses/1?user_id={{user_id}}`
   - Headers: Authorization (auto-added)

3. **Create Subscription**
   - Method: POST
   - URL: `{{base_url}}/users/subscription/create`
   - Body: (see examples above)

---

## 🔧 Option 3: Using Browser Console (JavaScript)

### Test from Browser DevTools:

```javascript
// Set your token
const token = 'your_auth_token_here';
const userId = 123;

// Test course access
fetch(`https://portal.kasome.com/api/users/courses/1?user_id=${userId}`, {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
})
.then(res => res.json())
.then(data => {
  console.log('Course Access:', data);
  console.log('Has Access:', data.access?.has_access);
  console.log('Access Type:', data.access?.access_type);
});

// Test create subscription
fetch('https://portal.kasome.com/api/users/subscription/create', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    user_id: userId,
    subscription_type: 'all_courses',
    amount: 50000,
    start_date: '2025-01-12',
    end_date: '2025-12-31'
  })
})
.then(res => res.json())
.then(data => console.log('Subscription Created:', data));
```

---

## 🔧 Option 4: Using Laravel Tinker

### Test from Laravel Artisan:

```bash
cd /Users/davidjohn/Downloads/kasome.08.12.2025/kasomeportal
php artisan tinker
```

```php
// In tinker:

// Get a user
$user = App\Models\app_user::find(123);

// Check course access
$hasAccess = $user->hasCourseAccess(1);
echo "Has access to course 1: " . ($hasAccess ? 'YES' : 'NO') . "\n";

// Get accessible course IDs
$accessibleIds = $user->getAccessibleCourseIds();
echo "Accessible courses: " . count($accessibleIds) . "\n";

// Create subscription
$subscription = App\Models\Subscription::create([
    'user_id' => 123,
    'subscription_type' => 'all_courses',
    'amount' => 50000,
    'start_date' => now(),
    'end_date' => now()->addYear(),
    'status' => 'active',
    'created_by' => 123,
    'updated_by' => 123,
]);
echo "Subscription created: " . $subscription->id . "\n";

// Check access again
$hasAccess = $user->hasCourseAccess(1);
echo "Has access after subscription: " . ($hasAccess ? 'YES' : 'NO') . "\n";
```

---

## 🧪 Testing Checklist

### Basic Functionality
- [ ] **Login** - Get authentication token
- [ ] **Check Course Access** - Verify access checking works
- [ ] **Create All Courses Subscription** - Test subscription creation
- [ ] **Create Specific Courses Subscription** - Test with course selection
- [ ] **Check Access After Subscription** - Verify access is granted

### Subscription Management
- [ ] **Get User Subscriptions** - List all subscriptions
- [ ] **Add Courses to Subscription** - Add more courses
- [ ] **Remove Courses from Subscription** - Remove courses
- [ ] **Cancel Subscription** - Cancel subscription

### Access Scenarios
- [ ] **Free Course** - Should always have access
- [ ] **Individual Payment** - Should have access if paid
- [ ] **Class Subscription** - Should have access to class courses
- [ ] **All Courses Subscription** - Should have access to all courses
- [ ] **Specific Courses Subscription** - Should only have access to selected courses
- [ ] **No Subscription** - Should be locked

### Edge Cases
- [ ] **Expired Subscription** - Should not grant access
- [ ] **Multiple Subscriptions** - Handle correctly
- [ ] **Invalid Course ID** - Should handle gracefully
- [ ] **Invalid User ID** - Should return error

---

## 🐛 Troubleshooting

### Error: "Unauthenticated"
- **Solution:** Check that token is valid and included in Authorization header
- **Format:** `Authorization: Bearer {token}`

### Error: "User not found"
- **Solution:** Verify `user_id` is correct and user exists in database

### Error: "Course not found"
- **Solution:** Verify course ID exists in `tbl_practical_video` table

### Access always returns false
- **Check:** Database migration has been run
- **Check:** Subscription exists and is active
- **Check:** Subscription dates are valid (not expired)

### Subscription created but no access
- **Check:** `subscription_type` is set correctly
- **Check:** If `specific_courses`, verify courses are linked in `tbl_course_subscriptions`
- **Check:** Subscription status is 'active'
- **Check:** Current date is between `start_date` and `end_date`

---

## 📊 Sample Test Data

### Create Test Subscription (All Courses)
```bash
curl -X POST "https://portal.kasome.com/api/users/subscription/create" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1526,
    "subscription_type": "all_courses",
    "amount": 50000,
    "start_date": "2025-01-12 00:00:00",
    "end_date": "2025-12-31 23:59:59"
  }'
```

### Create Test Subscription (Specific Courses)
```bash
# First, find some course IDs
# Then:
curl -X POST "https://portal.kasome.com/api/users/subscription/create" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1526,
    "subscription_type": "specific_courses",
    "amount": 30000,
    "start_date": "2025-01-12 00:00:00",
    "end_date": "2025-12-31 23:59:59",
    "course_ids": [1, 2, 3]
  }'
```

---

## ✅ Verification Steps

### 1. Verify Database Migration
```sql
-- Check subscription_type column exists
SELECT column_name FROM information_schema.columns 
WHERE table_name = 'tbl_subscriptions' AND column_name = 'subscription_type';

-- Check course_subscriptions table exists
SELECT table_name FROM information_schema.tables 
WHERE table_name = 'tbl_course_subscriptions';
```

### 2. Verify Subscription Creation
```sql
-- Check subscription was created
SELECT * FROM tbl_subscriptions WHERE user_id = 123 ORDER BY id DESC LIMIT 1;

-- If specific_courses, check courses are linked
SELECT * FROM tbl_course_subscriptions WHERE subscription_id = 14;
```

### 3. Verify Access Logic
- Test with user who has subscription → should return `has_access: true`
- Test with user without subscription → should return `has_access: false`
- Test with free course → should return `has_access: true` for everyone

---

**Ready to test!** 🚀

