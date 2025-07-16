@extends('layout.auth')

@section('content')
<div class="flex justify-between">
    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 pt-2">
        Lead Lists
    </h2>
    <div>
        <form method="get" action="{{route('leads.list.search')}}">
            <label class="block text-sm">
                <div class="relative text-gray-500 focus-within:text-indigo-600">
                    <input class="block w-full pr-20 mt-1 text-sm text-black 
                    dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 
                    focus:border-indigo-400 focus:outline-none rounded  border-gray-500
                    focus:shadow-outline-indigo dark:focus:shadow-outline-gray 
                    form-input"
                    name="search"
                    placeholder="Search list" 
                    value="{{request()->query('search')}}"/>
                    <button 
                    type="submit"
                    class="absolute inset-y-0 right-0 px-4 text-sm 
                    font-medium leading-5 text-white transition-colors 
                    duration-150 bg-indigo-600 border border-transparent 
                    rounded-r-md active:bg-indigo-600 hover:bg-indigo-700 
                    focus:outline-none focus:shadow-outline-indigo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </label>
        </form>
    </div>
</div>
<div class="mt-3">
    <div class="w-full overflow-hidden rounded-lg">
        <div class="w-full overflow-x-auto">
            <div class="grid grid-cols-12 p-3 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm font-semibold px-3 text-sm">
                <div class="col-span-3">Name</div>
                <div class="col-span-2">List ID</div>
                <div class="col-span-2">Leads</div>
                <div class="col-span-2">Source</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-1"></div>
            </div>
            <div>
                @foreach($leadlist as $item)
                <div class="grid grid-cols-12 p-3 px-3 mb-2 bg-white hover:bg-indigo-100 rounded-lg shadow-sm font-normal cursor-pointer text-gray-600 text-sm">
                    <div class="col-span-3">
                        <a href="{{route('leads.show', ['listId' => $item->list_hash, 'src' => $item->src])}}" >
                            {{$item->list_name}}
                        </a>
                    </div>
                    <div class="col-span-2">{{$item->list_hash}}</div>
                    <div class="col-span-2 px-3">{{$item->total_leads}}</div>
                    <div class="col-span-2">
                        @if ($item->source == 'Audience')
                        <span class="bg-indigo-100 text-indigo-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-indigo-900 dark:text-indigo-300">{{$item->source}}</span>
                        @else
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">{{$item->source}}</span>
                        @endif
                    </div>
                    <div class="col-span-2">{{date_format(date_create($item->created_at), "d M, Y")}}</div>
                    <div class="hs-dropdown relative inline-flex">
                        <button id="hs-dropdown-default-{{$item->id}}" type="button" class="hs-dropdown-toggle py-2 px-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                                <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                            </svg>
                        </button>
                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-50 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-default-{{$item->id}}">
                            <div class="p-1 space-y-0.5">
                                <a href="#" class="update-list-modal flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700"
                                data-id="{{$item->id}}"
                                data-listid="{{$item->list_hash}}"
                                data-listname="{{$item->list_name}}"
                                data-listsource="{{$item->source}}"
                                aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-basic-modal" data-hs-overlay="#hs-basic-modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" />
                                        <path d="M5.25 5.25a3 3 0 00-3 3v10.5a3 3 0 003 3h10.5a3 3 0 003-3V13.5a.75.75 0 00-1.5 0v5.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5h5.25a.75.75 0 000-1.5H5.25z" />
                                    </svg>
                                    Edit list
                                </a>
                                <a href="{{route('leads.show', ['listId' => $item->list_hash, 'src' => $item->src])}}" class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                                    </svg>
                                    View leads
                                </a>
                                <a href="#" class="export flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700"
                                data-id="{{$item->id}}"
                                data-hash="{{$item->list_hash}}"
                                data-name="{{$item->list_name}}"
                                data-src="{{$item->src}}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 0 0 6 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5h6a1.5 1.5 0 0 0 1.5-1.5V15a.75.75 0 0 1 1.5 0v3.75a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3V5.25a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3V9A.75.75 0 0 1 15 9V5.25a1.5 1.5 0 0 0-1.5-1.5h-6Zm10.72 4.72a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H9a.75.75 0 0 1 0-1.5h10.94l-1.72-1.72a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                    Export leads
                                </a>
                                <form action="{{route('leads.remove_leadlist', ['listId' => $item->list_hash])}}" method="POST">
                                    @csrf
                                    @method('DELETE') 
                                    <input type="hidden" name="src" value="{{$item->src}}">
                                    <button type="submit" class="flex w-full items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clip-rule="evenodd" />
                                        </svg>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @if(count($leadlist)>0)
            {{ $leadlist->links() }}
            @endif
        </div>
    </div>
</div>

<!-- Update modal -->
<div id="hs-basic-modal" class="hs-overlay hs-overlay-open:opacity-100 hs-overlay-open:duration-500 hidden size-full fixed top-0 start-0 z-80 opacity-0 overflow-x-hidden transition-all overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="hs-basic-modal-label">
    <div class="sm:max-w-lg sm:w-full m-3 sm:mx-auto">
        <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl pointer-events-auto dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-neutral-700/70">
            <div class="flex justify-between items-center py-3 px-4 border-b border-gray-200 dark:border-neutral-700">
                <h3 id="hs-basic-modal-label" class="font-bold text-gray-800 dark:text-white flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mt-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                    Update lead list
                </h3>
                <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-400 dark:focus:bg-neutral-600" aria-label="Close" data-hs-overlay="#hs-basic-modal">
                <span class="sr-only">Close</span>
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                </button>
            </div>
            <form action="" method="post" id="update-list-form">
                @csrf
                @method('PUT')
                <div class="p-4 overflow-y-auto">
                    <input type="hidden" name="id" id="update-id">
                    <input type="hidden" name="list_source" id="update-list-source">
                    <div class="mb-4">
                        <label for="update-list-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">List name</label>
                        <input type="text" id="update-list-name" name="list_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                    </div>
                </div>
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200 dark:border-neutral-700">
                    <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" data-hs-overlay="#hs-basic-modal">
                    Close
                    </button>
                    <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                    Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$('.update-list-modal').click(function() {
    let listName = $(this).data('listname'),
        id = $(this).data('id'),
        listId = $(this).data('listid'),
        listSrc = $(this).data('listsource');
    
    $('#update-id').val(id)
    $('#update-list-name').val(listName)
    $('#update-list-source').val(listSrc)
    $('#update-list-form').attr('action', `/leadlist/update/${listId}`)
})

$('.export').click(function() {
    let listHash = $(this).data('hash'),
        listId = $(this).data('id'),
        listSrc = $(this).data('src'),
        listName = $(this).data('name');

    const date = new Date();
    const d = date.getDate();
    const m = date.getMonth();
    const y = date.getFullYear();
    const mtn = date.getMinutes();
    const fileName =  `${listName}_${d}${m}${y}${mtn}.csv`;

    $.ajax({
        method: 'get',
        url: `/api/leads/export?src=${listSrc}&hash=${listHash}&format=csv`,
        success: function(res) {
            const csvRows = [];
            const headers = Object.keys(res.data[0]);
            csvRows.push(headers.join(','));

            for (const row of res.data) {
                const values = headers.map(header => {
                    const val = row[header]
                    return `"${val}"`;
                });
        
                // To add, sepearater between each value
                csvRows.push(values.join(','));
            }
            const download = csvRows.join('\n');
        
            var blob = new Blob([download]);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = fileName;
            link.click();
        }
    })
})
</script>
@endsection