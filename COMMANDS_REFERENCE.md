# 📋 Commands Reference Guide

## Which Command Does What?

### 1. 💡 Inspiration Page (`/inspiration`)

**Purpose:** Fetch viral LinkedIn posts to save in your inspiration library

**Command:** 
```bash
php artisan app:fetch-linkedin-feeds
```

**What it does:**
- Fetches posts from RapidAPI based on your content preferences
- Searches by keywords, industries, and favorite creators
- Filters by engagement (min likes threshold)
- Saves to `viral_posts` table
- Used by Inspiration Library page to show you viral posts

**Scheduled:** 
- Runs **twice daily** at **12:15 PM** and **6:15 PM**
- See: `routes/console.php` line 13

**Manual Run:**
```bash
# Run for all users
php artisan app:fetch-linkedin-feeds

# Run for specific user
php artisan app:fetch-linkedin-feeds --user=1

# Limit results to save credits
php artisan app:fetch-linkedin-feeds --limit=50 --keywords=5
```

**Data Storage:** 
- Table: `viral_posts`
- Page: `/inspiration`
- Shows: Posts you can browse, favorite, remix, use as inspiration

---

### 2. 🤖 AI Auto-Comment (`/auto-comment`)

**Purpose:** Automatically find posts, generate AI comments, and post them

**Command:**
```bash
php artisan app:process-auto-comments
```

**What it does:**
- Step 1: Fetches posts matching your auto-comment preferences (keywords, followed accounts)
- Step 2: Generates AI comments for each post
- Step 3: Schedules and posts comments at optimal times
- Uses **filtered RapidAPI requests** to save credits

**Scheduled:**
- Runs **every hour** automatically
- See: `routes/console.php` line 15

**Manual Run:**
```bash
# Run everything (fetch + generate + post)
php artisan app:process-auto-comments

# Only fetch posts (don't generate or post)
php artisan app:process-auto-comments --fetch-only

# Only post scheduled comments (don't fetch)
php artisan app:process-auto-comments --post-only

# Run for specific user
php artisan app:process-auto-comments --user=1
```

**Data Storage:**
- Table: `auto_comment_posts`
- Page: `/auto-comment`
- Shows: Posts found, comments generated, posting status

---

## 📊 Summary Table

| Feature | Command | Schedule | Data Table | Page |
|---------|---------|----------|------------|------|
| **Inspiration Library** | `app:fetch-linkedin-feeds` | Twice daily (12:15 PM, 6:15 PM) | `viral_posts` | `/inspiration` |
| **AI Auto-Comment** | `app:process-auto-comments` | Every hour | `auto_comment_posts` | `/auto-comment` |

---

## 🎯 Quick Testing

### Test Inspiration Library:
```bash
php artisan app:fetch-linkedin-feeds --user=1 --limit=10
```
Then check: http://127.0.0.1:8000/inspiration

### Test Auto-Comment:
```bash
php artisan app:process-auto-comments --user=1 --fetch-only
```
Then check: http://127.0.0.1:8000/auto-comment

---

## 🔍 Where Data Comes From

### Inspiration Page:
1. **Command runs:** `app:fetch-linkedin-feeds`
2. **Fetches from:** RapidAPI (with filters applied)
3. **Saves to:** `viral_posts` table
4. **Page reads from:** `viral_posts` table (shown via `InspirationController`)

### Auto-Comment Page:
1. **Command runs:** `app:process-auto-comments`
2. **Fetches from:** RapidAPI (keywords) + Official LinkedIn API (followed accounts)
3. **Saves to:** `auto_comment_posts` table
4. **Page reads from:** `auto_comment_posts` table (shown via `AutoCommentController`)

---

## ⚡ Both Use Filtered RapidAPI

Both commands now use **filtered RapidAPI requests** to save credits:
- ✅ `min_likes` filter sent to API
- ✅ `limit` to stop at needed posts
- ✅ Early stop when limit reached
- ✅ No processing unnecessary posts


