<?php

/**
 * Author: WPMU DEV
 * Name: Notifications
 * Description: Display a visual notification for users when a new message is received.
 */
if (!class_exists('MM_Push_Notification')) {
    class MM_Push_Notification extends IG_Request
    {
        public function __construct()
        {
            if (is_user_logged_in()) {
                add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
                add_action('mm_message_sent', array(&$this, 'index_new_message'));
                add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
                add_action('wp_ajax_mm_push_notification', array(&$this, 'has_message'));
                add_action('wp_footer', array(&$this, 'js'));
            }
        }

        function has_message()
        {
            if (!wp_verify_nonce(mmg()->get('_wpnonce'), get_current_user_id())) {
                return;
            }
            $time = 0;
            $cache = get_user_meta(get_current_user_id(), "mm_notification", true);
            if ($cache == false) {
                $cache = array('status' => 0);
            }
            if ($cache['status'] == 1) {
                echo json_encode($cache);
                $cache['status'] = 0;
                update_user_meta(get_current_user_id(), "mm_notification", $cache);
                exit;
            }
            exit;
        }

        function index_new_message(MM_Message_Model $model)
        {
            $key = "mm_notification";
            delete_user_meta($model->send_to, $key);
            $cache = array();
            $cache['status'] = 1;
            //clean up messages
            $cache['messages'] = array();
            $unreads = MM_Conversation_Model::get_unread($model->send_to);

            $message = array(
                'id' => $model->id,
                'from' => $model->get_name($model->send_from),
                'subject' => $model->subject,
                'text' => mmg()->trim_text($model->content, 100)
            );
            $cache['messages'][] = $message;

            $cache['count'] = count($unreads);
            add_user_meta($model->send_to, $key, $cache);
        }

        function scripts()
        {
            wp_enqueue_script('mm-noty', plugin_dir_url(__FILE__) . "notification/assets/noty/packaged/jquery.noty.packaged.js", array('jquery'));
        }

        function js()
        {
            if (is_user_logged_in()) {
                global $current_user;
                ?>
                <script type="text/javascript">
                    jQuery(function ($) {
                        function poll() {
                            setTimeout(function () {
                                $.ajax({
                                    url: '<?php echo admin_url('admin-ajax.php') ?>',
                                    data: {
                                        _wpnonce: '<?php echo wp_create_nonce(get_current_user_id()) ?>',
                                        action: 'mm_push_notification'
                                    },
                                    success: function (data) {
                                        if (data != null && data.count != undefined) {
                                            $('.mm-admin-bar').find('span').text(data.count);
                                            if ($('.unread-count').size() > 0) {
                                                $('.unread-count').attr('title', data.count + ' ' + $('.unread-count').data('text'));
                                            }
                                            jQuery.each(data.messages, function (i, v) {
                                                var text = "From: " + v.from + "<br/>" + v.subject + "<br/>" + v.text;
                                                var n = noty({
                                                    text: text,
                                                    'theme': 'relax',
                                                    template: '<div class="noty_message"><span class="noty_text"></span><div class="noty_close"></div></div>',
                                                    dismissQueue: true,
                                                    'type': 'alert',
                                                    'layout': 'topRight',
                                                    maxVisible: 5,
                                                    closeWith: ['click'],
                                                    buttons: [
                                                        {
                                                            addClass: 'btn btn-primary btn-xs',
                                                            text: 'View', onClick: function ($noty) {
                                                            var url = '<?php echo get_permalink(mmg()->setting()->inbox_page) ?>?box=unread';
                                                            location.href = url;
                                                        }
                                                        },
                                                        {
                                                            addClass: 'btn btn-danger btn-xs',
                                                            text: 'Close', onClick: function ($noty) {
                                                            $noty.close();
                                                            }
                                                        }
                                                    ],
                                                    animation: {
                                                        open: {height: 'toggle'}, // jQuery animate function property object
                                                        close: {height: 'toggle'}, // jQuery animate function property object
                                                        easing: 'swing', // easing
                                                        speed: 500 // opening & closing animation speed
                                                    },
                                                    callback: {
                                                        onShow: function() {
                                                            $('.noty_buttons').addClass('ig-container');
                                                        }
                                                    }
                                                });
                                            })
                                        }
                                        //Setup the next poll recursively
                                        poll();
                                    }, dataType: "json"
                                });
                            }, 10000);
                        };
                        poll();
                    })
                </script>
            <?php
            }
        }
    }
}

new MM_Push_Notification();