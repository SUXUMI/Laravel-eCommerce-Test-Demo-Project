<?php


namespace App\Enums;


abstract class OrderStatus extends Enum
{
    const Ordered = 'ordered';
    const Paid = 'paid';
    const Returned = 'returned';
}
