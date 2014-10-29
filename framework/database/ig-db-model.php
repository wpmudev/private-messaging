<?php

if (!class_exists('IG_DB_Model')) {

    /**
     * @author: Hoang Ngo
     */
    class IG_DB_Model extends IG_Model
    {
        public $id;
        private $toolbox;

        public function __construct()
        {
            $this->_connect();
        }

        private function  _connect()
        {
            if (!is_object(R::$toolbox)) {
                R::setup('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, false);
                R::freeze(false);
                R::setStrictTyping(false);
                //$this->toolbox = RedBean_Setup::kickstart('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, false);
            }
            return R::$toolbox;
        }

        public function save()
        {
            $this->before_save();
            if ($this->exist) {
                $saved = $this->perform_update();
            } else {
                $saved = $this->perform_insert();
            }

            if ($saved) {
                return $this->finish_save($saved);
            }
            return false;
        }

        private function perform_update()
        {
            $data = $this->export();
            unset($data['id']);
            $toolbox = $this->_connect();

            $bean = $toolbox->getRedBean()->load($this->get_table(), $this->id);
            $bean->import($data);
            $id = $toolbox->getRedBean()->store($bean);
            return $id;
        }

        private function perform_insert()
        {
            $data = $this->export();
            $toolbox = $this->_connect();

            $bean = $toolbox->getRedBean()->dispense($this->get_table());
            $bean->import($data);
            $id = $toolbox->getRedBean()->store($bean);
            return $id;
        }

        private function finish_save($saved)
        {
            //loaded the data
            $model = $this->_find($saved);
            $this->after_save();
            $this->import($model->export());
            $this->exist = true;
            $this->id = $model->id;
            return $this;
        }

        protected function before_save()
        {

        }

        protected function after_save()
        {

        }

        ///////////////////////////////////////////////////////////////

        public static function find($id)
        {
            $class = get_called_class();
            $m = new $class;
            return $m->_find($id);
        }

        public function _find($id)
        {
            $toolbox = $this->_connect();
            $bean = $toolbox->getRedBean()->load($this->get_table(), $id);
            if ($bean->id) {
                $data = $bean->export();
                $model = $this->fetch_model($data);
                return $model;
            }
            return null;
        }

        public function _all_with_condition($sql = ' 1 = 1 ', $data = array())
        {
            $toolbox = $this->_connect();
            //$beans = $toolbox->getRedBean()->find($this->get_table(), ' '.$sql.' ', $data);
            $beans = R::find($this->get_table(), ' ' . $sql . ' ', $data);
            $models = array();
            foreach ($beans as $bean) {
                $models[] = $this->fetch_model($bean);
            }
            return $models;
        }

        public static function all_with_condition($sql = '', $data = array())
        {
            $class = get_called_class();
            $m = new $class;
            return $m->_all_with_condition($sql, $data);
        }

        private function fetch_model($data)
        {
            $class = get_called_class();
            $model = new $class;
            $model->import($data);
            $model->exist = true;
            return $model;
        }

        function delete()
        {
            $toolbox = $this->_connect();
            $bean = $toolbox->getRedBean()->load($this->get_table(), $this->id);
            $toolbox->getRedBean()->trash($bean);

        }

        function get_table()
        {
            global $wpdb;

            return $wpdb->prefix . $this->table;
        }
    }
}