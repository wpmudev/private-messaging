<?php

/**
 * Author: hoangngo
 */
class Notify_Controller extends IG_Request
{
    public function __construct()
    {
        add_action('mm_message_sent', array(&$this, 'sent_notification'));
        add_action('mm_conversation_read', array(&$this, 'read_notification'));
    }

    function read_notification(MM_Conversation_Model $model)
    {
        $setting = new MM_Setting_Model();
        $setting->load();
        $is_send = $setting->enable_receipt;
        //getting the messegers from this conv belong to this user
        if (!count($model->get_unread()))
            return;
        $messeger = $model->get_last_message();
        //check if this current user is sender
        if ($messeger->send_from == get_current_user_id())
            return;

        if (!$is_send)
            return;
        if ($setting->user_receipt == true) {
            //check does user enable
            $sender_setting = get_user_meta($messeger->send_from, 'messages_user_setting', true);
            if (!$sender_setting) {
                $sender_setting = array(
                    'enable_receipt' => 1,
                    'prevent_receipt' => 0
                );
            }
            if ($sender_setting['enable_receipt'] != true) {
                //user don't enable it,
                return;
            }
            //user enable it, checking does the receiver block it
            $reciver_setting = get_user_meta($messeger->send_to, 'messages_user_setting', true);
            if (!$reciver_setting) {
                $reciver_setting = array(
                    'enable_receipt' => 1,
                    'prevent_receipt' => 0
                );
            }
            if ($reciver_setting['prevent_receipt'] == true) {
                //this user has block it, return
                return;
            }
        }

        //from here, we can send notification
        $data = array(
            'SITE_NAME' => get_bloginfo('name'),
            'FROM_NAME' => $messeger->get_name($messeger->send_from),
            'POST_LINK' => add_query_arg('message_id', $model->id, home_url()),
            'FROM_MESSAGE' => $messeger->content,
            'TO_NAME' => $messeger->get_name($messeger->send_to),
        );
        $data = apply_filters('message_notification_params', $data, $this);


        $subject = $setting->receipt_subject;
        $content = $setting->receipt_content;
        foreach ($data as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $content = str_replace($key, $val, $content);
        }
        $sendto = get_userdata($messeger->send_from);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );
        wp_mail($sendto->user_email, $subject, $content, $headers);
    }

    function sent_notification(MM_Message_Model $model)
    {
        //send message
        $setting = new MM_Setting_Model();
        $setting->load();
        $data = array(
            'SITE_NAME' => get_bloginfo('name'),
            'FROM_NAME' => $model->get_name($model->send_from),
            'POST_LINK' => add_query_arg('message_id', $model->id, home_url()),
            'FROM_MESSAGE' => $model->content
        );
        $data = apply_filters('message_notification_params', $data, $this);

        $subject = $setting->noti_subject;
        $content = $setting->noti_content;
        foreach ($data as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $content = str_replace($key, $val, $content);
        }
        $sendto = get_userdata($model->send_to);
        $from = get_userdata($model->send_from);
        //prepare atachments
        $attachments = array();
        if ($model->attachment) {
            $ids = explode(',', $model->attachment);
            $ids = array_filter($ids);
            foreach ($ids as $id) {
                if (filter_var($id, FILTER_VALIDATE_INT)) {
                    $upload = IG_Uploader_Model::find($id);

                    if (is_object($upload) && $upload->file) {
                        $attachments[] = get_attached_file($upload->file);
                    }
                }
            }
        }
        $headers = array(
            'From: ' . $model->get_name($model->send_from) . ' <' . $from->user_email . '>',
            'Content-Type: text/html; charset=UTF-8'
        );

        wp_mail($sendto->user_email, $subject, $content, $headers, $attachments);
    }
}