<?php

namespace Illuminate\Support\Facades;

interface Auth
{
    /**
     * @return \App\Models\Operateur|false
     */
    public static function loginUsingId(mixed $id, bool $remember = false);

    /**
     * @return \App\Models\Operateur|false
     */
    public static function onceUsingId(mixed $id);

    /**
     * @return \App\Models\Operateur|null
     */
    public static function getUser();

    /**
     * @return \App\Models\Operateur
     */
    public static function authenticate();

    /**
     * @return \App\Models\Operateur|null
     */
    public static function user();

    /**
     * @return \App\Models\Operateur|null
     */
    public static function logoutOtherDevices(string $password);

    /**
     * @return \App\Models\Operateur
     */
    public static function getLastAttempted();
}