@extends('layout.auth')

@section('content')
<div class="border-b border-gray-200 dark:border-neutral-700 bg-white rounded-lg px-3">
    <nav class="flex gap-x-1" aria-label="Tabs" role="tablist" aria-orientation="horizontal">
        <a href="{{route('comment.index',['tab' => 'feeds'])}}" class="hs-tab-active:font-semibold hs-tab-active:border-blue-600 hs-tab-active:text-blue-600 py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 {{request()->query('tab') == 'feeds' ? 'active':''}}" id="tabs-with-underline-item-1" aria-selected="true" data-hs-tab="#tabs-with-underline-1" aria-controls="tabs-with-underline-1" role="tab">
        Comment Feeds 
        </a>
        <a href="{{route('comment.index',['tab' => 'campaigns'])}}" class="hs-tab-active:font-semibold hs-tab-active:border-blue-600 hs-tab-active:text-blue-600 py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 {{request()->query('tab') == 'campaigns' ? 'active':''}}" id="tabs-with-underline-item-2" aria-selected="false" data-hs-tab="#tabs-with-underline-2" aria-controls="tabs-with-underline-2" role="tab">
        Your Campaigns
        </a>
    </nav>
</div>

<div class="mt-3">
    <div id="tabs-with-underline-1" class="{{request()->query('tab') == 'feeds' ? 'hidden':''}}" role="tabpanel" aria-labelledby="tabs-with-underline-item-1">
        @include('comment.campaign')
    </div>
    <div id="tabs-with-underline-2" class="{{request()->query('tab') == 'campaigns' ? 'hidden':''}}" role="tabpanel" aria-labelledby="tabs-with-underline-item-2">
        @include('comment.feeds')
    </div>
</div>
@endsection