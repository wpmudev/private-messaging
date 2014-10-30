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

    function after_save()
    {
        $c = MM_Conversation_Model::find($this->conversation_id);
        $c->update_count();
    }
}