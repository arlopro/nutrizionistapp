<x-mail::message>
# Errore 500 — {{ $errorClass }}

**Data/Ora:** {{ $occurredAt }}
**URL:** `{{ $method }} {{ $url }}`
**User ID:** {{ $userId ?? 'non autenticato' }}
**IP:** {{ $ip }}

---

**Messaggio:** {{ $errorMessage }}

<x-mail::panel>
```
{!! $stackTrace !!}
```
</x-mail::panel>

</x-mail::message>
