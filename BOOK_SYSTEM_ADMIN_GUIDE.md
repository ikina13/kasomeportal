# Book System Admin Guide

## 📚 How to Use the Book System

This guide explains how to manage books, enable donations, and view sales in the admin panel.

---

## 🎯 Where to Find Everything

### 1. **Book Management** (`/admin/books`)
   - Create, edit, and manage all books
   - Enable/disable donation functionality
   - View sales count and revenue per book

### 2. **Book Purchases** (`/admin/book-purchases`) ⭐ NEW
   - View ALL purchases and donations across all books
   - Filter by purchase type (Purchase vs Donation)
   - See download statistics
   - Track payments and revenue

### 3. **Author Sales** (`/admin/author-models`)
   - View books by each author
   - See total sales and revenue per author
   - Click on an author → "Books" tab to see their books

---

## 📖 How to Enable Donation Books

### Step 1: Create or Edit a Book
1. Go to `/admin/books`
2. Click **"Create Book"** or edit an existing book
3. Fill in the book details (title, author, price, etc.)

### Step 2: Enable Donation
1. Scroll down to the **"Donation Settings"** section
2. Toggle **"Enable Donation"** to **ON** (green)
3. Optionally set a **"Minimum Donation Amount"**:
   - If set to 0 or empty: Users can donate any amount ≥ book price
   - If set to a specific amount: Users must donate at least that amount
4. Click **"Create"** or **"Save"**

### Step 3: What Happens Next?
- On the frontend (`/books`), users will see:
  - A **"Buy Now"** button (regular purchase)
  - A **"Donate"** button (if donation is enabled)
- When users click **"Donate"**, they can contribute by purchasing the book
- The purchase will be marked as **"Donation"** in the system
- You can see all donations in **Book Purchases** with the "Donation" badge

---

## 💰 How to View Sales and Purchases

### Option 1: View All Purchases (Recommended)
1. Go to `/admin/book-purchases`
2. You'll see a table with:
   - **Book Title** and cover image
   - **Customer** name
   - **Amount Paid** (TZS)
   - **Type** (Purchase or Donation) - color-coded badges
   - **Status** (Pending, Completed, Cancelled)
   - **Downloads** (current / max)
   - **Remaining Downloads**
   - **Purchased Date**

### Filtering Options:
- **Tabs at the top:**
  - "All Purchases" - Everything
  - "Purchases" - Regular purchases only
  - "Donations" - Donations only
  - "Completed" - Completed payments only

- **Filters:**
  - Filter by Purchase Type
  - Filter by Status
  - Filter by Date Range

### Option 2: View Purchases Per Book
1. Go to `/admin/books`
2. Click on a book to edit it
3. Look for the **"Purchases"** tab (relation manager)
4. You'll see all purchases for that specific book

### Option 3: View Sales Per Author
1. Go to `/admin/author-models`
2. Click on an author
3. Click the **"Books"** tab
4. You'll see:
   - All books by that author
   - Sales count per book
   - Revenue per book

---

## 📊 Understanding Sales Data

### In Book List (`/admin/books`):
- **Sales**: Total number of completed purchases
- **Revenue**: Total amount earned from that book (sum of all payments)
- **Donation Badge**: Shows if donation is enabled

### In Book Purchases (`/admin/book-purchases`):
- **Amount Paid**: The actual payment amount
- **Type Badge**:
  - 🔵 **Purchase** (blue) = Regular purchase
  - 🟢 **Donation** (green) = Donation purchase
- **Status Badge**:
  - 🟡 **Pending** = Payment initiated but not completed
  - 🟢 **Completed** = Payment successful, user can download
  - 🔴 **Cancelled** = Payment cancelled/failed

### Download Tracking:
- **Downloads**: How many times the book has been downloaded
- **Max Downloads**: Maximum allowed downloads (default: 5)
- **Remaining**: How many downloads are left

---

## 🔧 Admin Actions

### Reset Download Count:
- In Book Purchases, click the **"Reset Downloads"** action
- This allows the user to download again (resets count to 0)
- Useful if a user requests more downloads

### Regenerate Download Token:
- If a download token expires, click **"Regenerate Token"**
- This creates a new token valid for 24 hours

### Edit Purchase:
- Click **"Edit"** on any purchase to:
  - Change status (e.g., mark as completed manually)
  - Adjust download count
  - Change max downloads

---

## 📈 Sales Reports

### Total Sales Overview:
1. Go to `/admin/book-purchases`
2. Use filters to see:
   - Sales by date range
   - Total donations vs purchases
   - Revenue by period

### Author Performance:
1. Go to `/admin/author-models`
2. Each author shows:
   - Total books published
   - Total courses (if applicable)
   - Click on author → Books tab for detailed stats

---

## 🎓 Quick Examples

### Example 1: Create a Donation Book
1. Create a new book: "Support Education - English Grammar"
2. Set price: 10,000 TZS
3. Enable "Donation Settings" → Toggle ON
4. Set minimum donation: 5,000 TZS
5. Save
6. Users can now donate 5,000+ TZS to get this book

### Example 2: View Today's Sales
1. Go to `/admin/book-purchases`
2. Use the "Purchased From" filter
3. Set date to today
4. See all purchases made today
5. Check the revenue total at the bottom

### Example 3: Find All Donations
1. Go to `/admin/book-purchases`
2. Click the **"Donations"** tab
3. See all books purchased as donations
4. Filter by date if needed

---

## ❓ Common Questions

**Q: Where do I see which books are paid/completed?**
A: Go to `/admin/book-purchases` → Filter by "Status: Completed"

**Q: How do I enable donation for an existing book?**
A: Edit the book → Scroll to "Donation Settings" → Toggle ON → Save

**Q: Where can I see total revenue?**
A: 
- Per book: In `/admin/books` table, see "Revenue" column
- Per author: In author's Books tab
- Overall: In `/admin/book-purchases`, sum up all "Amount Paid" values

**Q: How do I distinguish purchases from donations?**
A: In `/admin/book-purchases`, look at the "Type" column:
- Blue badge = Purchase
- Green badge = Donation

**Q: Can I manually create a purchase?**
A: Yes, in `/admin/book-purchases` → Click "Create Book Purchase" (though normally purchases are created automatically via payment flow)

---

## 📍 Navigation Paths

- **Books**: `/admin/books`
- **Book Purchases**: `/admin/book-purchases`
- **Authors**: `/admin/author-models`

---

**Need Help?** All features are now clearly labeled and organized in the admin panel. Start with Book Purchases to see everything in one place!

