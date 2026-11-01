<?php

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.viewAny');
    }

    public function view(User $user, SiteSetting $siteSetting): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, SiteSetting $siteSetting): bool
    {
        return $user->can('settings.update');
    }
}
