<x-mail::message>
Hello {{$firstName}} {{$lastName}}

<x-mail::panel>
  You are registered as a staff member on our platform monstro.
  Email: {{$email}}
  Password: {{$password}}

  This is a temporary password please make sure to update it as soon as possible.
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
