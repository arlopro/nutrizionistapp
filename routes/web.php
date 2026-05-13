<?php

use App\Http\Controllers\Client\AvatarController as ClientAvatarController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\AppointmentController as ClientAppointmentController;
use App\Http\Controllers\Client\CheckInController as ClientCheckInController;
use App\Http\Controllers\Client\MealCompletionController as ClientMealCompletionController;
use App\Http\Controllers\Client\PlanController as ClientPlanController;
use App\Http\Controllers\Dev\ActivityController;
use App\Http\Controllers\Dev\DevController;
use App\Http\Controllers\Dev\ImpersonateController;
use App\Http\Controllers\Dev\GiftController;
use App\Http\Controllers\Dev\SettingsController as DevSettingsController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\Nutritionist\BillingController;
use App\Http\Controllers\Nutritionist\ClientController;
use App\Http\Controllers\Nutritionist\DashboardController as NutritionistDashboardController;
use App\Http\Controllers\Nutritionist\FoodController;
use App\Http\Controllers\Nutritionist\AppointmentController;
use App\Http\Controllers\Nutritionist\CheckInController as NutritionistCheckInController;
use App\Http\Controllers\Nutritionist\NutritionalPlanController;
use App\Http\Controllers\Nutritionist\PlanMealController;
use App\Http\Controllers\Nutritionist\PlanSupplementController;
use App\Http\Controllers\Nutritionist\RecipeController;
use App\Http\Controllers\Nutritionist\AnamnesisTemplateController;
use App\Http\Controllers\Nutritionist\AnamnesisSubmissionController;
use App\Http\Controllers\Client\AnamnesisController as ClientAnamnesisController;
use App\Http\Controllers\Client\MessageController as ClientMessageController;
use App\Http\Controllers\Nutritionist\MessageController;
use App\Http\Controllers\Nutritionist\LabResultController;
use App\Http\Controllers\Nutritionist\OnboardingController;
use App\Http\Controllers\Nutritionist\SettingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Role-based dashboard redirect
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('dev')) return redirect()->route('dev.dashboard');
    if ($user->hasRole('nutritionist')) return redirect()->route('nutritionist.dashboard');
    return redirect()->route('client.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Nutritionist routes
Route::middleware(['auth', 'verified', 'role:nutritionist'])->prefix('nutritionist')->name('nutritionist.')->group(function () {
    Route::get('/dashboard', [NutritionistDashboardController::class, 'index'])->name('dashboard');
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/send-invitation', [ClientController::class, 'sendInvitation'])->name('clients.send-invitation');
    Route::resource('foods', FoodController::class);
    Route::post('foods/{food}/hide', [FoodController::class, 'hide'])->name('foods.hide');
    Route::resource('recipes', RecipeController::class);
    Route::post('plans/calculate-tdee', [NutritionalPlanController::class, 'calculateTdee'])->name('plans.calculate-tdee');
    Route::resource('plans', NutritionalPlanController::class);
    Route::get('templates', [NutritionalPlanController::class, 'templates'])->name('plans.templates');
    Route::post('plans/{plan}/save-as-template', [NutritionalPlanController::class, 'saveAsTemplate'])->name('plans.save-as-template');
    Route::delete('templates/{plan}', [NutritionalPlanController::class, 'destroyTemplate'])->name('plans.templates.destroy');

    // Plan meal builder routes
    Route::post('plans/{plan}/duplicate', [NutritionalPlanController::class, 'duplicate'])->name('plans.duplicate');
    Route::post('plans/{plan}/meals', [PlanMealController::class, 'storeMeal'])->name('plans.meals.store');
    Route::patch('plans/{plan}/meals/{meal}', [PlanMealController::class, 'updateMeal'])->name('plans.meals.update');
    Route::delete('plans/{plan}/meals/{meal}', [PlanMealController::class, 'destroyMeal'])->name('plans.meals.destroy');
    Route::post('plans/{plan}/meals/{meal}/items', [PlanMealController::class, 'storeItem'])->name('plans.meals.items.store');
    Route::patch('plans/{plan}/items/{item}', [PlanMealController::class, 'updateItem'])->name('plans.items.update');
    Route::delete('plans/{plan}/items/{item}', [PlanMealController::class, 'destroyItem'])->name('plans.items.destroy');
    Route::post('plans/{plan}/items/{item}/alternatives', [PlanMealController::class, 'storeAlternative'])->name('plans.items.alternatives.store');
    Route::post('plans/{plan}/days/{day}/duplicate', [PlanMealController::class, 'duplicateDay'])->name('plans.days.duplicate');
    Route::post('plans/{plan}/days/{day}/apply-to-week', [PlanMealController::class, 'applyDayToWeek'])->name('plans.days.apply-to-week');

    // Plan supplements
    Route::post('plans/{plan}/supplements', [PlanSupplementController::class, 'store'])->name('plans.supplements.store');
    Route::patch('plans/{plan}/supplements/{supplement}', [PlanSupplementController::class, 'update'])->name('plans.supplements.update');
    Route::delete('plans/{plan}/supplements/{supplement}', [PlanSupplementController::class, 'destroy'])->name('plans.supplements.destroy');

    // Check-ins (nutritionist view)
    Route::get('check-ins', [NutritionistCheckInController::class, 'index'])->name('check-ins.index');
    Route::get('check-ins/{checkIn}', [NutritionistCheckInController::class, 'show'])->name('check-ins.show');
    Route::patch('check-ins/{checkIn}/notes', [NutritionistCheckInController::class, 'addNotes'])->name('check-ins.notes');
    Route::patch('check-ins/{checkIn}/review', [NutritionistCheckInController::class, 'review'])->name('check-ins.review');
    Route::get('check-ins/photo-compare', [NutritionistCheckInController::class, 'photoCompare'])->name('check-ins.photo-compare');

    // Appointments
    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    // PDF export (Starter+)
    Route::get('plans/{plan}/pdf', [NutritionalPlanController::class, 'exportPdf'])->middleware('plan.feature:pdf_export')->name('plans.pdf');

    // Lab results (esami ematochimici)
    Route::resource('lab-results', LabResultController::class);

    // Anamnesi templates
    Route::resource('anamnesis', AnamnesisTemplateController::class)
        ->parameters(['anamnesis' => 'anamnesi'])
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('anamnesis/send', [AnamnesisSubmissionController::class, 'send'])->name('anamnesis.send');
    Route::get('anamnesis/submissions/{submission}', [AnamnesisSubmissionController::class, 'show'])->name('anamnesis.submissions.show');

    // Messages
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{client}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{client}', [MessageController::class, 'store'])->name('messages.store');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::patch('settings/locations', [SettingsController::class, 'updateLocations'])->name('settings.locations.update');
    Route::patch('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    Route::delete('settings/logo', [SettingsController::class, 'deleteLogo'])->name('settings.logo.delete');
    Route::delete('settings/avatar', [SettingsController::class, 'deleteAvatar'])->name('settings.avatar.delete');

    // Onboarding
    Route::patch('onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
    Route::post('onboarding/dismiss', [OnboardingController::class, 'dismiss'])->name('onboarding.dismiss');

    // Billing / Subscription
    Route::get('billing', [BillingController::class, 'index'])->name('billing');
    Route::get('billing/success', [BillingController::class, 'stripeSuccess'])->name('billing.stripe-success');
    Route::post('billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('billing/billing-info', [BillingController::class, 'updateBillingInfo'])->name('billing.info.update');
    Route::post('billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});

// Client routes
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/plan', [ClientPlanController::class, 'index'])->name('plan');
    Route::post('/meals/{meal}/toggle-complete', [ClientMealCompletionController::class, 'toggle'])->name('meals.toggle-complete');
    Route::resource('check-ins', ClientCheckInController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('check-ins/{checkIn}/patient-notes', [ClientCheckInController::class, 'updatePatientNotes'])->name('check-ins.patient-notes');
    Route::get('/anamnesis', [ClientAnamnesisController::class, 'index'])->name('anamnesis.index');
    Route::get('/anamnesis/{submission}', [ClientAnamnesisController::class, 'show'])->name('anamnesis.show');
    Route::post('/anamnesis/{submission}', [ClientAnamnesisController::class, 'submit'])->name('anamnesis.submit');
    Route::get('/appointments', [ClientAppointmentController::class, 'index'])->name('appointments');
    Route::get('/messages', [ClientMessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [ClientMessageController::class, 'store'])->name('messages.store');
    Route::get('/settings', [ClientAvatarController::class, 'index'])->name('settings');
    Route::post('/settings/avatar', [ClientAvatarController::class, 'update'])->name('settings.avatar.update');
    Route::delete('/settings/avatar', [ClientAvatarController::class, 'destroy'])->name('settings.avatar.delete');
});

// Profile routes (shared)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dev routes
Route::middleware(['auth', 'role:dev'])->prefix('dev')->name('dev.')->group(function () {
    Route::get('/', [DevController::class, 'dashboard'])->name('dashboard');
    Route::get('/nutritionists', [DevController::class, 'nutritionists'])->name('nutritionists');
    Route::get('/nutritionists/{user}', [DevController::class, 'showNutritionist'])->name('nutritionists.show');
    Route::get('/plans', [DevController::class, 'plans'])->name('plans');
    Route::get('/users', [DevController::class, 'users'])->name('users');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity');
    Route::post('/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('impersonate');
    Route::get('/settings/payments', [DevSettingsController::class, 'payments'])->name('settings.payments');
    Route::post('/settings/payments/{provider}', [DevSettingsController::class, 'update'])->name('settings.payments.update');
    Route::post('/settings/payments/{provider}/test', [DevSettingsController::class, 'test'])->name('settings.payments.test');
    Route::post('/nutritionists/{user}/gift', [GiftController::class, 'store'])->name('nutritionists.gift');
    Route::delete('/nutritionists/{user}/gift/{gift}', [GiftController::class, 'destroy'])->name('nutritionists.gift.destroy');
});

// Stop impersonation (accessible to everyone with active impersonation)
Route::post('/impersonate/stop', [ImpersonateController::class, 'stop'])->middleware('auth')->name('impersonate.stop');

// Cashier webhook
Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])->name('cashier.webhook');

// PayPal webhook
Route::post('/paypal/webhook', [PayPalWebhookController::class, 'handle'])->name('paypal.webhook');

require __DIR__.'/auth.php';
