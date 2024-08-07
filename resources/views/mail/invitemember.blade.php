<x-mail::message>
Hello

<x-mail::panel>
  You are invite to a program. Please click on the link below to get yourself enrolled.
  <a href="{{$url}}">Click here</a>
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
