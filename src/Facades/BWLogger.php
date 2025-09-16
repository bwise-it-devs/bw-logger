<?php
namespace BwiseMedia\BWLogger\Facades;

use Illuminate\Support\Facades\Facade;

class BWLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bw-logger';
    }
}
