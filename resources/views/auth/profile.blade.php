@extends('layout.auth')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
        Account settings
    </h2>
    <div class="grid gap-3 mb-8 md:grid-cols-2 mt-4">
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                Personal Information
            </h4>
            <p class="text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
            <form action="{{route('auth.update')}}" method="post" class="mt-6">
                @csrf
                @method('PUT')
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Name</span>
                    <input
                    type="text"
                    class="block w-full mt-1 text-sm dark:border-gray-600 
                    dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:text-gray-300 
                    dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                    id="name" name="name" value="{{ auth()->user()->name }}" 
                    placeholder="Name" 
                    required
                    />
                </label>
                <label class="block text-sm mt-6">
                    <span class="text-gray-700 dark:text-gray-400">Email</span>
                    <input
                    type="email"
                    class="block w-full mt-1 text-sm dark:border-gray-600 
                    dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:text-gray-300 
                    dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                    placeholder="email@example.com"
                    id="email" name="email" value="{{ auth()->user()->email }}"
                    required
                    readonly
                    />
                </label>
                <label class="block text-sm mt-6">
                    <span class="text-gray-700 dark:text-gray-400">LinkedIn profile ID</span>
                    <input
                    type="text"
                    class="block w-full mt-1 text-sm dark:border-gray-600 
                    dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:text-gray-300 
                    dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                    id="linkedin-id" name="linkedin_id" value="{{ auth()->user()->linkedin_id }}" 
                    placeholder="LinkedIn ID"
                    />
                </label>
                <label class="block text-sm mt-6">
                    <span class="text-gray-700 dark:text-gray-400">Time zone</span>
                    <select class="block w-full mt-1 text-sm 
                    dark:text-gray-300 dark:border-gray-600 rounded-md
                    dark:bg-gray-700 form-select focus:border-indigo-400 
                    focus:outline-none focus:shadow-outline-indigo 
                    dark:focus:shadow-outline-gray border-gray-300"
                    id="timezone"
                    name="timezone">
                        @foreach($timezones as $item)
                        <option value="{{$item->id}}" {{$item->id == auth()->user()->time_zone_id ? 'selected':'' }}>
                            {{$item->time_zone}}
                        </option>
                        @endforeach
                    </select>
                </label>
                <button 
                type="submit"
                class="block w-full px-4 py-2 mt-6 text-sm font-medium 
                leading-5 text-center text-white transition-colors duration-150 
                bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                    Save
                </button>
            </form>
        </div>
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
                Update Password
            </h4>
            <p class="text-gray-600 dark:text-gray-400">
                Ensure your account is using a long, random password to stay secure.
            </p>
            <form action="{{ route('auth.updatePassword') }}" method="post">
                @csrf
                @method('PUT')
                <label class="block mt-6 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Current password</span>
                    <input
                    type="password"
                    class="block w-full mt-1 text-sm dark:border-gray-600 
                    dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:text-gray-300 
                    dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                    placeholder="***************"
                    id="current-password" name="old_password" 
                    required
                    />
                </label>
                <label class="block mt-6 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">New password</span>
                    <input
                    type="password"
                    class="block w-full mt-1 text-sm dark:border-gray-600 
                    dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:text-gray-300 
                    dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                    placeholder="***************"
                    id="new-password" name="new_password" 
                    required
                    />
                </label>
                <label class="block mt-6 text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Confirm new password</span>
                    <input
                    type="password"
                    class="block w-full mt-1 text-sm dark:border-gray-600 
                    dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                    focus:shadow-outline-indigo dark:text-gray-300 
                    dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                    placeholder="***************"
                    id="confirm-password" name="confirm_password"
                    required
                    />
                </label>
                <button 
                type="submit"
                class="block w-full px-4 py-2 mt-6 text-sm font-medium 
                leading-5 text-center text-white transition-colors duration-150 
                bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                    Save
                </button>
            </form>
        </div>
    </div>
    <div class="grid gap-3 mb-8 mt-3 md:grid-cols-2">
        <div class="min-w-0 p- bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="px-4 py-5">
                <div>
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Email Integration
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Set-up an email integration here and your emails will directly get pushed into your ESP.
                        </p>
                    </div>

                    <div class="md:flex flex-nowrap gap-1">
                        <button 
                        type="button"
                        class="block px-2 py-2 mt-6 text-xs font-medium mailchimp
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        MailChimp
                        </button>
                        <button 
                        type="button"
                        class="block px-2 py-2 mt-6 text-xs font-medium getresponse
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        Get Response
                        </button>
                        <button 
                        type="button"
                        class="block px-2 py-2 mt-6 text-xs font-medium emailoctopus
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        Email Octopus
                        </button>
                        <button 
                        type="button"
                        class="block px-2 py-2 mt-6 text-xs font-medium converterkit
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        ConverterKit
                        </button>
                        <button 
                        type="button"
                        class="block px-2 py-2 mt-6 text-xs font-medium mailerlite
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        Mailerlite
                        </button>
                        <button 
                        type="button"
                        class="block px-2 py-2 mt-6 text-xs font-medium webhook
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        Webhook
                        </button>
                    </div>
                    <!-- fields -->
                    <form action="{{route('auth.updateEsp')}}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="flex gap-4 mt-6 mailchimp-input esp-input hidden">
                            <label class="block text-sm w-full mb-4">
                                <span class="text-gray-700 dark:text-gray-400">Mailchimp API Key</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="mailchimp_key" name="mailchimp_key"
                                value="{{$esp?->mailchimp?->apikey}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Leave Empty to disable this integration
                                </p>
                            </label>
                            <label class="block text-sm w-full">
                                <span class="text-gray-700 dark:text-gray-400">Mailchimp List ID</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="mailchimp_listid" name="mailchimp_listid"
                                value="{{$esp?->mailchimp?->listid}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Ensure you add the List/Audience ID
                                </p>
                            </label>
                        </div>
                        <div class="flex gap-4 mt-6 getresponse-input esp-input hidden">
                            <label class="block text-sm w-full mb-4">
                                <span class="text-gray-700 dark:text-gray-400">GetResponse API Key</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="getresponse_key" name="getresponse_key"
                                value="{{$esp?->getresponse?->apikey}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Leave Empty to disable this integration
                                </p>
                            </label>
                            <label class="block text-sm w-full">
                                <span class="text-gray-700 dark:text-gray-400">GetResponse Campaign ID</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="getresponse_campaignid" name="getresponse_campaignid"
                                value="{{$esp?->getresponse?->campaignId}}"/>
                            </label>
                        </div>
                        <div class="flex gap-4 mt-6 emailoctopus-input esp-input hidden">
                            <label class="block text-sm w-full mb-4">
                                <span class="text-gray-700 dark:text-gray-400">Email Octopus API Key</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="emailoctopus_key" name="emailoctopus_key"
                                value="{{$esp?->emailoctopus?->apikey}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Leave Empty to disable this integration
                                </p>
                            </label>
                            <label class="block text-sm w-full">
                                <span class="text-gray-700 dark:text-gray-400">Email Octopus List ID</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="emailoctopus_listid" name="emailoctopus_listid"
                                value="{{$esp?->emailoctopus?->listid}}"/>
                            </label>
                        </div>
                        <div class="flex gap-4 mt-6 converterkit-input esp-input hidden">
                            <label class="block text-sm w-full mb-4">
                                <span class="text-gray-700 dark:text-gray-400">ConverterKit API Key</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="converterkit_key" name="converterkit_key"
                                value="{{$esp?->converterkit?->apikey}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Leave Empty to disable this integration
                                </p>
                            </label>
                            <label class="block text-sm w-full">
                                <span class="text-gray-700 dark:text-gray-400">ConverterKit Form ID</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="converterkit_formid" name="converterkit_formid"
                                value="{{$esp?->converterkit?->formId}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Ensure you add the Form ID
                                </p>
                            </label>
                        </div>
                        <div class="flex gap-4 mt-6 mailerlite-input esp-input hidden">
                            <label class="block text-sm w-full mb-4">
                                <span class="text-gray-700 dark:text-gray-400">MailerLite API Key</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="mailerlite_key" name="mailerlite_key"
                                value="{{$esp?->mailerlite?->apikey}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Leave Empty to disable this integration
                                </p>
                            </label>
                            <label class="block text-sm w-full">
                                <span class="text-gray-700 dark:text-gray-400">MailerLite Form ID</span>
                                <input
                                type="text"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="mailerlite_groupid" name="mailerlite_groupid"
                                value="{{$esp?->mailerlite?->groupId}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Ensure you add the Form ID
                                </p>
                            </label>
                        </div>
                        <div class="flex gap-4 mt-6 webhook-input esp-input hidden">
                            <label class="block text-sm w-full">
                                <span class="text-gray-700 dark:text-gray-400">Webhook URL</span>
                                <input
                                type="url"
                                class="block w-full mt-1 text-sm dark:border-gray-600 
                                dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                                focus:shadow-outline-indigo dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                                id="webhook" name="webhook"
                                value="{{$esp?->webhook}}"/>
                                <p class="mt-1 text-gray-500 text-xs">
                                    Leave Empty to disable this integration
                                </p>
                            </label>
                        </div>

                        <button 
                        type="submit"
                        class="block px-4 py-2 mt-6 text-sm font-medium 
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    Api Key
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Manage your API key here.
                </p>
            </div>
            <div class="mt-4">
                <form action="{{route('auth.generateToken')}}" method="post">
                    @csrf
                    <label class="block text-sm w-full mb-4">
                        <input
                        type="text"
                        class="block w-full mt-1 text-sm dark:border-gray-600 
                        dark:bg-gray-700 focus:border-indigo-400 focus:outline-none 
                        focus:shadow-outline-indigo dark:text-gray-300 
                        dark:focus:shadow-outline-gray form-input rounded-md border-gray-300"
                        id="api_token" name="api_token"
                        value="{{auth()->user()->access_token}}"
                        disabled/>
                    </label>
                    <button 
                        type="submit"
                        class="block px-2 py-2 mt-3 text-xs font-medium
                        leading-5 text-center text-white transition-colors duration-150 
                        bg-indigo-600 border border-transparent rounded-lg active:bg-indigo-600 
                        hover:bg-indigo-700 focus:outline-none focus:shadow-outline-indigo">
                        {{ auth()->user()->access_token ? 'Regenerate':'Generate'}}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('.mailchimp').click(function() {
        $('.esp-input').hide()
        $('.mailchimp-input').show()
    })
    $('.getresponse').click(function() {
        $('.esp-input').hide()
        $('.getresponse-input').show()
    })
    $('.emailoctopus').click(function() {
        $('.esp-input').hide()
        $('.emailoctopus-input').show()
    })
    $('.converterkit').click(function() {
        $('.esp-input').hide()
        $('.converterkit-input').show()
    })
    $('.mailerlite').click(function() {
        $('.esp-input').hide()
        $('.mailerlite-input').show()
    })
    $('.webhook').click(function() {
        $('.esp-input').hide()
        $('.webhook-input').show()
    })
</script>
@endsection