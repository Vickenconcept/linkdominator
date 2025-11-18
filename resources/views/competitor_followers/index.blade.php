@extends('layout.auth')

@section('content')
<div class="p-4 sm:p-6">
    @if (session('status'))
        <div class="mb-4 rounded border border-orange-200 bg-orange-50 text-orange-800 px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('competitor_followers.title') }}</h2>
        <form method="POST" action="{{ route('competitor-followers.fetch') }}" class="grid grid-cols-1 gap-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-700 mb-1">{{ __('competitor_followers.company_url_label') }}</label>
                <input name="company_url" type="url" required placeholder="https://www.linkedin.com/company/..." class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" />
                @error('company_url')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">{{ __('competitor_followers.session_cookie_label') }}</label>
                <textarea name="linkedin_session_cookie" required rows="3" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="li_at=..."></textarea>
                <p class="text-xs text-gray-500 mt-1">{{ __('competitor_followers.session_cookie_help') }}</p>
                @error('linkedin_session_cookie')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">{{ __('competitor_followers.user_agent_label') }}</label>
                <textarea name="linkedin_user_agent" required rows="2" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="{{ __('competitor_followers.user_agent_placeholder') }}"></textarea>
                <p class="text-xs text-gray-500 mt-1">{{ __('competitor_followers.user_agent_help') }}</p>
                @error('linkedin_user_agent')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex items-center justify-center rounded bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 w-full md:w-auto">{{ __('competitor_followers.fetch_button') }}</button>
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
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_created') }}</th>
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audiences as $aud)
                        <tr class="border-t">
                            <td class="py-2 pr-4">
                                <div class="font-medium text-gray-900">{{ $aud->audience_name }}</div>
                                <div class="text-xs text-gray-500">{{ optional(json_decode($aud->source_meta))->company_url }}</div>
                            </td>
                            <td class="py-2 pr-4 text-gray-700">{{ $aud->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-2 pr-4">
                                <a href="{{ route('competitor-followers.show', $aud->id) }}" class="text-orange-600 hover:underline mr-3">{{ __('competitor_followers.view') }}</a>
                                <a href="{{ route('competitor-followers.export', $aud->id) }}" class="text-gray-700 hover:underline">{{ __('competitor_followers.export') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-gray-500">{{ __('competitor_followers.empty') }}</td>
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


