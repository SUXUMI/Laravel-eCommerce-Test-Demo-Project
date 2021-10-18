<?php


namespace App\Services;


use App\Contracts\CartInterface;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Product;

class CartService implements CartInterface
{
    protected $ownerId;

    protected $model;

    public function __construct($ownerId)
    {
        $this->setOwnerId($ownerId)->grabModel();
    }

    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Change Cart's owner_id
     *
     * @param $newOwnerId
     * @return $this
     */
    public function changeOwnerId($newOwnerId)
    {
        // if not the same Owner Id
        if ($newOwnerId != $this->ownerId):
            $newOwnersExistedCart = Cart::where('owner_id', '=', $newOwnerId);

            // Change - the new owner's cart does not exist
            if (!$newOwnersExistedCart):
                $this->setOwnerId($newOwnerId);

                $this->model->owner_id = $this->ownerId;

            // else, merge cart items (products)
            else:
                $existingCartService = new self($newOwnerId);

                $this->products()->each(function($cartProduct) use ($existingCartService) {
                    $existingCartService->add($cartProduct->product, $cartProduct->quantity);
                });

                $this->destroy();

                $this->setOwnerId($newOwnerId);

                $this->grabModel();
            endif;

            if (!$this->isEmpty()) $this->save();
        endif;

        return $this;
    }

    /**
     * Determine the model
     *
     * @return $this
     */
    public function grabModel()
    {
        // avoid empty cart creation
        $this->model = Cart::where('owner_id', $this->ownerId)->first() ?? new Cart(['owner_id' => $this->ownerId]);

        return $this;
    }

    /**
     * Add Product to Cart  or  Update!
     *
     * @param Product $product
     * @param $quantity
     * @return bool
     */
    public function add(Product $product, $quantity)
    {
        // save if model just initiated
        $this->save();

        return (bool)$this->model->products()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => min($product->quantity, $quantity)]
        );
    }

    /**
     * Get Cart Product
     *
     * @param Product $product
     * @return CartProduct|null
     */
    public function get(Product $product)
    {
        return $this->model->products()->where('product_id', $product->id)->first();
    }

    /**
     * Check Product existence in Cart
     *
     * @param Product $product
     * @return bool
     */
    public function has(Product $product)
    {
        return (bool)$this->get($product);
    }

    /**
     * Get Products Collection
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function products()
    {
        return $this->model->products()->get();
    }

    public function isEmpty()
    {
        return !(bool)$this->model->products()->count();
    }

    /**
     * Remove Product from Cart
     *
     * @param Product $product
     * @return bool
     */
    public function remove(Product $product)
    {
        if ($this->has($product)) {
            // response = true  - if exists
            // response = false - if not exists
            return (bool)$this->model->products()->where('product_id', $product->id)->delete();
        }

        return true;
    }

    /**
     * CLear Cart
     *
     * @return $this
     */
    public function clear()
    {
        $this->products()->each(function(CartProduct $item) {
            $this->remove($item->product);
        });

        return $this;
    }

    /**
     * Get Cart Summary Cost
     *
     * @return float|int
     */
    public function cost()
    {
        $cost = 0;

        $this->products()->each(function(CartProduct $item) use (&$cost) {
            // in case of not casted in own model!
            $cost += (float)$item->quantity * (float)$item->product->price;
        });

        return $cost;
    }

    /**
     * Save current cart
     *
     * @return bool
     */
    public function save()
    {
        return $this->model->save();
    }

    public function destroy()
    {
        return $this->model->delete();
    }
}
