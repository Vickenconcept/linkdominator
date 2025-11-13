# 📘 VIRAL POSTS COMMAND - USAGE GUIDE

## 🚀 Complete Guide for Multi-User Environments

---

## 📋 COMMAND OVERVIEW

```bash
php artisan app:fetch-linkedin-feeds [OPTIONS]
```

### **Available Options:**

| Flag | Default | Description | Best For |
|------|---------|-------------|----------|
| `--user=ID` | All users | Fetch for specific user only | Testing, VIP users |
| `--limit=N` | 50 | Max posts per user | Control quota usage |
| `--keywords=N` | 5 | Max keywords per user | Reduce API calls |
| `--system-wide` | false | Fetch once, share with all | Many users ⭐ |

---

## 📊 API QUOTA CALCULATIONS

### **Without Optimization:**
```
100 users × 10 keywords × 2 pages = 2,000 API requests
Result: Quota exhausted quickly! ❌
```

### **With Optimization:**
```bash
php artisan app:fetch-linkedin-feeds --system-wide --limit=200 --keywords=12

Calculation: 12 keywords × 2 pages = 24 API requests
Result: All 100 users get 200 posts to browse! ✅
Savings: 99% fewer API calls!
```

---

## 🎯 USAGE BY TEAM SIZE

### **1-10 Users** (Small Team)
```bash
# Each user gets personalized library
php artisan app:fetch-linkedin-feeds --limit=30 --keywords=3

API Usage: 3 keywords × 2 pages × 10 users = 60 requests
Monthly: ~1,800 requests (if daily)
Plan Needed: BASIC (500/mo) won't work, PRO (5,000/mo) ✅
```

### **10-50 Users** (Medium Team)
```bash
# System-wide mode (recommended)
php artisan app:fetch-linkedin-feeds --system-wide --limit=150 --keywords=10

API Usage: 10 keywords × 2 pages = 20 requests
Monthly: ~600 requests (if daily)
Plan Needed: BASIC won't work, PRO ✅
```

### **50-500 Users** (Large Team)
```bash
# System-wide only (must use)
php artisan app:fetch-linkedin-feeds --system-wide --limit=300 --keywords=15

API Usage: 15 keywords × 2 pages = 30 requests
Monthly: ~900 requests (if daily)
Plan Needed: PRO (5,000/mo) ✅
```

### **500+ Users** (Enterprise)
```bash
# System-wide + weekly instead of daily
php artisan app:fetch-linkedin-feeds --system-wide --limit=500 --keywords=20

API Usage: 20 keywords × 2 pages = 40 requests weekly
Monthly: ~160 requests
Plan Needed: BASIC might work, PRO safe ✅
```

---

## 💡 RECOMMENDED SETUPS

### **SCENARIO 1: Development/Testing**
```bash
# Test with single user
php artisan app:fetch-linkedin-feeds --user=1 --limit=10 --keywords=2

API Usage: ~4 requests
Time: ~15 seconds
```

### **SCENARIO 2: Production (Most Common)**
```bash
# Daily system-wide fetch
php artisan app:fetch-linkedin-feeds --system-wide --limit=200 --keywords=12

API Usage: ~24 requests/day
Monthly: ~720 requests
Users: Unlimited (all share library)
```

**Schedule it:**
```php
// app/Console/Kernel.php
$schedule->command('app:fetch-linkedin-feeds --system-wide --limit=200 --keywords=12')
         ->daily()
         ->at('02:00');
```

### **SCENARIO 3: Hybrid Approach**
```bash
# Daily system-wide for everyone
php artisan app:fetch-linkedin-feeds --system-wide --limit=150

# Weekly personalized for premium users
php artisan app:fetch-linkedin-feeds --limit=20 --keywords=3
```

**Schedule:**
```php
// Daily: System library
$schedule->command('app:fetch-linkedin-feeds --system-wide --limit=150')
         ->daily()->at('02:00');

// Weekly: Premium personalized
$schedule->command('app:fetch-linkedin-feeds --limit=20 --keywords=3')
         ->weekly()->sundays()->at('03:00');
```

---

## 🌐 SYSTEM-WIDE MODE (Recommended)

### **What It Does:**
- Fetches viral posts covering multiple industries
- Saves to `user_id = 1` (shared library)
- All users can browse and filter
- Uses ~24 API requests total (not per-user!)

### **How Users Access:**
1. Visit `/inspiration`
2. Browse system-wide library
3. Filter by category (Real Estate, Healthcare, etc.)
4. Find posts relevant to their industry
5. Still personalized via filtering!

### **Benefits:**
- ✅ **94-99% API savings**
- ✅ Works for unlimited users
- ✅ Still personalized (via filtering)
- ✅ Quality content for everyone

---

## 📊 API USAGE SUMMARY (After Command Runs)

```bash
═══════════════════════════════════════════════
📊 API USAGE SUMMARY:
   API Requests Made: 24
   Estimated Monthly Usage: 720 (if run daily)
   Time Elapsed: 45 seconds
═══════════════════════════════════════════════
```

**This helps you:**
- Monitor API usage
- Plan for upgrades
- Optimize settings

---

## 🎛️ OPTIMIZATION MATRIX

| Users | Mode | Limit | Keywords | API Requests | Monthly (Daily) |
|-------|------|-------|----------|--------------|-----------------|
| 1-5 | Per-user | 30 | 3 | 30 | 900 |
| 5-20 | Per-user | 20 | 3 | 120 | 3,600 ❌ |
| 10-50 | System-wide | 150 | 10 | 20 | 600 ✅ |
| 50+ | System-wide | 200 | 12 | 24 | 720 ✅ |
| 100+ | System-wide | 300 | 15 | 30 | 900 ✅ |

✅ = Fits in PRO plan (5,000/month)  
❌ = Needs ULTRA plan

---

## 🔧 PRODUCTION DEPLOYMENT

### **Recommended for Your App:**

#### **1. Set Default in Code:**
```php
// app/Console/Commands/FetchLinkedinFeeds.php
// Change line 306:
return \App\Models\User::where('id', 1)->get();
// This makes it system-wide by default
```

#### **2. Or Use Flag in Cron:**
```php
// app/Console/Kernel.php
$schedule->command('app:fetch-linkedin-feeds --system-wide --limit=200 --keywords=12')
         ->daily()
         ->at('02:00');
```

#### **3. Monitor Usage:**
Check logs after each run to see API request count

---

## 🎯 QUICK REFERENCE

### **Common Commands:**

```bash
# Most efficient (for many users)
php artisan app:fetch-linkedin-feeds --system-wide

# Specific user
php artisan app:fetch-linkedin-feeds --user=5

# Conservative (save API quota)
php artisan app:fetch-linkedin-feeds --system-wide --limit=100 --keywords=8

# Aggressive (build large library)
php artisan app:fetch-linkedin-feeds --system-wide --limit=500 --keywords=20

# Test mode
php artisan app:fetch-linkedin-feeds --user=1 --limit=5 --keywords=1
```

---

## ✅ FINAL SETUP RECOMMENDATION

### **For Production with Multiple Users:**

```bash
php artisan app:fetch-linkedin-feeds --system-wide --limit=200 --keywords=12
```

**Result:**
- ✅ 200 viral posts (past month, 100+ likes)
- ✅ ~24 API requests only
- ✅ All users can browse
- ✅ Filter by industry/category
- ✅ Monthly usage: ~720 requests (fits PRO plan)

**Schedule:**
```php
$schedule->command('app:fetch-linkedin-feeds --system-wide --limit=200 --keywords=12')
         ->daily()->at('02:00');
```

---

**The system is now production-ready and optimized for multi-user environments!** 🎉


