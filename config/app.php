<?php

use Illuminate\Support\Facades\Facade;

return [

    'currency_symbol' => env('CURRENCY_SYMBOL', 'MAD'),

    'aliases' => Facade::defaultAliases()->merge([
        'Cart' => App\Facades\Cart::class,
    ])->toArray(),

];
