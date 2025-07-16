<div>
    <div class="block max-w-4xl mx-auto p-10 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <h5 class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Should We Monitor?</h5>
        <p class="font-normal text-gray-700 dark:text-gray-400 mb-4">
            <span class="font-bold">Profiles:</span> Ideal if you're tracking specific individuals, companies, or competitors. You'll create comments whenever the posts arrive, helping you stay on top of their latest activities.
        </p>
        <p class="font-normal text-gray-700 dark:text-gray-400 mb-4">
            <span class="font-bold">Keywords:</span> Best for tracking broader industry trends, product mentions, or specific topics. You'll capture conversations and content around the terms you choose, even from unexpected sources.
        </p>
        <form action="{{route('comment.store-campaign')}}" method="post">
            @csrf
            <div class="flex gap-4">
                <div class="flex items-center px-4 border border-gray-200 rounded-sm dark:border-gray-700">
                    <input id="bordered-radio-1" type="radio" value="keyword" name="campaign_type" class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                    {{ $campaign && $campaign->campaign_type == 'keyword' || !$campaign->campaign_type ? 'checked':'' }} >
                    <label for="bordered-radio-1" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">List of Keywords (Default)</label>
                </div>
                <div class="flex items-center px-4 border border-gray-200 rounded-sm dark:border-gray-700">
                    <input id="bordered-radio-2" type="radio" value="profile" name="campaign_type" class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                    {{ $campaign && $campaign->campaign_type == 'profile' ? 'checked':''}} >
                    <label for="bordered-radio-2" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">List of LinkedIn Profiles</label>
                </div>
            </div>
            <div>
                <input type="hidden" name="campaign_id" value="{{$cid}}">
                <input type="hidden" name="step" value="type">
                <div class="flex gap-2">
                    <a href="{{route('comment.create-campaign', ['step' => 'express-setup', 'cid' => $cid])}}"
                    class="block px-4 py-2 text-sm font-medium leading-2 
                    text-white transition-colors duration-150 bg-gray-600 
                    border border-transparent rounded-lg active:bg-gray-600 mt-4
                    hover:bg-gray-700 focus:outline-none focus:shadow-outline-gray
                    flex gap-1">
                        <span class="pt-2">Back</span>
                    </a>
                    <button type="submit"
                    class="block px-4 py-2 text-sm font-medium leading-2 
                    text-white transition-colors duration-150 bg-indigo-600 
                    border border-transparent rounded-lg active:bg-indigo-600 mt-4
                    hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo
                    flex gap-1">
                        <span class="pt-2">Next</span>
                        <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>