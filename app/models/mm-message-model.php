<?php

/**
 * Author: Hoang Ngo
 */
class MM_Message_Model extends IG_Post_Model
{
    const UNREAD = 'unread', READ = 'read';
    protected $table = 'mm_message';

    public $id;
    public $subject;
    public $content;
    public $send_to;
    public $send_from;
    public $reply_to;
    public $status;
    public $date;

    public $post_status;

    public $attachment;

    public $conversation_id;


    protected $mapped = array(
        'id' => 'ID',
        'subject' => 'post_title',
        'send_from' => 'post_author',
        'reply_to' => 'post_parent',
        'content' => 'post_content',
        'date' => 'post_date',
        'post_status' => 'post_status'
    );

    protected $relations = array(
        array(
            'type' => 'meta',
            'key' => '_send_to',
            'map' => 'send_to'
        ),
        array(
            'type' => 'meta',
            'key' => '_attachment',
            'map' => 'attachment'
        ),
        array(
            'type' => 'meta',
            'key' => '_conversation_id',
            'map' => 'conversation_id'
        ),
        array(
            'type' => 'meta',
            'key' => '_status',
            'map' => 'status'
        ),
    );

    public function before_validate()
    {
        if (fRequest::get('is_reply', 'int', 0) == 1) {
            $this->rules = array(
                'content' => 'required',
            );
        } else {
            $this->rules = array(
                'subject' => 'required',
                'content' => 'required',
                'send_to' => 'required'
            );
        }
    }

    public function before_save()
    {
        if (!$this->exist) {
            if (empty($this->send_from)) {
                $this->send_from = get_current_user_id();
            }
            $this->post_status = 'publish';
        }
    }

    public function get_name($user_id)
    {
        $userdata = get_userdata($user_id);
        $name = $userdata->user_info . ' ' . $userdata->last_name;
        $name = trim($name);
        if (!empty($name)) {
            return $name;
        }
        return $userdata->user_login;
    }

    /*function after_save()
    {
        $c = MM_Conversation_Model::model()->find($this->conversation_id);
        //clear cache
        //create status for this message
        $sent_status = new MM_Message_Status_Model();
        $sent_status->conversation_id = $c->id;
        $sent_status->message_id = $this->id;
        $sent_status->status = MM_Message_Status_Model::STATUS_UNREAD;
        $sent_status->user_id = $this->send_to;
        $sent_status->type = MM_Message_Status_Model::TYPE_MESSAGE;
        $sent_status->save();
        //
        $from_status = new MM_Message_Status_Model();
        $from_status->conversation_id = $c->id;
        $from_status->message_id = $this->id;
        $from_status->status = MM_Message_Status_Model::STATUS_UNREAD;
        $from_status->user_id = $this->send_from;
        $from_status->type = MM_Message_Status_Model::TYPE_MESSAGE;
        $from_status->save();
    }*/

    public static function model($class_name = __CLASS__)
    {
        return parent::model($class_name);
    }
}