<?php


namespace App\Facades;

use App\Services\CartService;

/**
 * Class CartFacade
 *
 * @method static mixed getOwnerId()
 *
 * @package App\Facades
 *
 * @see CartService
 */
class CartFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
