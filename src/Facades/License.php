<?php


namespace Gai871013\License\Facades;

use Illuminate\Support\Facades\Facade;

class License extends Facade
{
    /**
     * License 门面
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Gai871013\License\License::class;
    }
}
