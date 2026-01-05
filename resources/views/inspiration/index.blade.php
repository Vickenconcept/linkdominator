@extends('layout.auth')

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">💡 Inspiration Library</h2>
        <p class="text-sm text-gray-500 mt-1">Discover viral LinkedIn posts and use them as inspiration</p>
    </div>
    <a href="{{ route('content-creator.create') }}" 
       class="text-white px-4 py-2 rounded-lg font-medium transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">
        <i class="fas fa-plus mr-2"></i>Create New Post
    </a>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-50 rounded-lg">
                <i class="fas fa-bookmark text-[#0077b5]"></i>
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

<!-- Content Preferences (Collapsible) -->
<div class="bg-white rounded-lg shadow mb-8">
    <button onclick="togglePreferences()" 
            class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors cursor-pointer">
        <div class="flex items-center">
            <i class="fas fa-sliders-h text-[#0077b5] mr-3"></i>
            <div class="text-left">
                <h3 class="text-lg font-semibold text-gray-900">Content Preferences</h3>
                <p class="text-sm text-gray-500">Customize what viral posts you want to see</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-500 hidden sm:inline" id="preferences-text">Click to expand</span>
            <div class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center hover:border-[#0077b5] hover:bg-blue-50 transition-all">
                <i id="preferences-icon" class="fas fa-chevron-down text-gray-600 transition-transform duration-300"></i>
            </div>
        </div>
    </button>
    
    <div id="preferences-section" class="px-6 pb-6 hidden">
        <form method="POST" action="{{ route('inspiration.preferences.update') }}" class="space-y-6">
            @csrf
            
            <!-- Industries -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-industry mr-1 text-gray-400"></i>Industries (What field are you in?)
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @php
                    $industries = [
                        'Business & Entrepreneurship',
                        'Technology & Software',
                        'Marketing & Advertising',
                        'Sales & Business Development',
                        'Leadership & Management',
                        'Healthcare & Medical',
                        'Real Estate & Property',
                        'Finance & Investment',
                        'E-commerce & Retail',
                        'Education & Training',
                        'Consulting & Coaching',
                        'Legal & Law',
                        'Human Resources',
                        'Product Management',
                        'Design & Creative',
                        'Content Creation'
                    ];
                    $userIndustries = $preferences->industries ?? ['Business & Entrepreneurship', 'Marketing & Advertising'];
                    @endphp
                    
                    @foreach($industries as $industry)
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-[#0077b5] transition-colors
                        {{ in_array($industry, $userIndustries) ? 'bg-blue-50 border-[#0077b5]' : 'border-gray-200' }}">
                        <input type="checkbox" 
                               name="industries[]" 
                               value="{{ $industry }}"
                               {{ in_array($industry, $userIndustries) ? 'checked' : '' }}
                               class="w-4 h-4 border-gray-300 rounded focus:ring-[#0077b5]" style="accent-color: #0077b5;">
                        <span class="ml-2 text-sm text-gray-700">{{ $industry }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Topics -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tags mr-1 text-gray-400"></i>Topics & Keywords (What interests you?)
                </label>
                <div id="topics-container" class="flex flex-wrap gap-2 mb-2">
                    @php
                    $userTopics = $preferences->topics ?? ['entrepreneurship', 'marketing', 'leadership'];
                    @endphp
                    
                    @foreach($userTopics as $topic)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700">
                        {{ $topic }}
                        <button type="button" onclick="removeTopic(this)" class="ml-2 text-blue-500 hover:text-blue-700">
                            <i class="fas fa-times"></i>
                        </button>
                        <input type="hidden" name="topics[]" value="{{ $topic }}">
                    </span>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <input type="text" 
                           id="new-topic" 
                           placeholder="e.g., AI, productivity, real estate tips"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]">
                    <button type="button" 
                            onclick="addTopic()"
                            class="px-4 py-2 text-white rounded-md transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)';">
                        <i class="fas fa-plus mr-1"></i>Add
                    </button>
                </div>
            </div>
            
            <!-- Date Range (CRITICAL for high engagement) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-1 text-gray-400"></i>Post Age (Older posts have more time to accumulate likes)
                </label>
                <select name="date_range" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]">
                    <option value="past-week" {{ ($preferences->date_range ?? 'past-month') == 'past-week' ? 'selected' : '' }}>
                        Past Week (Latest, but lower engagement)
                    </option>
                    <option value="past-2-weeks" {{ ($preferences->date_range ?? 'past-month') == 'past-2-weeks' ? 'selected' : '' }}>
                        Past 2 Weeks (Balanced)
                    </option>
                    <option value="past-3-weeks" {{ ($preferences->date_range ?? 'past-month') == 'past-3-weeks' ? 'selected' : '' }}>
                        Past 3 Weeks (Good engagement)
                    </option>
                    <option value="past-month" {{ ($preferences->date_range ?? 'past-month') == 'past-month' ? 'selected' : '' }}>
                        Past Month (Best for high engagement) ⭐
                    </option>
                    <option value="any-time" {{ ($preferences->date_range ?? 'past-month') == 'any-time' ? 'selected' : '' }}>
                        Any Time (All posts)
                    </option>
                </select>
                <p class="text-xs text-gray-600 mt-2">
                    💡 <strong>Recommended:</strong> Past Month (2-4 weeks) - Posts have time to accumulate 100+ likes
                </p>
            </div>
            
            <!-- Engagement Threshold -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-fire mr-1 text-gray-400"></i>Minimum Engagement (How many likes to be considered "viral"?)
                </label>
                <div class="flex items-center gap-4">
                    <input type="range" 
                           name="min_engagement" 
                           id="min-engagement" 
                           min="50" 
                           max="1000" 
                           step="50" 
                           value="{{ $preferences->min_engagement ?? 100 }}"
                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <span id="engagement-value" class="text-2xl font-bold text-[#0077b5] min-w-[100px] text-right">
                        {{ $preferences->min_engagement ?? 100 }}+ likes
                    </span>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>50 (Good posts)</span>
                    <span>1000+ (Very viral)</span>
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    💡 <strong>Tip:</strong> 100-300 likes is ideal for quality viral content. Combine with "Past Month" date range for best results.
                </p>
            </div>
            
            <!-- Save Button -->
            <div class="flex justify-end pt-4 border-t">
                <button type="submit" 
                        class="px-6 py-2 text-white rounded-md transition-all font-medium" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">
                    <i class="fas fa-save mr-2"></i>Save Preferences
                </button>
            </div>
        </form>
        
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>How it works:</strong> Based on your preferences, our system will automatically fetch viral posts matching your industries and topics. Run <code class="px-2 py-1 bg-white rounded">php artisan app:fetch-linkedin-feeds</code> to fetch posts or wait for the daily automatic fetch.
            </p>
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
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]">
        </div>
        
        <!-- Category Filter -->
        <select name="category" 
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]">
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
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]">
            <option value="">All Engagement</option>
            <option value="10" {{ request('engagement') == '10' ? 'selected' : '' }}>🔥 10%+ (Viral)</option>
            <option value="5" {{ request('engagement') == '5' ? 'selected' : '' }}>⚡ 5%+ (High)</option>
            <option value="3" {{ request('engagement') == '3' ? 'selected' : '' }}>✨ 3%+ (Good)</option>
        </select>
        
        <!-- Date Filter -->
        <select name="days" 
                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]">
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
                       class="w-4 h-4 bg-gray-100 border-gray-300 rounded focus:ring-[#0077b5]" style="accent-color: #0077b5;">
                <span class="ml-2 text-sm text-gray-700">
                    <i class="fas fa-star text-yellow-500"></i> Favorites only
                </span>
            </label>
            
            <div class="flex space-x-2">
                <button type="submit" 
                        class="px-4 py-2 text-white rounded-md transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)';">
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
<div class="bg-gradient-to-r from-blue-50 to-blue-50 border border-[#0077b5] rounded-lg p-6 mb-8">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-lightbulb text-[#0077b5] text-2xl"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">How to Build Your Inspiration Library</h3>
            <div class="space-y-2 text-sm text-gray-700">
                <p><strong>Step 1:</strong> Browse LinkedIn feed and find high-engagement posts</p>
                <p><strong>Step 2:</strong> Click the <span class="px-2 py-1 text-white rounded text-xs" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);">Save to LinkDominator</span> button (from Chrome extension)</p>
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
    <div class="bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-all relative overflow-hidden" style="max-width: 100%;">
        <!-- Engagement Badge -->
        <div class="absolute top-2 right-2 z-10">
            <span class="px-2 py-0.5 rounded text-xs font-semibold shadow-sm text-white @if($post->engagement_rate >= 10) bg-red-500 @endif" @if($post->engagement_rate < 10) style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" @endif>
                @if($post->engagement_rate >= 10) 🔥 @elseif($post->engagement_rate >= 5) ⚡ @else ✨ @endif
                {{ number_format($post->engagement_rate, 1) }}%
            </span>
        </div>

        <!-- Favorite Star -->
        <div class="absolute top-2 left-2 z-10">
            <button onclick="toggleFavorite({{ $post->id }})" 
                    class="p-1.5 bg-white rounded-full shadow-sm hover:shadow-md transition-all">
                <i class="fas fa-star text-xs {{ $post->is_favorite ? 'text-yellow-500' : 'text-gray-300' }}"></i>
            </button>
        </div>

        <div class="p-4">
            <!-- Author Info - LinkedIn Style -->
            <div class="flex items-center mb-3">
                @if($post->author_image_url)
                <img src="{{ $post->author_image_url }}" 
                     alt="{{ $post->author_name }}" 
                     class="w-10 h-10 rounded-full object-cover">
                @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0077b5] to-[#005885] flex items-center justify-center text-white font-semibold text-sm">
                    {{ substr($post->author_name, 0, 1) }}
                </div>
                @endif
                <div class="ml-2 flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $post->author_name }}</div>
                    @if($post->author_headline)
                    <div class="text-xs text-gray-500 truncate">{{ Str::limit($post->author_headline, 35) }}</div>
                    @endif
                    <div class="text-xs text-gray-400 mt-0.5">{{ $post->saved_at->diffForHumans() }}</div>
                </div>
            </div>

            <!-- Post Content Preview - LinkedIn Style -->
            <div class="mb-3">
                <p class="text-sm text-gray-900 leading-relaxed line-clamp-4 whitespace-pre-wrap">{{ $post->content }}</p>
            </div>

            <!-- Post Media - LinkedIn Style -->
            @if($post->post_type === 'image' && $post->images && count($post->images) > 0)
            <div class="mb-3 -mx-4">
                <img src="{{ $post->images[0] }}" alt="Post image" class="w-full max-h-64 object-cover">
            </div>
            @endif
            
            @if($post->post_type === 'carousel' && $post->images && count($post->images) > 0)
            <div class="mb-3 -mx-4">
                <div class="grid grid-cols-3 gap-0">
                    @foreach(array_slice($post->images, 0, 3) as $image)
                    <img src="{{ $image }}" alt="Carousel image" class="w-full h-24 object-cover">
                    @endforeach
                </div>
                @if(count($post->images) > 3)
                <div class="text-xs text-gray-500 mt-1 px-2">
                    +{{ count($post->images) - 3 }} more
                </div>
                @endif
            </div>
            @endif

            <!-- Engagement Metrics - LinkedIn Style (Small) -->
            <div class="flex items-center justify-between py-2 mb-2 text-xs text-gray-500 border-t border-gray-100">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center">
                        <i class="far fa-thumbs-up mr-1"></i>{{ $post->likes > 0 ? number_format($post->likes) : '' }}
                    </span>
                    <span>{{ $post->comments > 0 ? number_format($post->comments) . ' comments' : '' }}</span>
                    <span>{{ $post->shares > 0 ? number_format($post->shares) . ' shares' : '' }}</span>
                </div>
            </div>

            <!-- Action Buttons - Simplified & LinkedIn Style -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 space-x-1">
                <button onclick="useAsInspiration({{ $post->id }})" 
                        class="flex-1 px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 rounded transition-colors">
                    <i class="fas fa-lightbulb mr-1 text-[#0077b5]"></i>Use
                </button>
                
                <button onclick="remixPost({{ $post->id }})" 
                        class="flex-1 px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 rounded transition-colors">
                    <i class="fas fa-magic mr-1 text-[#0077b5]"></i>Remix
                </button>
                
                @if($post->post_url)
                <a href="{{ $post->post_url }}" 
                   target="_blank"
                   class="flex-1 px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 rounded transition-colors text-center">
                    <i class="fab fa-linkedin mr-1 text-[#0077b5]"></i>View
                </a>
                @endif
                
                <button onclick="deletePost({{ $post->id }})" 
                        class="px-2 py-1.5 text-xs font-medium text-gray-500 hover:bg-red-50 hover:text-red-600 rounded transition-colors">
                    <i class="fas fa-trash"></i>
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
                    <span class="flex-shrink-0 w-6 h-6 text-white rounded-full flex items-center justify-center text-xs mr-2" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);">1</span>
                    <span>Install LinkDominator Chrome Extension</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 text-white rounded-full flex items-center justify-center text-xs mr-2" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);">2</span>
                    <span>Browse LinkedIn feed</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 text-white rounded-full flex items-center justify-center text-xs mr-2" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);">3</span>
                    <span>Click "Save to LinkDominator" on posts with high engagement</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 text-white rounded-full flex items-center justify-center text-xs mr-2" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);">4</span>
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]">
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
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0077b5]"
                              readonly></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeRemixModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button onclick="generateRemix()" 
                            id="generateRemixBtn"
                            class="px-4 py-2 text-white rounded-md transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)';">
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

// Toggle preferences section
function togglePreferences() {
    const section = document.getElementById('preferences-section');
    const icon = document.getElementById('preferences-icon');
    const text = document.getElementById('preferences-text');
    
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
        icon.classList.add('rotate-180');
        if (text) text.textContent = 'Click to collapse';
    } else {
        section.classList.add('hidden');
        icon.classList.remove('rotate-180');
        if (text) text.textContent = 'Click to expand';
    }
}

// Add topic tag
function addTopic() {
    const input = document.getElementById('new-topic');
    const topic = input.value.trim();
    
    if (!topic) return;
    
    const container = document.getElementById('topics-container');
    const tag = document.createElement('span');
    tag.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700';
    tag.innerHTML = `
        ${topic}
        <button type="button" onclick="removeTopic(this)" class="ml-2 text-blue-500 hover:text-blue-700">
            <i class="fas fa-times"></i>
        </button>
        <input type="hidden" name="topics[]" value="${topic}">
    `;
    
    container.appendChild(tag);
    input.value = '';
}

// Remove topic tag
function removeTopic(button) {
    button.parentElement.remove();
}

// Update engagement slider value display
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('min-engagement');
    const valueDisplay = document.getElementById('engagement-value');
    
    if (slider && valueDisplay) {
        slider.addEventListener('input', function() {
            valueDisplay.textContent = this.value + '+ likes';
        });
    }
    
    // Auto-open preferences if user hasn't set them
    const hasPreferences = {{ isset($preferences->id) ? 'true' : 'false' }};
    if (!hasPreferences) {
        togglePreferences();
    }
});

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

