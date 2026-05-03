# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Development (runs Laravel server, queue, logs, and Vite concurrently)
composer dev

# Full project setup (first time)
composer setup

# Run all tests
composer test

# Frontend build
npm run build        # TypeScript check + Vite production build
npm run dev          # Vite dev server only
```

## Architecture

Multi-tenant SaaS for nutritionists — Laravel 13 / PHP 8.3 backend, Vue 3 + Inertia.js v2 frontend, Tailwind CSS, SQLite (dev) / MySQL (prod), Stripe (via Cashier).

### Three roles with separate route namespaces and dashboards
- **Nutritionist** (`middleware(['auth', 'role:nutritionist'])`, routes `nutritionist.*`) — manages clients, meal plans, recipes, appointments, check-ins, lab results, billing
- **Client** (`middleware(['auth', 'role:client'])`, routes `client.*`) — views assigned plans, logs check-ins, books appointments
- **Dev** (`middleware(['auth', 'role:dev'])`, routes `dev.*`) — impersonates any user, views all data; bypasses all Gate policies via `Gate::before`

### Request lifecycle
1. Routes in `routes/web.php` dispatch to controllers under `app/Http/Controllers/{Nutritionist,Client,Dev,Auth}/`
2. Controllers authorize via policies in `app/Policies/`, then return `Inertia::render('PageName', $props)`
3. `HandleInertiaRequests` middleware shares `auth.user`, `roles`, `avatarUrl`, and flash messages to every page
4. Vue pages in `resources/js/Pages/{Auth,Nutritionist,Client,Dev,Profile}/` receive props and render
5. Ziggy provides route helpers on the frontend (`route('nutritionist.clients.index')`)

### Key models and their relationships
```
User → NutritionistProfile | ClientProfile
NutritionistProfile → ClientProfile[] (has many clients)
ClientProfile → NutritionalPlan[] → PlanMeal[] → PlanMealItem[] (Food/Recipe)
ClientProfile → CheckIn[] → CheckInPhoto[], CheckInMeasurement[]
ClientProfile → Appointment[], LabResult[], AnamnesisSubmission[]
NutritionistProfile → Recipe[], AnamnesisTemplate[], Appointment[]
User → Message[] (sender/receiver)
```

### Enums (in `app/Enums/`)
Domain types used throughout: `PlanStatus`, `ClientGoal`, `ClientStatus`, `ActivityLevel`, `Gender`, `MealType`, `MeasurementType`, `PhotoType`, `FoodCategory`, `AppointmentStatus`, `AppointmentType`.

### Services
- `TdeeCalculator` — BMR/TDEE calculation integrated into plan creation
- `SubscriptionService` — Stripe subscription management

### Frontend conventions
- TypeScript enabled; types defined in `resources/js/types/` — `PageProps` interface holds shared Inertia props
- Layouts: `AuthenticatedLayout` (nutritionist/client), `GuestLayout`, `DevLayout`
- Charts via `vue-chartjs` wrapping Chart.js
- Flash feedback via `usePage().props.flash` (success/error keys)
- File/photo storage on the `public` disk

### Authorization pattern
Every resource has a corresponding Policy. Controller methods call `$this->authorize('action', $model)` before acting. Dev role skips all checks. Nutritionists can only access their own clients' data.
