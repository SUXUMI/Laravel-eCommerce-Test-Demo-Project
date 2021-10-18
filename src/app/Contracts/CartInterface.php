<?php


namespace App\Contracts;


use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Support\Collection;

interface CartInterface
{
    /**
     * Set Cart Owner (either Session Id or User Id)
     * @param $ownerId
     * @return self
     */
    public function setOwnerId($ownerId);

    /**
     * Get Cart Owner Id
     * @return string
     */
    public function getOwnerId();

    /**
     * Change Cart's owner_id
     *
     * @param $newOwnerId
     * @return self
     */
    public function changeOwnerId($newOwnerId);

    /**
     * Add Product to the Cart or Update!
     *
     * @param Product $product
     * @param float $quantity
     * @return bool
     */
    public function add(Product $product, $quantity);

    /**
     * Get Cart Product
     *
     * @param Product $product
     * @return CartProduct|null
     */
    public function get(Product $product);

    /**
     * Check Product existence in the Cart
     *
     * @param Product $product
     * @return bool
     */
    public function has(Product $product);

    /**
     * Get Products Collection
     *
     * @return Collection
     */
    public function products();

    /**
     * Check if the Cart is empty
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * Remove Product from the Cart
     *
     * @param Product $product
     * @return bool
     */
    public function remove(Product $product);

    /**
     * CLear the Cart
     *
     * @return $this
     */
    public function clear();

    /**
     * Get Cart Summary Cost
     *
     * @return float
     */
    public function cost();

    /**
     * Delete the Cart
     *
     * @return mixed
     */
    public function destroy();
}
