<x-mail::message>
# Il tuo Piano Nutrizionale è pronto!

Ciao **{{ $plan->client->user->name }}**,

il tuo nutrizionista **{{ $plan->client->nutritionist->full_name }}** ha preparato un nuovo piano nutrizionale per te:

- **Piano:** {{ $plan->title }}
@if($plan->start_date)
- **Inizio:** {{ $plan->start_date->translatedFormat('d F Y') }}
@endif
@if($plan->end_date)
- **Fine:** {{ $plan->end_date->translatedFormat('d F Y') }}
@endif

<x-mail::button :url="config('app.url') . '/client/plans/' . $plan->id">
Visualizza il Piano
</x-mail::button>

Consulta il piano nella tua area riservata per vedere i dettagli dei pasti e le indicazioni.

Grazie,<br>
{{ config('app.name') }}
</x-mail::message>
