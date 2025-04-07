<?php
/**
 * The product indexer functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/indexers
 */

/**
 * The product indexer functionality of the plugin.
 *
 * Handles indexing of WooCommerce products for the chatbot.
 *
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/indexers
 */
class WCIC_Product_Indexer {

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Add hooks for product updates
        add_action('save_post_product', array($this, 'index_product'), 10, 3);
        add_action('woocommerce_update_product', array($this, 'index_product_by_id'));
        add_action('woocommerce_delete_product', array($this, 'delete_product_index'));
        
        // Add hook for scheduled indexing
        add_action('wcic_index_products', array($this, 'index_all_products'));
    }

    /**
     * Index a product when it's saved or updated.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    WP_Post   $post       The post object.
     * @param    bool      $update     Whether this is an existing post being updated.
     */
    public function index_product($post_id, $post, $update) {
        // Skip revisions and autosaves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Skip if not a product
        if ($post->post_type !== 'product') {
            return;
        }
        
        // Index the product
        $this->index_product_by_id($post_id);
    }

    /**
     * Index a product by its ID.
     *
     * @since    1.0.0
     * @param    int       $product_id    The product ID.
     * @return   bool                     Whether the indexing was successful.
     */
    public function index_product_by_id($product_id) {
        // Get the product
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return false;
        }
        
        // Extract product data
        $product_data = $this->extract_product_data($product);
        
        // Save to database
        global $wpdb;
        
        // Check if product already exists in index
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wcic_product_index WHERE product_id = %d",
                $product_id
            )
        );
        
        if ($existing) {
            // Update existing record
            $result = $wpdb->update(
                $wpdb->prefix . 'wcic_product_index',
                array(
                    'product_data' => json_encode($product_data),
                    'last_updated' => current_time('mysql')
                ),
                array('product_id' => $product_id)
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $wpdb->prefix . 'wcic_product_index',
                array(
                    'product_id' => $product_id,
                    'product_data' => json_encode($product_data),
                    'last_updated' => current_time('mysql')
                )
            );
        }
        
        return $result !== false;
    }

    /**
     * Delete a product from the index.
     *
     * @since    1.0.0
     * @param    int       $product_id    The product ID.
     * @return   bool                     Whether the deletion was successful.
     */
    public function delete_product_index($product_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'wcic_product_index',
            array('product_id' => $product_id)
        );
        
        return $result !== false;
    }

    /**
     * Index all products.
     *
     * @since    1.0.0
     * @return   int|bool    The number of products indexed, or false on failure.
     */
    public function index_all_products() {
        // Get all published products
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        $products = get_posts($args);
        
        if (empty($products)) {
            return 0;
        }
        
        $success_count = 0;
        
        foreach ($products as $product_id) {
            $result = $this->index_product_by_id($product_id);
            
            if ($result) {
                $success_count++;
            }
        }
        
        return $success_count;
    }

    /**
     * Extract data from a product for indexing.
     *
     * @since    1.0.0
     * @param    WC_Product    $product    The product object.
     * @return   array                     The extracted product data.
     */
    private function extract_product_data($product) {
        // Basic product data
        $data = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'type' => $product->get_type(),
            'status' => $product->get_status(),
            'featured' => $product->get_featured(),
            'catalog_visibility' => $product->get_catalog_visibility(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'sku' => $product->get_sku(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'date_created' => $product->get_date_created() ? $product->get_date_created()->format('Y-m-d H:i:s') : null,
            'date_modified' => $product->get_date_modified() ? $product->get_date_modified()->format('Y-m-d H:i:s') : null,
            'stock_status' => $product->get_stock_status(),
            'stock_quantity' => $product->get_stock_quantity(),
            'weight' => $product->get_weight(),
            'dimensions' => array(
                'length' => $product->get_length(),
                'width' => $product->get_width(),
                'height' => $product->get_height(),
            ),
            'permalink' => get_permalink($product->get_id()),
        );
        
        // Categories
        $categories = array();
        $category_ids = $product->get_category_ids();
        
        if (!empty($category_ids)) {
            foreach ($category_ids as $category_id) {
                $category = get_term($category_id, 'product_cat');
                
                if ($category && !is_wp_error($category)) {
                    $categories[] = array(
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    );
                }
            }
        }
        
        $data['categories'] = $categories;
        
        // Tags
        $tags = array();
        $tag_ids = $product->get_tag_ids();
        
        if (!empty($tag_ids)) {
            foreach ($tag_ids as $tag_id) {
                $tag = get_term($tag_id, 'product_tag');
                
                if ($tag && !is_wp_error($tag)) {
                    $tags[] = array(
                        'id' => $tag->term_id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    );
                }
            }
        }
        
        $data['tags'] = $tags;
        
        // Attributes
        $attributes = array();
        $product_attributes = $product->get_attributes();
        
        if (!empty($product_attributes)) {
            foreach ($product_attributes as $attribute_name => $attribute) {
                if ($attribute->is_taxonomy()) {
                    $attribute_taxonomy = $attribute->get_taxonomy_object();
                    $attribute_values = array();
                    
                    foreach ($attribute->get_terms() as $term) {
                        $attribute_values[] = array(
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                        );
                    }
                    
                    $attributes[] = array(
                        'id' => $attribute_taxonomy->attribute_id,
                        'name' => $attribute_taxonomy->attribute_label,
                        'position' => $attribute->get_position(),
                        'visible' => $attribute->get_visible(),
                        'variation' => $attribute->get_variation(),
                        'options' => $attribute_values,
                    );
                } else {
                    $attributes[] = array(
                        'name' => $attribute->get_name(),
                        'position' => $attribute->get_position(),
                        'visible' => $attribute->get_visible(),
                        'variation' => $attribute->get_variation(),
                        'options' => $attribute->get_options(),
                    );
                }
            }
        }
        
        $data['attributes'] = $attributes;
        
        // Images
        $image_id = $product->get_image_id();
        $gallery_ids = $product->get_gallery_image_ids();
        
        $data['images'] = array();
        
        if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'full');
            $image_thumbnail = wp_get_attachment_image_url($image_id, 'thumbnail');
            
            $data['images'][] = array(
                'id' => $image_id,
                'url' => $image_url,
                'thumbnail' => $image_thumbnail,
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                'position' => 0,
            );
        }
        
        if (!empty($gallery_ids)) {
            $position = 1;
            
            foreach ($gallery_ids as $gallery_id) {
                $image_url = wp_get_attachment_image_url($gallery_id, 'full');
                $image_thumbnail = wp_get_attachment_image_url($gallery_id, 'thumbnail');
                
                $data['images'][] = array(
                    'id' => $gallery_id,
                    'url' => $image_url,
                    'thumbnail' => $image_thumbnail,
                    'alt' => get_post_meta($gallery_id, '_wp_attachment_image_alt', true),
                    'position' => $position,
                );
                
                $position++;
            }
        }
        
        // Additional data for variable products
        if ($product->is_type('variable')) {
            $data['variations'] = array();
            $variations = $product->get_available_variations();
            
            if (!empty($variations)) {
                foreach ($variations as $variation) {
                    $variation_obj = wc_get_product($variation['variation_id']);
                    
                    if ($variation_obj) {
                        $variation_data = array(
                            'id' => $variation_obj->get_id(),
                            'attributes' => $variation_obj->get_attributes(),
                            'price' => $variation_obj->get_price(),
                            'regular_price' => $variation_obj->get_regular_price(),
                            'sale_price' => $variation_obj->get_sale_price(),
                            'stock_status' => $variation_obj->get_stock_status(),
                            'stock_quantity' => $variation_obj->get_stock_quantity(),
                        );
                        
                        // Variation image
                        $variation_image_id = $variation_obj->get_image_id();
                        
                        if ($variation_image_id) {
                            $variation_data['image'] = array(
                                'id' => $variation_image_id,
                                'url' => wp_get_attachment_image_url($variation_image_id, 'full'),
                                'thumbnail' => wp_get_attachment_image_url($variation_image_id, 'thumbnail'),
                                'alt' => get_post_meta($variation_image_id, '_wp_attachment_image_alt', true),
                            );
                        }
                        
                        $data['variations'][] = $variation_data;
                    }
                }
            }
        }
        
        return $data;
    }
}