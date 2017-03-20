<?php namespace Barryvdh\Cors;

/**
 * Class LumenServiceProvider
 * @deprecated Use the regular ServiceProvider for Lumen
 */
class LumenServiceProvider extends ServiceProvider
{
    protected function isLumen()
    {
        return true;
    }
}
