@extends('layout.auth')

@section('content')
<div class="mt-4">
    <div class="border-b border-gray-200 dark:border-neutral-700 bg-white rounded-lg">
        <nav class="flex gap-x-4" aria-label="Tabs" role="tablist" aria-orientation="horizontal">
            <a href="{{route('calls')}}" class="hs-tab-active:font-semibold hs-tab-active:border-blue-600 hs-tab-active:text-blue-600 py-4 px-2 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500" id="tabs-with-underline-item-1" aria-selected="false" data-hs-tab="#tabs-with-underline-1" aria-controls="tabs-with-underline-1" role="tab">
            Call Status 
            </a>
            <a href="{{route('calls.reminders')}}" class="hs-tab-active:font-semibold hs-tab-active:border-blue-600 hs-tab-active:text-blue-600 py-4 px-2 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 active" id="tabs-with-underline-item-2" aria-selected="true" data-hs-tab="#tabs-with-underline-2" aria-controls="tabs-with-underline-2" role="tab">
            Call Reminders
            </a>
        </nav>
    </div>
    <div class="mt-3">
        <div id="tabs-with-underline-2" role="tabpanel" aria-labelledby="tabs-with-underline-item-2">
            @include('callmanager.reminder-list')
        </div>
    </div>
</div>
@endsection