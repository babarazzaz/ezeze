<?php
/**
 * Admin conversations page for the plugin.
 *
 * @since      1.0.0
 * @package    WC_Intelligent_Chatbot
 * @subpackage WC_Intelligent_Chatbot/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get conversations
global $wpdb;
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

$total_conversations = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM {$wpdb->prefix}wcic_conversations");
$total_pages = ceil($total_conversations / $per_page);

$conversations = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT session_id, MIN(created_at) as started_at, MAX(created_at) as last_message, 
        COUNT(*) as message_count, MAX(user_id) as user_id
        FROM {$wpdb->prefix}wcic_conversations 
        GROUP BY session_id
        ORDER BY last_message DESC
        LIMIT %d OFFSET %d",
        $per_page,
        $offset
    )
);
?>

<div class="wrap wcic-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wcic-conversations-stats">
        <div class="wcic-stat-box">
            <h3><?php _e('Conversation Statistics', 'wc-intelligent-chatbot'); ?></h3>
            <p><?php printf(__('Total Conversations: %d', 'wc-intelligent-chatbot'), $total_conversations); ?></p>
            <?php 
            $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wcic_conversations");
            $avg_messages = $total_conversations > 0 ? round($total_messages / $total_conversations, 1) : 0;
            ?>
            <p><?php printf(__('Total Messages: %d', 'wc-intelligent-chatbot'), $total_messages); ?></p>
            <p><?php printf(__('Average Messages per Conversation: %s', 'wc-intelligent-chatbot'), $avg_messages); ?></p>
            
            <?php 
            $registered_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}wcic_conversations WHERE user_id > 0");
            $guest_users = $total_conversations - $registered_users;
            ?>
            <p><?php printf(__('Registered Users: %d', 'wc-intelligent-chatbot'), $registered_users); ?></p>
            <p><?php printf(__('Guest Users: %d', 'wc-intelligent-chatbot'), $guest_users); ?></p>
        </div>
    </div>
    
    <div class="wcic-conversations-list">
        <h3><?php _e('Recent Conversations', 'wc-intelligent-chatbot'); ?></h3>
        
        <?php if (empty($conversations)): ?>
            <p><?php _e('No conversations found.', 'wc-intelligent-chatbot'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Session ID', 'wc-intelligent-chatbot'); ?></th>
                        <th><?php _e('User', 'wc-intelligent-chatbot'); ?></th>
                        <th><?php _e('Started', 'wc-intelligent-chatbot'); ?></th>
                        <th><?php _e('Last Message', 'wc-intelligent-chatbot'); ?></th>
                        <th><?php _e('Messages', 'wc-intelligent-chatbot'); ?></th>
                        <th><?php _e('Actions', 'wc-intelligent-chatbot'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($conversations as $conversation): ?>
                        <tr>
                            <td><?php echo esc_html(substr($conversation->session_id, 0, 10) . '...'); ?></td>
                            <td>
                                <?php if ($conversation->user_id): ?>
                                    <?php 
                                    $user = get_user_by('id', $conversation->user_id);
                                    echo $user ? esc_html($user->display_name) : __('Unknown User', 'wc-intelligent-chatbot');
                                    ?>
                                <?php else: ?>
                                    <?php _e('Guest', 'wc-intelligent-chatbot'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($conversation->started_at)); ?></td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($conversation->last_message)); ?></td>
                            <td><?php echo intval($conversation->message_count); ?></td>
                            <td>
                                <a href="#" class="button button-small wcic-view-conversation" data-session="<?php echo esc_attr($conversation->session_id); ?>"><?php _e('View', 'wc-intelligent-chatbot'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php printf(
                                _n('%s conversation', '%s conversations', $total_conversations, 'wc-intelligent-chatbot'),
                                number_format_i18n($total_conversations)
                            ); ?>
                        </span>
                        
                        <span class="pagination-links">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page
                            ));
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Conversation Detail Modal -->
    <div id="wcic-conversation-modal" style="display: none;">
        <div class="wcic-modal-content">
            <span class="wcic-modal-close">&times;</span>
            <h3><?php _e('Conversation Details', 'wc-intelligent-chatbot'); ?></h3>
            <div id="wcic-conversation-details"></div>
        </div>
    </div>
</div>

<style>
.wcic-conversations-stats {
    margin-bottom: 30px;
}

.wcic-stat-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    border-radius: 5px;
}

.wcic-conversations-list {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 30px;
}

/* Modal Styles */
#wcic-conversation-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.wcic-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    border-radius: 5px;
    position: relative;
    max-height: 80vh;
    overflow-y: auto;
}

.wcic-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.wcic-modal-close:hover,
.wcic-modal-close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.wcic-conversation-message {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
}

.wcic-user-message {
    background-color: #f0f0f0;
    margin-left: 20%;
    margin-right: 0;
}

.wcic-bot-message {
    background-color: #e6f2ff;
    margin-right: 20%;
    margin-left: 0;
}

.wcic-message-time {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.wcic-recommendations {
    margin-top: 10px;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 5px;
}

.wcic-recommendation-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.wcic-recommendation-image {
    width: 50px;
    height: 50px;
    margin-right: 10px;
}

.wcic-recommendation-details {
    flex: 1;
}

.wcic-recommendation-title {
    font-weight: bold;
}

.wcic-recommendation-price {
    color: #777;
}
</style>

<script>
jQuery(document).ready(function($) {
    // View conversation details
    $('.wcic-view-conversation').on('click', function(e) {
        e.preventDefault();
        
        var session_id = $(this).data('session');
        var modal = $('#wcic-conversation-modal');
        var details_container = $('#wcic-conversation-details');
        
        details_container.html('<p><?php _e('Loading conversation...', 'wc-intelligent-chatbot'); ?></p>');
        modal.show();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wcic_get_conversation',
                nonce: wcic_admin_params.nonce,
                session_id: session_id
            },
            success: function(response) {
                if (response.success) {
                    var html = '';
                    
                    if (response.data.messages.length === 0) {
                        html = '<p><?php _e('No messages found for this conversation.', 'wc-intelligent-chatbot'); ?></p>';
                    } else {
                        $.each(response.data.messages, function(index, message) {
                            var messageClass = message.is_user ? 'wcic-user-message' : 'wcic-bot-message';
                            
                            html += '<div class="wcic-conversation-message ' + messageClass + '">';
                            html += '<div class="wcic-message-content">' + message.message + '</div>';
                            
                            if (message.recommendations && message.recommendations.length > 0) {
                                html += '<div class="wcic-recommendations">';
                                html += '<h4><?php _e('Recommendations', 'wc-intelligent-chatbot'); ?></h4>';
                                
                                $.each(message.recommendations, function(i, rec) {
                                    html += '<div class="wcic-recommendation-item">';
                                    
                                    if (rec.image) {
                                        html += '<img src="' + rec.image + '" class="wcic-recommendation-image" />';
                                    }
                                    
                                    html += '<div class="wcic-recommendation-details">';
                                    html += '<div class="wcic-recommendation-title">' + rec.title + '</div>';
                                    
                                    if (rec.price) {
                                        html += '<div class="wcic-recommendation-price">' + rec.price + '</div>';
                                    }
                                    
                                    html += '</div>'; // End details
                                    html += '</div>'; // End item
                                });
                                
                                html += '</div>'; // End recommendations
                            }
                            
                            html += '<div class="wcic-message-time">' + message.time + '</div>';
                            html += '</div>'; // End message
                        });
                    }
                    
                    details_container.html(html);
                } else {
                    details_container.html('<p><?php _e('Error loading conversation.', 'wc-intelligent-chatbot'); ?></p>');
                }
            },
            error: function() {
                details_container.html('<p><?php _e('Error loading conversation. Please try again.', 'wc-intelligent-chatbot'); ?></p>');
            }
        });
    });
    
    // Close modal
    $('.wcic-modal-close').on('click', function() {
        $('#wcic-conversation-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        var modal = $('#wcic-conversation-modal');
        if (e.target === modal[0]) {
            modal.hide();
        }
    });
});
</script>