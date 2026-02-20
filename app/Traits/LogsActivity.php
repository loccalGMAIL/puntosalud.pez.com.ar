<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => ActivityLog::record('created', $model, $model->activityDescription()));
        static::updated(fn ($model) => ActivityLog::record('updated', $model, $model->activityDescription()));
        static::deleted(fn ($model) => ActivityLog::record('deleted', $model, $model->activityDescription()));
    }

    /**
     * Descripción legible del registro. Cada modelo puede sobreescribir este método.
     */
    public function activityDescription(): string
    {
        return '#' . $this->getKey();
    }
}
