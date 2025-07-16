<div class="mt-3">
    <div class="w-full overflow-hidden rounded-lg">
        <div class="w-full overflow-x-auto">
            <div class="grid grid-cols-12 p-3 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm font-semibold px-3 text-sm">
                <div class="col-span-2">Campaign</div>
                <div class="col-span-2">Request</div>
                <div class="col-span-2">Recipients</div>
                <div class="col-span-2">Contacted</div>
                <div class="col-span-2">Replies</div>
                <div class="col-span-2">Not reached</div>
                <div class="col-span-1"></div>
            </div>
            <div>
                @foreach($callReminder as $item)
                <div class="grid grid-cols-12 p-3 px-3 mb-2 bg-white hover:bg-indigo-100 rounded-lg shadow-sm font-normal cursor-pointer text-gray-600 text-sm">
                    <div class="col-span-2">{{$item->name}}</div>
                    <div class="col-span-2">{{$item->requests}}</div>
                    <div class="col-span-2">{{$item->recipients}}</div>
                    <div class="col-span-2">{{$item->contacted}}</div>
                    <div class="col-span-2">{{$item->replies}}</div>
                    <div class="col-span-1">{{$item->not_reached}}</div>
                    <div class="col-span-1">
                        <div class="flex gap-3">
                            <button type="button"
                            data-id="{{$item->id}}"
                            data-cname="{{$item->name}}"
                            class="view-reminder-modal text-indigo-600">
                                View
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @if(count($callReminder)>0)
            {{ $callReminder->links() }}
            @endif
        </div>
    </div>
</div>

<button type="button" id="view-reminder-btn" style="display: none;" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-basic-modal" data-hs-overlay="#hs-basic-modal">
  Open modal
</button>
<div id="hs-basic-modal" class="hs-overlay hs-overlay-open:opacity-100 hs-overlay-open:duration-500 hidden size-full fixed top-0 start-0 z-80 opacity-0 overflow-x-hidden transition-all overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="hs-basic-modal-label">
    <div class="sm:max-w-2xl sm:w-full m-3 sm:mx-auto">
        <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                <h3 id="hs-basic-modal-label" class="font-bold text-gray-800 dark:text-white modal-header-text">
                Reminders for
                </h3>
                <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600" aria-label="Close" data-hs-overlay="#hs-basic-modal">
                <span class="sr-only">Close</span>
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                </button>
            </div>
            <form method="post" action="{{route('calls.update.reminder-message')}}">
                @csrf
                @method('put')
                <div class="p-4 overflow-y-auto">
                    <div>
                        <input type="hidden" name="id" class="call-reminder-id" />
                        <div class="mb-4">
                            <div class="flex justify-between">
                                <label class="font-medium text-gray-900">
                                    16-24 Hours Before
                                </label>
                                <div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="16_24_hours_before_status" id="_16-24-status" class="sr-only peer" >
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex gap-1 mt-2">
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@firstName','16-24')">
                                        First name
                                    </button>
                                </div>
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@lastName','16-24')">
                                        Last name
                                    </button>
                                </div>
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@name','16-24')">
                                        Full name
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-4 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@position','16-24')">
                                        Position
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@company','16-24')">
                                        Company
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@location','16-24')">
                                        Location
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <textarea id="_16-24-message" name="16_24_hours_before_message" rows="6" class="text-xs block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="mt-3 mb-4">
                            <div class="flex justify-between">
                                <label class="font-medium text-gray-900">
                                    Couple Hours Before
                                </label>
                                <div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="couple_hours_before_status" id="_couple-status" class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex gap-1 mt-2">
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@firstName','couple')">
                                        First name
                                    </button>
                                </div>
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@lastName','couple')">
                                        Last name
                                    </button>
                                </div>
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@name','couple')">
                                        Full name
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-4 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@position','couple')">
                                        Position
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@company','couple')">
                                        Company
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@location','couple')">
                                        Location
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <textarea id="_couple-message" name="couple_hours_before_message" rows="6" class="text-xs block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="mt-3">
                            <div class="flex justify-between">
                                <label class="font-medium text-gray-900">
                                    10-40 Minutes Before
                                </label>
                                <div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="10_40_minutes_before_status" id="_10-40-status" class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex gap-1 mt-2">
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@firstName','10-40')">
                                        First name
                                    </button>
                                </div>
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@lastName','10-40')">
                                        Last name
                                    </button>
                                </div>
                                <div class="">
                                    <button type="button" class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@name','10-40')">
                                        Full name
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-4 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@position','10-40')">
                                        Position
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@company','10-40')">
                                        Company
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                    text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                    focus:shadow-outline-indigo"
                                    onclick="addVariableToMessage('@location','10-40')">
                                        Location
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <textarea id="_10-40-message" name="10_40_minutes_before_message" rows="6" class="text-xs block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                    <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-overlay="#hs-basic-modal">
                    Close
                    </button>
                    <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                    Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('.view-reminder-modal').click(function(){
    $('#view-reminder-btn').click()
    $('.modal-header-text').text('Reminder for '+ $(this).data('cname'))

    // Set value to fields
    $('.call-reminder-id').val($(this).data('id'))
    
    $.ajax({
        url: `/call/reminder-messages/${$(this).data('id')}`,
        method: 'get',
        success: function(res){
            $('#_16-24-message').val(res.data['16_24_hours_before_message'])
            if(res.data['16_24_hours_before_status'] == 1)
                $('#_16-24-status').prop('checked',true).val(1)
            else $('#_16-24-status').val(0)

            $('#_couple-message').val(res.data['couple_hours_before_message'])
            if(res.data['couple_hours_before_status'] == 1)
                $('#_couple-status').prop('checked',true).val(1)
            else $('#_couple-status').val(0)

            $('#_10-40-message').val(res.data['10_40_minutes_before_message'])
            if(res.data['10_40_minutes_before_status'] == 1)
                $('#_10-40-status').prop('checked',true).val(1)
            else $('#_10-40-status').val(0)
        }
    })
})

$('#_16-24-status').change(function(){
    if($(this).prop('checked')){
        $(this).val(1)
    }else{
        $(this).val(0)
    }
})
$('#_couple-status').change(function(){
    if($(this).prop('checked')){
        $(this).val(1)
    }else{
        $(this).val(0)
    }
})
$('#_10-40-status').change(function(){
    if($(this).prop('checked')){
        $(this).val(1)
    }else{
        $(this).val(0)
    }
})

const addVariableToMessage = (mv, source) => {
    let cursorPos, textBefore, textAfter, message;

    if(source == '16-24'){
        message = document.querySelector("#_16-24-message")
    }else if(source == 'couple'){
        message = document.querySelector("#_couple-message")
    }else if(source == '10-40'){
        message = document.querySelector("#_10-40-message")
    }
    cursorPos = message.selectionStart
    textBefore = message.value.substring(0,  cursorPos)
    textAfter  = message.value.substring(cursorPos, message.value.length)
    message.value = textBefore + mv + textAfter
}
</script>