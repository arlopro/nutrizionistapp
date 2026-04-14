<x-mail::message>
# Promemoria Appuntamento

Ciao **{{ $appointment->client?->user?->name ?? 'Paziente' }}**,

ti ricordiamo che hai un appuntamento fissato:

- **Data:** {{ $appointment->starts_at->translatedFormat('l d F Y') }}
- **Orario:** {{ $appointment->starts_at->format('H:i') }} – {{ $appointment->ends_at->format('H:i') }}
@if($appointment->location)
- **Luogo:** {{ $appointment->location }}
@endif
- **Nutrizionista:** {{ $appointment->nutritionist->full_name }}

@if($appointment->notes)
**Note:** {{ $appointment->notes }}
@endif

<x-mail::button :url="config('app.url')">
Vai all'area riservata
</x-mail::button>

Se hai bisogno di spostare o annullare l'appuntamento, contatta il tuo nutrizionista il prima possibile.

Grazie,<br>
{{ config('app.name') }}
</x-mail::message>
