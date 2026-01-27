# 🔧 PhantomBuster Rate Limit Fix Guide

## 🚨 **Problem**

**Error:** `Agent maximum parallel executions limit of 1 (agent limit) reached`

**Root Cause:**
- Multiple `FetchAudienceEmailJob` jobs are processed simultaneously by queue workers
- Each job tries to launch a PhantomBuster phantom
- Your PhantomBuster account only allows **1 parallel execution**
- When multiple workers process jobs at the same time, they all try to launch phantoms, hitting the rate limit (429)

---

## ✅ **Solutions** (Choose One or Combine)

### **Solution 1: Use Dedicated Queue with Single Worker** ⭐ **RECOMMENDED**

This is the simplest and most effective solution.

#### Step 1: Update Job to Use Dedicated Queue

Modify `app/Jobs/FetchAudienceEmailJob.php`:

```php
// Change all dispatch calls to use 'phantombuster' queue
FetchAudienceEmailJob::dispatch($item->id, $item->con_public_identifier)
    ->onQueue('phantombuster'); // Changed from 'default'
```

**Files to update:**
- `app/Http/Controllers/LeadController.php` (line 231)
- `app/Http/Controllers/LinkedInCompetitorController.php` (line 289)
- `app/Http/Controllers/ChromeApiController.php` (line 477)
- `app/Console/Commands/FetchMissingAudienceEmails.php` (line 55)

#### Step 2: Run Single Queue Worker for PhantomBuster Queue

```bash
# Only process phantombuster queue with 1 worker
php artisan queue:work --queue=phantombuster --tries=3 --timeout=600
```

#### Step 3: Update Supervisor Config (Production)

```ini
[program:linkdominator-phantombuster-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --queue=phantombuster --sleep=3 --tries=3 --max-time=3600 --timeout=600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/phantombuster-queue.log
stopwaitsecs=3600
```

**Benefits:**
- ✅ Only 1 job processes at a time
- ✅ No code changes to service layer
- ✅ Easy to monitor and manage
- ✅ Other queues can still run with multiple workers

---

### **Solution 2: Add Rate Limiting to Job** 

Add Laravel's rate limiting to prevent concurrent executions.

#### Update `app/Jobs/FetchAudienceEmailJob.php`:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchAudienceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Retry up to 3 times
    public $timeout = 600; // 10 minutes timeout
    public $backoff = [60, 120, 300]; // Wait 1min, 2min, 5min between retries

    public int $audienceListItemId;
    public string $publicIdentifier;

    public function __construct(int $audienceListItemId, string $publicIdentifier)
    {
        $this->audienceListItemId = $audienceListItemId;
        $this->publicIdentifier = $publicIdentifier;
    }

    public function handle(): void
    {
        // Rate limiting: Only allow 1 phantom launch at a time
        $lockKey = 'phantombuster:launch:lock';
        $lockTimeout = 600; // 10 minutes max wait
        
        // Try to acquire lock (wait up to 10 minutes)
        $acquired = false;
        $waitTime = 0;
        $maxWait = 600; // 10 minutes
        
        while (!$acquired && $waitTime < $maxWait) {
            $acquired = Cache::lock($lockKey, $lockTimeout)->get();
            
            if (!$acquired) {
                // Wait 5 seconds before retrying
                sleep(5);
                $waitTime += 5;
                Log::info('FetchAudienceEmailJob: Waiting for PhantomBuster lock', [
                    'audience_list_id' => $this->audienceListItemId,
                    'wait_time' => $waitTime
                ]);
            }
        }
        
        if (!$acquired) {
            Log::error('FetchAudienceEmailJob: Could not acquire PhantomBuster lock after timeout', [
                'audience_list_id' => $this->audienceListItemId
            ]);
            throw new \Exception('PhantomBuster is busy. Please try again later.');
        }
        
        try {
            // Your existing handle() logic here...
            // ... (rest of the code)
            
        } finally {
            // Always release the lock
            Cache::lock($lockKey)->release();
        }
    }
}
```

**Benefits:**
- ✅ Prevents concurrent phantom launches
- ✅ Jobs wait in queue until lock is available
- ✅ Works with multiple queue workers

**Drawbacks:**
- ⚠️ Jobs may wait longer in queue
- ⚠️ Requires Redis or Memcached for distributed locks

---

### **Solution 3: Add Delays Between Job Dispatches**

Space out job dispatches to prevent simultaneous launches.

#### Update `app/Console/Commands/FetchMissingAudienceEmails.php`:

```php
foreach ($items->chunk($batchSize) as $chunk) {
    foreach ($chunk as $index => $item) {
        try {
            // Add delay: 30 seconds between each job
            $delay = $index * 30; // 0, 30, 60, 90 seconds...
            
            FetchAudienceEmailJob::dispatch($item->id, $item->con_public_identifier)
                ->onQueue('default')
                ->delay(now()->addSeconds($delay));
            
            $dispatched++;
            $bar->advance();
        } catch (\Throwable $th) {
            // ... error handling
        }
    }
    
    // Wait between batches to ensure previous batch completes
    if ($chunk !== $items->last()) {
        sleep(60); // Wait 1 minute between batches
    }
}
```

**Benefits:**
- ✅ Simple to implement
- ✅ Spreads out phantom launches

**Drawbacks:**
- ⚠️ Doesn't prevent concurrent launches from other sources (web requests)
- ⚠️ Fixed delays may not match actual phantom execution time

---

### **Solution 4: Check Running Phantoms Before Launch** (Advanced)

Add logic to check if a phantom is already running before launching a new one.

#### Add method to `PhantomBusterService.php`:

```php
/**
 * Check if any phantoms are currently running
 */
public function hasRunningPhantoms(string $phantomId): bool
{
    try {
        // Get list of running containers for this phantom
        $url = "{$this->apiUrl}/agent/{$phantomId}/containers";
        
        $response = Http::timeout(10)
            ->withHeaders([
                'X-Phantombuster-Key-1' => $this->apiKey,
            ])
            ->get($url);
        
        if ($response->successful()) {
            $containers = $response->json();
            // Check if any containers are in "running" state
            if (is_array($containers)) {
                foreach ($containers as $container) {
                    $status = $container['status'] ?? $container['state'] ?? null;
                    if (in_array($status, ['running', 'pending', 'queued'])) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    } catch (\Exception $e) {
        Log::warning('PhantomBuster: Could not check running phantoms', [
            'error' => $e->getMessage()
        ]);
        // If we can't check, assume no phantoms are running (fail open)
        return false;
    }
}
```

#### Update `launchPhantom()` method:

```php
public function launchPhantom(string $phantomId, array $arguments = []): array
{
    // Check if phantom is already running
    if ($this->hasRunningPhantoms($phantomId)) {
        throw new \Exception(
            'PhantomBuster phantom is already running. ' .
            'Please wait for the current execution to finish before launching a new one.'
        );
    }
    
    // ... rest of existing code
}
```

**Benefits:**
- ✅ Prevents launching when phantom is already running
- ✅ More accurate than fixed delays

**Drawbacks:**
- ⚠️ Requires additional API call (may have rate limits)
- ⚠️ More complex implementation

---

## 🎯 **Recommended Approach**

**For Production:** Use **Solution 1** (Dedicated Queue) + **Solution 2** (Rate Limiting)

1. **Dedicated queue** ensures only 1 worker processes PhantomBuster jobs
2. **Rate limiting** provides additional safety if multiple workers accidentally run
3. **Easy to monitor** - separate queue logs

---

## 📋 **Implementation Steps**

### **Quick Fix (Solution 1 Only):**

1. Update all `FetchAudienceEmailJob::dispatch()` calls to use `->onQueue('phantombuster')`
2. Run queue worker: `php artisan queue:work --queue=phantombuster`
3. Update Supervisor config for production

### **Complete Fix (Solution 1 + 2):**

1. Implement Solution 1 (dedicated queue)
2. Add rate limiting to `FetchAudienceEmailJob` (Solution 2)
3. Ensure Redis/Memcached is configured for locks
4. Test with multiple jobs

---

## 🧪 **Testing**

### Test Rate Limiting:

```bash
# Dispatch multiple jobs quickly
php artisan tinker
>>> for($i=1; $i<=5; $i++) { \App\Jobs\FetchAudienceEmailJob::dispatch($i, 'test-user')->onQueue('phantombuster'); }
```

### Monitor Queue:

```bash
# Watch queue processing
php artisan queue:work --queue=phantombuster --verbose

# Check queue status
php artisan queue:monitor phantombuster
```

---

## 📊 **Monitoring**

### Check for Rate Limit Errors:

```bash
# Search logs for 429 errors
grep "429" storage/logs/laravel.log | grep "PhantomBuster"
```

### Monitor Queue Worker:

```bash
# Check if worker is running
ps aux | grep "queue:work.*phantombuster"

# Check queue size
php artisan queue:size phantombuster
```

---

## ⚠️ **Important Notes**

1. **PhantomBuster Plan Limits:**
   - Free/Basic: 1 parallel execution
   - Pro: More parallel executions (check your plan)
   - Consider upgrading if you need more throughput

2. **Job Timeout:**
   - Phantom scraping can take 5-10 minutes
   - Set `$timeout = 600` (10 minutes) in job

3. **Retry Logic:**
   - Jobs will retry on failure
   - Rate limit errors should use exponential backoff
   - Consider `$backoff = [60, 120, 300]` (wait 1min, 2min, 5min)

4. **Queue Configuration:**
   - Use `database` queue for reliability
   - Or `redis` for better performance
   - Ensure `jobs` table exists: `php artisan migrate`

---

## 🔄 **Alternative: Upgrade PhantomBuster Plan**

If you need to process many emails simultaneously, consider upgrading your PhantomBuster plan to allow more parallel executions.

**Check your plan limits:**
- Log into PhantomBuster dashboard
- Check "Parallel Executions" limit
- Upgrade if needed

---

**Last Updated:** 2026-01-14
**Related Files:**
- `app/Jobs/FetchAudienceEmailJob.php`
- `app/Services/PhantomBusterService.php`
- `app/Http/Controllers/LeadController.php`
- `app/Http/Controllers/LinkedInCompetitorController.php`
- `app/Http/Controllers/ChromeApiController.php`
- `app/Console/Commands/FetchMissingAudienceEmails.php`
