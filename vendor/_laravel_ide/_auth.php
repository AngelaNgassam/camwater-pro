<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * @return \App\Models\Operateur|null
     */
    public function user();
}