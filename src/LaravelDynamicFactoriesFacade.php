<?php

namespace Mblarsen\LaravelDynamicFactories;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mblarsen\LaravelDynamicFactories\Skeleton\SkeletonClass
 */
class LaravelDynamicFactoriesFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dynamic-factories';
    }
}
