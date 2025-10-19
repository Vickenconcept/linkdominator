
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════\n";
echo "📊 CHECKING AUTHORS IN DATABASE\n";
echo "═══════════════════════════════════════════════\n";
echo "Total posts: " . \App\Models\ViralPost::count() . "\n\n";

echo "Posts by author:\n";
echo "═══════════════════════════════════════════════\n";

$authors = \App\Models\ViralPost::selectRaw('author_name, COUNT(*) as count, MAX(likes) as max_likes')
    ->groupBy('author_name')
    ->orderBy('count', 'desc')
    ->limit(15)
    ->get();

foreach($authors as $author) {
    $name = $author->author_name ?: 'Unknown';
    echo sprintf("%-30s: %3d posts (top: %s likes)\n", 
        $name, 
        $author->count, 
        number_format($author->max_likes)
    );
}

echo "\n";
echo "Sample posts with URLs:\n";
echo "═══════════════════════════════════════════════\n";

$samples = \App\Models\ViralPost::orderBy('likes', 'desc')->limit(5)->get(['author_name', 'author_profile_url', 'likes']);

foreach($samples as $post) {
    echo "Author: " . ($post->author_name ?: 'Unknown') . "\n";
    echo "Profile: " . ($post->author_profile_url ?: 'N/A') . "\n";
    echo "Likes: " . number_format($post->likes) . "\n\n";
}

echo "═══════════════════════════════════════════════\n";

