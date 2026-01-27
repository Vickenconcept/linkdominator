@extends('layout.auth')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">🤖 AI Auto Comments</h2>
        <p class="text-sm text-gray-500 mt-1">Automatically comment on LinkedIn posts based on your preferences</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('auto-comment.preferences') }}" 
           class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-cog mr-2"></i>Preferences
        </a>
        @if($preference && $preference->is_active)
            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-check-circle mr-2"></i>Active
            </span>
        @else
            <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-pause-circle mr-2"></i>Inactive
            </span>
        @endif
    </div>
</div>

<!-- Status Card -->
@if($preference)
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <p class="text-sm font-medium text-gray-600">Posts Found</p>
            <p class="text-2xl font-bold text-gray-900">{{ $posts->total() }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $posts->where('status', 'pending')->count() }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Scheduled</p>
            <p class="text-2xl font-bold text-[#0077b5]">{{ $posts->where('status', 'scheduled')->count() }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600">Posted</p>
            <p class="text-2xl font-bold text-green-600">{{ $posts->where('status', 'posted')->count() }}</p>
        </div>
    </div>
</div>
@else
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
        <div>
            <h3 class="font-semibold text-yellow-900">Setup Required</h3>
            <p class="text-sm text-yellow-700 mt-1">Configure your preferences to start auto-commenting</p>
        </div>
        <a href="{{ route('auto-comment.preferences') }}" 
           class="ml-auto text-white px-4 py-2 rounded-lg font-medium transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">
            Setup Now
        </a>
    </div>
</div>
@endif

<!-- Posts Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Matched Posts</h3>
    </div>
    
    @if($posts->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($posts as $post)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                            {{ Str::limit($post->post_content, 60) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $post->post_date ? $post->post_date->format('M d, Y') : 'N/A' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $post->author_name ?? 'Unknown' }}</div>
                        <div class="text-xs text-gray-500">{{ $post->author_headline ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs">
                            @if($post->generated_comment)
                                {{ Str::limit($post->generated_comment, 80) }}
                            @else
                                <span class="text-gray-400">Pending generation...</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($post->status == 'posted')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Posted
                            </span>
                        @elseif($post->status == 'scheduled')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-50 text-[#0077b5]">
                                Scheduled
                            </span>
                            @if($post->scheduled_at)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $post->scheduled_at->format('M d, H:i') }}
                                </div>
                            @endif
                        @elseif($post->status == 'pending')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @elseif($post->status == 'failed')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Failed
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($post->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        @if($post->post_url)
                            <a href="{{ $post->post_url }}" target="_blank" 
                               class="text-[#0077b5] hover:text-[#005885] mr-3">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @endif
                        <form action="{{ route('auto-comment.delete-post', $post->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure?')"
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $posts->links() }}
    </div>
    @else
    <div class="px-6 py-12 text-center">
        <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
        <p class="text-gray-500">No posts found yet. Posts will appear here once the system finds matches.</p>
        @if(!$preference)
            <a href="{{ route('auto-comment.preferences') }}" 
               class="mt-4 inline-block text-white px-4 py-2 rounded-lg font-medium transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">
                Setup Preferences
            </a>
        @endif
    </div>
    @endif
</div>
@endsection
