#!/bin/bash

# Quick API Testing Script
# Usage: ./TEST_API_QUICK_START.sh

BASE_URL="https://portal.kasome.com/api"
# BASE_URL="http://localhost:8000/api"  # For local testing

echo "🧪 Kasome Subscription API Testing"
echo "=================================="
echo ""

# Step 1: Login
echo "1️⃣  Logging in..."
read -p "Enter phone number: " PHONE
read -sp "Enter password: " PASSWORD
echo ""

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/users/login" \
  -H "Content-Type: application/json" \
  -d "{\"phone\":\"$PHONE\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
USER_ID=$(echo $LOGIN_RESPONSE | grep -o '"id":[0-9]*' | cut -d':' -f2)

if [ -z "$TOKEN" ]; then
  echo "❌ Login failed!"
  echo "Response: $LOGIN_RESPONSE"
  exit 1
fi

echo "✅ Login successful!"
echo "Token: ${TOKEN:0:20}..."
echo "User ID: $USER_ID"
echo ""

# Step 2: Check Course Access
echo "2️⃣  Testing course access..."
read -p "Enter course ID to test: " COURSE_ID

echo ""
echo "📡 Checking access to course $COURSE_ID..."
ACCESS_RESPONSE=$(curl -s -X GET "$BASE_URL/users/courses/$COURSE_ID?user_id=$USER_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

HAS_ACCESS=$(echo $ACCESS_RESPONSE | grep -o '"has_access":[^,}]*' | cut -d':' -f2)
ACCESS_TYPE=$(echo $ACCESS_RESPONSE | grep -o '"access_type":"[^"]*' | cut -d'"' -f4)

echo "Response:"
echo "$ACCESS_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$ACCESS_RESPONSE"
echo ""
echo "✅ Has Access: $HAS_ACCESS"
echo "✅ Access Type: $ACCESS_TYPE"
echo ""

# Step 3: Get User Subscriptions
echo "3️⃣  Getting user subscriptions..."
SUBSCRIPTIONS=$(curl -s -X GET "$BASE_URL/users/subscriptions?user_id=$USER_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "Subscriptions:"
echo "$SUBSCRIPTIONS" | python3 -m json.tool 2>/dev/null || echo "$SUBSCRIPTIONS"
echo ""

# Step 4: Test Creating Subscription
echo "4️⃣  Test subscription creation? (y/n)"
read -p "> " CREATE_SUB

if [ "$CREATE_SUB" = "y" ]; then
  echo ""
  echo "Choose subscription type:"
  echo "1) All Courses"
  echo "2) Specific Courses"
  read -p "> " SUB_TYPE_CHOICE
  
  if [ "$SUB_TYPE_CHOICE" = "1" ]; then
    SUB_TYPE="all_courses"
    COURSE_IDS="[]"
  else
    SUB_TYPE="specific_courses"
    read -p "Enter course IDs (comma separated): " COURSE_IDS_INPUT
    COURSE_IDS="[$(echo $COURSE_IDS_INPUT | tr ',' ',')]"
  fi
  
  read -p "Enter amount: " AMOUNT
  read -p "Enter start date (YYYY-MM-DD): " START_DATE
  read -p "Enter end date (YYYY-MM-DD): " END_DATE
  
  CREATE_PAYLOAD="{\"user_id\":$USER_ID,\"subscription_type\":\"$SUB_TYPE\",\"amount\":$AMOUNT,\"start_date\":\"$START_DATE\",\"end_date\":\"$END_DATE\""
  
  if [ "$SUB_TYPE" = "specific_courses" ]; then
    CREATE_PAYLOAD="$CREATE_PAYLOAD,\"course_ids\":$COURSE_IDS"
  fi
  
  CREATE_PAYLOAD="$CREATE_PAYLOAD}"
  
  echo ""
  echo "Creating subscription..."
  CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/users/subscription/create" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d "$CREATE_PAYLOAD")
  
  echo "Response:"
  echo "$CREATE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$CREATE_RESPONSE"
  echo ""
fi

echo "✅ Testing complete!"
echo ""
echo "💡 Tip: Save your token for more testing:"
echo "export AUTH_TOKEN=\"$TOKEN\""
echo "export USER_ID=\"$USER_ID\""

