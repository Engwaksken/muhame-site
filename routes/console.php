<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

Artisan::command('cms:create-admin {email} {--name=Administrator}', function (string $email) {
    if (Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->fails()) {
        $this->error('Please provide a valid email address.');

        return 1;
    }

    $password = (string) $this->secret('Password (at least 12 characters)');

    if (mb_strlen($password) < 12) {
        $this->error('The password must be at least 12 characters.');

        return 1;
    }

    if (! Role::query()->where('name', 'Super Admin')->exists()) {
        $this->call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
    }

    $user = User::query()->firstOrNew(['email' => $email]);
    $isNew = ! $user->exists;
    $user->fill(['name' => $user->name ?: $this->option('name'), 'password' => $password]);
    $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
    $user->assignRole('Super Admin');

    $this->info(($isNew ? 'Created' : 'Updated').' Super Admin '.$email.'. Sign in at '.url('/admin'));

    return 0;
})->purpose('Create (or reset the password of) a Super Admin user for the admin panel');
