<?php

/**
 * Author: Hoang Ngo
 */
class IG_Option_Model extends IG_Model
{
    public function __construct()
    {
        $this->load();
    }

    public function save()
    {
        update_option($this->table, $this->export());
    }

    public function load()
    {
        $data = get_option($this->table);
        if ($data) {
            $this->import($data);
        }
    }
}