@extends('layout.auth')

@section('content')
<script src="{{ asset('js/gojs/go-debug.js') }}"></script>

<div class="block p-6 bg-white border border-gray-200 rounded-lg shadow-md">
    <div class="pb-4">
        <ol class="items-center justify-between w-full space-y-4 sm:flex sm:space-x-8 sm:space-y-0 rtl:space-x-reverse">
            <li class="flex items-center space-x-2.5 rtl:space-x-reverse" style="color: #0077b5;">
                <span class="flex items-center justify-center w-8 h-8 border rounded-full shrink-0 text-white" style="border-color: #0077b5; background: linear-gradient(135deg, #0077b5 0%, #005885 100%);">
                    1
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">Add leads</h3>
                </span>
            </li>
            <li class="flex items-center space-x-2.5 rtl:space-x-reverse text-gray-500
                {{ in_array(request()->query('step'), ['sequence','sequence-custom','sequence-endorsement','sequence-profileView','sequence-leadGen','summarize']) ? '':'' }}" style="{{ in_array(request()->query('step'), ['sequence','sequence-custom','sequence-endorsement','sequence-profileView','sequence-leadGen','summarize']) ? 'color: #0077b5;' : '' }}">
                <span class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 
                {{ in_array(request()->query('step'), ['sequence','sequence-custom','sequence-endorsement','sequence-profileView','sequence-leadGen','summarize']) ? '':'' }}" style="{{ in_array(request()->query('step'), ['sequence','sequence-custom','sequence-endorsement','sequence-profileView','sequence-leadGen','summarize']) ? 'border-color: #0077b5; background: linear-gradient(135deg, #0077b5 0%, #005885 100%); color: white;' : '' }}">
                    2
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">Create a sequence</h3>
                </span>
            </li>
            <li class="flex items-center space-x-2.5 rtl:space-x-reverse text-gray-500
                {{ in_array(request()->query('step'), ['summarize']) ? '':'' }}" style="{{ in_array(request()->query('step'), ['summarize']) ? 'color: #0077b5;' : '' }}">
                <span class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 
                {{ in_array(request()->query('step'), ['summarize']) ? '':'' }}" style="{{ in_array(request()->query('step'), ['summarize']) ? 'border-color: #0077b5; background: linear-gradient(135deg, #0077b5 0%, #005885 100%); color: white;' : '' }}">
                    3
                </span>
                <span>
                    <h3 class="font-medium leading-tight text-sm">Summarize and launch</h3>
                </span>
            </li>
        </ol>
    </div>
    <hr>
    <div class="mt-4">
        @switch(request()->query('step'))
            @case('lead')
                <div class="leads-section form-section">
                    @include('campaign.sections.lead')
                </div>
                @break

            @case('sequence')
                <div class="sequence-section form-section">
                    @include('campaign.sections.sequence')
                </div>
                @break

            @case('summarize')
                <div class="summarize-section form-section">
                    @include('campaign.sections.summarize')
                </div>
                @break

            @case('sequence-custom')
                <div class="sequence-custom form-section">
                    @include('campaign.sections.sequence-custom')
                </div>
                @break

            @case('sequence-endorsement')
                <div class="sequence-endorsement form-section">
                    @include('campaign.sections.sequence-endorseskill')
                </div>
                @break

            @case('sequence-profileView')
                <div class="sequence-profileview form-section">
                    @include('campaign.sections.sequence-profileview')
                </div>
                @break

            @case('sequence-leadGen')
                <div class="sequence-leadGen form-section">
                    @include('campaign.sections.sequence-leadgen')
                </div>
                @break
        @endswitch
    </div>
</div>
@endsection