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
                        <th class="py-2 pr-4">{{ __('competitor_followers.th_last_activity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $row)
                        <tr class="border-t">
                            <td class="py-2 pr-4 text-gray-900">{{ trim(($row->con_first_name ?? '') . ' ' . ($row->con_last_name ?? '')) }}</td>
                            <td class="py-2 pr-4 text-gray-700">{{ $row->con_job_title }}</td>
                            <td class="py-2 pr-4 text-gray-700">{{ $row->con_company_name }}</td>
                            <td class="py-2 pr-4 text-gray-700">{{ $row->con_location }}</td>
                            <td class="py-2 pr-4">
                                @if ($row->con_profile_url)
                                    <a href="{{ $row->con_profile_url }}" target="_blank" class="text-[#0077b5] hover:text-[#005885] hover:underline">{{ __('competitor_followers.view_profile') }}</a>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-gray-700">{{ optional($row->con_last_activity)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">{{ __('competitor_followers.empty_rows') }}</td>
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
@endsection


