@extends('layout.auth')

@section('content')
<div class="p-4 sm:p-6">
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $audience->audience_name }}</h2>
                <p class="text-sm text-gray-600">{{ optional(json_decode($audience->source_meta))->company_url }}</p>
            </div>
            <div>
                <a href="{{ route('competitor-followers.export', $audience->id) }}" class="inline-flex items-center justify-center rounded text-white px-4 py-2 transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">{{ __('competitor_followers.export') }}</a>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow">
        <div class="p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="text-gray-600">
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_name') }}</th>
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_job') }}</th>
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_company') }}</th>
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_location') }}</th>
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_profile') }}</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $row)
                        <tr class="border-t" id="row-{{ $row->id }}">
                            <td class="py-2 pr-4 text-gray-900">{{ trim(($row->con_first_name ?? '') . ' ' . ($row->con_last_name ?? '')) }}</td>
                            <td class="py-2 pr-4 text-gray-700">{{ $row->con_job_title }}</td>
                            <td class="py-2 pr-4 text-gray-700">{{ $row->con_company_name }}</td>
                            <td class="py-2 pr-4 text-gray-700">{{ $row->con_location }}</td>
                            <td class="py-2 pr-4">
                                @php
                                    // Build profile URL from public identifier (same logic as FetchAudienceEmailJob)
                                    $profileUrl = null;
                                    if (!empty($row->con_public_identifier)) {
                                        $profileUrl = 'https://www.linkedin.com/in/' . $row->con_public_identifier . '/';
                                    } elseif (!empty($row->con_profile_url)) {
                                        // Fallback to stored profile URL if public identifier is missing
                                        $profileUrl = $row->con_profile_url;
                                    }
                                @endphp
                                @if ($profileUrl)
                                    <a href="{{ $profileUrl }}" target="_blank" class="inline-flex items-center justify-center rounded text-white px-3 py-1.5 text-xs transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                                        </svg>
                                        View Profile
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">N/A</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-gray-700 email-cell-{{ $row->id }}">
                                @if ($row->con_email)
                                    {{ $row->con_email }}
                                @else
                                    <span class="text-gray-400">Not Found</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4">
                                @if (!empty($row->con_email))
                                    <span class="text-green-600 text-xs">✓ Email Found</span>
                                @elseif(!empty($row->email_fetch_attempted_at))
                                    <span class="text-gray-500 text-xs">No email found</span>
                                @else
                                    <button 
                                        type="button" 
                                        class="get-email-btn inline-flex items-center justify-center rounded text-white px-3 py-1.5 text-xs transition-all" 
                                        style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" 
                                        onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" 
                                        onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';"
                                        data-audience-list-id="{{ $row->id }}"
                                        data-audience-id="{{ $audience->id }}">
                                        Get Email
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-500">{{ __('competitor_followers.empty_rows') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $list->links() }}
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.get-email-btn').on('click', function() {
        const btn = $(this);
        const audienceListId = btn.data('audience-list-id');
        const audienceId = btn.data('audience-id');
        const originalText = btn.text();
        
        // Disable button and show loading
        btn.prop('disabled', true);
        btn.text('Fetching...');
        btn.css('opacity', '0.6');
        
        $.ajax({
            url: `/competitor-followers/${audienceId}/fetch-email`,
            method: 'POST',
            data: {
                audience_list_id: audienceListId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 'success') {
                    if (response.email) {
                        // Email already existed
                        $('.email-cell-' + audienceListId).html(response.email);
                        btn.replaceWith('<span class="text-green-600 text-xs">✓ Email Found</span>');
                    } else {
                        // Job dispatched - poll for email update
                        btn.text('Processing...');
                        btn.css('background', '#ffc107');
                        
                        let attempts = 0;
                        const maxAttempts = 60; // 60 seconds max (job might take time)
                        
                        const checkEmail = setInterval(function() {
                            attempts++;
                            
                            $.ajax({
                                url: `/competitor-followers/${audienceId}/check-email/${audienceListId}`,
                                method: 'GET',
                                success: function(checkResponse) {
                                    if (checkResponse.has_email && checkResponse.email) {
                                        // Email found!
                                        clearInterval(checkEmail);
                                        $('.email-cell-' + audienceListId).html(checkResponse.email);
                                        btn.replaceWith('<span class="text-green-600 text-xs">✓ Email Found</span>');
                                    } else if (checkResponse.email_fetch_completed && !checkResponse.has_email) {
                                        // Email fetch completed but no email found
                                        clearInterval(checkEmail);
                                        btn.prop('disabled', true);
                                        btn.replaceWith('<span class="text-gray-500 text-xs">No email found</span>');
                                    } else if (attempts >= maxAttempts) {
                                        // Timeout
                                        clearInterval(checkEmail);
                                        btn.prop('disabled', false);
                                        btn.text(originalText);
                                        btn.css('opacity', '1');
                                        btn.css('background', 'linear-gradient(135deg, #0077b5 0%, #005885 100%)');
                                        alert('Email fetch is still processing. Please refresh the page in a few moments to check.');
                                    }
                                },
                                error: function() {
                                    if (attempts >= maxAttempts) {
                                        clearInterval(checkEmail);
                                        btn.prop('disabled', false);
                                        btn.text(originalText);
                                        btn.css('opacity', '1');
                                    }
                                }
                            });
                        }, 2000); // Check every 2 seconds
                    }
                } else {
                    alert('Error: ' + (response.message || 'Failed to fetch email'));
                    btn.prop('disabled', false);
                    btn.text(originalText);
                    btn.css('opacity', '1');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to fetch email';
                alert('Error: ' + errorMsg);
                btn.prop('disabled', false);
                btn.text(originalText);
                btn.css('opacity', '1');
            }
        });
    });
});
</script>
@endsection


