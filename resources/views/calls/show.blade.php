@extends('layout.auth')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Call Details</h1>
                    <p class="mt-2 text-gray-600">Conversation with {{ $call->recipient }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('calls') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Calls
                    </a>
                    @if($call->calendar_link)
                    <a href="{{ $call->calendar_link }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Schedule Call
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content - Chat History -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Conversation History</h2>
                        <p class="text-sm text-gray-500">{{ count($conversationHistory) }} messages</p>
                    </div>
                    
                    <div class="p-6">
                        @if(count($conversationHistory) > 0)
                            <div id="chatScrollContainer" class="h-[65vh] overflow-y-auto pr-2">
                                <div id="chatMessages" class="space-y-4">
                                    @foreach($conversationHistory as $message)
                                        <div class="flex {{ $message['type'] === 'lead' ? 'justify-start' : 'justify-end' }}">
                                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl {{ $message['type'] === 'lead' ? 'bg-gray-100 text-gray-900 rounded-bl-sm' : 'bg-orange-500 text-white rounded-br-sm' }}">
                                                <div class="text-xs font-semibold mb-1 opacity-80">
                                                    {{ $message['type'] === 'lead' ? $call->recipient : 'AI Assistant' }}
                                                </div>
                                                <div class="text-sm leading-relaxed">{{ $message['message'] }}</div>
                                                <div class="text-[11px] mt-1 opacity-75 text-right">
                                                    {{ \Carbon\Carbon::parse($message['timestamp'])->format('M j, Y g:i A') }}
                                                </div>
                                                @if(isset($message['ai_analysis']) && $message['ai_analysis'])
                                                    <div class="mt-2 text-[11px] opacity-75">
                                                        <span class="font-medium">Analysis:</span> {{ $message['ai_analysis']['intent'] ?? 'N/A' }} ({{ $message['ai_analysis']['sentiment'] ?? 'N/A' }})
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <p class="mt-2">No conversation history available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar - Call Information -->
            <div class="space-y-6">
                <!-- Call Status Card -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Call Status</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-gray-500">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($call->call_status === 'scheduling_initiated') bg-green-100 text-green-800
                                @elseif($call->call_status === 'not_interested') bg-red-100 text-red-800
                                @elseif($call->call_status === 'needs_info') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $call->call_status)) }}
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Lead Score</span>
                                <span class="text-sm font-medium">{{ $call->lead_score ?? 'N/A' }}/10</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Interactions</span>
                                <span class="text-sm font-medium">{{ $call->interaction_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Last Activity</span>
                                <span class="text-sm font-medium">{{ $call->last_interaction_at ? \Carbon\Carbon::parse($call->last_interaction_at)->format('M j, Y g:i A') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Information Card -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Lead Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500">Name</span>
                                <p class="text-sm font-medium">{{ $call->recipient }}</p>
                            </div>
                            @if($call->company)
                            <div>
                                <span class="text-sm text-gray-500">Company</span>
                                <p class="text-sm font-medium">{{ $call->company }}</p>
                            </div>
                            @endif
                            @if($call->job_title)
                            <div>
                                <span class="text-sm text-gray-500">Job Title</span>
                                <p class="text-sm font-medium">{{ $call->job_title }}</p>
                            </div>
                            @endif
                            @if($call->location)
                            <div>
                                <span class="text-sm text-gray-500">Location</span>
                                <p class="text-sm font-medium">{{ $call->location }}</p>
                            </div>
                            @endif
                            @if($call->linkedin_profile_url)
                            <div>
                                <span class="text-sm text-gray-500">LinkedIn Profile</span>
                                <a href="{{ $call->linkedin_profile_url }}" target="_blank" class="text-sm text-orange-600 hover:text-orange-500">View Profile</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Campaign Information Card -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Campaign</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500">Campaign Name</span>
                                <p class="text-sm font-medium">{{ $call->campaign_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Sequence</span>
                                <p class="text-sm font-medium">{{ $call->sequence ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Profile</span>
                                <p class="text-sm font-medium">{{ $call->profile ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meeting Information Card -->
                @if($call->calendar_link || $call->meeting_link || $call->scheduled_time)
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Meeting Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @if($call->scheduled_time)
                            <div>
                                <span class="text-sm text-gray-500">Scheduled Time</span>
                                <p class="text-sm font-medium">{{ \Carbon\Carbon::parse($call->scheduled_time)->format('M j, Y g:i A') }}</p>
                            </div>
                            @endif
                            @if($call->calendar_link)
                            <div>
                                <span class="text-sm text-gray-500">Calendar Link</span>
                                <a href="{{ $call->calendar_link }}" target="_blank" class="text-sm text-orange-600 hover:text-orange-500 block">Schedule Call</a>
                            </div>
                            @endif
                            @if($call->meeting_link)
                            <div>
                                <span class="text-sm text-gray-500">Meeting Link</span>
                                <a href="{{ $call->meeting_link }}" target="_blank" class="text-sm text-orange-600 hover:text-orange-500 block">Join Meeting</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- AI Analysis Card -->
                @if($aiAnalysis)
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">AI Analysis</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500">Intent</span>
                                <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $aiAnalysis['intent'] ?? 'N/A')) }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Sentiment</span>
                                <p class="text-sm font-medium">{{ ucfirst($aiAnalysis['sentiment'] ?? 'N/A') }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Lead Score</span>
                                <p class="text-sm font-medium">{{ $aiAnalysis['lead_score'] ?? 'N/A' }}/10</p>
                            </div>
                            @if(isset($aiAnalysis['reasoning']))
                            <div>
                                <span class="text-sm text-gray-500">Reasoning</span>
                                <p class="text-sm text-gray-700">{{ $aiAnalysis['reasoning'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<script>
    (function(){
        function scrollChatToBottom(){
            var c = document.getElementById('chatScrollContainer');
            if(!c) return;
            c.scrollTop = c.scrollHeight;
        }
        // Initial scroll
        document.addEventListener('DOMContentLoaded', scrollChatToBottom);
        // Fallback if DOMContentLoaded already fired
        setTimeout(scrollChatToBottom, 100);
        // Observe for dynamic content changes (e.g., future updates)
        var target = document.getElementById('chatMessages');
        if (target && 'MutationObserver' in window) {
            var observer = new MutationObserver(function(){
                scrollChatToBottom();
            });
            observer.observe(target, { childList: true, subtree: true });
        }
    })();
</script>
@endsection
