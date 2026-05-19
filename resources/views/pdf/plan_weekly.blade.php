<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Riepilogo settimanale – {{ $plan->title }}</title>
<style>
@page { margin: 0; size: A4 landscape; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; background: #fff; line-height: 1.5; }

.pdf-footer {
    position: fixed; bottom: 0; left: 0; right: 0;
    height: 24px; padding: 0 36px;
    border-top: 1px solid #f0f0f0; background: #fff;
    font-size: 7px; color: #9ca3af;
}
.pdf-footer table { width: 100%; height: 24px; }
.pdf-footer td { vertical-align: middle; }
.pdf-footer td.r { text-align: right; }

.topbar { padding: 11px 36px; border-bottom: 1px solid #f0f0f0; }
.topbar table { width: 100%; }
.topbar td { vertical-align: middle; }
.topbar td.r { text-align: right; font-size: 8.5px; color: #6b7280; }
.app-name { font-size: 11px; font-weight: 700; color: #111827; }
.app-name-green { color: #16a34a; }

.content { padding: 16px 36px 32px; }
.eyebrow { font-size: 7.5px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #16a34a; margin-bottom: 3px; }
.section-heading { font-size: 18px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; line-height: 1.1; margin-bottom: 3px; }
.subtitle { font-size: 8px; color: #6b7280; margin-bottom: 10px; }

/* ── GRID TABLE ── */
.weekly-tbl {
    width: 100%;
    border-collapse: separate;
    border-spacing: 4px 0;   /* 4px gap between columns, 0 between rows */
    table-layout: fixed;
}

/* Header row — day names */
.weekly-tbl thead td {
    padding: 0;              /* zero padding: header div fills the cell */
    vertical-align: bottom;
    width: 14.28%;
}
.w-day-hdr {
    background: #0f172a;
    padding: 7px 6px;
    border-radius: 6px 6px 0 0;
    border: 1px solid #0f172a;   /* dark border = invisible on dark bg */
    border-bottom: none;
    text-align: center;
}
.w-day-abbr {
    font-size: 6.5px; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; color: #6b7280; margin-bottom: 1px;
}
.w-day-name { font-size: 11px; font-weight: 700; color: #fff; line-height: 1.2; }

/* Meal rows — left/right/bottom border per cell; top only on first row */
.weekly-tbl tbody td {
    vertical-align: top;
    padding: 7px 8px;
    border-left:   1px solid #e5e7eb;
    border-right:  1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    border-top: none;
    width: 14.28%;
}
/* First body row: add top border so it connects to the header card */
.weekly-tbl tbody tr:first-child td {
    border-top: 1px solid #e5e7eb;
}
/* Bottom-round the last row */
.weekly-tbl tbody tr:last-child td { border-radius: 0 0 5px 5px; }

/* Alternating row background */
.weekly-tbl tbody tr:nth-child(odd)  td { background: #ffffff; }
.weekly-tbl tbody tr:nth-child(even) td { background: #f9fafb; }

/* Meal label inside cell */
.w-meal-label {
    font-size: 6px; font-weight: 900; letter-spacing: 0.8px;
    text-transform: uppercase; color: #9ca3af; margin-bottom: 3px;
    border-bottom: 1px solid #f0f0f0; padding-bottom: 2px;
}
.w-meal-items {
    font-size: 8px; color: #374151; line-height: 1.5;
}
.w-empty { color: #d1d5db; font-size: 8px; font-style: italic; }
</style>
</head>
<body>

@php
$dayNames   = ['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica'];
$dayAbbr    = ['LUN','MAR','MER','GIO','VEN','SAB','DOM'];
$mealsByDay = $plan->meals->groupBy('day_of_week')->sortKeys();
$numDays    = $mealsByDay->count();
$sortedDays = $mealsByDay->keys();

// Collect all unique meal types in sort_order
$orderedTypes = $plan->meals
    ->sortBy('sort_order')
    ->map(fn($m) => $m->meal_type instanceof \BackedEnum ? $m->meal_type->value : $m->meal_type)
    ->unique()
    ->values();

// Build lookup: day_of_week → meal_type_value → meal object
$mealGrid = [];
foreach ($mealsByDay as $day => $meals) {
    foreach ($meals as $m) {
        $key = $m->meal_type instanceof \BackedEnum ? $m->meal_type->value : $m->meal_type;
        $mealGrid[$day][$key] = $m;
    }
}

// Helper: get display label from type value
$typeLabels = [];
foreach ($plan->meals as $m) {
    $key = $m->meal_type instanceof \BackedEnum ? $m->meal_type->value : $m->meal_type;
    $typeLabels[$key] = $m->meal_type instanceof \BackedEnum ? $m->meal_type->label() : $m->meal_type;
}
@endphp

<div class="pdf-footer">
    <table><tr>
        <td>Vista settimanale &middot; {{ $plan->client->user->name }}</td>
        <td class="r">Orientamento orizzontale</td>
    </tr></table>
</div>

<div class="topbar">
    <table><tr>
        <td>
            @if($appLogoBase64)
                <img src="{{ $appLogoBase64 }}" alt="NutrizionistApp" style="height:20px; width:auto; vertical-align:middle;">
            @else
                <span class="app-name">Nutrizionist<span class="app-name-green">App</span></span>
            @endif
        </td>
        <td class="r">{{ $plan->client->user->name }} &middot; v1.{{ $plan->id }}</td>
    </tr></table>
</div>

<div class="content">
    <div class="eyebrow">Riepilogo</div>
    <div class="section-heading">La tua settimana a colpo d&rsquo;occhio</div>
    <p class="subtitle">Tutti i {{ $numDays }} giorni con i pasti principali per ogni giornata.</p>

    <table class="weekly-tbl">

        {{-- ── HEADER ROW: day names ── --}}
        <thead>
            <tr>
                @foreach($sortedDays as $day)
                <td>
                    <div class="w-day-hdr">
                        <div class="w-day-abbr">{{ $dayAbbr[$day] ?? 'G'.($day+1) }}</div>
                        <div class="w-day-name">{{ $dayNames[$day] ?? 'Giorno '.($day+1) }}</div>
                    </div>
                </td>
                @endforeach
            </tr>
        </thead>

        {{-- ── BODY: one row per meal type ── --}}
        <tbody>
            @foreach($orderedTypes as $typeKey)
            <tr>
                @foreach($sortedDays as $day)
                <td>
                    <div class="w-meal-label">{{ $typeLabels[$typeKey] ?? $typeKey }}</div>
                    @if(isset($mealGrid[$day][$typeKey]))
                        @php
                            $wMeal = $mealGrid[$day][$typeKey];
                            if ($wMeal->free_text) {
                                $wItems = \Illuminate\Support\Str::limit($wMeal->free_text, 90);
                            } else {
                                $wItems = $wMeal->items
                                    ->map(fn($wi) => $wi->food?->name ?? $wi->recipe?->name)
                                    ->filter()
                                    ->take(6)
                                    ->implode(', ');
                                if ($wMeal->items->count() > 6) $wItems .= '…';
                            }
                        @endphp
                        @if($wItems)
                            <div class="w-meal-items">{{ $wItems }}</div>
                        @else
                            <div class="w-empty">—</div>
                        @endif
                    @else
                        <div class="w-empty">—</div>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>

    </table>
</div>

</body>
</html>
