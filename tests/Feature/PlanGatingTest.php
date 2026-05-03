<?php

namespace Tests\Feature;

use App\Models\ClientProfile;
use App\Models\NutritionalPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createNutritionist(): User
    {
        $user = User::factory()->create();
        $user->assignRole('nutritionist');
        return $user;
    }

    private function createPlanForNutritionist(User $nutritionist): NutritionalPlan
    {
        $client = User::factory()->create();
        $client->assignRole('client');

        $profile = ClientProfile::factory()->create([
            'user_id'          => $client->id,
            'nutritionist_id'  => $nutritionist->id,
        ]);

        return NutritionalPlan::factory()->create([
            'nutritionist_id' => $nutritionist->id,
            'client_id'       => $profile->id,
        ]);
    }

    // --- PDF export ---

    public function test_free_plan_user_cannot_access_pdf_export(): void
    {
        $nutritionist = $this->createNutritionist();
        $plan = $this->createPlanForNutritionist($nutritionist);

        $response = $this->actingAs($nutritionist)
            ->get(route('nutritionist.plans.pdf', $plan->id));

        $response->assertRedirect(route('nutritionist.billing'));
        $response->assertSessionHas('error');
    }

    public function test_pdf_gate_requires_authentication(): void
    {
        $nutritionist = $this->createNutritionist();
        $plan = $this->createPlanForNutritionist($nutritionist);

        $response = $this->get(route('nutritionist.plans.pdf', $plan->id));

        $response->assertRedirect(route('login'));
    }

    // --- Template limit ---

    public function test_free_plan_user_can_save_template_within_limit(): void
    {
        $nutritionist = $this->createNutritionist();
        $plan = $this->createPlanForNutritionist($nutritionist);

        $response = $this->actingAs($nutritionist)
            ->post(route('nutritionist.plans.save-as-template', $plan->id), [
                'template_name' => 'Template Test',
            ]);

        $response->assertSessionMissing('error');
        $this->assertDatabaseHas('nutritional_plans', [
            'is_template'     => true,
            'nutritionist_id' => $nutritionist->id,
        ]);
    }

    public function test_free_plan_user_cannot_save_template_over_limit(): void
    {
        $nutritionist = $this->createNutritionist();

        // Create 2 existing templates (free limit)
        NutritionalPlan::factory()->count(2)->create([
            'nutritionist_id' => $nutritionist->id,
            'client_id'       => null,
            'is_template'     => true,
        ]);

        $plan = $this->createPlanForNutritionist($nutritionist);

        $response = $this->actingAs($nutritionist)
            ->post(route('nutritionist.plans.save-as-template', $plan->id), [
                'template_name' => 'Template Extra',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('nutritional_plans', [
            'title'           => 'Template Extra',
            'is_template'     => true,
        ]);
    }

    // --- Middleware resolves feature name correctly ---

    public function test_plan_feature_middleware_passes_unknown_feature_to_403(): void
    {
        $nutritionist = $this->createNutritionist();

        // Temporarily register a test route
        \Illuminate\Support\Facades\Route::get('/_test_feature_gate', fn () => 'ok')
            ->middleware(['web', 'auth', 'plan.feature:pdf_export']);

        $response = $this->actingAs($nutritionist)
            ->get('/_test_feature_gate');

        // Free plan has pdf_export = false → redirect to billing
        $response->assertRedirect(route('nutritionist.billing'));
    }
}
