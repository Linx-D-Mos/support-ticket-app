<?php

namespace App\Observers;

use App\Enums\EventEnum;
use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        
        $new_values = [];

        foreach ($model->getDirty() as $key => $newValues) {
            $new_values[$key] = $newValues;
        }

        Audit::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'event' => EventEnum::CREATED->value,
            'old_values' => null,
            'new_values' => $new_values ?? null,
            'url' => request()->url(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),

        ]);
    }
    public function updated(Model $model): void
    {
        $old_values = [];
        $new_values = [];

        foreach ($model->getDirty() as $key => $value) {
            $old_values[$key] =  $model->getOriginal($key);
            $new_values[$key] =  $value;
        }
        Audit::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'event' => EventEnum::UPDATED->value,
            'old_values' => $old_values ?? null,
            'new_values' => $new_values ?? null,
            'url' => request()->url() ?? null,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
        ]);
    }
    public function deleted(Model $model): void
    {
        $old_values = [];
        $new_values = [];
        foreach ($model->getOriginal() as $key => $oldValues) {
            $old_values[$key] =  $oldValues;
        }
        Audit::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'event' => EventEnum::DELETED->value,
            'old_values' => $old_values ?? null,
            'new_values' => $new_values ?? null,
            'url' => request()->url() ?? null,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent(),
        ]);
    }
    public function restored(Model $model): void
    {
        $new_values= [];
        $old_values = [];
        foreach ($model->getAttributes() as $key => $newValues) {
            $old_values[$key] =  $newValues;
           
        }
        Audit::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'event' => EventEnum::RESTORED->value,
            'old_values' => $old_values ?? null,
            'new_values' => $new_values ?? null,
            'url' => request()->url() ?? null,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
        ]);
    }
}
