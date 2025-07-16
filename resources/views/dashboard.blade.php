@extends('layout.auth')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="grid gap-6 mb-8 md:grid-cols-4 xl:grid-cols-4">
    <div class="max-w-xs flex flex-col bg-white border border-gray-200 border-t-4 border-t-indigo-600 shadow-2xs rounded-xl dark:bg-neutral-900 dark:border-neutral-700 dark:border-t-indigo-500 dark:shadow-neutral-700/70">
        <div class="p-4 md:p-5">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white num-connects"></h3>
            <p class="mt-2 text-gray-500 dark:text-neutral-400">
                Connections
            </p>
            <!-- <a class="mt-3 inline-flex items-center gap-x-1 text-sm font-semibold rounded-lg border border-transparent text-indigo-600 decoration-2 hover:text-indigo-700 hover:underline focus:underline focus:outline-hidden focus:text-indigo-700 disabled:opacity-50 disabled:pointer-events-none dark:text-indigo-500 dark:hover:text-indigo-600 dark:focus:text-indigo-600" href="#">
                Card link
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </a> -->
        </div>
    </div>
    <div class="max-w-xs flex flex-col bg-white border border-gray-200 border-t-4 border-t-indigo-600 shadow-2xs rounded-xl dark:bg-neutral-900 dark:border-neutral-700 dark:border-t-indigo-500 dark:shadow-neutral-700/70">
        <div class="p-4 md:p-5">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white sent-invite"></h3>
            <p class="mt-2 text-gray-500 dark:text-neutral-400">
            Pending sent invitations
            </p>
            <!-- <a class="mt-3 inline-flex items-center gap-x-1 text-sm font-semibold rounded-lg border border-transparent text-indigo-600 decoration-2 hover:text-indigo-700 hover:underline focus:underline focus:outline-hidden focus:text-indigo-700 disabled:opacity-50 disabled:pointer-events-none dark:text-indigo-500 dark:hover:text-indigo-600 dark:focus:text-indigo-600" href="#">
                Card link
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </a> -->
        </div>
    </div>
    <div class="max-w-xs flex flex-col bg-white border border-gray-200 border-t-4 border-t-indigo-600 shadow-2xs rounded-xl dark:bg-neutral-900 dark:border-neutral-700 dark:border-t-indigo-500 dark:shadow-neutral-700/70">
        <div class="p-4 md:p-5">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white profile-views"></h3>
            <p class="mt-2 text-gray-500 dark:text-neutral-400">
                Profile views since last week
            </p>
            <!-- <a class="mt-3 inline-flex items-center gap-x-1 text-sm font-semibold rounded-lg border border-transparent text-indigo-600 decoration-2 hover:text-indigo-700 hover:underline focus:underline focus:outline-hidden focus:text-indigo-700 disabled:opacity-50 disabled:pointer-events-none dark:text-indigo-500 dark:hover:text-indigo-600 dark:focus:text-indigo-600" href="#">
                Card link
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </a> -->
        </div>
    </div>
    <div class="max-w-xs flex flex-col bg-white border border-gray-200 border-t-4 border-t-indigo-600 shadow-2xs rounded-xl dark:bg-neutral-900 dark:border-neutral-700 dark:border-t-indigo-500 dark:shadow-neutral-700/70">
        <div class="p-4 md:p-5">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white appearance"></h3>
            <p class="mt-2 text-gray-500 dark:text-neutral-400">
                Weekly search appearances
            </p>
            <!-- <a class="mt-3 inline-flex items-center gap-x-1 text-sm font-semibold rounded-lg border border-transparent text-indigo-600 decoration-2 hover:text-indigo-700 hover:underline focus:underline focus:outline-hidden focus:text-indigo-700 disabled:opacity-50 disabled:pointer-events-none dark:text-indigo-500 dark:hover:text-indigo-600 dark:focus:text-indigo-600" href="#">
                Card link
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </a> -->
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid gap-6 mb-8 md:grid-cols-2">
    <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div id="pie-chart"></div>
    </div>
    <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <div id="line-chart"></div>
    </div>
</div>

<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
    <div id="bar-chart"></div>
</div>

<script src="{{ asset('js/dashboard-bar-chart.js') }}"></script>
<script src="{{ asset('js/dashboard-pie-chart.js') }}"></script>
<script src="{{ asset('js/dashboard-line-chart.js') }}"></script>

<script>
function ministats(){
    $.ajax({
        url: '/ministats',
        method: 'get',
        success: function(res){
            $('.appearance').text(`${res.searchAppearance}`)
            $('.profile-views').text(`${res.profileViews}%`)
            $('.sent-invite').text(`${res.sentInvites}`)
            $('.num-connects').text(`${res.numConnections}`)
        },
        error: function(error){
            console.log(error)
        }
    })
}
ministats()
</script>
@endsection