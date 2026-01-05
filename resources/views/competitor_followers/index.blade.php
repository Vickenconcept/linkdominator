@extends('layout.auth')

@section('content')
<div class="p-4 sm:p-6">
    @if (session('status'))
        <div class="mb-4 rounded border border-[#0077b5] bg-blue-50 text-[#0077b5] px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('competitor_followers.title') }}</h2>
            <a href="{{ route('social-account.index') }}" class="text-sm text-[#0077b5] hover:text-[#005885] font-medium">Manage LinkedIn session →</a>
        </div>
        @if (session('error'))
            <div class="rounded border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if (!$hasLinkedInSession)
            <div class="rounded border border-[#0077b5] bg-blue-50 text-[#005885] px-4 py-3 text-sm">
                <p class="font-medium">Add your LinkedIn session cookie first</p>
                <p class="mt-1">Visit the Social Accounts page, open your connected LinkedIn profile, and paste your <code class="font-mono bg-white/60 px-1 py-0.5 rounded">li_at</code> cookie + user agent. We'll auto-fill it for every competitor fetch.</p>
            </div>
        @endif
        <form method="POST" action="{{ route('competitor-followers.fetch') }}" class="grid grid-cols-1 gap-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-700 mb-1">{{ __('competitor_followers.company_url_label') }}</label>
                <input name="company_url" type="url" required placeholder="https://www.linkedin.com/company/..." class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0077b5]" />
                @error('company_url')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex items-center justify-center rounded text-white px-4 py-2 w-full md:w-auto transition-all" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">{{ __('competitor_followers.fetch_button') }}</button>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow">
        <div class="p-4 sm:p-6 border-b">
            <h3 class="text-base font-semibold text-gray-900">{{ __('competitor_followers.history_title') }}</h3>
        </div>
        <div class="p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="text-gray-600">
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_name') }}</th>
                        <th class="py-2 pr-4">Followers</th>
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_created') }}</th>
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audiences as $aud)
                        <tr class="border-t">
                            <td class="py-2 pr-4">
                                <div class="font-medium text-gray-900">{{ $aud->audience_name ?? 'Competitor Followers' }}</div>
                                <div class="text-xs text-gray-500">{{ optional(json_decode($aud->source_meta))->company_url }}</div>
                            </td>
                            <td class="py-2 pr-4 text-gray-700">
                                <span class="font-semibold">{{ $aud->followers_count ?? 0 }}</span>
                                <span class="text-xs text-gray-500">people</span>
                            </td>
                            <td class="py-2 pr-4 text-gray-700">{{ $aud->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-2 pr-4">
                                <a href="{{ route('competitor-followers.show', $aud->id) }}" class="text-[#0077b5] hover:text-[#005885] hover:underline mr-3">{{ __('competitor_followers.view') }}</a>
                                <a href="{{ route('competitor-followers.export', $aud->id) }}" class="text-gray-700 hover:underline">{{ __('competitor_followers.export') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500">{{ __('competitor_followers.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $audiences->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


