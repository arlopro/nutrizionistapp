<x-mail::message>
# Nuovo messaggio

**{{ $message->sender->full_name }}** ti ha inviato un messaggio:

<x-mail::panel>
{{ Str::limit($message->body, 300) }}
</x-mail::panel>

<x-mail::button :url="config('app.url')">
Vai ai messaggi
</x-mail::button>

Grazie,<br>
{{ config('app.name') }}
</x-mail::message>
