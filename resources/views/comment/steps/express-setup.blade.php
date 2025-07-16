<div>
    <div class="block max-w-4xl mx-auto p-10 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <h5 class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Express AI Campaign Creator</h5>
        <p class="font-normal text-gray-700 dark:text-gray-400 mb-4">
            Share links to your profile and company, and we'll analyze them to create a customized LinkedIn campaign draft.
        </p>
        <hr>
        <form action="{{route('comment.store-campaign')}}" method="post">
            @csrf
            <div class="mt-4">
                <label for="linkedin-profile-url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your LinkedIn Profile URL *</label>
                <p class="mb-2 text-gray-400">Example: https://www.linkedin.com/in/xankovich/</p>
                <input type="url" id="linkedin-profile-url" value="{{ $campaign ? $campaign->linkedin_profile_url : '' }}" name="linkedin_profile_url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
            </div>
            <div>
                <input type="hidden" name="campaign_id" value="{{$cid}}">
                <input type="hidden" name="step" value="express-setup">
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
        </form>
    </div>
</div>