<x-mail::message>
# You've Been Invited to Join {{ config('app.name') }} Family Group! 🎉

Hello,  

<x-mail::panel>
You have been invited by **{{ $senderName }} ({{ $senderEmail }})** to join their family group as **{{ $relationshipName }}**.  

Being part of this group allows you to stay connected, share benefits, and enjoy a seamless experience together.  

To accept this invitation and complete your enrollment, please click the link below:  

[Join Now]({{ $url }})  
</x-mail::panel>

If you did not request this invitation, please ignore this email.  

We look forward to welcoming you to the family!  

Best regards,  
**{{ config('app.name') }} Team**  
</x-mail::message>
