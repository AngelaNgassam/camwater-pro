<?php

namespace Illuminate\Http;

interface Request
{
    /**
     * @return \App\Models\Operateur|null
     */
    public function user($guard = null);
}