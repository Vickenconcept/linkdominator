<div>
    <div class="flex justify-between">
        <h2 class="pt-1 text-lg font-semibold text-gray-700 dark:text-gray-200 ">
            Campaigns
        </h2>
        <a href="{{route('comment.create-campaign', ['step' => 'express-setup'])}}"
        class="block px-4 py-4 text-sm font-medium leading-2 
        text-white transition-colors duration-150 bg-indigo-600 
        border border-transparent rounded-lg active:bg-indigo-600 
        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
            <span class="mt-1">New campaign</span>
        </a>
    </div>
    @include('comment.campaign-list')
</div>