<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', [], $this->safe($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);
        if ($changes === []) return;
        $old = [];
        foreach (array_keys($changes) as $key) $old[$key] = $model->getRawOriginal($key);
        $this->record($model, 'updated', $this->safe($old), $this->safe($changes));
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->safe($model->getAttributes()), []);
    }

    private function record(Model $model, string $event, array $old, array $new): void
    {
        foreach (['password', 'remember_token', 'token', 'secret'] as $key) {
            unset($old[$key], $new[$key]);
        }
        AuditLog::query()->create([
            'actor_id' => Auth::id(),
            'event' => $event,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function safe(array $values): array
    {
        return array_intersect_key($values, array_flip(['name', 'email', 'title', 'slug', 'status', 'published_at', 'role', 'guard_name']));
    }
}
