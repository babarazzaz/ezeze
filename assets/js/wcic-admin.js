/**
 * Admin JavaScript for the WC Intelligent Chatbot plugin.
 *
 * @since      1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab navigation
        $('.wcic-admin-wrap .nav-tab').on('click', function(e) {
            e.preventDefault();
            
            // Hide all tabs
            $('.wcic-settings-tab').hide();
            
            // Remove active class from all tabs
            $('.nav-tab').removeClass('nav-tab-active');
            
            // Show the selected tab
            $($(this).attr('href')).show();
            
            // Add active class to the clicked tab
            $(this).addClass('nav-tab-active');
        });
        
        // Initialize color pickers
        if ($.fn.wpColorPicker) {
            $('.wcic-color-picker').wpColorPicker();
        }
        
        // Toggle excluded pages field
        $('input[name="wcic_display_on_pages"]').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#wcic-excluded-pages').show();
            } else {
                $('#wcic-excluded-pages').hide();
            }
        });
        
        // Test AI connection
        $('#wcic-test-ai-connection').on('click', function() {
            var api_key = $('input[name="wcic_openai_api_key"]').val();
            var result_span = $('#wcic-connection-result');
            
            if (!api_key) {
                result_span.html('<span style="color: red;">Please enter an API key first.</span>');
                return;
            }
            
            result_span.html('<span style="color: blue;">' + wcic_admin_params.i18n.testing_connection + '</span>');
            
            $.ajax({
                url: wcic_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcic_test_ai_connection',
                    nonce: wcic_admin_params.nonce,
                    api_key: api_key
                },
                success: function(response) {
                    if (response.success) {
                        result_span.html('<span style="color: green;">' + response.data.message + '</span>');
                    } else {
                        result_span.html('<span style="color: red;">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    result_span.html('<span style="color: red;">Connection error. Please try again.</span>');
                }
            });
        });
        
        // Index products
        $('#wcic-index-products').on('click', function() {
            var button = $(this);
            var result_span = $('#wcic-product-indexing-result');
            
            button.prop('disabled', true);
            result_span.html('<span style="color: blue;">' + wcic_admin_params.i18n.indexing_products + '</span>');
            
            $.ajax({
                url: wcic_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcic_manual_index_products',
                    nonce: wcic_admin_params.nonce
                },
                success: function(response) {
                    button.prop('disabled', false);
                    
                    if (response.success) {
                        result_span.html('<span style="color: green;">' + response.data.message + '</span>');
                        // Refresh the page after a short delay to update stats
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        result_span.html('<span style="color: red;">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    button.prop('disabled', false);
                    result_span.html('<span style="color: red;">' + wcic_admin_params.i18n.indexing_error + '</span>');
                }
            });
        });
        
        // Index pages
        $('#wcic-index-pages').on('click', function() {
            var button = $(this);
            var result_span = $('#wcic-page-indexing-result');
            
            button.prop('disabled', true);
            result_span.html('<span style="color: blue;">' + wcic_admin_params.i18n.indexing_pages + '</span>');
            
            $.ajax({
                url: wcic_admin_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcic_manual_index_pages',
                    nonce: wcic_admin_params.nonce
                },
                success: function(response) {
                    button.prop('disabled', false);
                    
                    if (response.success) {
                        result_span.html('<span style="color: green;">' + response.data.message + '</span>');
                        // Refresh the page after a short delay to update stats
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        result_span.html('<span style="color: red;">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    button.prop('disabled', false);
                    result_span.html('<span style="color: red;">' + wcic_admin_params.i18n.indexing_error + '</span>');
                }
            });
        });
        
        // View conversation details
        $('.wcic-view-conversation').on('click', function(e) {
            e.preventDefault();
            
            var session_id = $(this).data('session');
            var modal = $('#wcic-conversation-modal');
            var details_container = $('#wcic-conversation-details');
            
            details_container.html('<p>Loading conversation...</p>');
            modal.show();
            
            $.ajax({
                url: wcic_admin_params.ajax_url,
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
                            html = '<p>No messages found for this conversation.</p>';
                        } else {
                            $.each(response.data.messages, function(index, message) {
                                var messageClass = message.is_user ? 'wcic-user-message' : 'wcic-bot-message';
                                
                                html += '<div class="wcic-conversation-message ' + messageClass + '">';
                                html += '<div class="wcic-message-content">' + message.message + '</div>';
                                
                                if (message.recommendations && message.recommendations.length > 0) {
                                    html += '<div class="wcic-recommendations">';
                                    html += '<h4>Recommendations</h4>';
                                    
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
                        details_container.html('<p>Error loading conversation.</p>');
                    }
                },
                error: function() {
                    details_container.html('<p>Error loading conversation. Please try again.</p>');
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

})(jQuery);