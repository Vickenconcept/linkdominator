<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

class ResetDailyEmailScrapingCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:reset-daily-scraping-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily email scraping counts for all users at the start of each new day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Resetting daily email scraping counts for ALL users...');
        
        try {
            $today = now()->toDateString();
            $resetCount = 0;
            $totalUsers = User::count();
            $usersWithCounts = 0;
            
            $this->info("Today's date: {$today}");
            $this->info("Total users in database: {$totalUsers}");
            $this->newLine();
            
            // Reset ALL users regardless of reset date
            $users = User::all();
            
            foreach ($users as $user) {
                $oldCount = $user->daily_profile_email_scraping_count ?? 0;
                
                // Always reset to 0 and update reset date
                $user->update([
                    'daily_profile_email_scraping_count' => 0,
                    'daily_profile_email_scraping_reset_at' => $today
                ]);
                
                $resetCount++;
                
                if ($oldCount > 0) {
                    $usersWithCounts++;
                    $this->line("  ✅ User #{$user->id}: Reset count from {$oldCount} to 0");
                    Log::info('Reset daily email scraping count', [
                        'user_id' => $user->id,
                        'old_count' => $oldCount,
                        'reset_date' => $today
                    ]);
                }
            }
            
            $this->newLine();
            $this->info("✅ Reset daily counts for {$resetCount} user(s)");
            if ($usersWithCounts > 0) {
                $this->info("   {$usersWithCounts} user(s) had counts > 0 that were reset");
            }
            
            // Verify all counts are 0
            $remainingCounts = User::where('daily_profile_email_scraping_count', '>', 0)->count();
            if ($remainingCounts > 0) {
                $this->newLine();
                $this->warn("⚠️  Warning: {$remainingCounts} user(s) still have counts > 0 after reset!");
            } else {
                $this->info("   ✓ All users now have count = 0");
            }
            
            Log::info('Daily email scraping counts reset completed', [
                'reset_count' => $resetCount,
                'users_with_counts_reset' => $usersWithCounts,
                'reset_date' => $today
            ]);
            
            return 0;
        } catch (\Throwable $th) {
            $this->error('Error resetting daily counts: ' . $th->getMessage());
            Log::error('ResetDailyEmailScrapingCounts error', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return 1;
        }
    }
}
