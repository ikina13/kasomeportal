# Subscription API Endpoints for Testing

**Base URL:** `http://localhost:8000/api`

---

## 1. LOGIN (Get Token)

**Endpoint:** `POST /api/users/login`

**Request:**
```json
{
  "phone": "+255753477509",
  "password": "your_password"
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/users/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"+255753477509","password":"your_password"}'
```

**Response:** Returns `token` and `data.id` (user_id)

---

## 2. CREATE SUBSCRIPTION - ALL COURSES

**Endpoint:** `POST /api/users/subscription/create`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request:**
```json
{
  "user_id": 1448,
  "subscription_type": "all_courses",
  "amount": 50000,
  "start_date": "2025-01-12",
  "end_date": "2025-12-31"
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/users/subscription/create \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1448,
    "subscription_type": "all_courses",
    "amount": 50000,
    "start_date": "2025-01-12",
    "end_date": "2025-12-31"
  }'
```

---

## 3. CREATE SUBSCRIPTION - SPECIFIC COURSES

**Endpoint:** `POST /api/users/subscription/create`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request:**
```json
{
  "user_id": 1448,
  "subscription_type": "specific_courses",
  "amount": 30000,
  "start_date": "2025-01-12",
  "end_date": "2025-12-31",
  "course_ids": [1, 2, 3, 5, 10]
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/users/subscription/create \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1448,
    "subscription_type": "specific_courses",
    "amount": 30000,
    "start_date": "2025-01-12",
    "end_date": "2025-12-31",
    "course_ids": [1, 2, 3]
  }'
```

---

## 4. GET USER SUBSCRIPTIONS

**Endpoint:** `GET /api/users/subscriptions?user_id={user_id}`

**Headers:**
- `Authorization: Bearer {token}`

**cURL:**
```bash
curl -X GET "http://localhost:8000/api/users/subscriptions?user_id=1448" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 5. ADD COURSES TO SUBSCRIPTION

**Endpoint:** `POST /api/users/subscription/{subscription_id}/courses/add`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request:**
```json
{
  "user_id": 1448,
  "course_ids": [15, 20, 25]
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/users/subscription/1/courses/add \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1448,
    "course_ids": [15, 20, 25]
  }'
```

---

## 6. REMOVE COURSES FROM SUBSCRIPTION

**Endpoint:** `POST /api/users/subscription/{subscription_id}/courses/remove`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request:**
```json
{
  "course_ids": [15, 20]
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/users/subscription/1/courses/remove \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "course_ids": [15, 20]
  }'
```

---

## 7. CANCEL SUBSCRIPTION

**Endpoint:** `POST /api/users/subscription/{subscription_id}/cancel`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request:**
```json
{
  "user_id": 1448
}
```

**cURL:**
```bash
curl -X POST http://localhost:8000/api/users/subscription/1/cancel \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1448
  }'
```

---

## 8. CHECK COURSE ACCESS

**Endpoint:** `GET /api/users/courses/{course_id}?user_id={user_id}`

**Headers:**
- `Authorization: Bearer {token}`

**cURL:**
```bash
curl -X GET "http://localhost:8000/api/users/courses/1?user_id=1448" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response includes `access` object:**
```json
{
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

## 9. GET ACCESSIBLE COURSES

**Endpoint:** `GET /api/users/courses/accessible?user_id={user_id}`

**Headers:**
- `Authorization: Bearer {token}`

**cURL:**
```bash
curl -X GET "http://localhost:8000/api/users/courses/accessible?user_id=1448" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 10. CHECK COURSE ACCESS (Dedicated Endpoint)

**Endpoint:** `GET /api/users/courses/{course_id}/access?user_id={user_id}`

**Headers:**
- `Authorization: Bearer {token}`

**cURL:**
```bash
curl -X GET "http://localhost:8000/api/users/courses/1/access?user_id=1448" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Testing Flow:

1. **Login** → Get token and user_id
2. **Create All Courses Subscription** → Test subscription creation
3. **Create Specific Courses Subscription** → Test with course selection
4. **Get User Subscriptions** → View all subscriptions
5. **Check Course Access** → Verify access is working
6. **Add/Remove Courses** → Modify subscription
7. **Cancel Subscription** → Test cancellation

---

## Quick Test Script:

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/users/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"+255753477509","password":"your_password"}' \
  | grep -o '"token":"[^"]*' | cut -d'"' -f4)

USER_ID=1448

# 2. Create All Courses Subscription
curl -X POST http://localhost:8000/api/users/subscription/create \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"user_id\":$USER_ID,\"subscription_type\":\"all_courses\",\"amount\":50000,\"start_date\":\"2025-01-12\",\"end_date\":\"2025-12-31\"}"

# 3. Create Specific Courses Subscription
curl -X POST http://localhost:8000/api/users/subscription/create \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"user_id\":$USER_ID,\"subscription_type\":\"specific_courses\",\"amount\":30000,\"start_date\":\"2025-01-12\",\"end_date\":\"2025-12-31\",\"course_ids\":[1,2,3]}"

# 4. Get Subscriptions
curl -X GET "http://localhost:8000/api/users/subscriptions?user_id=$USER_ID" \
  -H "Authorization: Bearer $TOKEN"

# 5. Check Course Access
curl -X GET "http://localhost:8000/api/users/courses/1?user_id=$USER_ID" \
  -H "Authorization: Bearer $TOKEN"
```

