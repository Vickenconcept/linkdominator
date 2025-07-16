<div>
<div class="block max-w-4xl mx-auto px-10 py-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <form method="post" action="{{route('comment.store-campaign')}}">
        @csrf
        <div class="">
            <h3>Campaign Name *</h3>
            <p class="text-gray-500 mt-2 text-sm">Any name you like</p>
            <input type="text" id="campaign_name" value="{{ $campaign->campaign_name }}" name="campaign_name" class="mt-2 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
        </div>
        <div class="mt-4">
            <label for="max_comment_per_day_campaign" class="flex justify-between block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                <span>Maximum Comments Per Day (For This Campaign)*</span>
                <span class="max_comment_per_day_campaign_value">50 / 500</span>
            </label>
            <input id="max_comment_per_day_campaign" type="range" min="0" max="500" value="{{ $campaign->max_comment_per_day_campaign ?? '50' }}" name="max_comment_per_day_campaign" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
        </div>
        <div class="mt-4">
            <label for="max_comment_per_profile_day" class="flex justify-between block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                <span>Maximum Comments Per LinkedIn Profile / Day *</span>
                <span class="max_comment_per_profile_day_value">1 / 10</span>
            </label>
            <input id="max_comment_per_profile_day" type="range" min="0" max="10" value="{{ $campaign->max_comment_per_profile_day ?? '1' }}" name="max_comment_per_profile_day" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
        </div>
        <div class="mt-4">
            <label for="max_comment_per_profile_week" class="flex justify-between block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                <span>Maximum Comments Per LinkedIn Profile / Week *</span>
                <span class="max_comment_per_profile_week_value">7 / 50</span>
            </label>
            <input id="max_comment_per_profile_week" type="range" min="0" max="50" value="{{ $campaign->max_comment_per_profile_week ?? '7' }}" name="max_comment_per_profile_week" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
        </div>
        <div class="mt-4">
            <label for="max_comment_per_profile_month" class="flex justify-between block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                <span>Maximum Comments Per LinkedIn Profile / Month *</span>
                <span class="max_comment_per_profile_month_value">30 / 150</span>
            </label>
            <input id="max_comment_per_profile_month" type="range" min="0" max="150" value="{{ $campaign->max_comment_per_profile_month ?? '30' }}" name="max_comment_per_profile_month" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
        </div>
        <div class="mt-4">
            <label for="skip_post_older_than" class="flex justify-between block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                <span>Skip Posts Older Than (Days) *</span>
                <span class="skip_post_older_than_value">90 / 90</span>
            </label>
            <input id="skip_post_older_than" type="range" min="0" max="90" step="5" value="{{ $campaign->skip_post_older_than ?? '90' }}" name="skip_post_older_than" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
        </div>
        <div>
            <input type="hidden" name="campaign_id" value="{{$cid}}">
            <input type="hidden" name="step" value="final">

            <button type="submit"
            class="block px-4 py-4 text-sm font-medium leading-2 
            text-white transition-colors duration-150 bg-indigo-600 
            border border-transparent rounded-lg active:bg-indigo-600 mt-4
            hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo
            flex gap-1">
                <span class="">Submit</span>
            </button>
        </div>
    </form>
</div>
</div>
<script>
    if(!$('#campaign_name').val()) $('#campaign_name').val('New campaign')
    
    $('#max_comment_per_day_campaign').change(function(){
        $('.max_comment_per_day_campaign_value').text(`${$(this).val()} / 500`)
    })
    $('#max_comment_per_profile_day').change(function(){
        $('.max_comment_per_profile_day_value').text(`${$(this).val()} / 10`)
    })
    $('#max_comment_per_profile_week').change(function(){
        $('.max_comment_per_profile_week_value').text(`${$(this).val()} / 50`)
    })
    $('#max_comment_per_profile_month').change(function(){
        $('.max_comment_per_profile_month_value').text(`${$(this).val()} / 150`)
    })
    $('#skip_post_older_than').change(function(){
        $('.skip_post_older_than_value').text(`${$(this).val()} / 90`)
    })
    
</script>