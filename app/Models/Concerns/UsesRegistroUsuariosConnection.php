<?php

namespace App\Models\Concerns;

trait UsesRegistroUsuariosConnection
{
    public function getConnectionName(): ?string
    {
        return config('auth.user_connection') ?: parent::getConnectionName();
    }
}
