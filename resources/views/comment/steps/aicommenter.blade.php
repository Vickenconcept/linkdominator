<div>
    <div class="block text-md max-w-4xl mx-auto p-10 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <h5 class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">AI Commenter Settings</h5>
        <p class="font-normal text-gray-700 dark:text-gray-400 mb-4">
            We can generate smart AI comments for your target posts. Activate AI comments below to test them out. You can review and decide when you're ready to post on LinkedIn. No comments will be published without your confirmation.
        </p>
        <form method="post" action="{{route('comment.store-campaign')}}">
            @csrf
            <div>
                <div class="border-b border-gray-200 dark:border-neutral-700">
                    <nav class="flex gap-x-1" aria-label="Tabs" role="tablist" aria-orientation="horizontal">
                        <button type="button" class="hs-tab-active:font-semibold hs-tab-active:border-blue-600 hs-tab-active:text-blue-600 py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 {{ $campaign->ai_commenter == 'off' ? 'active' : ''}}" id="tabs-with-underline-item-1" aria-selected="true" data-hs-tab="#tabs-with-underline-1" aria-controls="tabs-with-underline-1" role="tab">
                            <input type="radio" name="ai_commenter" value="off" id="off" checked style="display: none;">
                            <label for="off" class="inline-flex gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9" />
                                </svg>
                                Off
                            </label>
                        </button>
                        <button type="button" class="hs-tab-active:font-semibold hs-tab-active:border-blue-600 hs-tab-active:text-blue-600 py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 {{ $campaign->ai_commenter == 'common' || !$campaign->ai_commenter ? 'active' : ''}}" id="tabs-with-underline-item-2" aria-selected="false" data-hs-tab="#tabs-with-underline-2" aria-controls="tabs-with-underline-2" role="tab">
                            <input type="radio" name="ai_commenter" value="common" id="common" style="display: none;">
                            <label for="common" class="inline-flex gap-1">
                                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6l3 3v-3h2V9h-2M4 4h11v8H9l-3 3v-3H4V4Z"/>
                                </svg>
                                Common
                            </label>
                        </button>
                        <button type="button" class="hs-tab-active:font-semibold hs-tab-active:border-blue-600 hs-tab-active:text-blue-600 py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 {{ $campaign->ai_commenter == 'custom' ? 'active' : ''}}" id="tabs-with-underline-item-3" aria-selected="false" data-hs-tab="#tabs-with-underline-3" aria-controls="tabs-with-underline-3" role="tab">
                            <input type="radio" name="ai_commenter" value="custom" id="custom" style="display: none;">
                            <label for="custom" class="inline-flex gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                Custom
                            </label>
                        </button>
                    </nav>
                </div>
                <div class="mt-3">
                    <div id="tabs-with-underline-1" class="{{ $campaign->ai_commenter == 'off' ? '':'hidden' }}" role="tabpanel" aria-labelledby="tabs-with-underline-item-1">
                        
                    </div>
                    <div id="tabs-with-underline-2" class="{{ $campaign->ai_commenter == 'common' || !$campaign->ai_commenter ? '' : 'hidden' }} bg-gray-50 p-4 rounded" role="tabpanel" aria-labelledby="tabs-with-underline-item-2">
                        <h3>AI Commenter Settings</h3>
                        <h2>Choose your comments style</h2>
                        <div class="flex gap-3 my-4">
                            <div class="flex items-center px-4 border border-gray-200 rounded-sm dark:border-gray-700 bg-white">
                                <input id="short-clean" type="radio" value="short and clean" name="ai_comment_style" class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800"
                                {{ $campaign->ai_comment_style == 'short and clean' || !$campaign->ai_comment_style ? 'checked':'' }}>
                                <label for="short-clean" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Short and Clean (Default)</label>
                            </div>
                            <div class="flex items-center px-4 border border-gray-200 rounded-sm dark:border-gray-700 bg-white">
                                <input id="long-text" type="radio" value="long text" name="ai_comment_style" class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800" 
                                {{ $campaign->ai_comment_style == 'long text' ? 'checked':'' }}>
                                <label for="long-text" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Long text</label>
                            </div>
                        </div>
                        <hr>
                        <div class="my-4">
                            <h3>Comments Type</h3>
                            <div class="mt-2">
                                <div class="flex items-center mb-3">
                                    <input id="promo" type="checkbox" {{ $campaign->ai_comment_type == 'promo' ? 'checked':'' }} value="promo" name="ai_comment_type" class="shrink-0 mt-0.5 border-gray-200 rounded-sm text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                    <label for="promo" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Make Product Promo Comments</label>
                                </div>
                                <div class="flex items-center mb-3">
                                    <input id="neutral" type="checkbox" {{ $campaign->ai_comment_type == 'neutral' || !$campaign->ai_comment_type ? 'checked': '' }} value="neutral" name="ai_comment_type" class="shrink-0 mt-0.5 border-gray-200 rounded-sm text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                    <label for="neutral" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Make Neutral Comments</label>
                                </div>
                                <div class="flex items-center mb-3">
                                    <input id="neutral-promo" type="checkbox" {{ $campaign->ai_comment_type == 'neutral-promo' ? 'checked': '' }} value="neutral-promo" name="ai_comment_type" class="shrink-0 mt-0.5 border-gray-200 rounded-sm text-blue-600 focus:ring-blue-500 checked:border-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                    <label for="neutral-promo" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Make Neutral Comment + Each 10th Promo Comment</label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="my-4">
                            <h3>Your Product Name and Description</h3>
                            <div>
                                <label for="product-description" class="block mb-2 text-sm font-medium text-gray-500 dark:text-white">Name and Describe Your Product Here.</label>
                                <textarea id="product-description" rows="4" name="product_name_description" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $campaign->product_name_description }}</textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="my-4">
                            <h3>Your Product Unique Selling Proposition</h3>
                            <div>
                                <label for="selling-propostion" class="block mb-2 text-sm font-medium text-gray-500 dark:text-white">Provide your Unique Selling Proposition.</label>
                                <textarea id="selling-propostion" rows="4" name="product_unique_selling_point" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $campaign->product_unique_selling_point }}</textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="my-4">
                            <h3>[Your Persona] Description</h3>
                            <div>
                                <label for="persona-description" class="block mb-2 text-sm font-medium text-gray-500 dark:text-white">Provide a brief description of yourself so the AI commenter can mirror your tone and style.</label>
                                <textarea id="persona-description" rows="4" name="persona_description" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $campaign->persona_description}}</textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="my-4">
                            <h3>What AI need to do</h3>
                            <div>
                                <label for="what_ai_need_todo" class="block mb-2 text-sm font-medium text-gray-500 dark:text-white">Describe what actions to take and what to avoid.</label>
                                <textarea id="what_ai_need_todo" rows="4" name="what_ai_need_todo" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $campaign->what_ai_need_todo }}</textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="my-4">
                            <h3>What AI Should Avoid</h3>
                            <div>
                                <label for="what_ai_should_avoid" class="block mb-2 text-sm font-medium text-gray-500 dark:text-white">Describe what to avoid.</label>
                                <textarea id="what_ai_should_avoid" rows="4" name="what_ai_should_avoid" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $campaign->what_ai_should_avoid }}</textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="my-4">
                            <h3>Tone and Style</h3>
                            <div>
                                <label for="tone_style" class="block mb-2 text-sm font-medium text-gray-500 dark:text-white"></label>
                                <textarea id="tone_style" rows="4" name="tone_style" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $campaign->tone_style }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div id="tabs-with-underline-3" class="{{ $campaign->ai_commenter == 'custom' ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="tabs-with-underline-item-3">
                        <div class="">
                            <h3>Webhook URL *</h3>
                            <p class="text-gray-500 mt-2 text-sm">Url of your service, responsible for comment generation.</p>
                            <input type="url" id="webhook_url" name="custom_webhook" value="{{ $campaign->custom_webhook }}" class="mt-2 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="https://..." />
                        </div>
                        <p class="text-gray-500 mt-2 text-sm">
                            Whenever a new post is found and passed all filters, an object with the following structure will be sent to provided url via a POST request.
                        </p>
                        <div class="mt-3 text-xs">
<pre class="bg-gray-200 p-3 text-wrap">
POST 
-------
HEADERS
X-Api-Key:"{{$accessToken ?? 'Kindly generate a token from your account setting section'}}"
-------
BODY
{
    "post_type": "photo",
    "post_url": "https://www.linkedin.com/feed/update/urn:li:activity:7317914525817569280/",
    "posted": "2025-04-15 14:19:43.000",
    "poster_linkedin_url": "https://www.linkedin.com/in/ali-gill-378166170",
    "poster_name": "Ali Gill",
    "poster_title": "Digital Marketing Strategist | Expert in Web Development & SEO",
    "text": "📢 Just published my first LinkedIn blog!",
    "urn": "7317914525817569280"
}
</pre>
                        </div>
                        <div class="mt-3">
                            <h3>Callback URL *</h3>
                            <p class="text-gray-500 mt-2 text-sm">Url of our service - call it when comment is ready</p>
                            <input type="url" id="callback_url" class="mt-2 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="https://app.linkdominator.com/comment/campaign-activities/generate" disabled />
                        </div>
                        <p class="text-gray-500 mt-2 text-sm">
                            Comment generation may take a while. After you create a comment, send it to provided URL with POST request
                        </p>
                        <div class="mt-3 text-xs">
<pre class="bg-gray-200 p-3">
POST https://app.linkdominator.com/comment/campaign-activities/generate
-------
HEADERS
X-Api-Key: "{{$accessToken ?? 'Kindly generate a token from your account setting section.'}}"
-------
BODY
{
    "urn": "7317914525817569280",
    "answer": "This is the answer to post",
}
</pre>
                        </div>
                    </div>
                </div>
                <div>
                    <input type="hidden" name="campaign_id" value="{{$cid}}">
                    <input type="hidden" name="step" value="ai_commenter">
                    <div class="flex gap-2">
                        <a href="{{route('comment.create-campaign', ['step' => 'search', 'cid' => $cid])}}"
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
            </div>
        </div>
    </div>
</div>
<script>
    const whatAiNeedTodo = `## What you need to do

Given a LinkedIn post, generate a concise and engaging comment from my perspective ([My persona] described below). The comment should:

- Be relevant to the content of the post and show genuine interest.
- If you mention a post author - use only First name. If there is no data about post author name, or it is not a human name - don't use any names in reply.
- Add value to the conversation with insights or constructive feedback.
- ALWAYS mention one or two of [My product sales points] that are most relevant to the post and fit the context of the post (listed below). Don’t always use the first ones.
- Keep the comment concise and conversational, without overwhelming the reader with too much detail.
- Maintain a professional and friendly tone.
- Use ONLY one natural-looking emoji if the original post contains emojis at all.
- Use new lines to make comment more readable
    `
    const whatAIShouldAvoid = `- Don't be overly promotional or salesy.
- Don't write overly long or unnatural comments.
- Don't add sign to message
- Don't add hashtags in comments.
- Don't use these phrases: "game changer"
    `
    const toneAndStyle = 'The tone is professional yet enthusiastic, aiming to inspire confidence in technological progress. It balances a knowledgeable, technical approach with an optimistic outlook on innovation. The style is conversational, engaging the reader with a positive message about the benefits of AI-driven solutions. The use of upbeat phrases and emojis, like 🚀, gives it a modern, forward-thinking vibe while still maintaining a formal, expert tone.'

    if(!$('#tone_style').val()) $('#tone_style').val(toneAndStyle);
    if(!$('#what_ai_should_avoid').val()) $('#what_ai_should_avoid').val(whatAIShouldAvoid);
    if(!$('#what_ai_need_todo').val()) $('#what_ai_need_todo').val(whatAiNeedTodo)

</script>