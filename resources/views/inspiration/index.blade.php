@extends('layout.auth')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">💡 Inspiration Library</h2>
        <p class="text-sm text-gray-500 mt-1">Discover viral LinkedIn posts and use them as inspiration</p>
    </div>
    <a href="{{ route('content-creator.create') }}" 
       class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Create New Post
    </a>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
                <i class="fas fa-bookmark text-orange-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Saved Posts</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_posts'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
                <i class="fas fa-fire text-red-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Viral Posts</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['viral_posts'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
                <i class="fas fa-star text-yellow-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Favorites</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['favorites'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-chart-line text-blue-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Avg Engagement</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_engagement'], 1) }}%</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <form method="GET" action="{{ route('inspiration.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search posts, authors, keywords..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>
        
        <!-- Category Filter -->
        <select name="category" 
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
            <option value="">All Categories</option>
            <option value="marketing" {{ request('category') == 'marketing' ? 'selected' : '' }}>Marketing</option>
            <option value="sales" {{ request('category') == 'sales' ? 'selected' : '' }}>Sales</option>
            <option value="tech" {{ request('category') == 'tech' ? 'selected' : '' }}>Tech</option>
            <option value="entrepreneurship" {{ request('category') == 'entrepreneurship' ? 'selected' : '' }}>Entrepreneurship</option>
            <option value="leadership" {{ request('category') == 'leadership' ? 'selected' : '' }}>Leadership</option>
            <option value="productivity" {{ request('category') == 'productivity' ? 'selected' : '' }}>Productivity</option>
        </select>
        
        <!-- Engagement Filter -->
        <select name="engagement" 
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
            <option value="">All Engagement</option>
            <option value="10" {{ request('engagement') == '10' ? 'selected' : '' }}>🔥 10%+ (Viral)</option>
            <option value="5" {{ request('engagement') == '5' ? 'selected' : '' }}>⚡ 5%+ (High)</option>
            <option value="3" {{ request('engagement') == '3' ? 'selected' : '' }}>✨ 3%+ (Good)</option>
        </select>
        
        <!-- Date Filter -->
        <select name="days" 
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
            <option value="">All Time</option>
            <option value="7" {{ request('days') == '7' ? 'selected' : '' }}>Last 7 days</option>
            <option value="30" {{ request('days') == '30' ? 'selected' : '' }}>Last 30 days</option>
            <option value="90" {{ request('days') == '90' ? 'selected' : '' }}>Last 90 days</option>
        </select>
        
        <div class="md:col-span-5 flex items-center justify-between">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" 
                       name="favorite" 
                       value="1"
                       {{ request('favorite') ? 'checked' : '' }}
                       class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500">
                <span class="ml-2 text-sm text-gray-700">
                    <i class="fas fa-star text-yellow-500"></i> Favorites only
                </span>
            </label>
            
            <div class="flex space-x-2">
                <button type="submit" 
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md transition-colors">
                    <i class="fas fa-search mr-1"></i>Filter
                </button>
                <a href="{{ route('inspiration.index') }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors">
                    <i class="fas fa-redo mr-1"></i>Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- How to Use Guide (Show only if no posts yet) -->
@if($stats['total_posts'] == 0)
<div class="bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-200 rounded-lg p-6 mb-8">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-lightbulb text-orange-500 text-2xl"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">How to Build Your Inspiration Library</h3>
            <div class="space-y-2 text-sm text-gray-700">
                <p><strong>Step 1:</strong> Browse LinkedIn feed and find high-engagement posts</p>
                <p><strong>Step 2:</strong> Click the <span class="px-2 py-1 bg-orange-500 text-white rounded text-xs">Save to LinkDominator</span> button (from Chrome extension)</p>
                <p><strong>Step 3:</strong> Posts appear here with engagement metrics</p>
                <p><strong>Step 4:</strong> Click "Use as Inspiration" to remix them in your voice</p>
            </div>
            <div class="mt-4 p-3 bg-white rounded-lg">
                <p class="text-xs text-gray-600"><strong>💡 Pro Tip:</strong> Save posts with 1000+ likes for best inspiration. The Chrome extension will analyze engagement automatically!</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Viral Posts Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($posts as $post)
    <div class="bg-white rounded-lg shadow hover:shadow-xl transition-all relative overflow-hidden">
        <!-- Engagement Badge -->
        <div class="absolute top-3 right-3 z-10">
            <span class="px-3 py-1 rounded-full text-xs font-bold shadow-lg
                @if($post->engagement_rate >= 10) bg-gradient-to-r from-red-500 to-pink-500 text-white
                @elseif($post->engagement_rate >= 5) bg-gradient-to-r from-orange-500 to-yellow-500 text-white
                @else bg-gradient-to-r from-blue-500 to-cyan-500 text-white @endif">
                @if($post->engagement_rate >= 10) 🔥 @elseif($post->engagement_rate >= 5) ⚡ @else ✨ @endif
                {{ number_format($post->engagement_rate, 1) }}%
            </span>
        </div>

        <!-- Favorite Star -->
        <div class="absolute top-3 left-3 z-10">
            <button onclick="toggleFavorite({{ $post->id }})" 
                    class="p-2 bg-white rounded-full shadow-md hover:scale-110 transition-transform">
                <i class="fas fa-star {{ $post->is_favorite ? 'text-yellow-500' : 'text-gray-300' }}"></i>
            </button>
        </div>

        <div class="p-6 pt-14">
            <!-- Author Info -->
            <div class="flex items-center mb-4">
                @if($post->author_image_url)
                <img src="{{ $post->author_image_url }}" 
                     alt="{{ $post->author_name }}" 
                     class="w-10 h-10 rounded-full border-2 border-gray-200">
                @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-pink-400 flex items-center justify-center text-white font-bold">
                    {{ substr($post->author_name, 0, 1) }}
                </div>
                @endif
                <div class="ml-3 flex-1">
                    <div class="text-sm font-semibold text-gray-900">{{ $post->author_name }}</div>
                    @if($post->author_headline)
                    <div class="text-xs text-gray-500">{{ Str::limit($post->author_headline, 40) }}</div>
                    @endif
                </div>
            </div>

            <!-- Post Content Preview -->
            <div class="mb-4">
                <p class="text-sm text-gray-700 line-clamp-5">{{ $post->content }}</p>
            </div>

            <!-- Post Media -->
            @if($post->post_type === 'image' && $post->images && count($post->images) > 0)
            <div class="mb-4">
                <img src="{{ $post->images[0] }}" alt="Post image" class="w-full h-40 object-cover rounded-lg">
            </div>
            @endif
            
            @if($post->post_type === 'carousel' && $post->images && count($post->images) > 0)
            <div class="mb-4">
                <div class="grid grid-cols-3 gap-2">
                    @foreach(array_slice($post->images, 0, 3) as $image)
                    <img src="{{ $image }}" alt="Carousel image" class="w-full h-20 object-cover rounded">
                    @endforeach
                </div>
                @if(count($post->images) > 3)
                <div class="text-xs text-gray-500 mt-1">
                    +{{ count($post->images) - 3 }} more images
                </div>
                @endif
            </div>
            @endif

            <!-- Engagement Metrics -->
            <div class="grid grid-cols-4 gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-900">{{ number_format($post->likes) }}</div>
                    <div class="text-xs text-gray-500">Likes</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-900">{{ number_format($post->comments) }}</div>
                    <div class="text-xs text-gray-500">Comments</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-900">{{ number_format($post->shares) }}</div>
                    <div class="text-xs text-gray-500">Shares</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-900">{{ number_format($post->views) }}</div>
                    <div class="text-xs text-gray-500">Views</div>
                </div>
            </div>

            <!-- Post Meta -->
            <div class="flex items-center justify-between mb-4 text-xs text-gray-500">
                <div class="flex items-center space-x-2">
                    @if($post->category)
                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded">
                        {{ ucfirst($post->category) }}
                    </span>
                    @endif
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">
                        {{ ucfirst($post->post_type) }}
                    </span>
                </div>
                <span>{{ $post->saved_at->diffForHumans() }}</span>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <button onclick="useAsInspiration({{ $post->id }})" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-md transition-all flex items-center justify-center font-medium">
                    <i class="fas fa-lightbulb mr-2"></i>Use as Inspiration
                </button>
                
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="remixPost({{ $post->id }})" 
                            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                        <i class="fas fa-magic mr-1"></i>AI Remix
                    </button>
                    
                    @if($post->post_url)
                    <a href="{{ $post->post_url }}" 
                       target="_blank"
                       class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-md transition-colors text-center">
                        <i class="fab fa-linkedin mr-1"></i>View
                    </a>
                    @endif
                </div>
                
                <button onclick="deletePost({{ $post->id }})" 
                        class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md transition-colors">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16">
        <div class="text-gray-400 mb-4">
            <i class="fas fa-bookmark text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No viral posts saved yet</h3>
        <p class="text-gray-500 mb-6">Start saving high-performing LinkedIn posts for inspiration</p>
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md mx-auto text-left">
            <h4 class="font-semibold text-gray-900 mb-3">How to save viral posts:</h4>
            <ol class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs mr-2">1</span>
                    <span>Install LinkDominator Chrome Extension</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs mr-2">2</span>
                    <span>Browse LinkedIn feed</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs mr-2">3</span>
                    <span>Click "Save to LinkDominator" on posts with high engagement</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs mr-2">4</span>
                    <span>Posts appear here instantly!</span>
                </li>
            </ol>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($posts->hasPages())
<div class="mt-8">
    {{ $posts->appends(request()->query())->links() }}
</div>
@endif

<!-- Remix Modal -->
<div id="remixModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">AI Remix Post</h3>
                    <button onclick="closeRemixModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Rewrite in your voice:
                    </label>
                    <select id="remixTone" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="professional">Professional</option>
                        <option value="casual">Casual</option>
                        <option value="motivational">Motivational</option>
                        <option value="educational">Educational</option>
                        <option value="storytelling">Storytelling</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Original Post:
                    </label>
                    <div id="originalContent" class="p-4 bg-gray-50 rounded-lg text-sm text-gray-700 max-h-40 overflow-y-auto">
                        <!-- Will be filled by JavaScript -->
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Remixed Version:
                    </label>
                    <textarea id="remixedContent" 
                              rows="8" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                              readonly></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeRemixModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button onclick="generateRemix()" 
                            id="generateRemixBtn"
                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md transition-colors">
                        <span id="generateRemixText">
                            <i class="fas fa-magic mr-2"></i>Generate Remix
                        </span>
                        <span id="generateRemixLoading" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Generating...
                        </span>
                    </button>
                    <button onclick="useRemixedContent()" 
                            id="useRemixBtn"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors hidden">
                        <i class="fas fa-check mr-2"></i>Use This
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '{{ csrf_token() }}';
let currentRemixPostId = null;

// Use viral post as inspiration (copy to Content Creator)
function useAsInspiration(postId) {
    fetch(`/inspiration/use/${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store in sessionStorage
                sessionStorage.setItem('inspiration_content', data.content);
                sessionStorage.setItem('inspiration_author', data.author);
                sessionStorage.setItem('inspiration_engagement', JSON.stringify(data.engagement));
                
                // Redirect to Content Creator
                window.location.href = '{{ route('content-creator.create') }}?from=inspiration';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading inspiration');
        });
}

// Open remix modal
function remixPost(postId) {
    currentRemixPostId = postId;
    
    // Find post data
    fetch(`/inspiration/use/${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('originalContent').textContent = data.content;
                document.getElementById('remixedContent').value = '';
                document.getElementById('useRemixBtn').classList.add('hidden');
                document.getElementById('remixModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading post');
        });
}

// Generate AI remix
function generateRemix() {
    if (!currentRemixPostId) return;
    
    const tone = document.getElementById('remixTone').value;
    const btn = document.getElementById('generateRemixBtn');
    const btnText = document.getElementById('generateRemixText');
    const btnLoading = document.getElementById('generateRemixLoading');
    
    btn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    
    fetch(`/inspiration/remix/${currentRemixPostId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ tone: tone })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
        
        if (data.success) {
            document.getElementById('remixedContent').value = data.content;
            document.getElementById('useRemixBtn').classList.remove('hidden');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
        console.error('Error:', error);
        alert('Error generating remix');
    });
}

// Use remixed content
function useRemixedContent() {
    const content = document.getElementById('remixedContent').value;
    
    // Store in sessionStorage
    sessionStorage.setItem('inspiration_content', content);
    sessionStorage.setItem('inspiration_remixed', 'true');
    
    // Redirect to Content Creator
    window.location.href = '{{ route('content-creator.create') }}?from=inspiration&remixed=true';
}

// Close remix modal
function closeRemixModal() {
    document.getElementById('remixModal').classList.add('hidden');
    currentRemixPostId = null;
}

// Toggle favorite
function toggleFavorite(postId) {
    fetch(`/inspiration/favorite/${postId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Delete post
function deletePost(postId) {
    if (confirm('Remove this post from your inspiration library?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/inspiration/delete/${postId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

