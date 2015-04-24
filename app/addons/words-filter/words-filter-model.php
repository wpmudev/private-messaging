<?php

/**
 * @author:Hoang Ngo
 */
class Words_Filter_Model extends IG_Option_Model
{
    public $replacer = '*';
    public $block_list = array();

    public $table = 'mm_words_filter';

    public function __construct()
    {
        parent::__construct();
        if (!is_array($this->block_list)) {
            $this->block_list = array();
        }
    }
}