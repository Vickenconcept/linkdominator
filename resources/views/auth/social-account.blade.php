@extends('layout.auth')

@section('content')
<div class="flex justify-between">
    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
        Accounts
    </h2>
    <button type="button"
    class="block px-4 py-4 text-sm font-medium leading-2 
    text-white transition-colors duration-150 bg-indigo-600 
    border border-transparent rounded-lg active:bg-indigo-600
    hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo"
    aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-basic-modal" data-hs-overlay="#hs-basic-modal">
        <span class="mt-1">Connect</span>
    </button>
</div>

<div class="mt-6">
    <div class="w-full overflow-hidden rounded-lg">
        <div class="w-full overflow-x-auto">
            <div class="grid grid-cols-12 p-3 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm font-semibold px-3 text-sm">
                <div class="col-span-1"></div>
                <div class="col-span-4">Email</div>
                <div class="col-span-3">Name</div>
                <div class="col-span-1">Provider</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-1"></div>
            </div>
            <div>
                @foreach($accounts as $account)
                <div class="grid grid-cols-12 py-4 px-3 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm font-normal text-sm">
                    <div class="col-span-1">
                        @if ($account->picture)
                        <img src="{{$account->picture}}" alt="" class="rounded-full w-8 h-8">
                        @else
                        <div class="bg-gray-200 rounded-full p-2 text-blue-600 w-[41px]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    <div class="col-span-4 pt-1">{{ $account->email }}</div>
                    @if($account->last_name)
                    <div class="col-span-3 pt-1">{{ $account->first_name . ' ' . $account->last_name }}</div>
                    @else
                    <div class="col-span-3 pt-1">{{ $account->first_name }}</div>
                    @endif
                    <div class="col-span-1 ">
                        <div class="w-[41px]">
                            @if ($account->oauth_provider == 'linkedin')
                            <div class="bg-gray-200 rounded-full p-2 text-blue-600">
                                <svg class="w-6 h-6 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M12.51 8.796v1.697a3.738 3.738 0 0 1 3.288-1.684c3.455 0 4.202 2.16 4.202 4.97V19.5h-3.2v-5.072c0-1.21-.244-2.766-2.128-2.766-1.827 0-2.139 1.317-2.139 2.676V19.5h-3.19V8.796h3.168ZM7.2 6.106a1.61 1.61 0 0 1-.988 1.483 1.595 1.595 0 0 1-1.743-.348A1.607 1.607 0 0 1 5.6 4.5a1.601 1.601 0 0 1 1.6 1.606Z" clip-rule="evenodd"/>
                                    <path d="M7.2 8.809H4V19.5h3.2V8.809Z"/>
                                </svg>
                            </div>
                            @elseif ($account->oauth_provider == 'calendly')
                            <div class="bg-gray-200 rounded-full p-2 text-indigo-600">
                                <svg role="img" class="w-6 h-6 text-indigo-600" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#006BFF"><title>Calendly</title><path d="M19.655 14.262c.281 0 .557.023.828.064 0 .005-.005.01-.005.014-.105.267-.234.534-.381.786l-1.219 2.106c-1.112 1.936-3.177 3.127-5.411 3.127h-2.432c-2.23 0-4.294-1.191-5.412-3.127l-1.218-2.106a6.251 6.251 0 0 1 0-6.252l1.218-2.106C6.736 4.832 8.8 3.641 11.035 3.641h2.432c2.23 0 4.294 1.191 5.411 3.127l1.219 2.106c.147.252.271.519.381.786 0 .004.005.009.005.014-.267.041-.543.064-.828.064-1.816 0-2.501-.607-3.291-1.306-.764-.676-1.711-1.517-3.44-1.517h-1.029c-1.251 0-2.387.455-3.2 1.278-.796.805-1.233 1.904-1.233 3.099v1.411c0 1.196.437 2.295 1.233 3.099.813.823 1.949 1.278 3.2 1.278h1.034c1.729 0 2.676-.841 3.439-1.517.791-.703 1.471-1.306 3.287-1.301Zm.005-3.237c.399 0 .794-.036 1.179-.11-.002-.004-.002-.01-.002-.014-.073-.414-.193-.823-.349-1.218.731-.12 1.407-.396 1.986-.819 0-.004-.005-.013-.005-.018-.331-1.085-.832-2.101-1.489-3.03-.649-.915-1.435-1.719-2.331-2.395-1.867-1.398-4.088-2.138-6.428-2.138-1.448 0-2.855.28-4.175.841-1.273.543-2.423 1.315-3.407 2.299S2.878 6.552 2.341 7.83c-.557 1.324-.842 2.726-.842 4.175 0 1.448.281 2.855.842 4.174.542 1.274 1.314 2.423 2.298 3.407s2.129 1.761 3.407 2.299c1.324.556 2.727.841 4.175.841 2.34 0 4.561-.74 6.428-2.137a10.815 10.815 0 0 0 2.331-2.396c.652-.929 1.158-1.949 1.489-3.03 0-.004.005-.014.005-.018-.579-.423-1.255-.699-1.986-.819.161-.395.276-.804.349-1.218.005-.009.005-.014.005-.023.869.166 1.692.506 2.404 1.035.685.505.552 1.075.446 1.416C22.184 20.437 17.619 24 12.221 24c-6.625 0-12-5.375-12-12s5.37-12 12-12c5.398 0 9.963 3.563 11.471 8.464.106.341.239.915-.446 1.421-.717.529-1.535.873-2.404 1.034.128.716.128 1.45 0 2.166-.387-.074-.782-.11-1.182-.11-4.184 0-3.968 2.823-6.736 2.823h-1.029c-1.899 0-3.15-1.357-3.15-3.095v-1.411c0-1.738 1.251-3.094 3.15-3.094h1.034c2.768 0 2.552 2.823 6.731 2.827Z"/></svg>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-span-2 pt-2">
                        @if($account->connected_status == 1)
                        <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">Connected</span>
                        @else
                        <span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">Disconnected</span>
                        @endif
                    </div>
                    <div class="col-span-1">
                        <form action="{{route('social-account.disconnect', ['id' => $account->id])}}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-2 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-indigo-800">Disconnect</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @if(count($accounts)>0)
            {{$accounts->links()}}
            @endif
        </div>
    </div>
</div>

<div id="hs-basic-modal" class="hs-overlay hs-overlay-open:opacity-100 hs-overlay-open:duration-500 hidden size-full fixed top-0 start-0 z-80 opacity-0 overflow-x-hidden transition-all overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="hs-basic-modal-label">
    <div class="sm:max-w-lg sm:w-full m-3 sm:mx-auto">
        <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                <h3 id="hs-basic-modal-label" class="font-bold text-gray-800 dark:text-white">
                Connect Account
                </h3>
                <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600" aria-label="Close" data-hs-overlay="#hs-basic-modal">
                <span class="sr-only">Close</span>
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                </button>
            </div>
            <div class="p-4 overflow-y-auto">
                <button class="linkedin-login flex gap-2 hover:bg-indigo-600 border border-indigo-600 rounded-md hover:text-white py-3 px-3 w-full">
                    <div class="bg-gray-200 rounded-full p-2 text-blue-600">
                        <svg class="w-6 h-6 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12.51 8.796v1.697a3.738 3.738 0 0 1 3.288-1.684c3.455 0 4.202 2.16 4.202 4.97V19.5h-3.2v-5.072c0-1.21-.244-2.766-2.128-2.766-1.827 0-2.139 1.317-2.139 2.676V19.5h-3.19V8.796h3.168ZM7.2 6.106a1.61 1.61 0 0 1-.988 1.483 1.595 1.595 0 0 1-1.743-.348A1.607 1.607 0 0 1 5.6 4.5a1.601 1.601 0 0 1 1.6 1.606Z" clip-rule="evenodd"/>
                            <path d="M7.2 8.809H4V19.5h3.2V8.809Z"/>
                        </svg>
                    </div>
                    <div class="text-start">
                        <span class="">LinkedIn</span>
                        <p class="text-xs">Connect your linkedin account</p>
                    </div>
                </button>
            </div>
            <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-overlay="#hs-basic-modal">
                Close
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    $('.linkedin-login').click(function(){
        window.location = "{{route('integration.login')}}"
    })
</script>
@endsection