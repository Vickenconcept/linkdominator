<div>
    <div class="block max-w-4xl mx-auto p-10 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <h5 class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Posts Search Settings</h5>
        <p class="font-normal text-gray-700 dark:text-gray-400 mb-4">
            We will monitor this list of profiles and track their new posts. With an AI filter, we'll respond only to topics that are relevant to you.
        </p>
        <form action="{{route('comment.store-campaign')}}" method="post">
            @csrf
            <input type="hidden" name="campaign_type" value="{{$campaign->campaign_type}}">
            @if ($campaign->campaign_type == 'keyword')
                <label for="search-expression" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    LinkedIn Search Expression List: *
                </label>
                <textarea id="search-expression" rows="10" name="keyword_list" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                placeholder="Marketing
Lead generation
SEO
market expansion strategy
Digital marketing
Mobile analytics
" required="">{{ $campaign?->keyword_list }}</textarea>
            @elseif ($campaign->campaign_type == 'profile')
                <label for="linkedin-profile" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    LinkedIn Profiles List: *
                </label>
                <textarea id="profile-search-expression" rows="10" name="profile_list" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                placeholder="Please paste each LinkedIn profile URL on a new line:
https://www.linkedin.com/in/sam-hartman-10028a1a7/ 
https://www.linkedin.com/in/muratbayram-/
https://www.linkedin.com/in/aye-chan-myat-6948811b6/ 
https://www.linkedin.com/in/ash-khazaei-141224213/ 
https://www.linkedin.com/in/martin-gaudette-consultant/" required="">{{ $campaign?->profile_list }}</textarea>
            @endif

            <div>
                <input type="hidden" name="campaign_id" value="{{$cid}}">
                <input type="hidden" name="step" value="search">
                <div class="flex gap-2">
                    <a href="{{route('comment.create-campaign', ['step' => 'type', 'cid' => $cid])}}"
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