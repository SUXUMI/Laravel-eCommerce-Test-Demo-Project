<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class SuccessfulLogin
{
    /**
     * Change Cart's owner_id from session_id to user_id
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // Update cart owner from `whatever_id` (session_id) to `user_id`
        \Cart::changeOwnerId(Auth::user()->id);
    }
}
