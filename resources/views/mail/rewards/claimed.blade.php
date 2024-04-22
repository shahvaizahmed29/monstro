<x-mail::message>
Hello

<x-mail::panel>
@if ($admin)
  A member has claimed/trade a reward.
  Member Id: $member_id
  Member Name: $member_name
  Reward Id: $reward_id
  Reward: $reward_name
@else
  You have successfully claimed/trade the reward. 
  You will be contacted by the admin please wait.
  Reward Id: $reward_id
  Reward: $reward_name
@endif


</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
