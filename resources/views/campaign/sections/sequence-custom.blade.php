<div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 flex gap-3" role="alert">    
    <span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
        </svg>
    </span>
    <div>
        <p><b>For Send an invite campaign</b></p>
        You can send around 100 connection requests per week. <br/>
        You can send up to 20 connection request per day on average.
    </div>
</div>
<div class="flex flex-row gap-3">
    <div class="w-40">
        <h4 class="font-semibold">Actions:</h4>
        <div class="mt-3"> 
            <button id="send-invite-action-btn" class="block w-full py-2 text-xs font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 mb-3
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-500
            hover:bg-gray-400 focus:outline-none focus:shadow-outline-gray action-btn" disabled>
                Send an invite
            </button>
            <button id="message-action-btn" class="block w-full py-2 text-xs font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 mb-3
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-500
            hover:bg-gray-400 focus:outline-none focus:shadow-outline-gray action-btn" disabled>
                Message
            </button>
            <button id="profile-view-action-btn" class="block w-full py-2 text-xs font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 mb-3
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-500
            hover:bg-gray-400 focus:outline-none focus:shadow-outline-gray action-btn" disabled>
                View profile
            </button>
            <button id="endorse-action-btn" class="block w-full py-2 text-xs font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 mb-3
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-500
            hover:bg-gray-400 focus:outline-none focus:shadow-outline-gray action-btn" disabled>
                Endorse skills
            </button>
            <button id="follow-action-btn" class="block w-full py-2 text-xs font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 mb-3
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-500
            hover:bg-gray-400 focus:outline-none focus:shadow-outline-gray action-btn" disabled>
                Follow
            </button>
            <button id="like-post-action-btn" class="block w-full py-2 text-xs font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 mb-3
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-500
            hover:bg-gray-400 focus:outline-none focus:shadow-outline-gray action-btn" disabled>
                Like a post
            </button>
            <button id="book-call-action-btn" class="block w-full py-2 text-xs font-medium leading-2 
            text-white transition-colors duration-150 bg-gray-400 mb-3
            border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-500
            hover:bg-gray-400 focus:outline-none focus:shadow-outline-gray action-btn" disabled>
                Book a call
            </button>
        </div>
    </div> 
    <div class="w-full bg-gray-100 rounded min-h-screen">
        <div id="myDiagramDiv" style="width:100%; min-height:100%"></div>
    </div>
</div>
<div class="mt-4 mb-4">
    <!-- Apply action -->
    <div class="pt-3">
        <button type="button" id="applyAction" class="block px-10 py-2 text-sm font-medium leading-2 
        text-white transition-colors duration-150 bg-indigo-500 w-80
        border border-transparent rounded active:bg-indigo-600
        hover:bg-indigo-600 focus:outline-none focus:shadow-outline-indigo"
        style="display: none;">
            Apply
        </button>
    </div>
    <button type="button" style="display: none" class="custom-modal-btn py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-custom-modal" data-hs-overlay="#hs-custom-modal">
        Toggle modal
    </button>
    <div id="hs-custom-modal" class="hs-overlay hidden size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="hs-custom-modal-label">
        <div class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all md:max-w-2xl md:w-full m-3 md:mx-auto">
            <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
                <div class="flex justify-between items-center py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                    <h3 id="hs-custom-modal-label" class="font-bold text-gray-800 dark:text-white modal-title">
                        Profile view
                    </h3>
                    <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600" aria-label="Close" data-hs-overlay="#hs-custom-modal">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto">
                    <div id="endorse-fields" class="custom-sequence-fields" style="display: none;">
                        <label for="invite-note" class="font-medium text-gray-900">
                            Total skills to endorse
                        </label>
                        <div class="grid grid-cols-6 gap-3 pt-2">
                            <div class="col-span-6">
                                <input type="number" id="total-endorse-skill" class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full px-3 disabled:opacity-50 disabled:pointer-events-none">
                            </div>
                        </div>
                    </div>

                    <!-- Delay fields -->
                    <div id="delay-fields" class="custom-sequence-fields w-80" style="display: none;">
                        <label class="font-medium text-gray-900">
                            Total delay time
                        </label>
                        <div class="grid grid-cols-6 gap-3 pt-2">
                            <div class="col-span-2">
                                <input type="number" id="time-number" class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full px-3 disabled:opacity-50 disabled:pointer-events-none">
                            </div>
                            <div class="col-span-3">
                                <select id="time-type" class="px-4 pe-9 block py-2 w-full border-gray-300 rounded focus:border-indigo-600 focus:ring-indigo-600 disabled:opacity-50 disabled:pointer-events-none">
                                    <option value="days">days</option>
                                    <option value="hours">hours</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Send message -->
                    <div id="send-message-fields" class="custom-sequence-fields" style="display: none;">
                        <div class="flex gap-1 mt-2">
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@firstName')">
                                    First name
                                </button>
                            </div>
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@lastName')">
                                    Last name
                                </button>
                            </div>
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@name')">
                                    Full name
                                </button>
                            </div>
                            <div>
                                <button class="px-4 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@position')">
                                    Position
                                </button>
                            </div>
                            <div>
                                <button class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@company')">
                                    Company
                                </button>
                            </div>
                            <div>
                                <button class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@location')">
                                    Location
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <textarea id="send-message" name="send_message" rows="6" class="text-xs block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6"></textarea>
                        </div>
                    </div>

                    <!-- Send invites fields -->
                    <div id="send-invites-fields" class="custom-sequence-fields" style="display: none;">
                        <div class="flex gap-2">
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo invite-variables"
                                onclick="addVariableToMessage('@firstName')" disabled>
                                    First name
                                </button>
                            </div>
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo invite-variables"
                                onclick="addVariableToMessage('@lastName')" disabled>
                                    Last name
                                </button>
                            </div>
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo invite-variables"
                                onclick="addVariableToMessage('@name')" disabled>
                                    Full name
                                </button>
                            </div>
                            <div>
                                <button class="px-4 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo invite-variables"
                                onclick="addVariableToMessage('@position')" disabled>
                                    Position
                                </button>
                            </div>
                            <div>
                                <button class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo invite-variables"
                                onclick="addVariableToMessage('@company')" disabled>
                                    Company
                                </button>
                            </div>
                            <div>
                                <button class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo invite-variables"
                                onclick="addVariableToMessage('@location')" disabled>
                                    Location
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <textarea id="invite-message" name="invite_message" rows="6" class="text-xs block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6" disabled></textarea>
                        </div>
                        <div class="mt-4 space-y-6">
                            <div class="relative flex gap-x-4">
                                <div class="flex h-6 items-center">
                                    <input id="invite-note" name="invite_note" value="on" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                                </div>
                                <div class="text-sm leading-6">
                                    <label for="invite-note" class="font-medium text-gray-900">
                                        Send an invite without a note
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Book a call -->
                    <div id="book-call-fields" class="custom-sequence-fields" style="display: none;">
                        <div class="flex gap-2">
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@firstName')">
                                    First name
                                </button>
                            </div>
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@lastName')">
                                    Last name
                                </button>
                            </div>
                            <div class="">
                                <button class="px-2 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@name')">
                                    Full name
                                </button>
                            </div>
                            <div>
                                <button class="px-4 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@position')">
                                    Position
                                </button>
                            </div>
                            <div>
                                <button class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@company')">
                                    Company
                                </button>
                            </div>
                            <div>
                                <button class="px-3 py-2 text-sm font-medium bg-indigo-500 border border-transparent
                                text-white rounded active:bg-indigo-600 hover:bg-indigo-600 focus:outline-none 
                                focus:shadow-outline-indigo"
                                onclick="addVariableToMessage('@location')">
                                    Location
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 call-message-space" style="display: none;">
                            <div class="mb-3">
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" id="use-ai-paraphrase" name="use_ai_paraphrase" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span>Use AI (paraphrase my message)</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">If checked, AI will improve and paraphrase your message. If unchecked, your exact message will be sent.</p>
                            </div>
                            <textarea id="call-message" name="call_message" rows="6" class="text-xs block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6" placeholder="Enter your call message here..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                    <button type="button" id="close-apply-action-main" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-overlay="#hs-custom-modal">
                    Close
                    </button>
                    <button type="button" id="apply-action-main" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-hidden focus:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none">
                    Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="flex justify-center gap-3 mt-4">
    <a href="{{route('campaign.create', ['step' => 'sequence', 'cid' => $cid])}}"
        class="block px-10 py-3 text-sm font-medium leading-2 
        text-white transition-colors duration-150 bg-gray-400 
        border border-transparent rounded-tr-lg rounded-bl-lg active:bg-gray-600
        hover:bg-gray-600 focus:outline-none focus:shadow-outline-gray">
        <span class=" flex gap-1 pt-1">
            Back
        </span>
    </a>
    <button type="button" onclick="saveSequence()"
        class="block px-10 py-3 text-sm font-medium leading-2 
        text-white transition-colors duration-150 bg-indigo-500 
        border border-transparent rounded-tr-lg rounded-bl-lg active:bg-indigo-600
        hover:bg-indigo-600 focus:outline-none focus:shadow-outline-indigo submit-custom-sequence">
        <span class=" flex gap-1">
            <span class="pt-1">Next</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </span>
    </button>
</div>

<script>
let dbSequenceType = '{{ $sequenceType }}';
let dbSequenceNode = @json($dbSequenceNode);
let dbSequenceLink = @json($dbSequenceLink);
let nodeDataModel, linkDataModel;

const callMessage = "Hi @firstName, I'd like to schedule a call to discuss how we can help your business grow. Are you available for a brief conversation this week? I can share some insights about lead generation and business development that might be valuable for @company."

const sendInviteNodes = [
    {key: 0, icon: "\uf007", label: "Send an invite",   type: 'action', value: 'send-invites',  color: "#5560E5", stroke: "white",  loc: "0 0", pos: 'center', hasInviteNote: true, message: "Hey @firstName,\ni will like to join your network.", runStatus: false},
    {key: 1, icon: "\uf017", label: "5 days",           type: 'delay',  value: 5, time: 'days', color: "#F3F4F6", stroke: "black",  loc: "-150 60", pos: 'left', runStatus: false, notAcceptedTime: 1},
    {key: 2, icon: "\uf058", label: "Accepted",         type: 'condition',  value: 'accepted',  color: "#9ca3af", stroke: "white",  loc: "150 60", pos: 'right'},
    {key: 3, icon: "\uf05e", label: "Still not accepted",type: 'condition',value: 'not accepted', color: "#9ca3af", stroke: "white",  loc: "-150 120", pos: 'left'},
    {key: 4, icon: "\uf017", label: "1 hour",           type: 'delay',  value: 1, time: 'hours',color: "#F3F4F6", stroke: "black",  loc: "150 120", pos: 'right', runStatus: false, acceptedTime: 1}
]
const sendInviteLinks = [
    {from: 0, to: 1, fromSpot: "Left", toSpot: "Top"},
    {from: 0, to: 2, fromSpot: "Right", toSpot: "Top"},
    {from: 1, to: 3, fromSpot: "Bottom", toSpot: "Top"},
    {from: 2, to: 4, fromSpot: "Bottom", toSpot: "Top"},
]
const likePostNodes = [
    {key: 0, icon: "\uf164", label: "Like a post", type: 'action', value: 'like-post',     color: "#5560E5", stroke: "white",  loc: "50 0", pos: 'center', runStatus: false},
    {key: 1, icon: "\uf017", label: "1 day",       type: 'delay',  value: 1, time: 'days', color: "#F3F4F6", stroke: "black",  loc: "50 60", pos: 'center', runStatus: false},
]
const profileViewNodes = [
    {key: 0, icon: "\uf06e", label: "View profile", type: 'action', value: 'profile-view',  color: "#5560E5", stroke: "white",  loc: "50 0", pos: 'center', runStatus: false},
    {key: 1, icon: "\uf017", label: "1 day",        type: 'delay',  value: 1, time: 'days', color: "#F3F4F6", stroke: "black",  loc: "50 60", pos: 'center', runStatus: false},
]
const followNodes = [
    {key: 0, icon: "\uf2bb", label: "Follow", type: 'action', value: 'follow',        color: "#5560E5", stroke: "white",  loc: "50 0", pos: 'center', runStatus: false},
    {key: 1, icon: "\uf017", label: "1 day",  type: 'delay',  value: 1, time: 'days', color: "#F3F4F6", stroke: "black",  loc: "50 60", pos: 'center', runStatus: false},
]
const messageNodes = [
    {key: 0, icon: "\uf27a", label: "Message",  type: 'action', value: 'message',       color: "#5560E5", stroke: "white",  loc: "50 0", message: '', pos: 'center', runStatus: false},
    {key: 1, icon: "\uf017", label: "1 day",    type: 'delay',  value: 1, time: 'days', color: "#F3F4F6", stroke: "black",  loc: "50 60", pos: 'center', runStatus: false},
]
const endorseNodes = [
    {key: 0, icon: "\uf058", label: "Endorse skills", type: 'action', value: 'endorse',       color: "#5560E5", stroke: "white",  loc: "50 0", totalSkills: 1, pos: 'center', runStatus: false},
    {key: 1, icon: "\uf017", label: "1 day",          type: 'delay',  value: 1, time: 'days', color: "#F3F4F6", stroke: "black",  loc: "50 60", pos: 'center', runStatus: false},
]
const endNodes = [
    {key: 0, icon: "\uf05e", label: "End of sequence", type: 'end', value: 'end', color: "#9ca3af", stroke: "white", loc: "50 0", pos: 'center', runStatus: false},
]
const addActionNodes = [
    {key: 0, icon: "",  label: "Add action", type: 'action',  value: 'add-action', color: "#FFFFFF", stroke: "black",  loc: "-150 0", pos: 'left'},
    {key: 1, icon: "",  label: "End",        type: 'action',  value: 'end',        color: "#FFFFFF", stroke: "black",  loc: "-215 0", pos: 'left'},
    {key: 2, icon: "",  label: "Add action", type: 'action',  value: 'add-action', color: "#FFFFFF", stroke: "black",  loc: "150 0",  pos: 'right'},
    {key: 3, icon: "",  label: "End",        type: 'action',  value: 'end',        color: "#FFFFFF", stroke: "black",  loc: "215 0",  pos: 'right'},
]
const bookCallNodes = [
    {key: 0, icon: "\uf133", label: "Book a call",  type: 'action', value: 'call',      color: "#5560E5", stroke: "white",  loc: "50 0", message: callMessage, pos: 'center', runStatus: false},
    {key: 1, icon: "\uf017", label: "1 day",    type: 'delay',  value: 1, time: 'days', color: "#F3F4F6", stroke: "black",  loc: "50 60", pos: 'center', runStatus: false},
]

if(dbSequenceType === 'custom' && dbSequenceNode.length > 0 && dbSequenceLink.length > 0){
    nodeDataModel = dbSequenceNode
    linkDataModel = dbSequenceLink
}else {
    nodeDataModel = [{
        key: 0, 
        icon: '',
        label: 'Add action',
        type: 'action',
        value: 'add-action',
        color: 'white', 
        stroke: 'black', 
        loc: '0 0',
    }]
    linkDataModel = [] // Initialize linkDataModel as empty array
}

let nodeItem, nodeKey, nodeIndex;
let applyActionBtn = document.querySelector('#applyAction')
let addActionSide = document.querySelectorAll('.action-btn')

// Helper function to ensure linkDataModel is always an array
const ensureLinkDataModel = () => {
    if (!linkDataModel || !Array.isArray(linkDataModel)) {
        linkDataModel = []
    }
}
let sendInviteActionBtn = document.querySelector('#send-invite-action-btn'),
    messageActionBtn = document.querySelector('#message-action-btn'),
    profileViewActionBtn = document.querySelector('#profile-view-action-btn'),
    endorseActionBtn = document.querySelector('#endorse-action-btn'),
    followActionBtn = document.querySelector('#follow-action-btn'),
    likePostActionBtn = document.querySelector('#like-post-action-btn'),
    bookCallActionBtn = document.querySelector('#book-call-action-btn'),
    applyActionMain = document.querySelector('#apply-action-main'),
    closeApplyActionMain = document.querySelector('#close-apply-action-main');

let delayFields = document.querySelector('#delay-fields'),
    timeNumber = document.querySelector('#time-number'),
    timeType = document.querySelector('#time-type');

let inviteMessageFields = document.querySelector('#send-invites-fields'),
    inviteNote = document.querySelector('#invite-note'),
    inviteMessage = document.querySelector('#invite-message'),
    inviteVariables = document.querySelectorAll('.invite-variables');

let sendMessageFields = document.querySelector('#send-message-fields'),
    sendMessage = document.querySelector('#send-message');

let endorseSkillFields = document.querySelector('#endorse-fields'),
    totalEndorseSkill = document.querySelector('#total-endorse-skill');

let callMessageSpace = document.querySelector('.call-message-space'),
    callMessageTextField = document.querySelector('#call-message'),
    callFields = document.querySelector('#book-call-fields');

let modalTitle = document.querySelector('.modal-title')

const init = () => {
    const diagram = new go.Diagram("myDiagramDiv");

    // Explicitly load a font.  Only do this once, not each time you load a model.
    const awesome = new FontFace("awesome",
        "url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/fonts/fontawesome-webfont.ttf)");
    document.fonts.add(awesome);
    // Wait for the Font Awesome font to load before actually loading the diagram.
    awesome.load().then(() => load());

    let nodeDataArray = []
    let linkDataArray = []

    const load = () => {
        diagram.nodeTemplate = new go.Node("Auto", { locationSpot: go.Spot.Center })
            .bind("location", "loc", go.Point.parse)
            .add(
                new go.Shape("Rectangle", {border: 0}).bind("fill", "color"), 
                new go.TextBlock({ margin: 8})
                    .bind("text")
                    .bind("stroke")
                    .bind("font"),
            );
        diagram.linkTemplate = new go.Link({
                routing: go.Routing.AvoidsNodes,
                corner: 10    // rounded corners
            })
            .bind("fromSpot", "fromSpot", go.Spot.parse)
            .bind("toSpot", "toSpot", go.Spot.parse)
            .add(
                new go.Shape()
            );

        // Event listener
        diagram.addDiagramListener('ObjectSingleClicked', function(e){
            nodeItem = e.subject.Hs.si
            nodeKey = nodeItem.key
            
            for(let elem of document.querySelectorAll('.custom-sequence-fields')){
                elem.style.display = 'none'
            }
            applyActionBtn.style.display = 'none'

            // Get the index of nodeDataModel using nodeKey 
            nodeIndex = getNodeDataModelIndex(nodeKey)
            
            if(nodeDataModel[nodeIndex].type == 'delay'){
                document.querySelector('.custom-modal-btn').click()

                timeNumber.value = nodeDataModel[nodeIndex].value
                timeType.value = nodeDataModel[nodeIndex].time
                delayFields.style.display = 'block'
                // applyActionBtn.style.display = 'block'

                modalTitle.innerHTML = 'Delay'

                toogleDisableActionSide([0,1,2,3,4,5], 'disable')            
            }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'send-invites'){
                document.querySelector('.custom-modal-btn').click()

                inviteMessage.value = nodeDataModel[nodeIndex].message
                inviteNote.checked = nodeDataModel[nodeIndex].hasInviteNote
                inviteMessageFields.style.display = 'block'
                // applyActionBtn.style.display = 'block'

                modalTitle.innerHTML = 'Send an Invite'

                inviteMessage.disabled = nodeDataModel[nodeIndex].hasInviteNote
                for(let elem of inviteVariables) {
                    elem.disabled = nodeDataModel[nodeIndex].hasInviteNote
                }
                toogleDisableActionSide([0,1,2,3,4,5], 'disable')
            }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'message'){
                document.querySelector('.custom-modal-btn').click()

                sendMessage.value = nodeDataModel[nodeIndex].message
                sendMessageFields.style.display = 'block'
                // applyActionBtn.style.display = 'block'

                modalTitle.innerHTML = 'Message'

                toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
            }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'endorse'){
                document.querySelector('.custom-modal-btn').click()

                totalEndorseSkill.value = nodeDataModel[nodeIndex].totalSkills
                endorseSkillFields.style.display = 'block'
                // applyActionBtn.style.display = 'block'
                
                modalTitle.innerHTML = 'Endorse Skills'

                toogleDisableActionSide([0,1,2,3,4,5], 'disable')
            }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'add-action'){
                if(nodeDataModel.length == 1 && nodeDataModel[0].value == 'add-action'){
                    toogleDisableActionSide([0,1,2,3,4,5], 'enable')
                }else if(nodeDataModel.length > 1 && nodeDataModel[0].value == 'send-invites'){
                    if(nodeDataModel[nodeIndex].pos == 'left') {
                        toogleDisableActionSide([2,4,5], 'enable')
                    }else if(nodeDataModel[nodeIndex].pos == 'right'){
                        toogleDisableActionSide([1,2,3,4,5,6], 'enable')
                    }
                }else{
                    if(nodeDataModel[0].value == 'message'){
                        toogleDisableActionSide([1,2,3,4,5,6], 'enable')
                    }else{
                        toogleDisableActionSide([1,2,3,4,5], 'enable')
                    }
                }
            }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].label == 'End'){
                setEndSequence()
            }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'call'){
                document.querySelector('.custom-modal-btn').click()

                callMessageTextField.value = nodeDataModel[nodeIndex].message
                document.querySelector('#use-ai-paraphrase').checked = nodeDataModel[nodeIndex].paraphrase_user_message || false
                callMessageSpace.style.display = 'block'
                callFields.style.display = 'block'
                modalTitle.innerHTML = 'Book a Call'

                toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
            }
        })
        // setAddActionNodes()
        setNodeLinkArray()
    }

    /**
     * Listen to action event and Update node model
     **/
    applyActionBtn.addEventListener('click', () => {
        if(nodeDataModel[nodeIndex].type == 'delay'){
            nodeDataModel[nodeIndex].label = timeNumber.value == 0 ? 'No delay' : `${timeNumber.value} ${timeType.value}`
            nodeDataModel[nodeIndex].value = timeNumber.value
            nodeDataModel[nodeIndex].time = timeType.value
            delayFields.style.display = 'none'
        }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'send-invites'){
            nodeDataModel[nodeIndex].message = inviteMessage.value
            nodeDataModel[nodeIndex].hasInviteNote = inviteMessage.disabled
            inviteMessageFields.style.display = 'none'
        }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'message'){
            nodeDataModel[nodeIndex].message = sendMessage.value
            sendMessageFields.style.display = 'none'
        }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'endorse'){
            nodeDataModel[nodeIndex].totalSkills = totalEndorseSkill.value
            endorseSkillFields.style.display = 'none'
        }
        setNodeLinkArray()
        applyActionBtn.style.display = 'none'
    })

    applyActionMain.addEventListener('click', () => {
        if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'call'){
            nodeDataModel[nodeIndex].message = callMessageTextField.value
            nodeDataModel[nodeIndex].paraphrase_user_message = document.querySelector('#use-ai-paraphrase').checked
            callMessageSpace.style.display = 'none'
            callFields.style.display = 'none'
        }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'send-invites'){
            nodeDataModel[nodeIndex].message = inviteMessage.value
            nodeDataModel[nodeIndex].hasInviteNote = inviteMessage.disabled
            inviteMessageFields.style.display = 'none'
        }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'message'){
            nodeDataModel[nodeIndex].message = sendMessage.value
            sendMessageFields.style.display = 'none'
        }else if(nodeDataModel[nodeIndex].type == 'delay'){
            nodeDataModel[nodeIndex].label = timeNumber.value == 0 ? 'No delay' : `${timeNumber.value} ${timeType.value}`
            nodeDataModel[nodeIndex].value = timeNumber.value
            nodeDataModel[nodeIndex].time = timeType.value
            delayFields.style.display = 'none'
        }else if(nodeDataModel[nodeIndex].type == 'action' && nodeDataModel[nodeIndex].value == 'endorse'){
            nodeDataModel[nodeIndex].totalSkills = totalEndorseSkill.value
            endorseSkillFields.style.display = 'none'
        }
        setNodeLinkArray()
        closeApplyActionMain.click()
    })

    /**
     * Listen to invite note status change and update node model
     **/
    inviteNote.addEventListener('change', (e) => {
        if(e.target.checked){
            inviteMessage.disabled = true
        }else {
            inviteMessage.disabled = false
        }
        for(let elem of inviteVariables) {
            elem.disabled = inviteMessage.disabled
        }
    })

    /**
     * Create send invite nodes and link
     */
    sendInviteActionBtn.addEventListener('click', () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        if(nodeDataModel.length == 1 && nodeDataModel[0].value == 'add-action'){
            nodeDataModel = sendInviteNodes
            linkDataModel = sendInviteLinks
        }
        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    })

    /**
     * Create message nodes and link
     */
    messageActionBtn.addEventListener('click', () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        let messageLinks, newNode;
        if(nodeDataModel.length > 1 && nodeDataModel[0].value == 'send-invites'){
            if(nodeDataModel[nodeIndex].pos == 'right'){
                unsetAddActionNodes()
                
                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})
                
                // Set loc
                loc = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of messageNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        message: item?.message || null,
                        loc: i == 0 ? `150 ${loc}` : `150 ${loc + 60}`,
                        pos: 'right', 
                        runStatus: false
                    }
                    
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['acceptedAction'] = acceptedAction ? acceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['acceptedTime'] = acceptedTime ? acceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['rightKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
                console.log(nodeDataModel)
            }
        }else if(nodeDataModel.length == 1 && nodeDataModel[0].value == 'add-action'){
            nodeDataModel = messageNodes
            linkDataModel.push({
                from: 0,
                to: 1,
                fromSpot: "Bottom",
                toSpot: "Top"
            })
        }else if(nodeDataModel.length > 1 && nodeDataModel[0].value != 'add-action'){
            unsetAddActionNodes();

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction')
            
            // Set loc
            loc = nodeDataModel[lastNodekeys.centerIndex].loc.split(" ")
            loc = parseInt(loc[1]) + 60
            
            let newNode0 = {
                key: lastNodekeys.lastKey +1,
                icon: messageNodes[0].icon,
                label: messageNodes[0].label,
                type: messageNodes[0].type,
                value: messageNodes[0].value,
                time: messageNodes[0]?.time || null,
                color: messageNodes[0].color,
                stroke: messageNodes[0].stroke,
                message: messageNodes[0]?.message || null,
                loc: `50 ${loc}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode0)
            let newLink0 = {
                from: lastNodekeys.centerKey,
                to: lastNodekeys.lastKey +1,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink0)
            let newNode1 = {
                key: lastNodekeys.lastKey +2,
                icon: messageNodes[1].icon,
                label: messageNodes[1].label,
                type: messageNodes[1].type,
                value: messageNodes[1].value,
                time: messageNodes[1]?.time || null,
                color: messageNodes[1].color,
                stroke: messageNodes[1].stroke,
                message: messageNodes[1]?.message || null,
                loc: `50 ${loc +60}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode1)
            let newLink1 = {
                from: lastNodekeys.centerKey +1,
                to: lastNodekeys.lastKey +2,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink1)
        }
        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    })

    /**
     * Create profile views nodes and link
     */
    profileViewActionBtn.addEventListener('click', () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        let lastNodekeys, loc, profileViewLinks, newNode;
        let newProfileViewNodes = []

        if(nodeDataModel.length > 1 && nodeDataModel[0].value == 'send-invites') {
            if(nodeDataModel[nodeIndex].pos === 'left'){
                unsetAddActionNodes()

                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})

                // Set loc
                loc = nodeDataModel[lastNodekeys['leftKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of profileViewNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        loc: i == 0 ? `-150 ${loc}` : `-150 ${loc + 60}`,
                        pos: 'left',
                        runStatus: false
                    }
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['notAcceptedAction'] = notAcceptedAction ? notAcceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['notAcceptedTime'] = notAcceptedTime ? notAcceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['leftKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
            }else if(nodeDataModel[nodeIndex].pos === 'right'){
                unsetAddActionNodes()

                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})

                // Set loc
                loc = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of profileViewNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        loc: i == 0 ? `150 ${loc}` : `150 ${loc + 60}`,
                        pos: 'right',
                        runStatus: false
                    }
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['acceptedAction'] = acceptedAction ? acceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['acceptedTime'] = acceptedTime ? acceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['rightKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
            }
        }else if(nodeDataModel.length == 1 && nodeDataModel[0].value == 'add-action'){
            nodeDataModel = profileViewNodes
            linkDataModel.push({
                from: 0,
                to: 1,
                fromSpot: "Bottom",
                toSpot: "Top"
            })
        }else if(nodeDataModel.length > 1 && nodeDataModel[0].value != 'add-action'){
            unsetAddActionNodes();

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction')
            
            // Set loc
            loc = nodeDataModel[lastNodekeys.centerIndex].loc.split(" ")
            loc = parseInt(loc[1]) + 60
            
            let newNode0 = {
                key: lastNodekeys.lastKey +1,
                icon: profileViewNodes[0].icon,
                label: profileViewNodes[0].label,
                type: profileViewNodes[0].type,
                value: profileViewNodes[0].value,
                time: profileViewNodes[0]?.time || null,
                color: profileViewNodes[0].color,
                stroke: profileViewNodes[0].stroke,
                loc: `50 ${loc}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode0)
            let newLink0 = {
                from: lastNodekeys.centerKey,
                to: lastNodekeys.lastKey +1,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink0)
            let newNode1 = {
                key: lastNodekeys.lastKey +2,
                icon: profileViewNodes[1].icon,
                label: profileViewNodes[1].label,
                type: profileViewNodes[1].type,
                value: profileViewNodes[1].value,
                time: profileViewNodes[1]?.time || null,
                color: profileViewNodes[1].color,
                stroke: profileViewNodes[1].stroke,
                loc: `50 ${loc +60}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode1)
            let newLink1 = {
                from: lastNodekeys.centerKey +1,
                to: lastNodekeys.lastKey +2,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink1)
        }
        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    })

    /**
     * Create endorse skills nodes and link
     */
    endorseActionBtn.addEventListener('click', () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        let newNode;
        if(nodeDataModel.length > 1 && nodeDataModel[0].value == 'send-invites'){
            if(nodeDataModel[nodeIndex].pos == 'right'){
                unsetAddActionNodes()
                
                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})
                
                // Set loc
                loc = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of endorseNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        totalSkills: item?.totalSkills || null,
                        loc: i == 0 ? `150 ${loc}` : `150 ${loc + 60}`,
                        pos: 'right',
                        runStatus: false
                    }
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['acceptedAction'] = acceptedAction ? acceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['acceptedTime'] = acceptedTime ? acceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['rightKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
            }
        }else if(nodeDataModel.length == 1 && nodeDataModel[0].value == 'add-action'){
            nodeDataModel = endorseNodes
            linkDataModel.push({
                from: 0,
                to: 1,
                fromSpot: "Bottom",
                toSpot: "Top"
            })
        }else if(nodeDataModel.length > 1 && nodeDataModel[0].value != 'add-action'){
            unsetAddActionNodes();

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction')
            
            // Set loc
            loc = nodeDataModel[lastNodekeys.centerIndex].loc.split(" ")
            loc = parseInt(loc[1]) + 60

            let newNode0 = {
                key: lastNodekeys.lastKey +1,
                icon: endorseNodes[0].icon,
                label: endorseNodes[0].label,
                type: endorseNodes[0].type,
                value: endorseNodes[0].value,
                time: endorseNodes[0]?.time || null,
                color: endorseNodes[0].color,
                stroke: endorseNodes[0].stroke,
                totalSkills: endorseNodes[0].totalSkills || null,
                loc: `50 ${loc}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode0)
            let newLink0 = {
                from: lastNodekeys.centerKey,
                to: lastNodekeys.lastKey +1,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink0)
            let newNode1 = {
                key: lastNodekeys.lastKey +2,
                icon: endorseNodes[1].icon,
                label: endorseNodes[1].label,
                type: endorseNodes[1].type,
                value: endorseNodes[1].value,
                time: endorseNodes[1]?.time || null,
                color: endorseNodes[1].color,
                stroke: endorseNodes[1].stroke,
                totalSkills: endorseNodes[0].totalSkills || null,
                loc: `50 ${loc +60}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode1)
            let newLink1 = {
                from: lastNodekeys.centerKey +1,
                to: lastNodekeys.lastKey +2,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink1)
        }
        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    })

    /**
     * Create follow nodes and link
     */
    followActionBtn.addEventListener('click', () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        let newNode;
        if(nodeDataModel.length > 1 && nodeDataModel[0].value == 'send-invites'){
            if(nodeDataModel[nodeIndex].pos == 'left'){
                unsetAddActionNodes()

                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})

                // Set loc
                loc = nodeDataModel[lastNodekeys['leftKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of followNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        loc: i == 0 ? `-150 ${loc}` : `-150 ${loc + 60}`,
                        pos: 'left',
                        runStatus: false
                    }
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['notAcceptedAction'] = notAcceptedAction ? notAcceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['notAcceptedTime'] = notAcceptedTime ? notAcceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['leftKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
            }else if(nodeDataModel[nodeIndex].pos == 'right'){
                unsetAddActionNodes()

                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})

                // Set loc
                loc = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of followNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        loc: i == 0 ? `150 ${loc}` : `150 ${loc + 60}`,
                        pos: 'right',
                        runStatus: false
                    }
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['acceptedAction'] = acceptedAction ? acceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['acceptedTime'] = acceptedTime ? acceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['rightKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
            }
        }else if(nodeDataModel.length == 1 && nodeDataModel[0].value == 'add-action'){
            nodeDataModel = followNodes
            linkDataModel.push({
                from: 0,
                to: 1,
                fromSpot: "Bottom",
                toSpot: "Top"
            })
        }else if(nodeDataModel.length > 1 && nodeDataModel[0].value != 'add-action'){
            unsetAddActionNodes();

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction')
            
            // Set loc
            loc = nodeDataModel[lastNodekeys.centerIndex].loc.split(" ")
            loc = parseInt(loc[1]) + 60

            let newNode0 = {
                key: lastNodekeys.lastKey +1,
                icon: followNodes[0].icon,
                label: followNodes[0].label,
                type: followNodes[0].type,
                value: followNodes[0].value,
                time: followNodes[0]?.time || null,
                color: followNodes[0].color,
                stroke: followNodes[0].stroke,
                loc: `50 ${loc}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode0)
            let newLink0 = {
                from: lastNodekeys.centerKey,
                to: lastNodekeys.lastKey +1,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink0)
            let newNode1 = {
                key: lastNodekeys.lastKey +2,
                icon: followNodes[1].icon,
                label: followNodes[1].label,
                type: followNodes[1].type,
                value: followNodes[1].value,
                time: followNodes[1]?.time || null,
                color: followNodes[1].color,
                stroke: followNodes[1].stroke,
                loc: `50 ${loc +60}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode1)
            let newLink1 = {
                from: lastNodekeys.centerKey +1,
                to: lastNodekeys.lastKey +2,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink1)
        }
        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    })

    /**
     * Create like a post nodes and link
     */
    likePostActionBtn.addEventListener('click', () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        let newNode;
        if(nodeDataModel.length > 1 && nodeDataModel[0].value == 'send-invites'){
            if(nodeDataModel[nodeIndex].pos == 'left'){
                unsetAddActionNodes()

                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})

                // Set loc
                loc = nodeDataModel[lastNodekeys['leftKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of likePostNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        loc: i == 0 ? `-150 ${loc}` : `-150 ${loc + 60}`,
                        pos: 'left',
                        runStatus: false
                    }
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['notAcceptedAction'] = notAcceptedAction ? notAcceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['notAcceptedTime'] = notAcceptedTime ? notAcceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['leftKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
            }else if(nodeDataModel[nodeIndex].pos === 'right'){
                unsetAddActionNodes()

                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})

                // Set loc
                loc = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of likePostNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        loc: i == 0 ? `150 ${loc}` : `150 ${loc + 60}`,
                        pos: 'right',
                        runStatus: false
                    }
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['acceptedAction'] = acceptedAction ? acceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['acceptedTime'] = acceptedTime ? acceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['rightKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
            }
        }else if(nodeDataModel.length == 1 && nodeDataModel[0].value == 'add-action'){
            nodeDataModel = likePostNodes
            linkDataModel.push({
                from: 0,
                to: 1,
                fromSpot: "Bottom",
                toSpot: "Top"
            })
        }else if(nodeDataModel.length > 1 && nodeDataModel[0].value != 'add-action'){
            unsetAddActionNodes();

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction')
            
            // Set loc
            loc = nodeDataModel[lastNodekeys.centerIndex].loc.split(" ")
            loc = parseInt(loc[1]) + 60

            let newNode0 = {
                key: lastNodekeys.lastKey +1,
                icon: likePostNodes[0].icon,
                label: likePostNodes[0].label,
                type: likePostNodes[0].type,
                value: likePostNodes[0].value,
                time: likePostNodes[0]?.time || null,
                color: likePostNodes[0].color,
                stroke: likePostNodes[0].stroke,
                loc: `50 ${loc}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode0)
            let newLink0 = {
                from: lastNodekeys.centerKey,
                to: lastNodekeys.lastKey +1,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink0)
            let newNode1 = {
                key: lastNodekeys.lastKey +2,
                icon: likePostNodes[1].icon,
                label: likePostNodes[1].label,
                type: likePostNodes[1].type,
                value: likePostNodes[1].value,
                time: likePostNodes[1]?.time || null,
                color: likePostNodes[1].color,
                stroke: likePostNodes[1].stroke,
                loc: `50 ${loc +60}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode1)
            let newLink1 = {
                from: lastNodekeys.centerKey +1,
                to: lastNodekeys.lastKey +2,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink1)
        }
        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    })

    /**
     * Create book a call nodes and link
     */
    bookCallActionBtn.addEventListener('click', () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        let messageLinks, newNode;

        if(nodeDataModel.length > 1 && nodeDataModel[0].value == 'send-invites'){
            if(nodeDataModel[nodeIndex].pos == 'right'){
                unsetAddActionNodes()
                
                // Get last item pos, keys after purged add action, end
                lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})
                
                // Set loc
                loc = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
                loc = parseInt(loc[1]) + 60

                for(const [i, item] of bookCallNodes.entries()){
                    newNode = {
                        key: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        icon: item.icon,
                        label: item.label,
                        type: item.type,
                        value: item.value,
                        time: item?.time || null,
                        color: item.color,
                        stroke: item.stroke,
                        message: item?.message || null,
                        loc: i == 0 ? `150 ${loc}` : `150 ${loc + 60}`,
                        pos: 'right', 
                        runStatus: false
                    }
                    
                    // check if acceptedAction or acceptedTime
                    let { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction } = getStatusLastIds()
                    if(item.type == 'action'){
                        newNode['acceptedAction'] = acceptedAction ? acceptedAction+1 : 1
                    }else if(item.type == 'delay'){
                        newNode['acceptedTime'] = acceptedTime ? acceptedTime+1 : 1
                    }
                    nodeDataModel.push(newNode)

                    linkDataModel.push({
                        from: i == 0 ? lastNodekeys['rightKey'] : lastNodekeys['lastKey']+1,
                        to: i == 0 ? lastNodekeys['lastKey']+1 : lastNodekeys['lastKey']+2,
                        fromSpot: "Bottom",
                        toSpot: "Top"
                    })
                }
                console.log(nodeDataModel)
            }
        }else if(nodeDataModel.length > 1 && nodeDataModel[0].value != 'add-action'){
            unsetAddActionNodes();

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction')
            
            // Set loc
            loc = nodeDataModel[lastNodekeys.centerIndex].loc.split(" ")
            loc = parseInt(loc[1]) + 60

            let newNode0 = {
                key: lastNodekeys.lastKey +1,
                icon: bookCallNodes[0].icon,
                label: bookCallNodes[0].label,
                type: bookCallNodes[0].type,
                value: bookCallNodes[0].value,
                time: bookCallNodes[0]?.time || null,
                color: bookCallNodes[0].color,
                stroke: bookCallNodes[0].stroke,
                message: bookCallNodes[0]?.message || null,
                loc: `50 ${loc}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode0)

            let newLink0 = {
                from: lastNodekeys.centerKey,
                to: lastNodekeys.lastKey +1,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink0)

            let newNode1 = {
                key: lastNodekeys.lastKey +2,
                icon: bookCallNodes[1].icon,
                label: bookCallNodes[1].label,
                type: bookCallNodes[1].type,
                value: bookCallNodes[1].value,
                time: bookCallNodes[1]?.time || null,
                color: bookCallNodes[1].color,
                stroke: bookCallNodes[1].stroke,
                message: bookCallNodes[1]?.message || null,
                loc: `50 ${loc +60}`,
                pos: 'center',
                runStatus: false
            }
            nodeDataModel.push(newNode1)

            let newLink1 = {
                from: lastNodekeys.centerKey +1,
                to: lastNodekeys.lastKey +2,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(newLink1)
        }

        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    })

    /**
     * Set add action|end nodes and links
     */
    const setAddActionNodes = () => {
        ensureLinkDataModel() // Ensure linkDataModel is initialized
        let rightLastNodeKey, leftLastNodeKey, centerLastNodeKey, lastNodekeys, lastNodeKey, loc, locLength;
        let addActionLinks = [], newAddActionNodes = []

        lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})
        
        if(lastNodekeys['rightKey'] || lastNodekeys['leftKey']) {
            rightLastNodeKey = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
            leftLastNodeKey = nodeDataModel[lastNodekeys['leftKey']].loc.split(" ")
        
            lastNodeKey = nodeDataModel.length -1;
            rightLastNodeKey = parseInt(rightLastNodeKey[1]) + 60
            leftLastNodeKey = parseInt(leftLastNodeKey[1]) + 60

            addActionNodes[0].key = lastNodeKey + 1
            addActionNodes[0].loc = `${addActionNodes[0].loc.split(" ")[0]} ${leftLastNodeKey}`
            addActionNodes[1].key = lastNodeKey + 2
            addActionNodes[1].loc = `${addActionNodes[1].loc.split(" ")[0]} ${leftLastNodeKey}`
            addActionNodes[2].key = lastNodeKey + 3
            addActionNodes[2].loc = `${addActionNodes[2].loc.split(" ")[0]} ${rightLastNodeKey}`
            addActionNodes[3].key = lastNodeKey + 4
            addActionNodes[3].loc = `${addActionNodes[3].loc.split(" ")[0]} ${rightLastNodeKey}`

        }else if(lastNodekeys['centerKey']){
            centerLastNodeKey = nodeDataModel[lastNodekeys['centerIndex']].loc.split(" ")
            centerLastNodeKey = parseInt(centerLastNodeKey[1]) + 60
            lastNodeKey = nodeDataModel.length -1;

            addActionNodes[0].key = lastNodeKey + 1
            addActionNodes[0].loc = `50 ${centerLastNodeKey}`
            addActionNodes[0].pos = 'center'
            addActionNodes[1].key = lastNodeKey + 2
            addActionNodes[1].loc = `115 ${centerLastNodeKey}`
            addActionNodes[1].pos = 'center'

        }else{
            lastNodeKey = nodeDataModel.length -1;
            loc = nodeDataModel[lastNodeKey].loc.split(" ")
            leftLastNodeKey = parseInt(loc[1]) + 60
            rightLastNodeKey = parseInt(loc[1]) + 60
        }

        // Need to check if last left and right key is end or not before add add action and end keys
        if(lastNodekeys['leftKey'] || lastNodekeys['rightKey']){
            if(lastNodekeys['leftKey'] && nodeDataModel[lastNodekeys['leftKey']].type !== 'end'){
                addActionLinks.push(
                    {from: lastNodekeys['leftKey'], to: addActionNodes[0].key, fromSpot: "Bottom", toSpot: "Top"},
                )
                newAddActionNodes.push(addActionNodes[0])
                newAddActionNodes.push(addActionNodes[1])
            }
            if(lastNodekeys['rightKey'] && nodeDataModel[lastNodekeys['rightKey']].type !== 'end'){
                addActionLinks.push(
                    {from: lastNodekeys['rightKey'], to: addActionNodes[2].key, fromSpot: "Bottom", toSpot: "Top"},
                )
                newAddActionNodes.push(addActionNodes[2])
                newAddActionNodes.push(addActionNodes[3])
            }
        }else if(lastNodekeys['centerKey'] && nodeDataModel[lastNodekeys['centerIndex']].type !== 'end'){
            addActionLinks.push(
                {from: lastNodekeys['centerKey'], to: addActionNodes[0].key, fromSpot: "Bottom", toSpot: "Top"},
            )
            newAddActionNodes.push(addActionNodes[0])
            newAddActionNodes.push(addActionNodes[1])
        }

        // Merge add actions in to nodeDataModel, linkDataModel
        if(nodeDataModel.length && nodeDataModel[0].value != 'add-action'){
            if(newAddActionNodes.length){
                for(let item of newAddActionNodes){
                    nodeDataModel.push(item)
                }
            }

            if(addActionLinks.length){
                for(let item of addActionLinks){
                    linkDataModel.push(item)
                }
            }
        }
    }

    /**
     * Unset add action|end nodes and links
     */
    const unsetAddActionNodes = () => {
        // Get add action|End node keys and index
        let addActionNodes = getNodeLastPosKeys('lastAddAction'),
            endActionNodes = getNodeLastPosKeys('End');

        if(addActionNodes.leftKey || addActionNodes.rightKey){
            let leftAddActionNodeKey = addActionNodes.leftKey,
                leftAddActionNodeIndex = addActionNodes.leftIndex,
                rightAddActionNodeKey = addActionNodes.rightKey,
                rightAddActionNodeIndex = addActionNodes.rightIndex;

            let leftEndActionNodeKey = endActionNodes.leftKey,
                leftEndActionNodeIndex = endActionNodes.leftIndex,
                rightEndActionNodeKey = endActionNodes.rightKey,
                rightEndActionNodeIndex = endActionNodes.rightIndex;

            // Get add action link index
            let leftAddActionLinkIndex = getAddActionLinkIndex(leftAddActionNodeKey),
                rightAddActionLinkIndex = getAddActionLinkIndex(rightAddActionNodeKey);

            // Set actions index to remove
            let nodeIndexToRemove = [leftAddActionNodeIndex, leftEndActionNodeIndex, rightAddActionNodeIndex, rightEndActionNodeIndex],
                linkIndexToRemove = [leftAddActionLinkIndex, rightAddActionLinkIndex];

            // Remove add actions|End nodes, links using their index
            for (let i = nodeIndexToRemove.length -1; i >= 0; i--){
                if(nodeIndexToRemove[i])
                    nodeDataModel.splice(nodeIndexToRemove[i], 1);
            }
            for (let i = linkIndexToRemove.length -1; i >= 0; i--){
                if(linkIndexToRemove[i])
                    linkDataModel.splice(linkIndexToRemove[i], 1);
            }
        }else if(addActionNodes.centerKey){
            let centerAddActionNodeKey = addActionNodes.centerKey,
                centerAddActionNodeIndex = addActionNodes.centerIndex;

            let centerEndActionNodeKey = endActionNodes.centerKey,
                centerEndActionNodeIndex = endActionNodes.centerIndex;

            let nodeIndexToRemove = [centerAddActionNodeIndex, centerEndActionNodeIndex]
            let centerAddActionLinkIndex = getAddActionLinkIndex(centerAddActionNodeKey)
            
            // Remove add actions|End nodes, links using their index
            for (let i = nodeIndexToRemove.length -1; i >= 0; i--){
                if(nodeIndexToRemove[i])
                    nodeDataModel.splice(nodeIndexToRemove[i], 1);
            }
            linkDataModel.splice(centerAddActionLinkIndex, 1);
        }            
    }

    const setEndSequence = () => {
        let rightLastNodeKey, leftLastNodeKey, lastNodekeys, addEndLinks, lastNodeKey, loc, locLength;

        if(nodeDataModel[nodeIndex].pos == 'left'){
            unsetAddActionNodes()

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})

            // Set loc
            loc = nodeDataModel[lastNodekeys['leftKey']].loc.split(" ")
            loc = parseInt(loc[1]) + 60

            for(const [i, item] of endNodes.entries()){
                nodeDataModel.push({
                    key: lastNodekeys['lastKey']+1,
                    icon: item.icon,
                    label: item.label,
                    type: item.type,
                    value: item.value,
                    color: item.color,
                    stroke: item.stroke,
                    loc: `-150 ${loc}`,
                    pos: 'left'
                })

                linkDataModel.push({
                    from: lastNodekeys['leftKey'],
                    to: lastNodekeys['lastKey']+1,
                    fromSpot: "Bottom",
                    toSpot: "Top"
                })
            }
        }else if(nodeDataModel[nodeIndex].pos == 'right'){
            unsetAddActionNodes()

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction', {left: true, right: true})
            
            // Set loc
            loc = nodeDataModel[lastNodekeys['rightKey']].loc.split(" ")
            loc = parseInt(loc[1]) + 60

            for(const [i, item] of endNodes.entries()){
                nodeDataModel.push({
                    key: lastNodekeys['lastKey']+1,
                    icon: item.icon,
                    label: item.label,
                    type: item.type,
                    value: item.value,
                    color: item.color,
                    stroke: item.stroke,
                    loc: `150 ${loc}`,
                    pos: 'right'
                })

                linkDataModel.push({
                    from: lastNodekeys['rightKey'],
                    to: lastNodekeys['lastKey']+1,
                    fromSpot: "Bottom",
                    toSpot: "Top"
                })
            }
        }else if(nodeDataModel[nodeIndex].pos == 'center') {
            unsetAddActionNodes()

            // Get last item pos, keys after purged add action, end
            lastNodekeys = getNodeLastPosKeys('addAction')
            
            // Set loc
            loc = nodeDataModel[lastNodekeys.centerKey].loc.split(" ")
            loc = parseInt(loc[1]) + 60

            let endNode0 = {
                key: lastNodekeys.lastKey +1,
                icon: endNodes[0].icon,
                label: endNodes[0].label,
                type: endNodes[0].type,
                value: endNodes[0].value,
                color: endNodes[0].color,
                stroke: endNodes[0].stroke,
                loc: `50 ${loc}`,
                pos: 'center'
            }
            nodeDataModel.push(endNode0)
            let endLink0 = {
                from: lastNodekeys.centerKey,
                to: lastNodekeys.lastKey +1,
                fromSpot: "Bottom",
                toSpot: "Top"
            }
            linkDataModel.push(endLink0)
        }
        toogleDisableActionSide([0,1,2,3,4,5,6], 'disable')
        setAddActionNodes()
        setNodeLinkArray()
    }

    const getNodeLastPosKeys = (ref, pos=null) => {
        let lastLeftNodeKey, lastRightNodeKey, lastCenterNodeKey, leftIndex, rightIndex, centerIndex;

        if(ref == 'addAction'){
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'left') {
                    lastLeftNodeKey = nodeDataModel[i].key
                    leftIndex = i
                    break;
                }
            }
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'right') {
                    lastRightNodeKey = nodeDataModel[i].key
                    rightIndex = i
                    break;
                }
            }
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'center') {
                    lastCenterNodeKey = nodeDataModel[i].key
                    centerIndex = i
                    break;
                }
            }
            
            // If no center node found, use the last node as center
            if (!lastCenterNodeKey && nodeDataModel.length > 0) {
                lastCenterNodeKey = nodeDataModel[nodeDataModel.length - 1].key
                centerIndex = nodeDataModel.length - 1
            }
        }else if(ref == 'lastAddAction'){
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'left' && nodeDataModel[i].value == 'add-action') {
                    lastLeftNodeKey = nodeDataModel[i].key
                    leftIndex = i
                    break;
                }
            }
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'right' && nodeDataModel[i].value == 'add-action') {
                    lastRightNodeKey = nodeDataModel[i].key
                    rightIndex = i
                    break;
                }
            }
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'center' && nodeDataModel[i].value == 'add-action') {
                    lastCenterNodeKey = nodeDataModel[i].key
                    centerIndex = i
                    break;
                }
            }
        }else if(ref == 'End'){
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'left' && nodeDataModel[i].label == 'End') {
                    lastLeftNodeKey = nodeDataModel[i].key
                    leftIndex = i
                    break;
                }
            }
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'right' && nodeDataModel[i].label == 'End') {
                    lastRightNodeKey = nodeDataModel[i].key
                    rightIndex = i
                    break;
                }
            }
            for (let i = nodeDataModel.length -1; i > 0; i--) {
                if (nodeDataModel[i].pos == 'center' && nodeDataModel[i].label == 'End') {
                    lastCenterNodeKey = nodeDataModel[i].key
                    centerIndex = i
                    break;
                }
            }
        }
        return {
            leftKey: lastLeftNodeKey,
            rightKey: lastRightNodeKey,
            centerKey: lastCenterNodeKey,
            leftIndex: leftIndex,
            rightIndex: rightIndex,
            centerIndex: centerIndex,
            lastKey: nodeDataModel.length > 0 ? nodeDataModel[nodeDataModel.length - 1].key : null
        }
    }

    const getAddActionKey = pos => {
        for(let i=nodeDataModel.length -1; i > 0; i--){
            if(nodeDataModel[i].value == 'add-action'){
                if(pos == 'left' && nodeDataModel[i].pos == 'left'){
                    return nodeDataModel[i].key;
                }else if(pos == 'right' && nodeDataModel[i].pos == 'right'){
                    return nodeDataModel[i].key;
                }
            }
        }
    }

    const getAddActionLinkIndex = (actionKey) => {
        for(let i=linkDataModel.length -1; i > 0; i--){
            if(linkDataModel[i].to == actionKey){
                return i;
            }
        }
        return;
    }

    const getNodeDataModelIndex = nodeKey => {
        for(const [i, item] of nodeDataModel.entries()){
            if(item.key === nodeKey){
                return i;
            }
        }
        return;
    }

    const toogleDisableActionSide = (idxs, action) => {
        if(action == 'enable'){
            for(let i of idxs){
                addActionSide[i].disabled = false
                addActionSide[i].classList.remove(
                    'bg-gray-400',
                    'active:bg-gray-500',
                    'hover:bg-gray-400',
                    'focus:shadow-outline-gray'
                )
                addActionSide[i].classList.add(
                    'bg-indigo-600',
                    'hover:bg-indigo-700',
                    'active:bg-indigo-600',
                    'focus:shadow-outline-indigo'
                )
            }
        }else {
            for(let i of idxs){
                addActionSide[i].disabled = true
                addActionSide[i].classList.add(
                    'bg-gray-400',
                    'active:bg-gray-500',
                    'hover:bg-gray-400',
                    'focus:shadow-outline-gray'
                )
                addActionSide[i].classList.remove(
                    'bg-indigo-600',
                    'hover:bg-indigo-700',
                    'active:bg-indigo-600',
                    'focus:shadow-outline-indigo'
                )
            }
        }
    }

    const getStatusLastIds = () => {
        let notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction;

        for (let i = nodeDataModel.length -1; i > 0; i--) {
            if (nodeDataModel[i].hasOwnProperty('acceptedTime')) {
                acceptedTime = nodeDataModel[i].acceptedTime
                break;
            }
        }
        for (let i = nodeDataModel.length -1; i > 0; i--) {
            if (nodeDataModel[i].hasOwnProperty('acceptedAction')) {
                acceptedAction = nodeDataModel[i].acceptedAction
                break;
            }
        }
        for (let i = nodeDataModel.length -1; i > 0; i--) {
            if (nodeDataModel[i].hasOwnProperty('notAcceptedTime')) {
                notAcceptedTime = nodeDataModel[i].notAcceptedTime
                break;
            }
        }
        for (let i = nodeDataModel.length -1; i > 0; i--) {
            if (nodeDataModel[i].hasOwnProperty('notAcceptedAction')) {
                notAcceptedAction = nodeDataModel[i].notAcceptedAction
                break;
            }
        }
        return { notAcceptedTime, notAcceptedAction, acceptedTime, acceptedAction }
    }

    /**
     * Set node, link array values
     */
    const setNodeLinkArray = () => {
        nodeDataArray = []
        for(let item of nodeDataModel) {
            if(item.type === 'action' && item.label === 'View profile')
                item.icon = "\uf06e"
            else if(item.type === 'action' && item.label === 'Follow')
                item.icon = "\uf2bb"
            else if(item.type === 'action' && item.label === 'Like a post')
                item.icon = "\uf164"
            else if(item.type === 'action' && item.label === 'Send an invite')
                item.icon = "\uf007"
            else if(item.type === 'action' && item.label === 'Endorse skills')
                item.icon = "\uf058"
            else if(item.type === 'action' && item.label === 'Message')
                item.icon = "\uf27a"
            else if(item.type === 'condition' && item.label === 'Accepted')
                item.icon = "\uf058"
            else if(item.type === 'condition' && item.label === 'Still not accepted')
                item.icon = "\uf05e"
            else if(item.type === 'delay')
                item.icon = "\uf017"
            else if(item.type === 'end')
                item.icon = "\uf05e"
            
            nodeDataArray.push({
                key: item.key, 
                text: `${item.icon} ${item.label}`,
                font: "12pt awesome", 
                color: item.color, 
                stroke: item.stroke, 
                loc: item.loc
            })
        }
        linkDataArray = []
        linkDataArray = linkDataModel;
        diagram.model = new go.GraphLinksModel(nodeDataArray, linkDataArray)
    }
}
init()

/**
 * Hide all fields.
 */
const hideAllFields = () => {
    inviteMessageFields.style.display = 'none'
    delayFields.style.display = 'none'
    sendMessageFields.style.display = 'none'
    endorseSkillFields.style.display = 'none'
    applyActionBtn.style.display = 'none'
}

/**
 * Add variable to message.
 * @param {string} mv
 */
const addVariableToMessage = mv => {
    let cursorPos, textBefore, textAfter;

    if(nodeDataModel[nodeKey].value == 'send-invites'){
        cursorPos = inviteMessage.selectionStart
        textBefore = inviteMessage.value.substring(0,  cursorPos)
        textAfter  = inviteMessage.value.substring(cursorPos, inviteMessage.value.length)
        inviteMessage.value = textBefore + mv + textAfter
    }else if(nodeDataModel[nodeKey].value == 'message'){
        cursorPos = sendMessage.selectionStart
        textBefore = sendMessage.value.substring(0,  cursorPos)
        textAfter  = sendMessage.value.substring(cursorPos, sendMessage.value.length)
        sendMessage.value = textBefore + mv + textAfter
    }else if(nodeDataModel[nodeKey].value == 'call'){
        cursorPos = callMessageTextField.selectionStart
        textBefore = callMessageTextField.value.substring(0,  cursorPos)
        textAfter  = callMessageTextField.value.substring(cursorPos, callMessageTextField.value.length)
        callMessageTextField.value = textBefore + mv + textAfter
    }
}

const saveSequence = async () => {
    let formData = new FormData();
    formData.append('sequence_type', 'custom');
    formData.append('node_model', JSON.stringify(nodeDataModel));
    formData.append('link_model', JSON.stringify(linkDataModel));

    let searchParams = new URLSearchParams(window.location.search);
    let sequenceStep = searchParams.get("step"),
        campaignId = searchParams.get("cid");
    let submitSequenceBtn = document.querySelector('.submit-custom-sequence')
    submitSequenceBtn.disabled = true

    await fetch(`/sequence/store?step=${sequenceStep}&cid=${campaignId}`, {
        headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        submitSequenceBtn.disabled = false
        if(res.status === 201){
            window.location = res.redirect
        }
    })
}

function fetchCalendlyEvents(){
    try {
        fetch(`/calendly-events`)
            .then(res => res.json())
            .then(res => {
                if(!res.ok){
                    console.log(res.message)
                }else {
                    console.log(res)
                }
            })
    } catch (error) {
        console.log(error.message)
    }
}
// fetchCalendlyEvents()
</script>