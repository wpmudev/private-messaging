<?php

/**
 * Author: WPMU DEV
 * Name: Notification (Beta)
 * Description: Display a small message for notify user when new messages arrived.
 */
if (!class_exists('MM_Push_Notification')) {
    class MM_Push_Notification extends IG_Request
    {
        public function __construct()
        {
            add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
            add_action('mm_message_sent', array(&$this, 'index_new_message'));
            add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
            add_action('wp_footer', array(&$this, 'js'));
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
            /*
            foreach ($unreads as $unread) {
                $m = MM_Message_Model::model()->find_one_by_attributes(array(
                    'conversation_id' => $unread->id,
                    'send_to' => $model->send_to,
                ), 'ID DESC');

                $message = array(
                    'id' => $m->id,
                    'from' => $m->get_name($m->send_from),
                    'subject' => $m->subject,
                    'text' => mmg()->trim_text($m->content, 100)
                );
                $cache['messages'][] = $message;
            }*/

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
            wp_enqueue_script('mm-eventsources', plugin_dir_url(__FILE__) . "notification/assets/eventsources.js");
        }

        function js()
        {
            if (is_user_logged_in()) {
                global $current_user;
                ?>
                <script type="text/javascript">
                    jQuery(function ($) {
                        var url = "<?php echo plugin_dir_url(__FILE__) ?>notification/process.php?key=<?php echo $current_user->user_login ?>&wpnonce=<?php echo wp_create_nonce(get_current_user_id()) ?>";
                        var es = new EventSource(url);
                        var listener = function (event) {
                            if (event.type == 'message' && event.data != undefined) {
                                var data = event.data.replace('};', '}');
                                data = jQuery.parseJSON(data);
                                jQuery('.mm-admin-bar').find('span').text(data.count);
                                if (jQuery('.unread-count').size() > 0) {
                                    jQuery('.unread-count').attr('data-original-title', data.count + ' ' + jQuery('.unread-count').data('text'));
                                }
                                jQuery.each(data.messages, function (i, v) {
                                    var text = "From: " + v.from + "<br/>" + v.subject + "<br/>" + v.text;
                                    var n = noty({
                                        text: text,
                                        'theme': 'relax',
                                        dismissQueue: true,
                                        'type': 'success',
                                        'layout': 'topRight',
                                        maxVisible: 5,
                                        closeWith: ['click'],
                                        buttons: [
                                            {
                                                addClass: 'mmessage-container btn btn-primary btn-xs',
                                                text: 'View', onClick: function ($noty) {
                                                $noty.close();
                                                var url = '<?php echo get_permalink(mmg()->setting()->inbox_page) ?>&box=unread';
                                                location.href= url;
                                            }
                                            }
                                        ]
                                    });
                                })
                            }
                        };
                        es.addEventListener("open", listener);
                        es.addEventListener("message", listener);
                        es.addEventListener("error", listener);
                    })
                </script>
            <?php
            }
        }
    }
}

new MM_Push_Notification();