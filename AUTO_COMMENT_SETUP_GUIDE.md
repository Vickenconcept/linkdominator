# 🤖 AI Auto-Comment Setup & Testing Guide

## 📋 Environment Variables

Add these to your `.env` file:

```env
# OpenAI/ChatGPT API Key (for generating comments)
OPENAI_KEY=sk-your-openai-api-key-here

# LinkedIn API Credentials (if using OAuth integration)
LINKEDIN_API=your-linkedin-api-key
LINKEDIN_CLIENT=your-linkedin-client-id
LINKEDIN_SECRET=your-linkedin-client-secret

# RapidAPI Key (optional - for keyword search if needed)
RAPIDAPI_KEY=your-rapidapi-key
```

## 🚀 Quick Start Testing

### 1. Run Database Migrations

```bash
php artisan migrate
```

### 2. Configure User Preferences

1. Log into your app
2. Go to **AI Auto Comments** in the sidebar
3. Click **Preferences**
4. Set up your preferences:
   - **Followed Accounts**: Add LinkedIn URNs (e.g., `urn:li:person:ABC123`)
   - **Keywords**: Add topics you're interested in
   - **Enable Auto-Commenting**: Toggle ON
   - **Comment Style/Tone**: Choose your preferred style
   - **Posting Times**: Set when comments should be posted (e.g., `9,14,18` for 9am, 2pm, 6pm)

### 3. Test the Command

#### Option A: Test Everything
```bash
php artisan app:process-auto-comments
```

#### Option B: Test Post Fetching Only
```bash
php artisan app:process-auto-comments --fetch-only
```

#### Option C: Test Comment Posting Only
```bash
php artisan app:process-auto-comments --post-only
```

#### Option D: Test for Specific User
```bash
php artisan app:process-auto-comments --user=1
```

## 📊 Monitoring & Logs

### View Logs

All activity is logged to `storage/logs/laravel.log`. Key log entries include:

- **📥 POSTS FOUND**: When new posts are discovered
- **💬 COMMENT GENERATED**: When AI generates a comment
- **⏰ COMMENT SCHEDULED**: When a comment is scheduled for later
- **✅ COMMENT POSTED**: When a comment is successfully posted to LinkedIn
- **❌ ERRORS**: Any failures with details

### Check Logs in Real-Time

```bash
# Windows PowerShell
Get-Content storage\logs\laravel.log -Tail 50 -Wait

# Linux/Mac
tail -f storage/logs/laravel.log | grep "AUTO-COMMENT"
```

### View Activity in Dashboard

1. Go to **AI Auto Comments** page
2. See:
   - Statistics (Posts Found, Pending, Scheduled, Posted)
   - List of all matched posts
   - Status of each post
   - Generated comments
   - When comments were/will be posted

## 🔍 What to Look For

### In Console Output:
```
🤖 Starting Auto-Comment Processing...
📥 Fetching new posts...
  👤 Processing for: John Doe
    🔍 Fetching posts from: urn:li:person:ABC123
    ✅ Saved post #1: urn:li:activity:123456 (Engagement: 150)
💬 Generating AI comments...
  ✅ Comment generated for post #1 (245 chars)
📤 Scheduling and posting comments...
  ✅ Comment posted successfully! Comment URN: urn:li:comment:789
📊 PROCESSING SUMMARY:
   Posts Found: 5
   Comments Generated: 5
   Comments Posted: 2
   Comments Scheduled: 3
```

### In Log File:
```
[2025-10-29 13:00:00] local.INFO: 🤖 AUTO-COMMENT PROCESSING STARTED
[2025-10-29 13:00:01] local.INFO: Post saved for commenting {"user_id":1,"post_id":1,"post_urn":"urn:li:activity:123456"}
[2025-10-29 13:00:02] local.INFO: Comment generated successfully {"post_id":1,"comment_length":245}
[2025-10-29 13:00:03] local.INFO: ✅ COMMENT POSTED SUCCESSFULLY {"post_id":1,"comment_urn":"urn:li:comment:789"}
```

## ⚙️ Scheduling

To run automatically, add to `routes/console.php`:

```php
use Illuminate\Console\Scheduling\Schedule;

Schedule::command('app:process-auto-comments')->hourly();
// Or run every 15 minutes:
// Schedule::command('app:process-auto-comments')->everyFifteenMinutes();
```

Then start the scheduler:
```bash
php artisan schedule:work
```

## 🐛 Troubleshooting

### No Posts Found?
- ✅ Check that user has `access_token` in database
- ✅ Verify followed accounts are correct LinkedIn URNs
- ✅ Ensure preferences are active (`is_active = true`)
- ✅ Check engagement threshold isn't too high
- ✅ Verify post age filters aren't excluding all posts

### Comments Not Generating?
- ✅ Check `OPENAI_KEY` is set in `.env`
- ✅ Verify OpenAI API key has credits
- ✅ Check logs for moderation failures

### Comments Not Posting?
- ✅ Verify LinkedIn access token is valid
- ✅ Check actor URN is correct (user's LinkedIn ID)
- ✅ Ensure daily limit hasn't been reached
- ✅ Verify posting times are in correct timezone

### See Errors?
- ✅ Check `storage/logs/laravel.log` for full error details
- ✅ Verify all environment variables are set
- ✅ Ensure database tables exist (run migrations)

## 📝 Notes

- **URN Format**: LinkedIn URNs should be in format `urn:li:person:ABC123` or `urn:li:organization:XYZ456`
- **Access Tokens**: Users need valid LinkedIn OAuth tokens stored in `users.access_token`
- **Rate Limits**: Be mindful of LinkedIn API rate limits
- **Testing**: Use `--fetch-only` and `--post-only` flags to test specific parts
- **Logs**: All actions are logged for debugging and monitoring

