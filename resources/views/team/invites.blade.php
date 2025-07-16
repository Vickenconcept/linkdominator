<div class="w-full overflow-hidden rounded-lg">
    <div class="w-full overflow-x-auto">
        <div class="grid grid-cols-12 p-3 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm font-semibold px-3 text-sm">
            <div class="col-span-4 flex gap-2">
               Email
            </div>
            <div class="col-span-2">Role</div>
            <div class="col-span-3">Sent By</div>
            <div class="col-span-2">Date</div>
            @if($team_user_type == 'owner')
            <div class="col-span-1"></div>
            @endif
        </div>
        <div>
            @foreach($teamInvites as $item)
            <div class="grid grid-cols-12 p-3 px-3 mb-2 bg-white hover:bg-indigo-100 rounded-lg shadow-sm font-normal cursor-pointer text-gray-600 text-sm">
                <div class="col-span-4 flex gap-2">
                    {{ $item->email }}
                </div>
                <div class="col-span-2 capitalize">
                    {{ $item->role }}
                </div>
                <div class="col-span-3">{{ $item->invitedBy->name }}</div>
                <div class="col-span-2">{{ date_format(date_create($item->created_at), "d M, Y") }}</div>
                @if($team_user_type == 'owner')
                <div class="col-span-1 flex gap-2">
                    <form action="{{route('team.resendInvite',['id' => $item->id])}}" method="post">
                        @csrf
                        <div class="hs-tooltip inline-block">
                            <button class="hs-tooltip-toggle text-xs font-normal text-center text-gray-500 focus:border-0 p-1 focus:ring-0 dark:text-white dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="shrink-0 size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                                    Resend invitation
                                </span>
                            </button>
                        </div>
                    </form>
                    <form action="{{route('team.deleteInvite',['id' => $item->id])}}" method="post">
                        @csrf
                        @method('DELETE')
                        <div class="hs-tooltip inline-block">
                            <button class="hs-tooltip-toggle text-xs font-normal text-center text-gray-500 focus:border-0 p-1 focus:ring-0 dark:text-white dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="shrink-0 size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                                    Delete
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @if(count($teamInvites)>0)
    {{ $teamInvites->links() }}
    @endif
</div>