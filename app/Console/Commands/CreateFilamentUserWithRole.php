<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateFilamentUserWithRole extends Command
{
    protected $signature = 'filament:user-create 
        {name : El nombre del usuario} 
        {email : El email del usuario} 
        {password : La contraseña del usuario} 
        {--admin : Asigna el rol admin} 
        {--super_admin : Asigna el rol super_admin}';

    protected $description = 'Crea un usuario para Filament y le asigna un rol si se indica';

    public function handle()
    {
        // Crea el usuario con los datos proporcionados
        $user = User::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => Hash::make($this->argument('password')),
        ]);

        // Asigna el rol según el flag recibido
        if ($this->option('admin')) {
            $user->assignRole('admin');
            $this->info("Usuario creado con rol admin.");
        } elseif ($this->option('super_admin')) {
            $user->assignRole('super_admin');
            $this->info("Usuario creado con rol super_admin.");
        } else {
            $this->info("Usuario creado sin rol asignado.");
        }
    }
}
