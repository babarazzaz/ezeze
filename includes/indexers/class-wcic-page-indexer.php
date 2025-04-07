<?php
/**
 * The page indexer functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/indexers
 */

/**
 * The page indexer functionality of the plugin.
 *
 * Handles indexing of WordPress pages and posts for the chatbot.
 *
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/indexers
 */
class WCIC_Page_Indexer {

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Add hooks for page/post updates
        add_action('save_post', array($this, 'index_post'), 10, 3);
        add_action('delete_post', array($this, 'delete_post_index'));
        
        // Add hook for scheduled indexing
        add_action('wcic_index_pages', array($this, 'index_all_pages'));
    }

    /**
     * Index a post when it's saved or updated.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    WP_Post   $post       The post object.
     * @param    bool      $update     Whether this is an existing post being updated.
     */
    public function index_post($post_id, $post, $update) {
        // Skip revisions and autosaves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Skip if not a page or post
        if (!in_array($post->post_type, array('page', 'post'))) {
            return;
        }
        
        // Skip if not published
        if ($post->post_status !== 'publish') {
            return;
        }
        
        // Index the post
        $this->index_post_by_id($post_id);
    }

    /**
     * Index a post by its ID.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @return   bool                  Whether the indexing was successful.
     */
    public function index_post_by_id($post_id) {
        // Get the post
        $post = get_post($post_id);
        
        if (!$post) {
            return false;
        }
        
        // Skip if not a page or post
        if (!in_array($post->post_type, array('page', 'post'))) {
            return false;
        }
        
        // Skip if not published
        if ($post->post_status !== 'publish') {
            return false;
        }
        
        // Extract post data
        $post_data = $this->extract_post_data($post);
        
        // Save to database
        global $wpdb;
        
        // Check if post already exists in index
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wcic_page_index WHERE post_id = %d",
                $post_id
            )
        );
        
        if ($existing) {
            // Update existing record
            $result = $wpdb->update(
                $wpdb->prefix . 'wcic_page_index',
                array(
                    'content_data' => json_encode($post_data),
                    'last_updated' => current_time('mysql')
                ),
                array('post_id' => $post_id)
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $wpdb->prefix . 'wcic_page_index',
                array(
                    'post_id' => $post_id,
                    'post_type' => $post->post_type,
                    'content_data' => json_encode($post_data),
                    'last_updated' => current_time('mysql')
                )
            );
        }
        
        return $result !== false;
    }

    /**
     * Delete a post from the index.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @return   bool                  Whether the deletion was successful.
     */
    public function delete_post_index($post_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'wcic_page_index',
            array('post_id' => $post_id)
        );
        
        return $result !== false;
    }

    /**
     * Index all pages and posts.
     *
     * @since    1.0.0
     * @return   int|bool    The number of pages indexed, or false on failure.
     */
    public function index_all_pages() {
        // Get all published pages and posts
        $args = array(
            'post_type' => array('page', 'post'),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        $posts = get_posts($args);
        
        if (empty($posts)) {
            return 0;
        }
        
        $success_count = 0;
        
        foreach ($posts as $post_id) {
            $result = $this->index_post_by_id($post_id);
            
            if ($result) {
                $success_count++;
            }
        }
        
        return $success_count;
    }

    /**
     * Extract data from a post for indexing.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     * @return   array               The extracted post data.
     */
    private function extract_post_data($post) {
        // Basic post data
        $data = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $this->clean_content($post->post_content),
            'excerpt' => $post->post_excerpt,
            'type' => $post->post_type,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'permalink' => get_permalink($post->ID),
        );
        
        // Featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        
        if ($thumbnail_id) {
            $data['featured_image'] = array(
                'id' => $thumbnail_id,
                'url' => wp_get_attachment_image_url($thumbnail_id, 'full'),
                'thumbnail' => wp_get_attachment_image_url($thumbnail_id, 'thumbnail'),
                'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
            );
        }
        
        // Categories and tags for posts
        if ($post->post_type === 'post') {
            // Categories
            $categories = array();
            $category_ids = wp_get_post_categories($post->ID);
            
            if (!empty($category_ids)) {
                foreach ($category_ids as $category_id) {
                    $category = get_category($category_id);
                    
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
            $tag_ids = wp_get_post_tags($post->ID, array('fields' => 'ids'));
            
            if (!empty($tag_ids)) {
                foreach ($tag_ids as $tag_id) {
                    $tag = get_tag($tag_id);
                    
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
        }
        
        return $data;
    }

    /**
     * Clean post content for indexing.
     *
     * @since    1.0.0
     * @param    string    $content    The post content.
     * @return   string                The cleaned content.
     */
    private function clean_content($content) {
        // Remove shortcodes
        $content = strip_shortcodes($content);
        
        // Remove HTML tags
        $content = wp_strip_all_tags($content);
        
        // Decode HTML entities
        $content = html_entity_decode($content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        return $content;
    }
}