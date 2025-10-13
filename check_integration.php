<?php
/**
 * Quick Integration Check Script
 * Run this to see if LinkedIn is connected
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n🔍 Checking LinkedIn Integration Status...\n";
echo "==========================================\n\n";

try {
    $integrations = DB::table('integrations')
        ->where('oauth_provider', 'linkedin')
        ->get();

    if ($integrations->isEmpty()) {
        echo "❌ NO LINKEDIN INTEGRATIONS FOUND\n";
        echo "\n📝 What to do:\n";
        echo "   1. Go to: http://your-domain/social-account\n";
        echo "   2. Click 'Connect LinkedIn'\n";
        echo "   3. Authorize the app\n";
        echo "   4. Run this script again\n\n";
    } else {
        echo "✅ Found " . $integrations->count() . " LinkedIn integration(s)\n\n";
        
        foreach ($integrations as $integration) {
            echo "Integration ID: {$integration->id}\n";
            echo "User ID: {$integration->user_id}\n";
            echo "OAuth UID: {$integration->oauth_uid}\n";
            echo "Name: {$integration->first_name} {$integration->last_name}\n";
            echo "Email: {$integration->email}\n";
            echo "Connected Status: " . ($integration->connected_status ? '✅ Active' : '❌ Inactive') . "\n";
            echo "Has Access Token: " . (!empty($integration->access_token) ? '✅ Yes' : '❌ No') . "\n";
            echo "Has Refresh Token: " . (!empty($integration->refresh_token) ? '✅ Yes' : '❌ No') . "\n";
            echo "Expires In: {$integration->expires_in} seconds\n";
            echo "Created: {$integration->created_at}\n";
            echo "Updated: {$integration->updated_at}\n";
            echo str_repeat('-', 50) . "\n\n";
        }
        
        echo "✅ LinkedIn is connected! You can now post.\n\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo "==========================================\n\n";

