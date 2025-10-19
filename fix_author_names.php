<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════\n";
echo "🔧 FIXING AUTHOR NAMES\n";
echo "═══════════════════════════════════════════════\n";

$posts = \App\Models\ViralPost::where('author_name', 'Unknown')->get();

echo "Found {$posts->count()} posts with 'Unknown' author\n";
echo "Updating...\n\n";

$updated = 0;

foreach ($posts as $post) {
    if ($post->author_profile_url) {
        // Extract name from URL
        preg_match('/linkedin\.com\/in\/([^\/\?]+)/', $post->author_profile_url, $matches);
        
        if (isset($matches[1])) {
            $username = $matches[1];
            
            // Convert to proper name
            $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $username);
            $name = str_replace(['-', '_'], ' ', $name);
            $name = ucwords($name);
            
            $post->update(['author_name' => $name]);
            $updated++;
            
            echo "✓ Updated: {$name} ({$username})\n";
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════════════\n";
echo "✅ Updated {$updated} author names\n";
echo "═══════════════════════════════════════════════\n";

