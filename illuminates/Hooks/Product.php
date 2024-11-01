<?php

namespace Illuminate\Hooks;

use Illuminate\Interfaces\IHook;
use Illuminate\Utils\Helper;
use Illuminate\Utils\QueryTranslations;

class Product implements IHook
{
    /**
     * Register Hooks
     *
     * @since 4.4.0
     * @var array
     */
    public function registerHooks()
    {
        if (Helper::getWooStatus()) {
            add_action('woocommerce_product_set_stock', array($this, 'productSetStock'), 99, 1);
            add_action('woocommerce_variation_set_stock', array($this, 'productSetStock'), 99, 1);
        }
    }

    public function productSetStock($product)
    {
        remove_action('woocommerce_product_set_stock', array($this, 'productSetStock'), 99);
        remove_action('woocommerce_variation_set_stock', array($this, 'productSetStock'));

        $stock          = $product->get_stock_quantity();
        $stockStatus    = $product->get_stock_status();
        $productID      = $product->get_id();
        $originalID     = $productID;

        $queryTranslate = QueryTranslations::getInstance();
        //Get original of product
        $originalProduct = $queryTranslate->getFromTranslateIDWithoutLang($productID, 'product');
        if (!empty($originalProduct)) {
            $originalID = $originalProduct->object_id;
            update_post_meta($originalID, '_stock', $stock);
            wc_update_product_stock_status($originalID, $stockStatus);
        }

        //Get all translate from original
        $hasTranslate   = $queryTranslate->getTranslateWithoutLang($originalID, 'product');
        if (!empty($hasTranslate)) {
            foreach ($hasTranslate as $itemTranslate) {
                if ($itemTranslate->translate_id == $productID) {
                    continue;
                }
                update_post_meta($itemTranslate->translate_id, '_stock', $stock);
                wc_update_product_stock_status($itemTranslate->translate_id, $stockStatus);
            }
        }

        add_action('woocommerce_variation_set_stock', array($this, 'productSetStock'), 99, 1);
        add_action('woocommerce_variation_set_stock', array($this, 'productSetStock'), 99, 1);
    }
}
