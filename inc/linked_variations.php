<?php

/**
 * Woocommerce additions to Linked Variations
 *
 * @package wclve
 *
 * @since 0.5.0
 */

 namespace wclve;

 require_once 'price.php';

 add_filter('the_title', __NAMESPACE__.'\\new_title', 10, 2);


function new_title($title, $id)
{

    if(!is_admin() && !is_singular('product')) {

        $related = \Iconic_WLV_Product::get_linked_variations_data($id);

        if(! count($related) > 0) {
            return $title;
        }
        else{
            return $related['group']->post->post_title;
        }


    }
    else{
        return $title;
    }


    
}


function product_exclusion_list()
{

    $link_query = new \WP_Query(
        array(
            'post_type' => 'cpt_iconic_wlv',
            'posts_per_page' => -1
        )
    );

        
    if(!$link_query->have_posts()) {
        $exclude = false;
    }
    else{
        $exclude = [];
        $include = [];
        while($link_query->have_posts()){
            $link_query->the_post();

            $variation = new \Iconic_WLV_Linked_Variations_Group(get_the_id());

            $ids = $variation->get_product_ids();


            $exclude = array_merge($exclude, $variation->get_product_ids());
            if(!in_array($ids[0], $include)) {
                $include[] = $ids[0];
            }
        }

        $exclude = array_values(array_diff($exclude, $include));


    }

        wp_reset_postdata();






        return $exclude;


}

add_shortcode('product_exclusion_list', __NAMESPACE__.'\\product_exclusion_list');


 /**
  * Exclude products from a particular category on the shop page
  */
function exclude_variations( $q )
{

    $exclude = product_exclusion_list();

    if($exclude) {
  
        $not_in = $q->get('post__not_in');
  
        $not_in = array_values(array_merge($exclude, $not_in));
  
  
        $q->set('post__not_in', $not_in);

    }
  
}
  add_action('woocommerce_product_query', __NAMESPACE__.'\\exclude_variations');  

    /**
     * Separate large format from personal in related
     *
     * @param  [type] $related_posts
     * @param  [type] $product_id
     * @param  [type] $args
     * @return void
     */
function exclude_variations_from_related_products( $related_posts, $product_id, $args  )
{
    // HERE define your product category slug
    $exclude = product_exclusion_list();


    $vars = \Iconic_WLV_Product::get_linked_variations_data($product_id);

    if($vars && is_array($vars['group']->product_ids['array'])) {
        $related_posts = array_diff($related_posts, $vars['group']->product_ids['array']);
    }


    if(is_array($exclude)) {
  
        $related_posts = array_diff($related_posts, $exclude);
    }
    
        return $related_posts;
    
}

add_filter('woocommerce_related_products', __NAMESPACE__.'\\exclude_variations_from_related_products', 10, 3);


// Add the custom columns to the book post type:
add_filter('manage_product_posts_columns', __NAMESPACE__.'\\add_linked_variation_columns', 100);
function add_linked_variation_columns($columns)
{
   
    $columns['variation'] = 'Variation Group';

    unset($columns['product_tag']);

    return $columns;
}

add_action('manage_product_posts_custom_column', __NAMESPACE__.'\\add_linked_variation_to_column', 10, 2);

function add_linked_variation_to_column( $column, $postid )
{
    if ($column == 'variation' ) {

        $group = \Iconic_WLV_Product::get_linked_variations_group($postid);

        if($group) {

            $post = $group->post;

            echo '<a href="'.get_edit_post_link($post->ID).'">'.$post->post_title.'</a>';

        }
        else{
            echo '-';
        }




        // echo 'test';
    }
}




