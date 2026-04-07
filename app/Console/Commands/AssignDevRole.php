<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignDevRole extends Command
{
    protected $signature = 'dev:assign-role {email : Email dell\'utente a cui assegnare il ruolo dev}';
    protected $description = 'Assegna il ruolo "dev" a un utente esistente';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Nessun utente trovato con email: {$email}");
            return self::FAILURE;
        }

        Role::firstOrCreate(['name' => 'dev', 'guard_name' => 'web']);

        $user->assignRole('dev');

        $this->info("Ruolo \"dev\" assegnato a {$user->name} ({$email})");
        return self::SUCCESS;
    }
}
