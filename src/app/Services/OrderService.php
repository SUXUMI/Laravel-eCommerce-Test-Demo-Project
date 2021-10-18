<?php


namespace App\Services;


use App\Contracts\CartInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * Create Irder based on Cart
     *
     * @param CartInterface $cart
     * @param null $status
     * @return Order
     * @throws \InvalidArgumentException|\Exception
     */
    public function createOrder(CartInterface $cart, $status = null)
    {
        if ($cart->isEmpty()) {
            return false;
        }

        $this->validateCartForOrderCreation($cart);

        \DB::beginTransaction();

        // make order
        $order = Order::create([
            'user_id' => $cart->getOwnerId(),
            'status' => $status ?: OrderStatus::Ordered,
        ]);

        $summary_cost = 0;

        // add products
        $cart->products()->each(function($cartProduct) use ($order, &$summary_cost) {
            $summary_cost += $cartProduct->price * $cartProduct->quantity;

            $order->products()->create([
                'product_id' => $cartProduct->product_id,
                'name' => $cartProduct->name,
                'price' => $cartProduct->price,
                'quantity' => $cartProduct->quantity,
            ]);

            // deduct from stock
            $cartProduct->product->quantity -= $cartProduct->quantity;
            $cartProduct->product->save();
        });

        $order->cost = $summary_cost;

        $order->save();

        // remove cart
        $cart->destroy();

        \DB::commit();

        return $order;
    }

    /**
     * Try to deduct from user's balance for making order `paid`
     *   otherwise \Exception will be raised!!!
     *
     * @param Order $order
     * @return bool
     * @throws \Exception|\InvalidArgumentException
     */
    public function payOrder(Order $order)
    {
        // find user and try to withdraws from his/her balance
        // and if succeed, set order as `paid`

        \DB::beginTransaction();

        // make payment
        $payment = (new PaymentService($order->user_id))->withdraw($order->cost);

        // update order
        $order->payment_id = $payment->id;
        $order->status = OrderStatus::Paid;

        $order->save();

        \DB::commit();

        return true;
    }

    /**
     * Refund based on order
     *   in case of error, \Exception will be thrown
     *
     * @param Order $order
     * @return bool
     */
    public function returnOrder(Order $order)
    {
        \DB::beginTransaction();

        $this->validateOrderForRefund($order);

        // return (paid amount - penalty)
        $refundAmount = $order->cost * (1 - (float)config('ecommerce.order.refund.penalty'));

        $refundDescription = sprintf('Refund penalty: %s%%', (float)config('ecommerce.order.refund.penalty'));

        $payment = (new PaymentService($order->user_id))->refund($refundAmount, $refundDescription);

        // return quantity to the product's stock
        $order->products->each(function(OrderProduct $orderProduct) {
            $product = Product::find($orderProduct->product_id);
            $product->quantity += $orderProduct->quantity;
            $product->save();
        });

        // update order
        $order->status = OrderStatus::Returned;
        $order->save();

        \DB::commit();

        return true;
    }

    /**
     * Validate if received Cart is assigned to an existing User
     *
     * @param CartInterface $cart
     * @return $this
     */
    private function validateCartForOrderCreation(CartInterface $cart)
    {
        if (!User::find( $cart->getOwnerId() )) {
            throw new \InvalidArgumentException(__('Cart must be assigned to an existing user.'));
        }

        return $this;
    }

    /**
     * Validate Order for Refund/Return
     *
     * @param Order $order
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function validateOrderForRefund(Order $order)
    {
        // check order status
        if ($order->status !== OrderStatus::Paid) {
            throw new \InvalidArgumentException('`Paid` Order could be refunded only!');
        }

        return $this;
    }
}
