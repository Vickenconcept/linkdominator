<div>
    <div class="flex justify-between my-6">
        <div class="flex gap-2">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 pt-2">
                Lists of leads
            </h3>
            <div class="mt-3"></div>
        </div>
        <button type="button" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-hidden focus:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none"
        aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-scale-animation-modal" data-hs-overlay="#hs-scale-animation-modal">
            Add leads
        </button>
    </div>
    @if(count($campaignlist) == 0)
    <div class="mt-10 mb-8">
        <span>
            <img src="{{asset('images/leads-no-content.svg')}}" alt="Content not available" class="mx-auto w-40">
        </span>
        <p class="text-center">Add leads from List to this campaign</p>
    </div>
    @endif
    <div class="mt-2 mb-5">
        <div>
            @foreach($campaignlist as $item)
            <div class="grid grid-cols-12 items-center p-4 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm  dark:bg-gray-700 dark:border-gray-600">
                <div class="col-span-4 flex gap-4 font-semibold text-sm text-gray-700 dark:text-gray-300">
                    <div class="bg-gray-200 rounded-full p-1 text-indigo-600">
                        <svg class="w-6 h-6  dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12.51 8.796v1.697a3.738 3.738 0 0 1 3.288-1.684c3.455 0 4.202 2.16 4.202 4.97V19.5h-3.2v-5.072c0-1.21-.244-2.766-2.128-2.766-1.827 0-2.139 1.317-2.139 2.676V19.5h-3.19V8.796h3.168ZM7.2 6.106a1.61 1.61 0 0 1-.988 1.483 1.595 1.595 0 0 1-1.743-.348A1.607 1.607 0 0 1 5.6 4.5a1.601 1.601 0 0 1 1.6 1.606Z" clip-rule="evenodd"/>
                            <path d="M7.2 8.809H4V19.5h3.2V8.809Z"/>
                        </svg>
                    </div>
                    <div class="mt-1">{{ $item->name }}</div>
                </div>
                <div class="col-span-4">
                    <p class="pb-0">{{ $item->leads }} Leads</p>
                    <small class="font-normal text-gray-400">Added</small>
                </div>
                <div class="col-span-4 flex justify-between">
                    <div>
                        <p class="pb-0">{{ date_format(date_create($item->created_at), "d M, Y") }}</p>
                        <small class="font-normal text-gray-400">Created date</small>
                    </div>
                    <form action="{{route('campaign.removelist', ['id' => $item->id])}}" method="post">
                        @csrf
                        <input type="hidden" name="cid" value="{{$cid}}">
                        <button class="text-xs font-normal text-center text-red-500 focus:border-0 p-0 focus:ring-0 dark:text-white dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @if(count($campaignlist)>0)
        {{ $campaignlist->links() }}
        @endif
    </div>

    @if(count($campaignlist) > 0)
    <div class="flex justify-center">
        <a href="{{route('campaign.create', ['step' => 'sequence', 'cid' => $cid])}}"
            class="block px-10 py-4 text-sm font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-600
            hover:bg-gray-600 focus:outline-none focus:shadow-outline-gray">
            <span class="mt-1">Next</span>
        </a>
    </div>
    @endif

    <div id="hs-scale-animation-modal" class="hs-overlay hidden size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="hs-scale-animation-modal-label">
        <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
            <div class="w-full flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
                <div class="flex justify-between items-center py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                    <h3 id="hs-scale-animation-modal-label" class="font-bold text-gray-800 dark:text-white">
                    Create a list of leads below
                    </h3>
                    <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600" aria-label="Close" data-hs-overlay="#hs-scale-animation-modal">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                    </button>
                </div>
                <form action="{{route('campaign.store')}}" method="post">
                    @csrf
                    <div class="p-4 overflow-y-auto">
                        <input type="hidden" name="step" value="lead" />
                        <input type="hidden" name="campaign_id" value="{{$cid}}" />
                        <label for="leadlist" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select a lead list</label>
                        <select id="leadlist" name="type"  class="py-3 px-4 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" required="">
                            @foreach($leadlist as $item)
                            <option value="{{ $item->type }}">{{ $item->list_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                        <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-overlay="#hs-scale-animation-modal">
                        Close
                        </button>
                        <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                        Add list
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>