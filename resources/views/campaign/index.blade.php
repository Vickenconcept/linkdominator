@extends('layout.auth')

@section('content')
<script>
// Real-time campaign status updates
let campaignUpdateInterval;

function startCampaignStatusUpdates() {
    // Clear any existing interval
    if (campaignUpdateInterval) {
        clearInterval(campaignUpdateInterval);
    }
    
    // Check for updates every 10 seconds
    campaignUpdateInterval = setInterval(updateCampaignStatuses, 10000);
    
    // Initial update
    updateCampaignStatuses();
}

function updateCampaignStatuses() {
    // Show loading indicator
    const updateIndicator = document.getElementById('update-indicator');
    if (updateIndicator) {
        updateIndicator.classList.remove('hidden');
    }
    
    fetch('/api/campaigns/status-updates', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.campaigns) {
            data.campaigns.forEach(campaign => {
                updateCampaignRow(campaign);
            });
        }
    })
    .catch(error => {
        console.log('Campaign status update error:', error);
    })
    .finally(() => {
        // Hide loading indicator
        if (updateIndicator) {
            updateIndicator.classList.add('hidden');
        }
    });
}

function updateCampaignRow(campaign) {
    const campaignRow = document.querySelector(`[data-campaign-id="${campaign.id}"]`);
    if (campaignRow) {
        // Update status
        const statusElement = campaignRow.querySelector('.campaign-status');
        if (statusElement) {
            statusElement.textContent = campaign.status;
            statusElement.className = `campaign-status ${getStatusClass(campaign.status)}`;
        }
        
        // Update accept rate
        const acceptRateElement = campaignRow.querySelector('.campaign-accept-rate');
        if (acceptRateElement) {
            acceptRateElement.textContent = campaign.accept_rate + '%';
        }
        
        // Update total leads
        const totalLeadsElement = campaignRow.querySelector('.campaign-total-leads');
        if (totalLeadsElement) {
            totalLeadsElement.textContent = campaign.total_leads;
        }
        
        // Update total lead lists
        const totalLeadListsElement = campaignRow.querySelector('.campaign-total-lead-lists');
        if (totalLeadListsElement) {
            totalLeadListsElement.textContent = campaign.total_lead_list;
        }
        
        // Log the update
        console.log(`🔄 Updated campaign ${campaign.id}: ${campaign.status} (${campaign.accept_rate}%)`);
    }
}

function getStatusClass(status) {
    switch(status.toLowerCase()) {
        case 'running':
            return 'text-green-600';
        case 'completed':
            return 'text-blue-600';
        case 'stop':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
}

// Start updates when page loads
document.addEventListener('DOMContentLoaded', function() {
    startCampaignStatusUpdates();
    
    // Stop updates when page is hidden (to save resources)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (campaignUpdateInterval) {
                clearInterval(campaignUpdateInterval);
                console.log('⏸️ Paused campaign status updates (page hidden)');
            }
        } else {
            startCampaignStatusUpdates();
            console.log('▶️ Resumed campaign status updates (page visible)');
        }
    });
});
</script>
<div class="flex justify-between">
    <div class="flex items-center gap-3">
        <h2 class="text-lg font-bold text-gray-700 dark:text-gray-200 pt-4">
            Campaigns
        </h2>
        <div id="update-indicator" class="hidden flex items-center gap-2 text-sm text-gray-500">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
            <span>Updating...</span>
        </div>
    </div>
    <a href="{{route('campaign.create', ['step' =>'lead'])}}"
    class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-hidden focus:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none">
        New campaign
    </a>
</div>
<div class="mt-2 mb-5">
    <div>
        @foreach($campaigns as $item)
        <div class="grid grid-cols-12 p-4 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm" data-campaign-id="{{ $item->id }}">
            <div class="col-span-4">
                <div class="items-center justify-between dark:bg-gray-700 dark:border-gray-600">
                    <div class="flex gap-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                        <div class="mt-1 text-indigo-600">
                            <a href="{{route('campaign.create', ['step' => 'lead', 'cid' => $item->id])}}">{{ $item->name }}</a>
                        </div>
                    </div>
                    <small class="font-normal text-gray-400 uppercase campaign-status {{ $item->status == 'running' ? 'text-green-600' : ($item->status == 'completed' ? 'text-blue-600' : ($item->status == 'stop' ? 'text-red-600' : 'text-gray-600')) }}">{{ $item->status }}</small>
                </div>
            </div>
            <div class="col-span-3 flex">
                <div class="w-full">
                    <div class="flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                        <span class="campaign-total-lead-lists">{{ $item->total_lead_list }}</span>
                    </div>
                    <small class="font-normal text-gray-400">Lists of Leads</small>
                </div>
                <div class="w-full">
                    <div class="flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span class="campaign-total-leads">{{ $item->total_leads }}</span>
                    </div>
                    <small class="font-normal text-gray-400">All Leads</small>
                </div>
            </div>
            <div class="col-span-3 flex">
                <div class="w-full">
                    <div class="campaign-accept-rate">
                        {{ $item->accept_rate }}%
                    </div>
                    <small class="font-normal text-gray-400">Acceptance Rate</small>
                </div>
                <!-- <div class="w-full">
                    <div class="">
                        0%
                    </div>
                    <small class="font-normal text-gray-400">Reply rate</small>
                </div> -->
            </div>
            <div class="col-span-2 flex">
                <div class="w-full">
                    <div class="">
                        {{ date_format(date_create($item->created_at), "d M, Y") }}
                    </div>
                    <small class="font-normal text-gray-400">Created</small>
                </div>
                <div class="hs-dropdown relative inline-flex">
                    <button id="hs-dropdown-default-{{$item->id}}" type="button" class="hs-dropdown-toggle py-3 px-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                            <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                        </svg>
                    </button>
                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-default-{{$item->id}}">
                        <div class="p-1 space-y-0.5">
                            <form action="{{route('campaign.delete', ['id' => $item->id])}}" method="POST">
                                @csrf
                                @method('DELETE') 
                                <button type="submit" class="flex w-full items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if(count($campaigns)>0)
    {{ $campaigns->links() }}
    @endif
</div>
@endsection