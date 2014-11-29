<?php

/**
 * @author:Hoang Ngo
 */
class MM_Message_Status_Model extends IG_DB_Model_Ex
{
    const STATUS_UNREAD = 0, STATUS_READ = 1, STATUS_ARCHIVE = -1, STATUS_DELETE = -2;
    const TYPE_MESSAGE = 1, TYPE_CONVERSATION = 2;

    protected $table = "mm_status";

    public $id;
    public $conversation_id;
    public $message_id;
    public $user_id;
    public $status;
    public $date_created;
    public $type;

    function get_table()
    {
        global $wpdb;

        return $wpdb->base_prefix . $this->table;
    }

    function before_save()
    {
        $this->date_created = date('Y-m-d H:i:s');
    }

    public static function model($class_name = __CLASS__)
    {
        return parent::model($class_name);
    }
}