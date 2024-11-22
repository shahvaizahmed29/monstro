<x-mail::message>
Hello {{$firstName}} {{$lastName}}

<x-mail::panel>
  You are registered to a program.
  Program Name: {{$programName}}
  Email: {{$email}}
  Password: {{$password}}
  Referral Code: {{$referral}}

  This is a temporary password please make sure to update it as soon as possible.
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
