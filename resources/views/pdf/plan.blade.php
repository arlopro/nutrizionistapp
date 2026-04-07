<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $plan->title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            background: #ffffff;
        }

        /* HEADER */
        .header {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            padding: 28px 32px;
            color: white;
        }
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-logo img {
            height: 52px;
            width: auto;
        }
        .header-logo-placeholder {
            width: 52px;
            height: 52px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
        }
        .header-brand h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header-brand p {
            font-size: 11px;
            opacity: 0.85;
            margin-top: 2px;
        }
        .header-info {
            text-align: right;
        }
        .header-info p {
            font-size: 10px;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* PLAN TITLE SECTION */
        .plan-section {
            padding: 22px 32px 18px;
            border-bottom: 2px solid #f0fdf4;
            background: #f9fafb;
        }
        .plan-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .plan-meta {
            display: flex;
            gap: 20px;
            margin-top: 8px;
            flex-wrap: wrap;
        }
        .plan-meta-item {
            font-size: 9px;
            color: #6b7280;
        }
        .plan-meta-item span {
            font-weight: 600;
            color: #374151;
        }

        /* MACRO TARGETS */
        .macro-bar {
            display: flex;
            gap: 0;
            margin: 16px 32px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .macro-item {
            flex: 1;
            padding: 10px 14px;
            text-align: center;
            background: #ffffff;
        }
        .macro-item + .macro-item {
            border-left: 1px solid #e5e7eb;
        }
        .macro-item .macro-value {
            font-size: 16px;
            font-weight: 700;
            color: #16a34a;
        }
        .macro-item .macro-label {
            font-size: 8px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .macro-item.kcal .macro-value { color: #f59e0b; }
        .macro-item.protein .macro-value { color: #3b82f6; }
        .macro-item.carbs .macro-value { color: #f97316; }
        .macro-item.fat .macro-value { color: #8b5cf6; }

        /* DESCRIPTION */
        .description-box {
            margin: 0 32px 16px;
            padding: 12px 16px;
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            border-radius: 0 6px 6px 0;
            font-size: 9.5px;
            color: #78350f;
            line-height: 1.5;
        }

        /* DAYS */
        .content {
            padding: 0 32px 32px;
        }

        .day-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .day-header {
            background: #16a34a;
            color: white;
            padding: 7px 14px;
            border-radius: 7px 7px 0 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .day-body {
            border: 1px solid #d1fae5;
            border-top: none;
            border-radius: 0 0 7px 7px;
            overflow: hidden;
        }

        /* MEAL */
        .meal-block {
            border-bottom: 1px solid #f0fdf4;
        }
        .meal-block:last-child {
            border-bottom: none;
        }
        .meal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px 6px;
            background: #f0fdf4;
        }
        .meal-name {
            font-size: 10px;
            font-weight: 700;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .meal-macros {
            font-size: 8.5px;
            color: #6b7280;
        }
        .meal-macros span {
            margin-left: 8px;
        }
        .meal-macros .kcal { color: #f59e0b; font-weight: 600; }
        .meal-macros .prot { color: #3b82f6; }
        .meal-macros .carb { color: #f97316; }
        .meal-macros .fat-c { color: #8b5cf6; }

        /* Free text meal */
        .meal-free-text {
            padding: 8px 14px;
            font-size: 9.5px;
            color: #374151;
            line-height: 1.6;
            white-space: pre-line;
        }

        /* Food items */
        .meal-items {
            padding: 6px 14px 8px;
        }
        .food-item {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 3px 0;
            border-bottom: 1px dotted #e5e7eb;
        }
        .food-item:last-child { border-bottom: none; }
        .food-name {
            font-size: 9.5px;
            color: #374151;
        }
        .food-qty {
            font-size: 8.5px;
            color: #9ca3af;
            margin-left: 6px;
        }
        .food-macros {
            font-size: 8px;
            color: #9ca3af;
            white-space: nowrap;
        }

        /* Meal notes */
        .meal-notes {
            padding: 4px 14px 8px;
            font-size: 8.5px;
            color: #6b7280;
            font-style: italic;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 32px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }
        .footer-left {
            font-size: 8px;
            color: #9ca3af;
        }
        .footer-right {
            font-size: 8px;
            color: #9ca3af;
        }

        /* PAGE BREAK */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="header-inner">
            <div style="display:flex; align-items:center; gap:14px;">
                @if($logoBase64)
                    <div class="header-logo">
                        <img src="{{ $logoBase64 }}" alt="Logo" style="height:52px; width:auto; border-radius:6px;">
                    </div>
                @endif
                <div class="header-brand">
                    <h1>{{ $nutritionist->name }}</h1>
                    @if($profile?->specialization)
                        <p>{{ $profile->specialization }}</p>
                    @endif
                    @if($profile?->city)
                        <p>{{ $profile->city }}</p>
                    @endif
                </div>
            </div>
            <div class="header-info">
                @if($profile?->phone)
                    <p>Tel: {{ $profile->phone }}</p>
                @endif
                @if($profile?->website)
                    <p>{{ $profile->website }}</p>
                @endif
                @if($profile?->instagram)
                    <p>@{{ $profile->instagram }}</p>
                @endif
                <p>{{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    <!-- PLAN INFO -->
    <div class="plan-section">
        <div class="plan-title">{{ $plan->title }}</div>
        <div style="font-size:9px; color:#6b7280; margin-top:2px;">
            Piano nutrizionale per <strong>{{ $plan->client->user->name }}</strong>
        </div>
        <div class="plan-meta">
            @if($plan->start_date)
                <div class="plan-meta-item">Dal <span>{{ \Carbon\Carbon::parse($plan->start_date)->format('d/m/Y') }}</span></div>
            @endif
            @if($plan->end_date)
                <div class="plan-meta-item">Al <span>{{ \Carbon\Carbon::parse($plan->end_date)->format('d/m/Y') }}</span></div>
            @endif
            <div class="plan-meta-item">Stato: <span>{{ $plan->status instanceof \BackedEnum ? $plan->status->label() : ucfirst($plan->status) }}</span></div>
        </div>
    </div>

    @if($plan->daily_calories || $plan->protein_grams || $plan->carbs_grams || $plan->fat_grams)
    <!-- MACRO TARGETS -->
    <div class="macro-bar">
        @if($plan->daily_calories)
            <div class="macro-item kcal">
                <div class="macro-value">{{ $plan->daily_calories }}</div>
                <div class="macro-label">kcal / giorno</div>
            </div>
        @endif
        @if($plan->protein_grams)
            <div class="macro-item protein">
                <div class="macro-value">{{ $plan->protein_grams }}g</div>
                <div class="macro-label">Proteine</div>
            </div>
        @endif
        @if($plan->carbs_grams)
            <div class="macro-item carbs">
                <div class="macro-value">{{ $plan->carbs_grams }}g</div>
                <div class="macro-label">Carboidrati</div>
            </div>
        @endif
        @if($plan->fat_grams)
            <div class="macro-item fat">
                <div class="macro-value">{{ $plan->fat_grams }}g</div>
                <div class="macro-label">Grassi</div>
            </div>
        @endif
    </div>
    @endif

    @if($plan->description)
    <div class="description-box">{{ $plan->description }}</div>
    @endif

    <!-- DAYS -->
    <div class="content">
        @php
            $dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
            $mealsByDay = $plan->meals->groupBy('day_of_week');
        @endphp

        @foreach($mealsByDay->sortKeys() as $day => $meals)
        <div class="day-block">
            <div class="day-header">{{ $dayNames[$day] ?? 'Giorno ' . ($day + 1) }}</div>
            <div class="day-body">
                @foreach($meals->sortBy('sort_order') as $meal)
                <div class="meal-block">
                    <div class="meal-header">
                        <div class="meal-name">{{ $meal->meal_type }}</div>
                        @if(!$meal->free_text && $meal->items->count())
                        @php
                            $mCal = $meal->items->sum(fn($i) => $i->food ? round($i->food->calories_per_100g * $i->quantity_grams / 100, 0) : 0);
                            $mProt = $meal->items->sum(fn($i) => $i->food ? round($i->food->protein_per_100g * $i->quantity_grams / 100, 1) : 0);
                            $mCarb = $meal->items->sum(fn($i) => $i->food ? round($i->food->carbs_per_100g * $i->quantity_grams / 100, 1) : 0);
                            $mFat = $meal->items->sum(fn($i) => $i->food ? round($i->food->fat_per_100g * $i->quantity_grams / 100, 1) : 0);
                        @endphp
                        <div class="meal-macros">
                            <span class="kcal">{{ $mCal }} kcal</span>
                            <span class="prot">P: {{ $mProt }}g</span>
                            <span class="carb">C: {{ $mCarb }}g</span>
                            <span class="fat-c">G: {{ $mFat }}g</span>
                        </div>
                        @endif
                    </div>

                    @if($meal->free_text)
                        <div class="meal-free-text">{{ $meal->free_text }}</div>
                    @else
                        <div class="meal-items">
                            @foreach($meal->items->sortBy('sort_order') as $item)
                            <div class="food-item">
                                <div>
                                    <span class="food-name">
                                        {{ $item->food?->name ?? $item->recipe?->name ?? 'Alimento' }}
                                    </span>
                                    <span class="food-qty">{{ $item->quantity_grams }}g</span>
                                </div>
                                @if($item->food)
                                <div class="food-macros">
                                    {{ round($item->food->calories_per_100g * $item->quantity_grams / 100) }} kcal
                                    &nbsp;|&nbsp;P {{ round($item->food->protein_per_100g * $item->quantity_grams / 100, 1) }}g
                                    &nbsp;C {{ round($item->food->carbs_per_100g * $item->quantity_grams / 100, 1) }}g
                                    &nbsp;G {{ round($item->food->fat_per_100g * $item->quantity_grams / 100, 1) }}g
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif

                    @if($meal->notes)
                        <div class="meal-notes">Nota: {{ $meal->notes }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        @if($plan->notes)
        <div style="margin-top: 20px; padding: 14px 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="font-size: 10px; font-weight: 700; color: #374151; margin-bottom: 6px;">Note generali</div>
            <div style="font-size: 9.5px; color: #6b7280; line-height: 1.6; white-space: pre-line;">{{ $plan->notes }}</div>
        </div>
        @endif
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-left">{{ $nutritionist->name }} &bull; Piano nutrizionale per {{ $plan->client->user->name }}</div>
        <div class="footer-right">Generato il {{ now()->format('d/m/Y') }}</div>
    </div>
</body>
</html>
