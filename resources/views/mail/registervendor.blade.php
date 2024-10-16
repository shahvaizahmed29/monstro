<x-mail::message>
Hello {{$vendor->firstName}} {{$vendor->lastName}}

<x-mail::panel>
  Your account has been created as a vendor.
  You can now login and update your information.
  Please login using the below link and credentials and complete the signup process.

  <h2>Credentials</h2>
  <p>Email: {{$user->email}}</p>
  <p>password: {{$password}}</p>

  <h2>Location Details</h2>
  <p>Location name: {{$location->name}}</p>
  <p>Location email: {{$location->email}}</p>
  <a href="/login">Click here</a>
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
