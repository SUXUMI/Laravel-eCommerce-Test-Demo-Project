<?php


namespace App\Services;


use App\Enums\PaymentType;
use App\Models\Payment;


class PaymentService
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Deposit User Balance
     *
     * @param $amount
     * @param null|string $description
     * @return Payment
     */
    public function deposit($amount, $description = null)
    {
        return $this->validateAmount($amount)
            ->create((float)$amount, PaymentType::Deposit, $description);
    }

    /**
     * Withdraw User Balance
     *
     * @param $amount
     * @param null|string $description
     * @return Payment
     */
    public function withdraw($amount, $description = null)
    {
        return $this->validateAmount($amount)
            ->create(-1*(float)$amount, PaymentType::Withdraw, $description);
    }

    /**
     * Refund
     *
     * @param $amount
     * @param null|string $description
     * @return Payment
     */
    public function refund($amount, $description = null)
    {
        return $this->validateAmount($amount)
            ->create((float)$amount, PaymentType::Refund, $description);
    }

    /**
     * Create payment record
     *   this will trigger User's balance update,
     *   thus DB Transaction is preferred
     *
     * @param float|int $amount
     * @param string $type
     * @param null|string $description
     * @return Payment
     * @throw \Exception
     */
    private function create($amount, $type, $description = null): Payment
    {
        if (!$this->userId) {
            throw new \InvalidArgumentException('User ID must be defined first.');
        }

        \DB::beginTransaction();
        $payment = Payment::create([
            'user_id' => $this->userId,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
        ]);
        \DB::commit();

        return $payment;
    }

    /**
     * Validate Amount
     *
     * @param $amount
     * @return $this
     * @throw \InvalidArgumentException
     */
    private function validateAmount($amount)
    {
        if (floatval($amount) <= 0) {
            throw new \InvalidArgumentException(sprintf('Invalid amount: %s', $amount));
        }

        return $this;
    }
}
