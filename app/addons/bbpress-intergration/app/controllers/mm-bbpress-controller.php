<?php

/**
 * @author:Hoang Ngo
 */
class MM_BBPress_Controller extends IG_Request
{
    public function __construct()
    {
        add_filter('bbp_get_reply_author_link', array(&$this, 'append_message_trigger'), 10, 2);
    }



    function append_message_trigger($author_link, $r)
    {
        //we need to make sure it is a reply
        if (!bbp_get_reply_id()) {
            return $author_link;
        }
        wp_enqueue_script('popoverasync', ig_uploader()->plugin_url . 'assets/popover/popoverasync.js', array(
            'jquery', 'ig-bootstrap', 'jquery-frame-transport'));
        wp_enqueue_style('igu-uploader', ig_uploader()->plugin_url . 'assets/style.css');
        $new_links = explode($r['sep'], $author_link);
        $author_id = bbp_get_reply_author_id(bbp_get_reply_id($r['post_id']));
        $message_link = do_shortcode("[pm_user user_id=$author_id class='btn btn-xs btn-primary']");
        $message_link = sprintf('<div style="%s">%s</div>', 'margin-top:5px', $message_link);
        array_splice($new_links, 1, 0, array($message_link));

        $new_links = implode($r['sep'], $new_links);

        //$logger = new IG_Logger('file', 'message_bbpress.txt');
        //$logger->log(var_export($new_links, true), IG_Logger::ERROR_LEVEL_DEBUG);

        return $new_links;
    }
}