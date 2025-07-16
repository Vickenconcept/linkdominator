<x-mail::message>
Hello,

<p class="mt-2">
    <strong>{{$sender}}</strong> has invited you to come collaborate with them on <strong>{{config('app.name')}}</strong>. 
    Use the button below to setup your account and get started.
</p>

<x-mail::button :url="$url">
Accept Invite
</x-mail::button>

<p style="margin-top: 60px;">
    By joining, your name and email address will be visible to other members of the team. Only join team you recognize.
</p>

<p style="margin-top: 60px;">
Welcome aboard,<br>
{{config('app.name')}} Team
</p>

<hr>
<p style="margin-top: 60px;">
    If you're having trouble with the button above, copy and paste the URL below into your web browser
</p>
<a href="{{$url}}" target="_blank" style="margin-top: 60px;">
    {{$url}}
</a>
</x-mail::message>