<div>
    <h3 class="mt-4 text-center font-semibold">
        Select a sequence from one of our pre-build template or create a custom one
    </h3>
    <div class="grid grid-cols-12 gap-4 mt-6 mb-10">
        @foreach($sequenceTypes as $item)
            <div class="col-span-3">
                <div class="max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                    <div class="bg-gray-100">
                        <a href="{{route('campaign.create', ['step' => $item['route'], 'cid' => $cid])}}">
                            <img class="rounded-t-lg" src="{{asset($item['icon'])}}" alt="" />
                        </a>
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between">
                            <h5 class="mb-2 text-md font-semibold tracking-tight text-gray-900 dark:text-white">
                                {{ $item['title'] }}
                            </h5>
                            <div class="hs-tooltip inline-block">
                                <span class="hs-tooltip-toggle">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                    </svg>
                                </span>
                                <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                                    {{ $item['tooltip'] }}
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            @if($item['title'] != 'Custom Campaign')
                            <a href="#" class="mb-5 font-normal text-indigo-600 hover:underline dark:text-gray-400 "></a>
                            @else
                            <div class="mb-5"></div>
                            @endif

                            @if($sequenceType && $sequenceType == $item['sequence_type'])
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Selected</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-center">
        <a href="{{route('campaign.create', ['step' => 'lead', 'cid' => $cid])}}"
            class="block px-10 py-4 text-sm font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-600
            hover:bg-gray-600 focus:outline-none focus:shadow-outline-gray">
            <span class="mt-1">Back</span>
        </a>
    </div>
</div>