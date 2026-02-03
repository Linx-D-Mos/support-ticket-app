<?php

namespace App\Observers;

use App\Enums\EventEnum;
use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function updated(Model $model): void{
        $old_values = [];
        $new_values = [];

        foreach($model->getDirty() as $key => $newValues){
            $old_values[$key] =  $model->getOriginal($key);
            $new_values[$key] =  $newValues;
        }
        Audit::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'event' => EventEnum::UPDATED,
            'old_values' => $old_values ?? null,
            'new_values' => $new_values ?? null,
            'url' => request()->url() ?? null,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
        ]);
    }
}
