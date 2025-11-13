@extends('layout.auth')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">🤖 AI Auto-Comment Preferences</h2>
        <p class="text-sm text-gray-500 mt-1">Configure your preferences for automatic commenting on LinkedIn posts</p>
    </div>

    <form method="POST" action="{{ route('auto-comment.store-preferences') }}" class="space-y-6">
        @csrf
        
        <!-- Active Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Enable Auto-Commenting</h3>
                    <p class="text-sm text-gray-500">Turn on automatic commenting based on your preferences</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $preference ? $preference->is_active : false) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                </label>
            </div>
        </div>

        <!-- Post Search Preferences -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📥 Post Search Preferences</h3>
            
            <!-- Keywords -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Keywords/Topics (comma-separated)
                </label>
                <input type="text" name="keywords" 
                       value="{{ old('keywords', $preference && $preference->keywords ? implode(', ', $preference->keywords) : '') }}"
                       placeholder="e.g., artificial intelligence, marketing, entrepreneurship"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                <p class="text-xs text-gray-500 mt-1">Posts containing these keywords will be considered</p>
            </div>

            <!-- Followed Accounts -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Followed Accounts (LinkedIn URNs or URLs, one per line)
                </label>
                <textarea name="followed_accounts" rows="4"
                          placeholder="urn:li:person:ABC123&#10;urn:li:person:XYZ456&#10;https://linkedin.com/in/username"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">{{ old('followed_accounts', $preference && $preference->followed_accounts ? implode("\n", $preference->followed_accounts) : '') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Posts from these accounts will be monitored</p>
            </div>

            <!-- Industries -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Industries (comma-separated)
                </label>
                <input type="text" name="industries" 
                       value="{{ old('industries', $preference && $preference->industries ? implode(', ', $preference->industries) : '') }}"
                       placeholder="e.g., Technology, Healthcare, Finance"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            </div>

            <!-- Minimum Engagement -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Minimum Engagement (likes/comments)
                </label>
                <input type="number" name="min_engagement" 
                       value="{{ old('min_engagement', $preference ? $preference->min_engagement : 50) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                <p class="text-xs text-gray-500 mt-1">Only posts with at least this much engagement will be considered</p>
            </div>
        </div>

        <!-- AI Comment Preferences -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💬 AI Comment Preferences</h3>
            
            <!-- Comment Style -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Comment Style</label>
                <select name="comment_style" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="professional" {{ old('comment_style', $preference ? $preference->comment_style : 'professional') == 'professional' ? 'selected' : '' }}>Professional</option>
                    <option value="casual" {{ old('comment_style', $preference ? $preference->comment_style : '') == 'casual' ? 'selected' : '' }}>Casual</option>
                    <option value="friendly" {{ old('comment_style', $preference ? $preference->comment_style : '') == 'friendly' ? 'selected' : '' }}>Friendly</option>
                    <option value="thoughtful" {{ old('comment_style', $preference ? $preference->comment_style : '') == 'thoughtful' ? 'selected' : '' }}>Thoughtful</option>
                </select>
            </div>

            <!-- Comment Tone -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Comment Tone</label>
                <select name="comment_tone" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="helpful" {{ old('comment_tone', $preference ? $preference->comment_tone : 'helpful') == 'helpful' ? 'selected' : '' }}>Helpful</option>
                    <option value="engaging" {{ old('comment_tone', $preference ? $preference->comment_tone : '') == 'engaging' ? 'selected' : '' }}>Engaging</option>
                    <option value="supportive" {{ old('comment_tone', $preference ? $preference->comment_tone : '') == 'supportive' ? 'selected' : '' }}>Supportive</option>
                    <option value="questioning" {{ old('comment_tone', $preference ? $preference->comment_tone : '') == 'questioning' ? 'selected' : '' }}>Questioning</option>
                </select>
            </div>

            <!-- Comment Instructions -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Custom Instructions for AI
                </label>
                <textarea name="comment_instructions" rows="3"
                          placeholder="e.g., Always include a question, Keep comments under 100 words, Reference specific points from the post"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">{{ old('comment_instructions', $preference ? $preference->comment_instructions : '') }}</textarea>
            </div>

            <!-- Avoid Topics -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Topics to Avoid
                </label>
                <textarea name="avoid_topics" rows="2"
                          placeholder="e.g., politics, religion, controversial topics"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">{{ old('avoid_topics', $preference ? $preference->avoid_topics : '') }}</textarea>
            </div>
        </div>

        <!-- Posting Schedule -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">⏰ Posting Schedule</h3>
            
            <!-- Posting Times -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Preferred Posting Times (hours, comma-separated, 0-23)
                </label>
                <input type="text" name="posting_times" 
                       value="{{ old('posting_times', $preference && $preference->posting_times ? implode(',', $preference->posting_times) : '9,14,18') }}"
                       placeholder="9,14,18"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                <p class="text-xs text-gray-500 mt-1">Comments will be posted at these hours (24-hour format)</p>
            </div>

            <!-- Timezone -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                <select name="timezone" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="UTC" {{ old('timezone', $preference ? $preference->timezone : 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="America/New_York" {{ old('timezone', $preference ? $preference->timezone : '') == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                    <option value="America/Chicago" {{ old('timezone', $preference ? $preference->timezone : '') == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                    <option value="America/Denver" {{ old('timezone', $preference ? $preference->timezone : '') == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                    <option value="America/Los_Angeles" {{ old('timezone', $preference ? $preference->timezone : '') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                </select>
            </div>

            <!-- Max Comments Per Day -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Maximum Comments Per Day
                </label>
                <input type="number" name="max_comments_per_day" 
                       value="{{ old('max_comments_per_day', $preference ? $preference->max_comments_per_day : 10) }}"
                       min="1" max="100"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            </div>

            <!-- Min Time Between Comments -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Minimum Time Between Comments (minutes)
                </label>
                <input type="number" name="min_time_between_comments" 
                       value="{{ old('min_time_between_comments', $preference ? $preference->min_time_between_comments : 60) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filters</h3>
            
            <!-- Skip Already Commented -->
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Skip Posts Already Commented On</label>
                    <p class="text-xs text-gray-500">Prevent commenting on posts you've already engaged with</p>
                </div>
                <input type="checkbox" name="skip_already_commented" value="1" 
                       {{ old('skip_already_commented', $preference ? $preference->skip_already_commented : true) ? 'checked' : '' }}
                       class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
            </div>

            <!-- Only Fresh Posts -->
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Only Fresh Posts (&lt; 24 hours)</label>
                    <p class="text-xs text-gray-500">Only comment on posts less than 24 hours old</p>
                </div>
                <input type="checkbox" name="only_fresh_posts" value="1" 
                       {{ old('only_fresh_posts', $preference ? $preference->only_fresh_posts : true) ? 'checked' : '' }}
                       class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
            </div>

            <!-- Skip Posts Older Than -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Skip Posts Older Than (days)
                </label>
                <input type="number" name="skip_posts_older_than_days" 
                       value="{{ old('skip_posts_older_than_days', $preference ? $preference->skip_posts_older_than_days : 7) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('auto-comment.index') }}" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                Save Preferences
            </button>
        </div>
    </form>
</div>
@endsection
