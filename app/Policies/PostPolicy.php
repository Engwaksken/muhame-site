<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('posts.viewAny');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('posts.view') && ($post->status === 'published' || $post->user_id === $user->id || $user->hasAnyRole(['Admin', 'Super Admin', 'Editor', 'Moderator']));
    }

    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('posts.update') && ($post->user_id === $user->id || $user->hasAnyRole(['Admin', 'Super Admin', 'Editor', 'Moderator']));
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('posts.delete') && ($post->user_id === $user->id || $user->hasAnyRole(['Admin', 'Super Admin']));
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->can('posts.publish') && ($post->user_id === $user->id || $user->hasAnyRole(['Admin', 'Super Admin', 'Editor', 'Moderator']));
    }
}
