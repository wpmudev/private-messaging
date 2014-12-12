<?php

/**
 * Author: Hoang Ngo
 */
class MM_Setting_Model extends IG_Option_Model
{
    protected $table = 'mm_settings';

    public $noti_subject = '';
    public $noti_content = '';

    public $receipt_subject = "";
    public $receipt_content = "";

    public $enable_receipt = 1;
    public $user_receipt = 1;

    public $per_page = 10;

    public $signup_text = "Sign up to become a registered member of the site";

    public $plugins;

    public $inbox_page;

    public $allow_attachment = false;

    public function __construct()
    {
        $this->noti_subject = "You've received a new message from FROM_NAME on SITE_NAME";
        $this->noti_content = "FROM_NAME has sent you a message on SITE_NAME<br/><br/>

        FROM_MESSAGE
        <br/><br/>
        Check your messages here <a href='POST_LINK'>POST_LINK</a>
        ";

        $this->receipt_content = "Dear FROM_NAME <br/><br/>
        The message you sent to TO_NAME on SITE_NAME has been read.";
        $this->receipt_subject = "The message you sent to TO_NAME on SITE_NAME has been read.";
        parent::__construct();
    }
}