@extends('layout.auth')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Content Creator</h2>
    <a href="{{ route('content-creator.create') }}" 
       class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Create New Post
    </a>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <i class="fas fa-file-alt text-blue-600 dark:text-blue-400"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Posts</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_posts'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                <i class="fas fa-edit text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Drafts</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['draft_posts'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                <i class="fas fa-clock text-orange-600 dark:text-orange-400"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Scheduled</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['scheduled_posts'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Published</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['published_posts'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('content-creator.index', ['status' => 'all']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ $status === 'all' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All Posts
            </a>
            <a href="{{ route('content-creator.index', ['status' => 'draft']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ $status === 'draft' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Drafts
            </a>
            <a href="{{ route('content-creator.index', ['status' => 'scheduled']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ $status === 'scheduled' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Scheduled
            </a>
            <a href="{{ route('content-creator.index', ['status' => 'published']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ $status === 'published' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Published
            </a>
        </nav>
    </div>
</div>

<!-- Posts Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($posts as $post)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow">
        <div class="p-6">
            <!-- Post Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($post->status === 'draft') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($post->status === 'scheduled') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                        @elseif($post->status === 'published') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                        {{ ucfirst($post->status) }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        {{ ucfirst($post->post_type) }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $post->word_count }} words
                    </span>
                </div>
            </div>

            <!-- Post Content Preview -->
            <div class="mb-4">
                <p class="text-gray-900 dark:text-white text-sm line-clamp-3">
                    {{ Str::limit($post->content, 150) }}
                </p>
            </div>

            <!-- Post Media -->
            @if($post->image_url)
            <div class="mb-4">
                <img src="{{ $post->image_url }}" alt="Post image" class="w-full h-32 object-cover rounded-lg">
            </div>
            @endif
            
            @if($post->carousel_images && count($post->carousel_images) > 0)
            <div class="mb-4">
                <div class="grid grid-cols-4 md:grid-cols-5 gap-2">
                    @foreach($post->carousel_images as $index => $image)
                    <div class="relative">
                        <img src="{{ $image }}" alt="Carousel image {{ $index + 1 }}" 
                             class="w-full h-10 object-cover rounded-full border border-gray-200">
                        <div class="absolute top-1 right-1 bg-orange-500 text-white text-xs px-1 py-0.5 rounded-full">
                            {{ $index + 1 }}
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-images mr-1"></i>
                    {{ count($post->carousel_images) }} images in carousel
                </div>
            </div>
            @endif
            
            @if($post->video_url)
            <div class="mb-4">
                <video controls class="w-full h-32 object-cover rounded-lg">
                    <source src="{{ $post->video_url }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            @endif

            <!-- Post Meta -->
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                <div class="flex items-center justify-between">
                    <span>Created: {{ $post->created_at->format('M j, Y') }}</span>
                    @if($post->scheduled_at)
                    <span>Scheduled: {{ $post->scheduled_at->format('M j, Y g:i A') }}</span>
                    @endif
                </div>
                @if($post->published_at)
                <div class="mt-1">
                    <span>Published: {{ $post->published_at->format('M j, Y g:i A') }}</span>
                </div>
                @endif
            </div>

            <!-- Analytics (for published posts) -->
            @if($post->status === 'published' && $post->analytics_data)
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $post->engagement['likes'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Likes</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $post->engagement['comments'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Comments</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $post->engagement['shares'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Shares</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $post->engagement['views'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Views</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex space-x-2">
                    @if($post->status === 'draft')
                    <button onclick="publishPost({{ $post->id }})" 
                            class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-md transition-colors">
                        Publish Now
                    </button>
                    <button onclick="schedulePost({{ $post->id }})" 
                            class="px-3 py-1 bg-orange-600 hover:bg-orange-700 text-white text-xs rounded-md transition-colors">
                        Schedule
                    </button>
                    @endif
                    
                    @if($post->status === 'scheduled')
                    <button onclick="editSchedule({{ $post->id }})" 
                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition-colors">
                        Edit Schedule
                    </button>
                    @endif
                </div>
                
                <div class="flex space-x-2">
                    @if($post->status === 'published')
                    <button onclick="viewAnalytics({{ $post->id }})" 
                            class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-md transition-colors">
                        Analytics
                    </button>
                    @endif
                    
                    @if($post->status !== 'published')
                    <button onclick="deletePost({{ $post->id }})" 
                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-md transition-colors">
                        Delete
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12">
        <div class="text-gray-400 dark:text-gray-600 mb-4">
            <i class="fas fa-file-alt text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No posts found</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first LinkedIn post.</p>
        <a href="{{ route('content-creator.create') }}" 
           class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            Create Your First Post
        </a>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($posts->hasPages())
<div class="mt-8">
    {{ $posts->links() }}
</div>
@endif

<!-- Schedule Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Schedule Post</h3>
                <form id="scheduleForm">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Schedule Date & Time
                        </label>
                        <input type="datetime-local" id="scheduleDateTime" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeScheduleModal()" 
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md transition-colors">
                            Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF token from Blade
const csrfToken = '{{ csrf_token() }}';
let currentPostId = null;

function publishPost(postId) {
    if (confirm('Are you sure you want to publish this post immediately?')) {
        fetch(`/content-creator/publish/${postId}`, {
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
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while publishing the post.');
        });
    }
}

function schedulePost(postId) {
    currentPostId = postId;
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function editSchedule(postId) {
    currentPostId = postId;
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
    currentPostId = null;
}

function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        fetch(`/content-creator/delete/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the post.');
        });
    }
}

function viewAnalytics(postId) {
    fetch(`/content-creator/analytics/${postId}`)
    .then(response => response.json())
    .then(data => {
        alert(`Analytics for Post ${postId}:\n\nLikes: ${data.engagement.likes}\nComments: ${data.engagement.comments}\nShares: ${data.engagement.shares}\nViews: ${data.engagement.views}\nEngagement Rate: ${data.engagement_rate}%`);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching analytics.');
    });
}

// Schedule form submission
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const scheduleDateTime = document.getElementById('scheduleDateTime').value;
    
    if (!scheduleDateTime) {
        alert('Please select a date and time.');
        return;
    }
    
    if (!currentPostId) {
        alert('No post selected for scheduling.');
        return;
    }
    
    fetch(`/content-creator/schedule/${currentPostId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            scheduled_at: scheduleDateTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while scheduling the post.');
    });
});
</script>
@endsection
