<div class="mt-3">
    <div class="w-full overflow-hidden rounded-lg">
        <div class="w-full overflow-x-auto">
            <div class="grid grid-cols-12 p-3 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm font-semibold px-3 text-sm">
                <div class="col-span-2">Name</div>
                <div class="col-span-2">Type</div>
                <div class="col-span-2">Status</div>
                <!-- <div class="col-span-2">Current Progress</div> -->
                <div class="col-span-2">Monitoring</div>
                <div class="col-span-3">Today Posts Found / Total</div>
                <div class="col-span-1"></div>
            </div>
            <div>
                @foreach($campaigns as $item)
                <div class="grid grid-cols-12 p-3 px-3 mb-2 bg-white hover:bg-indigo-100 rounded-lg shadow-sm font-normal cursor-pointer text-gray-600 text-sm">
                    <div class="col-span-2">{{ $item->campaign_name }}</div>
                    <div class="col-span-2">
                        @if($item->campaign_type == 'keyword')
                        {{ 'Monitor Search Phrases' }}
                        @else
                        {{ 'Monitor Profile Account' }}
                        @endif
                    </div>
                    <div class="col-span-2">
                        @if ($item->status == 'ongoing' || !$item->status)
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300">Ongoing</span>
                        @elseif ($item->status == 'processed')
                        <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">Processed</span>
                        @elseif ($item->status == 'stopped')
                        <span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">Stopped</span>
                        @endif
                    </div>
                    <div class="col-span-2">                        
                        @if ($item->campaign_type == 'keyword')
                            @if($item->keyword_list)
                            {{ count(explode("\n", $item->keyword_list)) }}
                            @else
                            0
                            @endif
                            Search Phrase(s)
                        @else
                            @if($item->profile_list)
                            {{ count(explode("\n", $item->profile_list)) }}
                            @else
                            0
                            @endif
                            Search Profile(s)
                        @endif
                    </div>
                    <div class="col-span-2">{{ $item->total_post_found }} / {{ $item->max_comment_per_day_campaign }}</div>
                    <div class="col-span-1 flex justify-end hs-dropdown">
                        <button id="hs-dropdown-default-{{$item->id}}" type="button" class="hs-dropdown-toggle py-2 px-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                                <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                            </svg>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-default-{{$item->id}}">
                            <div class="p-1 space-y-0.5">
                                <a href="{{ route('comment.create-campaign', ['cid' => $item->id, 'step' => 'express-setup']) }}" class="update-list-modal flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700"
                                aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-basic-modal" data-hs-overlay="#hs-basic-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" />
                                        <path d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z" />
                                    </svg>
                                    Edit
                                </a>
                                
                                <form action="{{route('comment.update-campaign-status', ['id' => $item->id])}}" method="POST">
                                    @csrf
                                    @method('PUT') 
                                    @if ($item->status == 'ongoing' || $item->status == 'processed')
                                    <input type="hidden" value="stopped" name="status">
                                    <button type="submit" class="block flex gap-1 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Stop
                                    </button>
                                    @elseif ($item->status == 'stopped')
                                    <input type="hidden" value="ongoing" name="status">
                                    <button type="submit" class="block flex gap-1 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                        </svg>
                                        Start
                                    </button>
                                    @endif
                                </form>
                                <form action="{{route('comment.delete-campaign', ['id' => $item->id])}}" method="POST">
                                    @csrf
                                    @method('DELETE') 
                                    <button type="submit" class="flex w-full items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" />
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @if(count($campaigns)>0)
            {{$campaigns->links()}}
        @endif
    </div>
</div>