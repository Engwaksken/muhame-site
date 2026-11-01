<x-mail::message>
# New contact message

**From:** {{ $contactMessage->name }} ({{ $contactMessage->email }})
@if ($contactMessage->subject)

**Subject:** {{ $contactMessage->subject }}
@endif

<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

Reply to this email to answer {{ $contactMessage->name }} directly.

<x-mail::button :url="url('/admin/contact-messages')">
Open inbox
</x-mail::button>
</x-mail::message>
