<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DevRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crea il ruolo dev se non esiste
        Role::firstOrCreate(['name' => 'dev', 'guard_name' => 'web']);

        $this->command->info('Ruolo "dev" creato.');
        $this->command->line('Per assegnarlo al tuo account:');
        $this->command->line('  php artisan dev:assign-role <email>');
    }
}
