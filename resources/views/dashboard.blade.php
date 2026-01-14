@extends('layout.auth')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="grid gap-6 mb-8 md:grid-cols-3 xl:grid-cols-3">
    <div class="flex flex-col bg-white border border-gray-200 border-t-4 border-t-[#0077b5] shadow-md rounded-xl hover:shadow-lg transition-shadow h-full">
        <div class="p-4 md:p-5 flex-1 flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800 num-connects"></h3>
                <p class="mt-2 text-gray-500">
                    Connections
                </p>
            </div>
        </div>
    </div>
    <div class="flex flex-col bg-white border border-gray-200 border-t-4 border-t-[#0077b5] shadow-md rounded-xl hover:shadow-lg transition-shadow h-full">
        <div class="p-4 md:p-5 flex-1 flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800 sent-invite"></h3>
                <p class="mt-2 text-gray-500">
                    Pending sent invitations
                </p>
            </div>
        </div>
    </div>
    <div class="flex flex-col bg-white border border-gray-200 border-t-4 border-t-[#0077b5] shadow-md rounded-xl hover:shadow-lg transition-shadow h-full">
        <div class="p-4 md:p-5 flex-1 flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800 profile-views"></h3>
                <p class="mt-2 text-gray-500">
                    Profile views since last week
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid gap-6 mb-8 md:grid-cols-2">
    <div class="min-w-0 p-4 bg-white rounded-lg shadow-md border border-gray-200">
        <div id="pie-chart"></div>
    </div>
    <div class="min-w-0 p-4 bg-white rounded-lg shadow-md border border-gray-200">
        <div id="line-chart"></div>
    </div>
</div>

<div class="min-w-0 p-4 bg-white rounded-lg shadow-md border border-gray-200">
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