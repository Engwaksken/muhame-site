<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Register;
use Illuminate\Database\Eloquent\Model;

class Registration extends Register
{
    /** @param array<string, mixed> $data */
    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);
        $user->assignRole('Viewer');

        return $user;
    }
}
