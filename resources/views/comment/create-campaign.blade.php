@extends('layout.auth')

@section('content')
<div class="block mt-4 p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
    <div class="pb-4">
        <ol class="items-center justify-between w-full space-y-4 sm:flex sm:space-x-8 sm:space-y-0 rtl:space-x-reverse">
            <li class="flex items-center text-indigo-600 dark:text-indigo-500 space-x-2.5 rtl:space-x-reverse">
                <span class="flex items-center justify-center w-8 h-8 border border-indigo-600 rounded-full shrink-0 dark:border-indigo-500 bg-indigo-600 text-white">
                    1
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">Express setup</h3>
                </span>
            </li>
            <li class="flex items-center space-x-2.5 rtl:space-x-reverse text-gray-500
                {{ request()->query('step') == 'type' || request()->query('step') == 'search' || request()->query('step') == 'ai_commenter' || request()->query('step') == 'final' ? 'text-indigo-600': '' }}">
                <span class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 
                {{ request()->query('step') == 'type' || request()->query('step') == 'search' || request()->query('step') == 'ai_commenter' || request()->query('step') == 'final' ? 'border-indigo-600 bg-indigo-600 text-white': '' }}">
                    2
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">Type</h3>
                </span>
            </li>
            <li class="flex items-center space-x-2.5 rtl:space-x-reverse text-gray-500
                {{ request()->query('step') == 'search' || request()->query('step') == 'ai_commenter' || request()->query('step') == 'final' ? 'text-indigo-600': '' }}">
                <span class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400 
                {{ request()->query('step') == 'search' || request()->query('step') == 'ai_commenter' || request()->query('step') == 'final' ? 'border-indigo-600 bg-indigo-600 text-white': '' }}">
                    3
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">Search</h3>
                </span>
            </li>
            <li class="flex items-center space-x-2.5 rtl:space-x-reverse text-gray-500
                {{ request()->query('step') == 'ai_commenter' || request()->query('step') == 'final' ? 'text-indigo-600': '' }}">
                <span class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400 
                {{ request()->query('step') == 'ai_commenter' || request()->query('step') == 'final' ? 'border-indigo-600 bg-indigo-600 text-white': '' }}">
                    4
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">AI Commenter</h3>
                </span>
            </li>
            <li class="flex items-center space-x-2.5 rtl:space-x-reverse text-gray-500
                {{ request()->query('step') == 'final' ? 'text-indigo-600': '' }}">
                <span class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400 
                {{ request()->query('step') == 'final' ? 'border-indigo-600 bg-indigo-600 text-white': '' }}">
                    5
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">Final</h3>
                </span>
            </li>
        </ol>
    </div>
    <hr>
    <div class="mt-4">
        @if (request()->has('step') && request()->query('step') == 'express-setup')
        <div class="comment-campaign-section form-section">
            @include('comment.steps.express-setup')
        </div>
        @elseif (request()->has('step') && request()->query('step') == 'type')
        <div class="comment-campaign-section form-section">
            @include('comment.steps.type')
        </div>
        @elseif (request()->has('step') && request()->query('step') == 'search')
        <div class="comment-campaign-section form-section">
            @include('comment.steps.search')
        </div>
        @elseif (request()->has('step') && request()->query('step') == 'ai_commenter')
        <div class="comment-campaign-section form-section">
            @include('comment.steps.aicommenter')
        </div>
        @elseif (request()->has('step') && request()->query('step') == 'final')
        <div class="comment-campaign-section form-section">
            @include('comment.steps.final')
        </div>
        @endif
    </div>
</div>
@endsection