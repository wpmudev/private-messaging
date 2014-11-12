<?php

/**
 * This class is use for extend only. This is a modal design for a worpdress post type
 * Support CRUD, validation and a simple load method. Better to use in something like Setting.
 *
 * $table variable will use as option_name
 *
 * @author: Hoang Ngo
 * @package: Database
 */
if (!class_exists('IG_Option_Model')) {
    class IG_Option_Model extends IG_Model
    {
        /**
         * constructor
         */
        public function __construct()
        {
            $this->load();
        }

        /**
         * This will save all this export() to table name
         */
        public function save()
        {
            update_option($this->get_table(), $this->export());
        }

        /**
         * Load the option data and fetch to this model
         */
        public function load()
        {
            $data = get_option($this->get_table());
            if ($data) {
                $this->import($data);
            }
        }
    }
}