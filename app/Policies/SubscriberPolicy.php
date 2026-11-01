<?php

namespace App\Policies;

use App\Models\Subscriber;
use App\Models\User;

class SubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subscribers.viewAny');
    }

    public function view(User $user, Subscriber $subscriber): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Subscriber $subscriber): bool
    {
        return false;
    }

    public function delete(User $user, Subscriber $subscriber): bool
    {
        return $user->can('subscribers.delete');
    }
}
