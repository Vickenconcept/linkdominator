<div>
    <h4 class="mb-2 font-semibold">Endorse My Skills</h4>
    <div class="flex flex-row gap-3">
        <div class="w-full bg-gray-100 rounded" style="height: 70vh;">
            <div id="myEndorsementDiagramDiv" style="width:100%; min-height:100%"></div>
        </div>
    </div>
    <div class="pt-3 mb-4"></div>
    <button type="button" style="display: none" class="endorse-modal-btn py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-endorse-modal" data-hs-overlay="#hs-endorse-modal">
        Toggle modal
    </button>
    <div id="hs-endorse-modal" class="hs-overlay hidden size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="hs-endorse-modal-label">
        <div class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-0 opacity-0 ease-out transition-all md:max-w-2xl md:w-full m-3 md:mx-auto">
            <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
                <div class="flex justify-between items-center py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                    <h3 id="hs-medium-modal-label" class="font-bold text-gray-800 dark:text-white modal-title">
                    Endorse
                    </h3>
                    <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600" aria-label="Close" data-hs-overlay="#hs-endorse-modal">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto">
                    <!-- Endorse fields -->
                    <div id="endorse-fields" class="bg-white rounded-lg endorse-sequence-field w-64" style="display: none;">
                        <div class="flex justify-between">
                            <h4 class="font-normal text-sm">Total skills to endorse:</h4>
                        </div>
                        <div class="grid grid-cols-6 gap-3 pt-2">
                            <div class="col-span-6">
                                <input type="number" id="total-skill" class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full px-3 disabled:opacity-50 disabled:pointer-events-none">
                            </div>
                        </div>
                    </div>

                    <!-- Delay fields -->
                    <div id="delay-fields" class="bg-white rounded-lg endorse-sequence-fields w-64" style="display: none;">
                        <div class="flex justify-between">
                            <h4 class="font-normal text-sm">Delay before the next action:</h4>
                        </div>
                        <div class="grid grid-cols-6 gap-3 pt-2">
                            <div class="col-span-2">
                                <input type="number" id="time-number" class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full px-3 disabled:opacity-50 disabled:pointer-events-none">
                            </div>
                            <div class="col-span-3">
                                <select id="time-type" class="px-4 pe-9 py-2 block w-full border-gray-300 rounded focus:border-indigo-600 focus:ring-indigo-600 disabled:opacity-50 disabled:pointer-events-none">
                                    <option value="days">days</option>
                                    <option value="hours">hours</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                    <button type="button" id="close-apply-action-main" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-overlay="#hs-endorse-modal">
                    Close
                    </button>
                    <button type="button" id="apply-action-main" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-hidden focus:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none">
                    Save changes
                    </button>
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
            hover:bg-indigo-600 focus:outline-none focus:shadow-outline-indigo submit-endorse-sequence">
            <span class=" flex gap-1">
                <span class="pt-1">Next</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </span>
        </button>
    </div>
</div>

<script>
let dbSequenceType = '{{ $sequenceType }}';
let dbSequenceNode = @json($dbSequenceNode);
let dbSequenceLink = @json($dbSequenceLink);
let nodeDataModel, linkDataModel;

if(dbSequenceType === 'endorse' && dbSequenceNode.length > 0 && dbSequenceLink.length > 0){
    nodeDataModel = dbSequenceNode
    linkDataModel = dbSequenceLink
}else{
    nodeDataModel = [
        {key: 0, icon: "\uf058", label: "Endorse skills",  type: 'action', value: 'endorse', time: 2,  color: "#5560E5", stroke: "white",  loc: "50 0", totalSkills: 1, runStatus: false},
        {key: 1, icon: "\uf017", label: "5 days",          type: 'delay',  value: 5, time: 'days',     color: "#F3F4F6", stroke: "black",  loc: "50 60", runStatus: false},
        {key: 2, icon: "\uf058", label: "Endorse skills",  type: 'action', value: 'endorse', time: 2,  color: "#5560E5", stroke: "white",  loc: "50 120", totalSkills: 1, runStatus: false},
        {key: 3, icon: "\uf017", label: "10 days",         type: 'delay',  value: 10, time: 'days',    color: "#F3F4F6", stroke: "black",  loc: "50 180", runStatus: false},
        {key: 4, icon: "\uf058", label: "Endorse skills",  type: 'action', value: 'endorse', time: 2,  color: "#5560E5", stroke: "white",  loc: "50 240", totalSkills: 1, runStatus: false},
        {key: 5, icon: "\uf017", label: "No delay",        type: 'delay',  value: 0, time: 'hours',    color: "#F3F4F6", stroke: "black",  loc: "50 300", runStatus: false},
        {key: 6, icon: "\uf05e", label: "End of sequence", type: 'end',    value: 'end', time: null,   color: "#9ca3af", stroke: "white",  loc: "50 360", totalSkills: 1, runStatus: false},
    ]
    linkDataModel = [
        {from: 0, to: 1},
        {from: 1, to: 2},
        {from: 2, to: 3},
        {from: 3, to: 4},
        {from: 4, to: 5},
        {from: 5, to: 6}
    ]
}

let nodeItem, nodeKey;
let delayFields = document.querySelector('#delay-fields')
let endorseFields = document.querySelector('#endorse-fields')

let endorseSkill = document.querySelector('#total-skill')
let timeNumber = document.querySelector('#time-number')
let timeType = document.querySelector('#time-type')

let applyActionMain = document.querySelector('#apply-action-main'),
    closeApplyActionMain = document.querySelector('#close-apply-action-main');

let modalTitle = document.querySelector('.modal-title')

const initEndorsement = () => {
    const diagram = new go.Diagram("myEndorsementDiagramDiv");

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
        diagram.linkTemplate = new go.Link()
            .add(
                new go.Shape()
            );
        // Event listener
        diagram.addDiagramListener('ObjectSingleClicked', function(e){
            nodeItem = e.subject.Hs.si
            nodeKey = nodeItem.key

            for(let elem of document.querySelectorAll('.endorse-sequence-field')){
                elem.style.display = 'none'
            }

            endorseFields.style.display = 'none'
            delayFields.style.display = 'none'
            if(nodeItem.type == 'delay'){
                document.querySelector('.endorse-modal-btn').click()

                delayFields.style.display = 'block'
                timeNumber.value = nodeItem.value
                timeType.value = nodeItem.time

                modalTitle.innerHTML = 'Delay'
            }else if(nodeItem.type == 'action' && nodeItem.value == 'endorse'){
                document.querySelector('.endorse-modal-btn').click()

                endorseFields.style.display = 'block'
                endorseSkill.value = nodeDataModel[nodeKey].totalSkills

                modalTitle.innerHTML = 'Endorse skills'
            }

            window.scrollTo(0, document.body.scrollHeight);
        })
        setNodeLinkArray()
    }

    /**
     * Set node, link array values
     **/
    const setNodeLinkArray = () => {
        nodeDataArray = []
        for(let item of nodeDataModel) {
            if(item.type === 'action')
                item.icon = "\uf058"
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
                loc: item.loc,
                type: item.type,
                value: item.value,
                time: item.time,
            })
        }
        linkDataArray = []
        linkDataArray = linkDataModel;
        diagram.model = new go.GraphLinksModel(nodeDataArray, linkDataArray)
    }

    /**
     * Update endorsement node model
     **/
    applyActionMain.addEventListener('click', () => {
        if(nodeDataModel[nodeKey].type == 'action'){
            nodeDataModel[nodeKey].totalSkills = endorseSkill.value
            endorseFields.style.display = 'none'
        }else if(nodeDataModel[nodeKey].type == 'delay'){
            nodeDataModel[nodeKey].label = timeNumber.value == 0 ? 'No delay' : `${timeNumber.value} ${timeType.value}`
            nodeDataModel[nodeKey].value = timeNumber.value
            nodeDataModel[nodeKey].time = timeType.value
            delayFields.style.display = 'none'
        }
        setNodeLinkArray()
        closeApplyActionMain.click()
    })
}
initEndorsement()

/**
 * Hide all fields.
 */
const hideAllFields = () => {
    delayFields.style.display = 'none'
    endorseFields.style.display = 'none'
}

/**
 * Save sequence
 **/
const saveSequence = async () => {
    let formData = new FormData();
    formData.append('sequence_type', 'endorse');
    formData.append('node_model', JSON.stringify(nodeDataModel));
    formData.append('link_model', JSON.stringify(linkDataModel));

    let searchParams = new URLSearchParams(window.location.search);
    let sequenceStep = searchParams.get("step"),
        campaignId = searchParams.get("cid");
    let submitSequenceBtn = document.querySelector('.submit-endorse-sequence')
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
</script>