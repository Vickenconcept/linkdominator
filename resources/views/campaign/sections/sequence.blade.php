<div>
    <h3 class="mt-4 text-center font-semibold">
        Select a sequence from one of our pre-build template or create a custom one
    </h3>
    <div class="grid grid-cols-12 gap-4 mt-6 mb-10">
        @foreach($sequenceTypes as $item)
            <div class="col-span-3">
                <div class="max-w-sm bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-xl transition-all cursor-pointer group hover:border-[#0077b5]" style="border-color: rgba(0, 119, 181, 0.2);">
                    <div class="bg-gray-50 group-hover:bg-gray-100 transition-colors rounded-t-lg">
                        <a href="{{route('campaign.create', ['step' => $item['route'], 'cid' => $cid])}}">
                            <img class="rounded-t-lg" src="{{asset($item['icon'])}}" alt="" />
                        </a>
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between">
                            <h5 class="mb-2 text-md font-semibold tracking-tight text-gray-900">
                                {{ $item['title'] }}
                            </h5>
                            <div class="hs-tooltip inline-block">
                                <span class="hs-tooltip-toggle">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 hover:text-gray-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                    </svg>
                                </span>
                                <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-800 text-xs font-medium text-white rounded-md shadow-md" role="tooltip">
                                    {{ $item['tooltip'] }}
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            @if($item['title'] != 'Custom Campaign')
                            <a href="#" class="mb-5 font-normal hover:underline" style="color: #0077b5;"></a>
                            @else
                            <div class="mb-5"></div>
                            @endif

                            @if($sequenceType && $sequenceType == $item['sequence_type'])
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium text-white ring-1 ring-inset" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%); border-color: #0077b5;">Selected</span>
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
            text-white transition-all duration-150 
            border border-transparent rounded-lg focus:outline-none" style="background: linear-gradient(135deg, #0077b5 0%, #005885 100%);" onmouseover="this.style.background='linear-gradient(135deg, #005885 0%, #004d6f 100%)'; this.style.boxShadow='0 4px 12px rgba(0, 119, 181, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, #0077b5 0%, #005885 100%)'; this.style.boxShadow='none';">
            <span class="mt-1">Back</span>
        </a>
    </div>
</div>