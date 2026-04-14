<x-mail::message>
# Check-in Settimanale

Ciao **{{ $client->user->name }}**,

è il momento di compilare il tuo check-in settimanale! Registra peso, misure e le tue osservazioni per permettere a **{{ $nutritionist->full_name }}** di seguire i tuoi progressi.

<x-mail::button :url="config('app.url') . '/client/check-ins/create'">
Compila il Check-in
</x-mail::button>

Il check-in regolare è fondamentale per monitorare i progressi e adattare il tuo piano nutrizionale.

Grazie,<br>
{{ config('app.name') }}
</x-mail::message>
