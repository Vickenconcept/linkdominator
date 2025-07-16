<div class="mt-3">
    <div class="w-full overflow-hidden rounded-lg">
        <div class="w-full overflow-x-auto">
            <div class="block max-w-3xl p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                @foreach($campaignPosts as $item)
                <div class="mb-3 flex gap-1">
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium inline-flex items-center px-2.5 py-0.5 rounded-sm me-2 dark:bg-gray-700 dark:text-gray-400 border border-gray-500 ">
                        {{ $item->campaign_name }}
                    </span>
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium inline-flex items-center px-2.5 py-0.5 rounded-sm me-2 dark:bg-gray-700 dark:text-gray-400 border border-gray-500 ">
                        collected: {{ date_format(date_create($item->created_at), "d/m/Y H:i") }}
                    </span>
                    @if ($item->comment_status)
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium inline-flex items-center px-2.5 py-0.5 rounded-sm me-2 dark:bg-gray-700 dark:text-gray-400 border border-gray-500 ">
                        {{ $item->comment_status }}
                    </span>
                    @endif
                </div>
                <div class="block mb-6 p-6 bg-gray-100 border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    <div class="mb-4 text-sm">
                        <p class="text-gray-500 inline-flex gap-2">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                            <a href="{{ $item->poster_linkedin_url }}" target="_blank" class="text-blue-600">{{ $item->poster_name }}</a>
                            {{ date_format(date_create($item->posted), "d/m/Y H:i") }}
                            <a href="{{ $item->post_url }}" target="_blank" class="text-blue-600">Go to post</a>
                        </p>
                    </div>
                    <hr>
                    <h5 class="mb-2 mt-4 text-md font-bold tracking-tight text-gray-900 dark:text-white">{{ $item->poster_title }}</h5>
                    <p class="font-normal text-gray-700 dark:text-gray-400 text-sm post" style="white-space:pre-wrap;">{{ $item->post }}</p>
                    <div class="my-4">
                        <!-- <button type="button" class="inline-flex gap-1 text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                            </svg>
                            Like
                        </button> -->
                    </div>
                    <hr>
                    <div class="my-4">
                        <label for="post-comment-{{$item->id}}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Comment</label>
                        <textarea id="post-comment-{{$item->id}}" rows="5" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your thoughts here..." {{ in_array($item->comment_status, ['skipped','publish','like_publish']) ? 'disabled':'' }}>{{ $item->comment ?? '' }}</textarea>
                    </div>
                    <div class="my-2 hidden comment-action-notification-{{$item->id}}">
                        <div class="success hidden p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                            
                        </div>
                        <div class="error hidden p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                            
                        </div>
                    </div>
                    <div class="my-2 flex justify-between">
                        <div class="flex gap-1 hs-tooltip"
                            data-post="{{$item->post}}"
                            data-cid="{{$item->campaign_id}}"
                            data-pid="{{$item->id}}"
                            data-urn="{{$item->urn}}"
                            data-comment="{{$item->comment}}">
                            <button 
                            type="button"
                            data-publish-type="publish"
                            data-tooltip-target="tooltip-copy-comment-{{$item->id}}"
                            class="block px-4 py-2 text-sm font-medium leading-2 
                            text-white transition-colors duration-150 bg-indigo-600 
                            border border-transparent rounded-lg active:bg-indigo-600 flex gap-1
                            hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo hs-tooltip-toggle copy-comment">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                </svg>
                                <span class="pt-2">Copy</span>

                                <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                                    Copy comment
                                </span>
                            </button>
                            <button 
                            type="button"
                            data-publish-type="skipped"
                            class="skip-comment flex gap-1 block px-4 py-2 text-sm font-medium leading-2 
                            text-white transition-colors duration-150 bg-red-600 
                            border border-transparent rounded-lg active:bg-red-600
                            hover:bg-red-700 focus:outline-none focus:shadow-outline-red"
                            {{ in_array($item->comment_status, ['skipped','publish','like_publish']) ? 'disabled':'' }}>
                                <span class="pt-2">Skip</span>
                                <span class="hidden skipped-spinner">
                                    <svg aria-hidden="true" role="status" class="mt-1 inline w-4 h-4 ml-1 text-blue-600 animate-spin " viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <div class="flex gap-1" 
                        data-post="{{$item->post}}"
                        data-cid="{{$item->campaign_id}}"
                        data-pid="{{$item->id}}">
                            <button type="button" data-type="neutral" class="generate-comment text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-2.5 text-center me-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800" {{ in_array($item->comment_status, ['skipped','publish','like_publish']) ? 'disabled':'' }}>
                                New Neutral Comment
                                <span class="hidden neutral-spinner">
                                    <svg aria-hidden="true" role="status" class="inline w-4 h-4 ml-1 text-blue-600 animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </button>
                            <button type="button" data-type="promo" class="generate-comment text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-2.5 text-center me-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800" {{ in_array($item->comment_status, ['skipped','publish','like_publish']) ? 'disabled':'' }}>
                                New Promo Comment
                                <span class="hidden promo-spinner">
                                    <svg aria-hidden="true" role="status" class="inline w-4 h-4 ml-1 text-blue-600 animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">
            @if(count($campaignPosts)>0)
                {{$campaignPosts->links()}}
            @endif
            </div>
        </div>
    </div>
</div>

<script>
$('.copy-comment').click(function(){
    navigator.clipboard.writeText($(this).parent().data('comment'))
    $(this).text('Copied')
    setTimeout(()=> {
        $(this).text('Copy')
    },2000)
})

$('.generate-comment').click(function(){
    let commentType = $(this).data('type'),
        post = $(this).parent().data('post'),
        campaignId = $(this).parent().data('cid'),
        postId = $(this).parent().data('pid');

    $('.generate-comment').attr('disabled', true);

    $(`.${commentType}-spinner`).toggleClass('hidden');

    $.ajax({
        method: 'post',
        beforeSend: function(request) {
            request.setRequestHeader('Content-Type', 'application/json'),
            request.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}")
        },
        url: '/comment/generate',
        data: JSON.stringify({
            campaignId: campaignId,
            postId: postId,
            post: post,
            commentType: commentType
        }),
        success: function(res) {
            $(`#post-comment-${postId}`).val(res.content)
            $('.generate-comment').attr('disabled',false)
            $(`.${commentType}-spinner`).toggleClass('hidden');
        },
        error: function(err, status, error) {
            $('.generate-comment').attr('disabled',false)
            $(`.${commentType}-spinner`).toggleClass('hidden');

            setNotification(err, postId, 'error')
        }
    })
})

$('.publish-comment').click(function(){
    let postId = $(this).parent().data('pid'),
        campaignId = $(this).parent().data('cid'),
        postUrn = $(this).parent().data('urn'),
        comment = $(this).parent().data('comment'),
        publishType = $(this).data('publish-type')
        
    $(this).attr('disabled', true);
    $(`.${publishType}-spinner`).toggleClass('hidden');

    $.ajax({
        method: 'post',
        beforeSend: function(request) {
            request.setRequestHeader('Content-Type', 'application/json'),
            request.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}")
        },
        url: '/comment/publish',
        data: JSON.stringify({
            campaignId: campaignId,
            postId: postId,
            postUrn: postUrn,
            comment: comment,
            publishType: publishType
        }),
        success: function(res) {
            $('.publish-comment').attr('disabled', false);
            $(`.${publishType}-spinner`).toggleClass('hidden');
            $(`#post-comment-${postId}`).attr('disabled', true)

            setNotification(res.message, postId, 'success')
        },
        error: function(err, status, error) {
            $('.publish-comment').attr('disabled', false);
            $(`.${publishType}-spinner`).toggleClass('hidden');

            setNotification(err, postId, 'error')
        }
    })
})

$('.skip-comment').click(function(){
    let postId = $(this).parent().data('pid')
    let publishType = $(this).data('publish-type')

    $(this).attr('disabled', true);
    $(`.${publishType}-spinner`).toggleClass('hidden');

    $.ajax({
        method: 'post',
        beforeSend: function(request) {
            request.setRequestHeader('Content-Type', 'application/json'),
            request.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}")
        },
        url: '/comment/skip',
        data: JSON.stringify({
            postId: postId,
            publishType: publishType
        }),
        success: function(res) {
            $('.skip-comment').attr('disabled', false);
            $(`.${publishType}-spinner`).toggleClass('hidden');
            $(`#post-comment-${postId}`).attr('disabled', true)

            setNotification(res.message, postId, 'success')
        },
        error: function(err, status, error) {
            $('.skip-comment').attr('disabled', false);
            $(`.${publishType}-spinner`).toggleClass('hidden');

            setNotification(err, postId, 'error')
        }
    })
})

function setNotification(message, pid, type){
    $(`.comment-action-notification-${pid}`).toggleClass('hidden')

    if(type == 'success'){
        $(`.comment-action-notification-${pid}`).children('.success').toggleClass('hidden').text(message)
    }else if(type == 'error'){
        if (message.responseJSON) {
            message = message.responseJSON.message
        }else {
            message = message.toString()
        }
        $(`.comment-action-notification-${pid}`).children('.error').toggleClass('hidden').text(message)
    }

    setTimeout(() => {
        $(`.comment-action-notification-${pid}`).toggleClass('hidden')
    }, 5000);
}
</script>