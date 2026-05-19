<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $plan->title }}</title>
<style>
@page          { margin: 0; size: A4 portrait; }
@page landscape { margin: 0; size: A4 landscape; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; background: #fff; line-height: 1.5; }

/* ── FIXED FOOTER ── */
.pdf-footer {
    position: fixed; bottom: 0; left: 0; right: 0;
    height: 26px; padding: 0 40px;
    border-top: 1px solid #f0f0f0; background: #fff;
    font-size: 7.5px; color: #9ca3af;
}
.pdf-footer table { width: 100%; height: 26px; }
.pdf-footer td { vertical-align: middle; }
.pdf-footer td.r { text-align: right; }
.pgn:after { content: "Pagina " counter(page); }

/* ── TOPBAR COVER ── */
.topbar { padding: 14px 40px; border-bottom: 1px solid #f0f0f0; }
.topbar table { width: 100%; }
.topbar td { vertical-align: middle; }
.topbar td.r { text-align: right; }
.app-icon {
    display: inline-block; width: 28px; height: 28px;
    background: #111827; border-radius: 7px;
    color: #fff; font-size: 14px; font-weight: 900;
    text-align: center; line-height: 28px; vertical-align: middle;
}
.app-name { display: inline-block; vertical-align: middle; margin-left: 8px; font-size: 12px; font-weight: 700; color: #111827; }
.app-name-green { color: #16a34a; }
.topbar-right-cover { font-size: 8px; color: #6b7280; line-height: 1.6; }
.topbar-right-cover strong { color: #111827; font-weight: 700; }
.topbar-right-page { font-size: 9px; color: #6b7280; }

/* ── COVER HERO ── */
.cover-hero { background: #f0fdf4; padding: 32px 40px 28px; }
.plan-label { font-size: 8px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #16a34a; margin-bottom: 10px; }
.client-name { font-size: 38px; font-weight: 900; color: #0f172a; line-height: 1.0; letter-spacing: -1.5px; margin-bottom: 16px; }
.badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 8px; font-weight: 600; margin-right: 5px; }
.badge-green { background: #dcfce7; color: #15803d; }
.badge-grey  { background: #f3f4f6; color: #374151; }

/* ── COVER OBJECTIVE ── */
.cover-obj { padding: 22px 40px 0; }
.obj-label { font-size: 8px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #16a34a; margin-bottom: 6px; }
.obj-text { font-size: 13px; color: #111827; line-height: 1.5; max-width: 460px; }

/* ── COVER STATS ── */
.cover-stats { padding: 22px 40px 0; border-top: 1px solid #e5e7eb; margin-top: 30px; }
.cover-stats table { width: 100%; }
.cover-stats td { vertical-align: top; padding-right: 24px; border-right: 1px solid #e5e7eb; }
.cover-stats td:last-child { border-right: none; padding-right: 0; }
.stat-lbl { font-size: 7px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px; }
.stat-val { font-size: 22px; font-weight: 900; line-height: 1; }
.stat-unit { font-size: 11px; font-weight: 400; color: #9ca3af; }
.stat-sub { font-size: 7.5px; color: #9ca3af; margin-top: 2px; }
.c-kcal { color: #f97316; }
.c-prot { color: #e11d48; }
.c-carb { color: #16a34a; }
.c-fat  { color: #7c3aed; }

/* ── COVER ATTRIBUTION ── */
.cover-attr { padding: 16px 40px 20px; border-top: 1px solid #f0f0f0; margin-top: 22px; }
.attr-lbl { font-size: 7.5px; color: #9ca3af; margin-bottom: 3px; }
.attr-name { font-size: 12px; font-weight: 700; color: #111827; }
.attr-role { font-size: 8.5px; color: #6b7280; margin-top: 1px; }

/* ── RUNNING TOPBAR ── */
.running-header { padding: 10px 40px; border-bottom: 1px solid #f0f0f0; }
.running-header table { width: 100%; }
.running-header td { vertical-align: middle; }
.running-header td.r { text-align: right; }

/* ── PAGE CONTENT ── */
.page-content { padding: 22px 40px 36px; }

/* ── SECTION EYEBROW ── */
.eyebrow { font-size: 8px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #16a34a; margin-bottom: 4px; }
.section-heading { font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; line-height: 1.1; margin-bottom: 16px; }

/* ── SINTESI: MACRO BOX ── */
.macro-section-title { font-size: 11px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.macro-section-icon { display: inline-block; width: 18px; height: 18px; background: #dcfce7; border-radius: 4px; text-align: center; line-height: 18px; font-size: 10px; margin-right: 5px; vertical-align: middle; }
.macro-outer { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
.macro-inner-table { width: 100%; border-collapse: collapse; }
.macro-inner-table td { vertical-align: top; border-right: 1px solid #e5e7eb; }
.macro-inner-table td:last-child { border-right: none; }
.macro-main-cell { background: #0f172a; padding: 12px 16px; width: 120px; }
.macro-main-lbl { font-size: 7px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #4b5563; margin-bottom: 4px; }
.macro-main-val { font-size: 22px; font-weight: 900; color: #f97316; line-height: 1; }
.macro-main-unit { font-size: 11px; font-weight: 400; color: #9ca3af; }
.macro-main-sub { font-size: 7.5px; color: #6b7280; margin-top: 5px; }
.macro-side-cell { padding: 10px 14px; background: #fff; }
.macro-side-val { font-size: 20px; font-weight: 900; line-height: 1; }
.macro-side-lbl { font-size: 7.5px; color: #9ca3af; margin-top: 2px; }
.macro-bar { height: 3px; border-radius: 2px; margin-top: 6px; }

/* ── SINTESI: DAILY CALORIES ── */
.daily-section { margin-bottom: 20px; }
.daily-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
.daily-table td { text-align: center; padding: 0 4px; border-right: 1px solid #f0f0f0; }
.daily-table td:last-child { border-right: none; }
.daily-day { font-size: 7px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px; }
.daily-kcal { font-size: 14px; font-weight: 900; color: #0f172a; line-height: 1; }
.daily-unit { font-size: 7px; color: #9ca3af; }
.daily-avg { font-size: 7.5px; color: #9ca3af; margin-top: 6px; padding-top: 6px; border-top: 1px solid #f0f0f0; }

/* ── DAY PAGE ── */
.day-header-table { width: 100%; margin-bottom: 14px; }
.day-header-table td { vertical-align: middle; }
.day-header-table td.r { text-align: right; }
.day-letter-box {
    display: inline-block; width: 34px; height: 34px;
    background: #111827; border-radius: 8px;
    color: #fff; font-size: 16px; font-weight: 900;
    text-align: center; line-height: 34px; vertical-align: middle;
}
.day-name-txt { font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.8px; line-height: 1; display: inline-block; vertical-align: middle; margin-left: 10px; }
.day-meta-txt { font-size: 8.5px; color: #9ca3af; margin-top: 3px; }
.day-total-kcal { font-size: 24px; font-weight: 900; color: #0f172a; line-height: 1; }
.day-total-unit { font-size: 11px; font-weight: 400; color: #9ca3af; }
.day-macros-line { margin-top: 3px; font-size: 8.5px; }
.day-macros-line .m { font-weight: 700; margin-left: 6px; }
.hr-line { height: 1px; background: #e5e7eb; margin-bottom: 12px; }

/* ── MEAL BLOCK ── */
.meal-block { margin-bottom: 14px; page-break-inside: avoid; }
.meal-header-bar { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px 5px 0 0; padding: 6px 10px; }
.meal-header-table { width: 100%; border-collapse: collapse; }
.meal-header-table td { vertical-align: middle; }
.meal-header-table td.r { text-align: right; }
.meal-type-name { font-size: 8px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #374151; }
.meal-macro-pill { display: inline-block; font-size: 8px; font-weight: 700; margin-left: 7px; }

.food-tbl { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 5px 5px; }
.food-tbl thead tr { background: #fff; }
.food-tbl th { font-size: 7px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #9ca3af; padding: 5px 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.food-tbl th.r, .food-tbl td.r { text-align: right; }
.food-tbl td { font-size: 9px; color: #374151; padding: 5px 8px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
.food-tbl tbody tr:last-child td { border-bottom: none; }
.food-name-cell { font-size: 9.5px; color: #111827; font-weight: 500; }
.food-qty-cell { color: #9ca3af; font-size: 8.5px; }
.food-num { color: #6b7280; }
.meal-prep-note { background: #f0fdf4; border-left: 3px solid #16a34a; padding: 5px 10px; font-size: 8px; color: #15803d; font-style: italic; border-radius: 0 0 4px 0; border: 1px solid #dcfce7; border-top: none; border-left: 3px solid #16a34a; }

/* ── SUPPLEMENTS TABLE ── */
.suppl-tbl { width: 100%; border-collapse: collapse; margin-top: 12px; }
.suppl-tbl th { font-size: 7px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #9ca3af; padding: 5px 0; border-bottom: 1px solid #e5e7eb; text-align: left; }
.suppl-tbl td { font-size: 9.5px; padding: 8px 0; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
.suppl-tbl tbody tr:last-child td { border-bottom: none; }
.suppl-name { font-weight: 700; color: #111827; }
.suppl-dose { color: #0d9488; font-weight: 700; font-size: 9px; }
.suppl-meta { color: #9ca3af; font-size: 8.5px; }
.suppl-col { padding-right: 16px; }

/* ── NOTES PAGE ── */
.quote-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px 18px; margin-bottom: 24px; }
.quote-mark { font-size: 28px; font-weight: 900; color: #86efac; line-height: 1; margin-bottom: 5px; }
.quote-text { font-size: 10px; color: #111827; line-height: 1.7; }
.subheading { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 10px; margin-top: 24px; }
.contact-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; }
.contact-card table { width: 100%; }
.contact-card td { vertical-align: middle; }
.contact-initials {
    display: inline-block; width: 36px; height: 36px;
    background: #dcfce7; color: #15803d;
    border-radius: 50%; font-size: 13px; font-weight: 700;
    text-align: center; line-height: 36px; vertical-align: middle;
}
.contact-name { font-size: 11px; font-weight: 700; color: #111827; }
.contact-role { font-size: 8.5px; color: #6b7280; margin-top: 2px; }
.contact-info { font-size: 8.5px; color: #6b7280; margin-top: 3px; }

/* ── GENERAL NOTES / PRINCIPI ── */
.notes-section { margin-top: 20px; }
.notes-item-row { display: table; width: 100%; padding: 7px 0; border-bottom: 1px solid #f9fafb; }
.notes-item-num { display: table-cell; width: 22px; vertical-align: top; }
.notes-num-badge { display: inline-block; width: 18px; height: 18px; background: #dcfce7; color: #15803d; border-radius: 50%; font-size: 8px; font-weight: 700; text-align: center; line-height: 18px; }
.notes-item-text { display: table-cell; font-size: 9.5px; color: #374151; line-height: 1.5; vertical-align: top; padding-top: 1px; }

/* ── WEEKLY RECAP (landscape) ── */
.weekly-page { page: landscape; page-break-before: always; }
.weekly-recap-content { padding: 22px 40px 36px; }
.weekly-tbl { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin-top: 12px; }
.weekly-tbl td { vertical-align: top; width: 14.28%; }
.w-day-hdr { background: #0f172a; padding: 8px 6px 8px; border-radius: 6px 6px 0 0; text-align: center; }
.w-day-abbr { font-size: 7px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; margin-bottom: 2px; }
.w-day-kcal { font-size: 12px; font-weight: 700; color: #fff; line-height: 1.2; }
.w-day-unit { font-size: 7.5px; color: #6b7280; }
.w-bar-row { height: 4px; }
.w-bar-row table { width: 100%; border-collapse: collapse; height: 4px; }
.w-bar-row td { height: 4px; padding: 0; }
.w-bar-prot { background: #e11d48; }
.w-bar-carb { background: #16a34a; }
.w-bar-fat  { background: #7c3aed; }
.w-macros-cell { background: #f9fafb; padding: 4px 6px; border: 1px solid #f0f0f0; border-top: none; }
.w-macro-txt { font-size: 7.5px; font-weight: 700; }
.w-meals-cell { padding: 6px 6px 8px; border: 1px solid #f0f0f0; border-top: none; border-radius: 0 0 6px 6px; min-height: 80px; }
.w-meal-block { margin-bottom: 6px; }
.w-meal-type { font-size: 6.5px; font-weight: 900; letter-spacing: 0.8px; text-transform: uppercase; color: #9ca3af; }
.w-meal-kcal { font-size: 9px; font-weight: 700; color: #111827; }
.w-meal-items { font-size: 7px; color: #6b7280; line-height: 1.4; margin-top: 1px; }
.weekly-avg-table { width: 100%; border-collapse: collapse; margin-top: 16px; border-top: 2px solid #e5e7eb; padding-top: 10px; }
.weekly-avg-table td { vertical-align: top; padding: 10px 20px 0 0; border-right: 1px solid #e5e7eb; }
.weekly-avg-table td:first-child { padding-left: 0; }
.weekly-avg-table td:last-child { border-right: none; }
.wav-lbl { font-size: 8px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 2px; }
.wav-range { font-size: 8px; color: #9ca3af; margin-bottom: 2px; }
.wav-val { font-size: 22px; font-weight: 900; line-height: 1; }
.wav-unit { font-size: 11px; font-weight: 400; color: #9ca3af; }
.legend-table { border-collapse: collapse; margin-bottom: 8px; }
.legend-table td { padding: 0 12px 0 0; vertical-align: middle; font-size: 7.5px; color: #6b7280; }
.legend-dot { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 4px; vertical-align: middle; }

/* ── UTILS ── */
.page-break { page-break-after: always; }
.mt8 { margin-top: 8px; }
.mt14 { margin-top: 14px; }
.mt20 { margin-top: 20px; }
</style>
</head>
<body>

@php
// ── DATA PREP ──
$dayNames = ['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica'];
$dayAbbr  = ['LUN','MAR','MER','GIO','VEN','SAB','DOM'];
$mealsByDay = $plan->meals->groupBy('day_of_week')->sortKeys();
$numDays  = $mealsByDay->count();
$initials = collect(explode(' ', $nutritionist->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
$docId    = 'NTR-' . now()->format('Y') . '-' . str_pad($plan->id, 4, '0', STR_PAD_LEFT);

// Per-day macros
$dayMacros = [];
foreach ($mealsByDay as $day => $meals) {
    $kcal=$prot=$carb=$fat=0;
    foreach ($meals as $m) {
        if (!$m->free_text) {
            foreach ($m->items as $i) {
                if ($i->food && $i->quantity_grams) {
                    $kcal += $i->food->calories_per_100g * $i->quantity_grams / 100;
                    $prot += $i->food->protein_per_100g * $i->quantity_grams / 100;
                    $carb += $i->food->carbs_per_100g * $i->quantity_grams / 100;
                    $fat  += $i->food->fat_per_100g  * $i->quantity_grams / 100;
                } elseif ($i->recipe) {
                    $kcal += $i->recipe->total_calories;
                    $prot += $i->recipe->total_protein;
                    $carb += $i->recipe->total_carbs;
                    $fat  += $i->recipe->total_fat;
                }
            }
        }
    }
    $dayMacros[$day] = ['kcal'=>$kcal,'prot'=>$prot,'carb'=>$carb,'fat'=>$fat];
}

$avgKcal = $numDays ? array_sum(array_column($dayMacros,'kcal'))/$numDays : 0;
$avgProt = $numDays ? array_sum(array_column($dayMacros,'prot'))/$numDays : 0;
$avgCarb = $numDays ? array_sum(array_column($dayMacros,'carb'))/$numDays : 0;
$avgFat  = $numDays ? array_sum(array_column($dayMacros,'fat'))/$numDays : 0;
$minKcal = $dayMacros ? min(array_column($dayMacros,'kcal')) : 0;
$maxKcal = $dayMacros ? max(array_column($dayMacros,'kcal')) : 0;

// Section numbers — note/contatti always present
$secSintesi     = 1;
$secGiorni      = 2;
$secSuppl       = $plan->supplements->count() ? 3 : null;
$secNote        = $secSuppl ? 4 : 3;   // always shown
$secRiepilogo   = $secNote + 1;

// Display macros (prefer calculated from items, fallback to plan targets)
$dispKcal = $plan->daily_calories ?: ($avgKcal > 0 ? round($avgKcal) : null);
$dispProt = $plan->protein_grams  ?: ($avgProt > 0 ? round($avgProt,1) : null);
$dispCarb = $plan->carbs_grams    ?: ($avgCarb > 0 ? round($avgCarb,1) : null);
$dispFat  = $plan->fat_grams      ?: ($avgFat  > 0 ? round($avgFat,1)  : null);
@endphp

{{-- ═══════════════════════════════════════════════════════════
     FIXED FOOTER (renders on every page)
═══════════════════════════════════════════════════════════ --}}
<div class="pdf-footer">
    <table><tr>
        <td>NutrizionistApp &middot; Piano &ldquo;{{ $plan->title }}&rdquo;</td>
        <td class="r"><span class="pgn"></span></td>
    </tr></table>
</div>

{{-- ═══════════════════════════════════════════════════════════
     PAGE 1 · COVER
═══════════════════════════════════════════════════════════ --}}

{{-- TOP BAR COVER --}}
<div class="topbar">
    <table><tr>
        <td>
            @if($appLogoBase64)
                <img src="{{ $appLogoBase64 }}" alt="NutrizionistApp" style="height:26px; width:auto; vertical-align:middle;">
            @else
                <span class="app-name">Nutrizionist<span class="app-name-green">App</span></span>
            @endif
        </td>
        <td class="r">
            <div class="topbar-right-cover">
                <strong>v1.{{ $plan->id }} &middot; emesso: {{ now()->translatedFormat('j F Y') }}</strong><br>
                Documento ID: {{ $docId }}
            </div>
        </td>
    </tr></table>
</div>

{{-- COVER HERO --}}
<div class="cover-hero">
    <div class="plan-label">Piano alimentare personalizzato</div>
    <div class="client-name">{{ $plan->client->user->name }}</div>
    <div>
        <span class="badge badge-green">{{ $plan->title }}</span>
        @if($plan->start_date)
            <span class="badge badge-grey">
                {{ \Carbon\Carbon::parse($plan->start_date)->format('d/m/Y') }}
                @if($plan->end_date) &ndash; {{ \Carbon\Carbon::parse($plan->end_date)->format('d/m/Y') }} @endif
            </span>
        @endif
        @if($numDays)
            <span class="badge badge-grey">{{ $numDays }} {{ $numDays == 1 ? 'giorno' : 'giorni' }} / settimana</span>
        @endif
    </div>
</div>

{{-- OBJECTIVE --}}
@if($plan->description)
<div class="cover-obj">
    <div class="obj-label">Obiettivo del piano</div>
    <div class="obj-text">{{ $plan->description }}</div>
</div>
@endif

{{-- STATS --}}
@if($dispKcal || $dispProt || $dispCarb || $dispFat)
<div class="cover-stats">
    <table><tr>
        @if($dispKcal)
        <td>
            <div class="stat-lbl">Calorie / giorno</div>
            <div class="stat-val c-kcal">{{ number_format($dispKcal) }}<span class="stat-unit"> kcal</span></div>
            <div class="stat-sub">media settimanale</div>
        </td>
        @endif
        @if($dispProt)
        <td>
            <div class="stat-lbl">Proteine</div>
            <div class="stat-val c-prot">{{ $dispProt }}<span class="stat-unit"> g</span></div>
            <div class="stat-sub">target giornaliero</div>
        </td>
        @endif
        @if($dispCarb)
        <td>
            <div class="stat-lbl">Carboidrati</div>
            <div class="stat-val c-carb">{{ $dispCarb }}<span class="stat-unit"> g</span></div>
            <div class="stat-sub">target giornaliero</div>
        </td>
        @endif
        @if($dispFat)
        <td>
            <div class="stat-lbl">Grassi</div>
            <div class="stat-val c-fat">{{ $dispFat }}<span class="stat-unit"> g</span></div>
            <div class="stat-sub">target giornaliero</div>
        </td>
        @endif
    </tr></table>
</div>
@endif

{{-- ATTRIBUTION --}}
<div class="cover-attr">
    <table style="width:100%; border-collapse:collapse;"><tr>
        <td style="vertical-align:middle;">
            <div class="attr-lbl">Predisposto da</div>
            <div class="attr-name">{{ $profile?->business_name ?: $nutritionist->name }}</div>
            <div class="attr-role">
                {{ $profile?->specialization ?? 'Nutrizionista' }}
                @if($profile?->license_number) &middot; Albo n.&nbsp;{{ $profile->license_number }} @endif
                @if($profile?->city) &middot; {{ $profile->city }} @endif
            </div>
        </td>
        @if($logoBase64)
        <td style="width:120px; text-align:right; vertical-align:middle;">
            <img src="{{ $logoBase64 }}" alt="Logo nutrizionista" style="max-height:48px; max-width:110px; width:auto;">
        </td>
        @endif
    </tr></table>
</div>

<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════════════
     PAGE 2 · IL TUO PIANO IN SINTESI
═══════════════════════════════════════════════════════════ --}}

<div class="running-header">
    <table><tr>
        <td>
            @if($appLogoBase64)
                <img src="{{ $appLogoBase64 }}" alt="NutrizionistApp" style="height:20px; width:auto; vertical-align:middle;">
            @else
                <span class="app-name" style="font-size:11px;">Nutrizionist<span class="app-name-green">App</span></span>
            @endif
        </td>
        <td class="r"><span class="topbar-right-page">{{ $plan->client->user->name }} &middot; v1.{{ $plan->id }}</span></td>
    </tr></table>
</div>

<div class="page-content">
    <div class="eyebrow">Sezione {{ $secSintesi }}</div>
    <div class="section-heading">Il tuo piano in sintesi</div>

    {{-- MACRONUTRIENTI BOX --}}
    <div class="mt8">
        <div class="macro-section-title">
            <span class="macro-section-icon">&#9889;</span>
            Macronutrienti (media settimanale)
        </div>
        <div class="macro-outer">
            <table class="macro-inner-table">
                <tr>
                    <td class="macro-main-cell">
                        <div class="macro-main-lbl">Calorie</div>
                        <div class="macro-main-val">{{ number_format($dispKcal ?? round($avgKcal)) }}<span class="macro-main-unit"> kcal</span></div>
                        <div class="macro-main-sub">target giornaliero medio</div>
                    </td>
                    @if($dispProt)
                    <td class="macro-side-cell">
                        <div class="macro-side-val c-prot">{{ $dispProt }}<span class="macro-side-lbl"> g</span></div>
                        <div class="macro-side-lbl">Proteine</div>
                        @if($dispKcal && $dispProt)
                            <div class="macro-bar" style="background:#e11d48; width:{{ min(100, round($dispProt*4/$dispKcal*100)) }}%;"></div>
                        @endif
                    </td>
                    @endif
                    @if($dispCarb)
                    <td class="macro-side-cell">
                        <div class="macro-side-val c-carb">{{ $dispCarb }}<span class="macro-side-lbl"> g</span></div>
                        <div class="macro-side-lbl">Carboidrati</div>
                        @if($dispKcal && $dispCarb)
                            <div class="macro-bar" style="background:#16a34a; width:{{ min(100, round($dispCarb*4/$dispKcal*100)) }}%;"></div>
                        @endif
                    </td>
                    @endif
                    @if($dispFat)
                    <td class="macro-side-cell">
                        <div class="macro-side-val c-fat">{{ $dispFat }}<span class="macro-side-lbl"> g</span></div>
                        <div class="macro-side-lbl">Grassi</div>
                        @if($dispKcal && $dispFat)
                            <div class="macro-bar" style="background:#7c3aed; width:{{ min(100, round($dispFat*9/$dispKcal*100)) }}%;"></div>
                        @endif
                    </td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    {{-- CALORIE PER GIORNO --}}
    @if($dayMacros)
    <div class="daily-section">
        <div class="macro-section-title">
            <span class="macro-section-icon">&#128197;</span>
            Calorie per giorno della settimana
        </div>
        <table class="daily-table">
            <tr>
                @foreach($mealsByDay->sortKeys() as $day => $meals)
                <td>
                    <div class="daily-day">{{ $dayAbbr[$day] ?? 'G'.($day+1) }}</div>
                    <div class="daily-kcal">{{ number_format(round($dayMacros[$day]['kcal'])) }}</div>
                    <div class="daily-unit">kcal</div>
                </td>
                @endforeach
            </tr>
        </table>
        @if($numDays)
        <div class="daily-avg">
            Media settimanale: <strong>{{ number_format(round($avgKcal)) }} kcal/giorno</strong>
            @if($minKcal != $maxKcal)
                &middot; Range {{ number_format(round($minKcal)) }}&ndash;{{ number_format(round($maxKcal)) }} kcal
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- PRINCIPI / NOTE SINTESI --}}
    @if($plan->notes)
    <div class="mt20">
        <div class="macro-section-title">
            <span class="macro-section-icon">&#9997;</span>
            Note generali
        </div>
        <div style="font-size:9.5px; color:#374151; line-height:1.7; white-space:pre-line; margin-top:6px;">{{ $plan->notes }}</div>
    </div>
    @endif
</div>

<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════════════
     SEZIONE 2 · GIORNI
═══════════════════════════════════════════════════════════ --}}

@php $dayCount = 0; @endphp
@foreach($mealsByDay->sortKeys() as $day => $meals)
@php
    $dayCount++;
    $dm = $dayMacros[$day];
    $firstLetter = strtoupper(substr($dayNames[$day] ?? 'G', 0, 1));
    $mealsSorted = $meals->sortBy('sort_order');
    $mealCount   = $mealsSorted->count();
@endphp

{{-- Running header --}}
<div class="running-header">
    <table><tr>
        <td>
            @if($appLogoBase64)
                <img src="{{ $appLogoBase64 }}" alt="NutrizionistApp" style="height:20px; width:auto; vertical-align:middle;">
            @else
                <span class="app-name" style="font-size:11px;">Nutrizionist<span class="app-name-green">App</span></span>
            @endif
        </td>
        <td class="r"><span class="topbar-right-page">{{ $plan->client->user->name }} &middot; v1.{{ $plan->id }}</span></td>
    </tr></table>
</div>

<div class="page-content">

    {{-- Day eyebrow --}}
    <div class="eyebrow">Sezione {{ $secGiorni }} &middot; Giorno {{ $dayCount }} di {{ $numDays }}</div>

    {{-- Day header --}}
    <table class="day-header-table">
        <tr>
            <td style="width:60%;">
                <span class="day-letter-box">{{ $firstLetter }}</span>
                <span class="day-name-txt">{{ $dayNames[$day] ?? 'Giorno '.($day+1) }}</span>
                <div class="day-meta-txt" style="margin-left:44px;">{{ $mealCount }} {{ $mealCount == 1 ? 'pasto' : 'pasti' }}</div>
            </td>
            <td class="r">
                @if($dm['kcal'] > 0)
                <div class="day-total-kcal">{{ number_format(round($dm['kcal'])) }}<span class="day-total-unit"> kcal totali</span></div>
                <div class="day-macros-line">
                    @if($dm['prot']>0)<span class="m c-prot">P {{ round($dm['prot'],1) }}g</span>@endif
                    @if($dm['carb']>0)<span class="m c-carb">C {{ round($dm['carb'],1) }}g</span>@endif
                    @if($dm['fat']>0)<span class="m c-fat">G {{ round($dm['fat'],1) }}g</span>@endif
                </div>
                @endif
            </td>
        </tr>
    </table>
    <div class="hr-line"></div>

    {{-- Meals --}}
    @foreach($mealsSorted as $meal)
    @php
        $mCal=$mProt=$mCarb=$mFat=0;
        if (!$meal->free_text && $meal->items->count()) {
            foreach ($meal->items as $i) {
                if ($i->food && $i->quantity_grams) {
                    $mCal  += $i->food->calories_per_100g * $i->quantity_grams / 100;
                    $mProt += $i->food->protein_per_100g * $i->quantity_grams / 100;
                    $mCarb += $i->food->carbs_per_100g * $i->quantity_grams / 100;
                    $mFat  += $i->food->fat_per_100g  * $i->quantity_grams / 100;
                } elseif ($i->recipe) {
                    $mCal  += $i->recipe->total_calories;
                    $mProt += $i->recipe->total_protein;
                    $mCarb += $i->recipe->total_carbs;
                    $mFat  += $i->recipe->total_fat;
                }
            }
        }
        $mealLabel = $meal->meal_type instanceof \BackedEnum ? $meal->meal_type->label() : $meal->meal_type;
    @endphp
    <div class="meal-block">
        {{-- Meal header --}}
        <div class="meal-header-bar">
            <table class="meal-header-table"><tr>
                <td><span class="meal-type-name">{{ $mealLabel }}</span></td>
                @if(!$meal->free_text && $meal->items->count())
                <td class="r" style="white-space:nowrap;">
                    <span class="meal-macro-pill" style="color:#f97316;">{{ round($mCal) }}kcal</span>
                    <span class="meal-macro-pill c-prot">P {{ round($mProt,1) }}g</span>
                    <span class="meal-macro-pill c-carb">C {{ round($mCarb,1) }}g</span>
                    <span class="meal-macro-pill c-fat">G {{ round($mFat,1) }}g</span>
                </td>
                @endif
            </tr></table>
        </div>

        @if($meal->free_text)
            <div style="padding:8px 10px; font-size:9.5px; color:#374151; line-height:1.6; white-space:pre-line; border:1px solid #e5e7eb; border-top:none; border-radius:0 0 5px 5px;">{{ $meal->free_text }}</div>
        @else
            <table class="food-tbl">
                <thead>
                    <tr>
                        <th style="width:38%;">Alimento</th>
                        <th style="width:12%;">Quantit&agrave;</th>
                        <th class="r" style="width:12%;">Kcal</th>
                        <th class="r" style="width:12%;">Prot</th>
                        <th class="r" style="width:12%;">Carb</th>
                        <th class="r" style="width:14%;">Grassi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meal->items->sortBy('sort_order') as $item)
                    <tr>
                        <td class="food-name-cell">{{ $item->food?->name ?? $item->recipe?->name ?? 'Alimento' }}</td>
                        <td class="food-qty-cell">
                            @if($item->food && $item->quantity_grams){{ $item->quantity_grams }}g
                            @else &mdash;
                            @endif
                        </td>
                        @if($item->food && $item->quantity_grams)
                        <td class="r food-num">{{ round($item->food->calories_per_100g * $item->quantity_grams / 100) }}</td>
                        <td class="r food-num">{{ round($item->food->protein_per_100g * $item->quantity_grams / 100, 1) }}g</td>
                        <td class="r food-num">{{ round($item->food->carbs_per_100g   * $item->quantity_grams / 100, 1) }}g</td>
                        <td class="r food-num">{{ round($item->food->fat_per_100g     * $item->quantity_grams / 100, 1) }}g</td>
                        @elseif($item->recipe)
                        <td class="r food-num">{{ round($item->recipe->total_calories) }}</td>
                        <td class="r food-num">{{ round($item->recipe->total_protein,1) }}g</td>
                        <td class="r food-num">{{ round($item->recipe->total_carbs,1) }}g</td>
                        <td class="r food-num">{{ round($item->recipe->total_fat,1) }}g</td>
                        @else
                        <td class="r food-num">&mdash;</td><td class="r food-num">&mdash;</td>
                        <td class="r food-num">&mdash;</td><td class="r food-num">&mdash;</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($meal->notes)
            <div class="meal-prep-note"><strong>Preparazione.</strong> {{ $meal->notes }}</div>
            @endif
        @endif
    </div>
    @endforeach

</div>

@if(!$loop->last)
<div class="page-break"></div>
@endif
@endforeach

{{-- ═══════════════════════════════════════════════════════════
     SUPPLEMENTI (se presenti)
═══════════════════════════════════════════════════════════ --}}
@if($plan->supplements->count())
<div class="page-break"></div>

<div class="running-header">
    <table><tr>
        <td>
            @if($appLogoBase64)
                <img src="{{ $appLogoBase64 }}" alt="NutrizionistApp" style="height:20px; width:auto; vertical-align:middle;">
            @else
                <span class="app-name" style="font-size:11px;">Nutrizionist<span class="app-name-green">App</span></span>
            @endif
        </td>
        <td class="r"><span class="topbar-right-page">{{ $plan->client->user->name }} &middot; v1.{{ $plan->id }}</span></td>
    </tr></table>
</div>

<div class="page-content">
    <div class="eyebrow">Sezione {{ $secSuppl }}</div>
    <div class="section-heading">Integrazione &amp; Supplementazione</div>
    <p style="font-size:9px; color:#6b7280; margin-bottom:4px;">Protocollo integrativo indicato dal nutrizionista.</p>

    <table class="suppl-tbl">
        <thead>
            <tr>
                <th class="suppl-col" style="width:30%;">Integratore</th>
                <th class="suppl-col" style="width:18%;">Dosaggio</th>
                <th class="suppl-col" style="width:26%;">Quando</th>
                <th style="width:26%;">Durata / Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plan->supplements->sortBy('sort_order') as $supp)
            <tr>
                <td class="suppl-col"><span class="suppl-name">{{ $supp->name }}</span></td>
                <td class="suppl-col">
                    @if($supp->dosage)
                        <span class="suppl-dose">{{ $supp->dosage }}{{ $supp->dosage_unit ? ' '.$supp->dosage_unit : '' }}</span>
                    @else <span class="suppl-meta">&mdash;</span> @endif
                </td>
                <td class="suppl-col"><span class="suppl-meta">{{ $supp->timing ?: '—' }}</span></td>
                <td>
                    <span class="suppl-meta">{{ $supp->duration ?? '' }}</span>
                    @if($supp->notes)<div style="font-size:8px; color:#9ca3af; font-style:italic; margin-top:2px;">{{ $supp->notes }}</div>@endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════
     NOTE DEL NUTRIZIONISTA (se presenti) — senza sezione firma
═══════════════════════════════════════════════════════════ --}}
<div class="page-break"></div>

<div class="running-header">
    <table><tr>
        <td>
            @if($appLogoBase64)
                <img src="{{ $appLogoBase64 }}" alt="NutrizionistApp" style="height:20px; width:auto; vertical-align:middle;">
            @else
                <span class="app-name" style="font-size:11px;">Nutrizionist<span class="app-name-green">App</span></span>
            @endif
        </td>
        <td class="r"><span class="topbar-right-page">{{ $plan->client->user->name }} &middot; v1.{{ $plan->id }}</span></td>
    </tr></table>
</div>

<div class="page-content">
    <div class="eyebrow">Sezione {{ $secNote ?? $secRiepilogo - 1 }}</div>
    <div class="section-heading">Note del nutrizionista</div>

    @if($plan->notes)
    <div class="quote-box">
        <div class="quote-mark">&#8220;</div>
        <div class="quote-text">{{ $plan->notes }}</div>
    </div>
    @endif

    <div class="subheading">Contatti</div>
    <div class="contact-card">
        <table><tr>
            <td style="width:50px; vertical-align:middle;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo nutrizionista" style="max-height:40px; max-width:44px; width:auto; border-radius:4px;">
                @else
                    <span class="contact-initials">{{ $initials }}</span>
                @endif
            </td>
            <td style="vertical-align:middle; padding-left:4px;">
                <div class="contact-name">{{ $profile?->business_name ?: $nutritionist->name }}</div>
                <div class="contact-role">
                    {{ $profile?->specialization ?? 'Nutrizionista' }}
                    @if($profile?->license_number) &middot; Albo n.&nbsp;{{ $profile->license_number }} @endif
                </div>
                <div class="contact-info">
                    &#9993; {{ $nutritionist->email }}
                    @if($profile?->website) &nbsp;&middot;&nbsp; &#127760; {{ $profile->website }} @endif
                    @if($profile?->instagram) &nbsp;&middot;&nbsp; @{{ $profile->instagram }} @endif
                    @if($profile?->city) &nbsp;&middot;&nbsp; {{ $profile->city }} @endif
                </div>
            </td>
        </tr></table>
    </div>
</div>


</body>
</html>
