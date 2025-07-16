@extends('layout.auth')

@section('content')
<h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
    New Post
</h2>
<div class="p-4 mb-2 mt-4 text-sm text-red-800 rounded-lg 
bg-red-100 dark:bg-gray-800 dark:text-red-400 hidden"
id="notification" role="alert">
    <span class="font-medium">Error!</span>
    <span class="notify-message"></span>
</div>
<form action="{{route('post.store')}}" method="post" class="mt-6" enctype="multipart/form-data">
    @csrf
    <div class="md:grid grid-cols-12 gap-6">
        <div class="col-span-8">
            <span class="text-gray-700 dark:text-gray-400">
                Content
            </span>
            <label class="block mt-4 text-sm">
                <!-- <span class="text-gray-700 dark:text-gray-400">Content</span> -->
                <textarea
                id="content"
                name="content"
                class="block w-full mt-2 text-sm dark:text-gray-300 
                dark:border-gray-600 dark:bg-gray-700 form-textarea 
                focus:border-indigo-400 focus:outline-none rounded-md
                focus:shadow-outline-indigo dark:focus:shadow-outline-gray"
                rows="15" required
                >{{old('content')}}</textarea>
            </label>
            <div class="flex justify-between mt-4">
                <label class="block text-sm">
                    <button type="button" id="use-aicontent"
                        class="bg-gray-300 text-gray-700 dark:text-gray-400 p-2 rounded-md focus-ring:0"
                        data-tooltip-target="tooltip-generate-aicontent"
                    >
                        Use AI Content
                    </button>
                    <div id="tooltip-generate-aicontent" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                        Generate AI content 
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </label>
                <label class="block text-sm mt-1">
                    <span class="text-gray-700 dark:text-gray-400">Word count: </span>
                    <div class="text-gray-700 dark:text-gray-400 inline-flex" id="words">0</div>
                    <input style="display: none;"
                    type="number" id="words-input" name="words"
                    class="block mt-1 text-sm dark:text-gray-300 w-[5rem]
                    dark:border-gray-600 dark:bg-gray-700 form-textarea 
                    focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:focus:shadow-outline-gray"
                    value="0" readonly>
                </label>
            </div>
            <div class="flex gap-3 mt-3" id="generate-aicontent-container" style="display: none;">
                <div class="w-full">
                    <input
                        type="text" id="ai_idea" name="ai_idea"
                        class="block text-sm dark:text-gray-300 w-full
                        dark:border-gray-600 dark:bg-gray-700 form-input 
                        focus:border-indigo-500 focus:outline-none rounded
                        focus:shadow-outline-indigo dark:focus:shadow-outline-gray"
                        placeholder="Enter topic to generate content"
                    >
                </div>
                <div>
                    <div style="display: block;" class="get-aicontent-block">
                        <button type="button" id="get-aicontent" class="p-1 rounded-md bg-gray-300" data-tooltip-target="tooltip-generate-aicontent-action">
                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                            </svg>
                        </button>
                        <div id="tooltip-generate-aicontent-action" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                            Generate content
                            <div class="tooltip-arrow" data-popper-arrow></div>
                        </div>
                    </div>
                    <div style="display: none;" id="loader-aicontent">
                        <button type="button" class="px-2 py-1 rounded-md bg-gray-300" data-tooltip-target="tooltip-generate-aicontent-action">
                            <div role="status">
                                <svg aria-hidden="true" class="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-indigo-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                </svg>
                                <span class="sr-only">Loading...</span>
                            </div>
                        </button>
                    </div>
                </div>
                <div>
                    <button type="button" id="cancel-aicontent" class="p-1 rounded-md bg-gray-300" data-tooltip-target="tooltip-cancel-aicontent-action">
                        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                        </svg>
                    </button>
                    <div id="tooltip-cancel-aicontent-action" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                        Cancel
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-4">
            <div class="">
                <span class="text-gray-700 dark:text-gray-400">
                    Add image or article:
                </span>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-300" id="image_help">
                    You can only upload an image when no article is added. 
                    You can only add an article when no image is uploaded.
                </div>
                <div class="mt-4 text-sm w-full image_label">
                    <label for="image" class="sr-only">Choose file</label>
                    <input type="file" name="image" id="image" class="block w-full border border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400
                        file:bg-gray-50 file:border-0
                        file:me-4
                        file:py-3 file:px-4
                        dark:file:bg-neutral-700 dark:file:text-neutral-400"
                        accept="image/gif, image/jpeg, image/png">

                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-300" id="image_help">
                        Recommended sizes: 1080 x 1080 (square), 1920 x 1080 (portrait). Max 5MB.
                    </div>
                </div>
                <label class="block mt-4 text-sm w-full article_label">
                    <input
                    type="url"
                    class="block w-full mt-1 text-sm dark:border-gray-600 
                    dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:text-gray-300 
                    dark:focus:shadow-outline-gray form-input rounded-md border-gray-200"
                    placeholder="https://myarticlelink.com"
                    id="article_link" name="article"
                    />
                </label>
            </div>
            <label class="block mt-6 mb-4 text-sm" id="p_status">
                <span class="text-gray-700 dark:text-gray-400">
                    Schedule (UTC)
                </span>
                <select class="block w-full mt-1 text-sm 
                dark:text-gray-300 dark:border-gray-600 rounded-md
                dark:bg-gray-700 form-select focus:border-indigo-400 
                focus:outline-none focus:shadow-outline-indigo 
                dark:focus:shadow-outline-gray"
                id="publish_status"
                name="save_mode">
                    <option value="instant">Instant</option>
                    <option value="schedule">Schedule</option>
                    <option value="draft">Draft</option>
                </select>
            </label>
            <label class="block mt-4 text-sm w-full hidden schedule_time_label">
                <input
                type="datetime-local"
                class="block w-full mt-1 text-sm dark:border-gray-600 
                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                focus:shadow-outline-indigo dark:text-gray-300 
                dark:focus:shadow-outline-gray form-input"
                id="schedule_time" name="schedule_time"
                />
            </label>
        </div>
    </div>
    <div class="mt-8">
        <button type="submit" 
        class="inline-flex w-full justify-center rounded-md 
        bg-indigo-600 px-3 py-2 text-sm font-semibold text-white 
        shadow-sm hover:bg-indigo-500 sm:w-auto"
        id="submit">
            <span>Submit</span>
        </button>
    </div>
</form>

<script>
$('#publish_status').change(function() {
    if($(this).val() == 'schedule')
        $('.schedule_time_label').show()
    else
        $('.schedule_time_label').hide()
})

// Get content count realtime
$('#content').bind('input propertychange',function() {
    let content = $(this).val()
    if(content) {
        $('#words').html(content.split(' ').length)
        $('#words-input').val(content.split(' ').length)
    }else {
        $('#words').html(0)
        $('#words-input').val(0)
    }
})

$('#use-aicontent').click(function(){
    $('#ai_idea').val('')
    $('#generate-aicontent-container').show()
})

$('#cancel-aicontent').click(function(){
    $('#ai_idea').val('')
    $('#generate-aicontent-container').hide()
})

$('#get-aicontent').click(function(){
    if($('#ai_idea').val()){
        let formData = new FormData;
        formData.append('topic', $('#ai_idea').val());

        $(this).attr('disabled', true)
        $('.get-aicontent-block').hide()
        $('#loader-aicontent').show()
        $('#cancel-aicontent').attr('disabled', true)

        fetch('/post/generate-aicontent',{
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
            method: 'post',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                if(res.status == 200){
                    $('#content').val(res.data.content)
                    $('#words').text(res.data.words)
                    $('#words-input').val(res.data.words)
                }
                $('#loader-aicontent').hide()
                $('.get-aicontent-block').show()
                $('#get-aicontent').attr('disabled', false)
                $('#cancel-aicontent').attr('disabled', false)
            })
    }
    return;
})

// validate image or article usage
$('#article_link').bind('input propertychange',function() {
    if($('#image').val()) {
        $(this).val('')
        return;
    }
})

// validate image
$('#image').change(function(ev) {
    if(!$('#article_link').val()) {
        let file = ev.target.files[0]
        let allowedTypes = ['image/gif', 'image/jpeg', 'image/png']

        if(allowedTypes.includes(file.type) === false) {
            displayError('File must be of gif, jpeg or png.')
            ev.target.files[0] = null
            $(this).val('')
            return;
        }else if(file.size > 5242880) {
            displayError('Maximum file size is 5mb.')
            ev.target.files[0] = null
            $(this).val('')
            return;
        }
    }else {
        ev.target.files[0] = null
        $(this).val('')
    }
})

function displayError(message) {
    $('#notification').show()
    $('.notify-message').html(message)
    
    setTimeout(() => {
        $('#notification').hide()
        $('.notify-message').html('')
    },5000)
}
</script>
@endsection