@extends('layout.auth')

@section('content')
<div class="flex justify-between">
    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
        Posts
    </h2>
    <div class="flex gap-3">
        <form action="{{route('post.index')}}" method="get">
            <label class="block text-sm">
                <div class="relative text-gray-500 focus-within:text-indigo-600">
                    <input 
                    type="text"
                    class="block w-full pr-20 text-sm text-black 
                    dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 
                    focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:focus:shadow-outline-gray 
                    form-input rounded-md border-gray-200"
                    placeholder="Search post"
                    name="post" value="{{request()->query('post')}}"/>
                    <button 
                    type="submit"
                    class="absolute inset-y-0 right-0 px-4 text-sm 
                    font-medium leading-5 text-white transition-colors 
                    duration-150 bg-indigo-600 border border-transparent 
                    rounded-r-md active:bg-indigo-600 hover:bg-indigo-700 
                    focus:outline-none focus:shadow-outline-indigo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </label>
        </form>
        <a href="{{route('post.create')}}"
        class="block px-4 py-2 pt-3 text-sm font-medium leading-2 
        text-white transition-colors duration-150 bg-indigo-600 
        border border-transparent rounded-lg active:bg-indigo-600 
        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo
        flex">
            New post
        </a>
    </div>
</div>

<div id="notice-board" class="hidden">
    <div id="notice"
    class="p-4 mb-4 mt-4 text-sm text-red-800 
    rounded-lg bg-red-100 dark:bg-gray-800 
    dark:text-red-400"
    role="alert"></div>
</div>
<div class="p-4 mb-4 mt-4 text-sm text-blue-800 rounded-lg bg-blue-100 dark:bg-gray-800 dark:text-blue-400" role="alert">
    <span class="font-bold">Note:</span> 
    <ul>
        <li class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            This platform is connected via API tokens which expire once in 60 days.
        </li>
        <li class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Signing out of your LinkedIn accounts on LinkDominator will hinder any scheduled post.
        </li>
        <li class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Changing your LinkedIn account passwords will hinder any scheduled post on LinkDominator.
        </li>
        <li class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Getting suspensions on your LinkedIn account will hinder any scheduled post on LinkDominator.
        </li>
        <li class="flex">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Exceeding the daily post limit will hinder any scheduled post on LinkDominator.
        </li>
    </ul>
</div>

<div class="mt-3">
    <div class="w-full overflow-hidden rounded-lg">
        <div class="w-full overflow-x-auto">
            <div class="grid grid-cols-12 p-3 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm font-semibold px-3 text-sm">
                <div class="col-span-4">Post</div>
                <div class="col-span-3">Status</div>
                <div class="col-span-2">Date Created</div>
                <div class="col-span-2"></div>
                <div class="col-span-1"></div>
            </div>
            <div>
                @foreach($posts as $post)
                <div class="grid grid-cols-12 gap-x-3 p-3 px-3 mb-2 bg-white hover:bg-indigo-100 border border-gray-200 rounded-lg shadow-sm font-normal cursor-pointer text-gray-600 text-sm post-list-{{$post->id}}">
                    <div class="col-span-4 truncate">
                        <a href="{{route('post.edit', ['id' => $post->id])}}">
                            {{$post->content}}
                        </a>
                    </div>
                    <div class="col-span-3 capitalize">{{$post->publish_status}}</div>
                    <div class="col-span-2">{{date_format(date_create($post->created_at), "d M, Y")}}</div>
                    <div class="col-span-2">
                        @if($post->comment)
                            {{$post->comment}}
                        @endif
                    </div>
                    <div class="col-span-1">
                    @if ($post->publish_status == 'draft' || $post->publish_status == 'failed' || $post->publish_status == 'scheduled')
                        <div class="flex gap-3">
                            <a href="{{route('post.edit', ['id' => $post->id])}}" class="open-update-post-modal text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" />
                                <path d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z" />
                                </svg>
                            </a>
                            <form action="{{route('post.delete', ['id' => $post->id])}}" method="post">
                                @csrf
                                @method('DELETE')
                                <button
                                type="submit"
                                class="open-delete-post-modal text-indigo-600" 
                                data-id="{{$post->id}}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                    <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @if(count($posts)>0)
    {{ $posts->links() }}
    @endif
</div>
@endsection