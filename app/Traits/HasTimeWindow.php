<?php

namespace App\Traits;

use Carbon\Carbon;

use function Symfony\Component\Clock\now;

trait HasTimeWindow
{
    /**
     * Determina si el modelo aún está dentro de la ventana de tiempo editable.
     *
     * @param int $minutes Tiempo límite en minutos (default 10)
     * @return bool
     */
    public function isEditableInTimeWindow(int $minutes = 10): bool
    {
        return $this->created_at->gt(Carbon::now()->subMinutes($minutes));
    }
}