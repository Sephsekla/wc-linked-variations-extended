<?php

/**
 * Woocommerce additions to Linked Variations
 *
 * @package wclve
 *
 * @since 0.5.0
 */

 namespace wclve\price;

 /**
  * Get minimum price for an individual produt
  *
  * @param  [object] $product
  * @return float
  */
function get_min_product_price($product)
{


    if ($product->is_type('variable') ) {
        return $product->get_variation_price('min', true);
    }
    else{
        return $product->get_price();
    }


}

 /**
  * Get maximum price for an individual produt
  *
  * @param  [object] $product
  * @return float
  */
function get_max_product_price($product)
{
    if ($product->is_type('variable') ) {
        return $product->get_variation_price('max', true);
    }
    else{
        return $product->get_price();
    }
}


 /**
  * Get minimum price for a linked group
  *
  * @param  [object] $product
  * @return float
  */
function get_min_linked_price($product)
{

    $vars = \Iconic_WLV_Product::get_linked_variations_data($product->id);

    if($vars && is_array($vars['group']->product_ids['array'])) {
        $related_posts = $vars['group']->product_ids['array'];

        $prices = [];

        foreach($related_posts as $related_post){
            $rel_product = wc_get_product($related_post);

            if($rel_product) {
                $prices[] = get_min_product_price($rel_product);
            }
        
        }

        return min($prices);

    }
    else{
        return get_min_product_price($product);
    }

}


 /**
  * Get maximum price for a linked group
  *
  * @param  [object] $product
  * @return float
  */
function get_max_linked_price($product)
{

    $vars = \Iconic_WLV_Product::get_linked_variations_data($product->id);
    
    if($vars && is_array($vars['group']->product_ids['array'])) {
        $related_posts = $vars['group']->product_ids['array'];
    
        $prices = [];
    
        foreach($related_posts as $related_post){
            $rel_product = wc_get_product($related_post);

            if($rel_product) {

                $prices[] = get_max_product_price($rel_product);

            }

    
        }

        
            return max($prices);
    
    }
    else{
        return get_max_product_price($product);
    }
    
}



function change_linked_price_display( $price, $product )
{



    /**
     * Exclude from Admin,
     * If we are getting the price for a variation we return as usual
     */
    if (is_admin() || $product->get_parent_id() ) {
        return $price;
    }
    else{

        $max = wc_price(get_max_linked_price($product));
        $min = wc_price(get_min_linked_price($product));

        $suffix = \wc_prices_include_tax() ? '' : ' +VAT';

        if($max === $min) {
            return $price;
        }
        elseif(!is_singular('product')) {

            return 'From '.$min.$suffix;
        }

        else{
    
            return $min.' - '.$max.$suffix;
        }

    }


}

add_filter('woocommerce_get_price_html', __NAMESPACE__.'\\change_linked_price_display', 2, 10);

function display_current_linked_price()
{
    $product = wc_get_product(get_the_id());

    if(\Iconic_WLV_Product::get_linked_variations_data(get_the_id()) && !$product->is_type('variable') || $product->is_type('variable') && (get_min_product_price($product) === get_max_product_price($product))) {
        


        echo \sprintf('<div class="woocommerce-variation-price"><span class="price"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">£</span>%s</bdi></span></span></div>', $product->get_price());
    }


}

add_action('woocommerce_single_product_summary', __NAMESPACE__.'\\display_current_linked_price', 29);