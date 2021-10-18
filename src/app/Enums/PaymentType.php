<?php


namespace App\Enums;


abstract class PaymentType extends Enum
{
    const Deposit = 'deposit';
    const Withdraw = 'withdraw';
    const Refund = 'refund';
}
