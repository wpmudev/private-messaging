<?php

/**
 * @author:Hoang Ngo
 */
if (!class_exists('IG_DB_Model_Ex')) {
    class IG_DB_Model_Ex extends IG_Model
    {
        public $id;

        /**
         * @var FluentPDO
         */
        private $driver;
        /**
         * @var array Model indexer
         */
        private static $_models = array();

        public function __connect()
        {
            if (!is_object($this->driver)) {
                $host = DB_HOST;
                $user = DB_USER;
                $password = DB_PASSWORD;
                $db = DB_NAME;
                $port = explode(':', $host);
                if (count($port) == 1) {
                    $port = 3306;
                } else {
                    $port = $port[1];
                }
                $pdo = new PDO("mysql:dbname=" . $db . ";host=" . $host . ";port=" . $port, $user, $password);
                $this->driver = new FluentPDO($pdo);
            }

            return $this->driver;
        }

        /**
         * @return bool|IG_DB_Model
         */
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

        /**
         * @return int|string
         */
        private function perform_update()
        {
            $data = $this->export();
            unset($data['id']);
            $data = $this->esc_db_field($data);
            $driver = $this->__connect();
            $query = $driver->update($this->get_table())->set($data)->where('id', $this->id);
            $query->execute();
            return $this->id;
        }

        /**
         * @return int|string
         */
        private function perform_insert()
        {
            $data = $this->export();
            $data = $this->esc_db_field($data);
            $driver = $this->__connect();
            $query = $driver->insertInto($this->get_table(), $data);
            $id = $query->execute();
            return $id;
        }

        private function esc_db_field($data)
        {
            $new_data = array();
            foreach ($data as $key => $val) {
                $new_data["`$key`"] = $val;
            }
            return $new_data;
        }

        /**
         * @param $saved
         *
         * @return $this
         */
        private function finish_save($saved)
        {
            //loaded the data
            $model = $this->find($saved);
            $this->after_save();
            $this->import($model->export());
            $this->exist = true;
            $this->id = $model->id;

            return $this;
        }

        /**
         * Addition actions before saving a model, eg: update date create
         */
        protected function before_save()
        {

        }

        /**
         * Addition actions after saving a model, eg another dependency of this model
         */
        protected function after_save()
        {

        }

        /**
         * This function will search the model by id. This id is the ID of wp_posts
         *
         * @param $id
         *
         * @return mixed|void
         */
        public function find($id)
        {
            $driver = $this->__connect();
            $record = $driver->from($this->get_table())->where('id', $id)->fetch();
            if ($record) {
                $model = $this->fetch_model($record);
                return $model;
            }

            return null;
        }

        /**
         * @param $condition
         * @param array $params
         * @return mixed|null
         */
        public function find_one($condition, $params = array(), $order = false)
        {
            $driver = $this->__connect();
            $query = $driver->from($this->get_table())->where($condition, $params);
            if ($order) {
                $query->orderBy($order);
            }
            $data = $query->fetch();
            if ($data) {
                $model = $this->fetch_model($data);
                return $model;
            }

            return null;
        }

        /**
         * @param $params
         * @param bool $order
         * @return mixed|null
         */
        public function find_one_with_attributes($params, $order = false)
        {
            $driver = $this->__connect();
            $params = $this->esc_db_field($params);
            $query = $driver->from($this->get_table())->where($params);
            if ($order) {
                $query->orderBy($order);
            }
            $data = $query->fetch();
            if ($data) {
                $model = $this->fetch_model($data);
                return $model;
            }
            return null;
        }

        /**
         * @param $condition
         * @param array $params
         * @param bool $limit
         * @param bool $offset
         * @param bool $order
         * @return array
         */
        public function find_all($condition, $params = array(), $limit = false, $offset = false, $order = false)
        {
            $driver = $this->__connect();
            $query = $driver->from($this->get_table())->where($condition, $params);
            if ($limit) {
                $query->limit($limit);
            }
            if ($offset) {
                $query->offset($offset);
            }
            if ($order) {
                $query->orderBy($order);
            }

            $data = $query->fetchAll();
            $models = array();
            foreach ($data as $row) {
                $models[] = $this->fetch_model($row);
            }

            return $models;
        }

        public function find_all_by_ids($ids, $limit = false, $offset = false, $order = false)
        {
            $driver = $this->__connect();
            $query = $driver->from($this->get_table())->where('id', $ids);

            if ($limit) {
                $query->limit($limit);
            }
            if ($offset) {
                $query->offset($offset);
            }
            if ($order) {
                $query->orderBy($order);
            }

            $data = $query->fetchAll();
            $models = array();
            foreach ($data as $row) {
                $models[] = $this->fetch_model($row);
            }

            return $models;
        }

        public function get_driver()
        {
            return $this->__connect();
        }

        /**
         * @param array $params
         * @param bool $limit
         * @param bool $offset
         * @param bool $order
         * @return array
         */

        public function find_by_attributes($params = array(), $limit = false, $offset = false, $order = false)
        {
            $driver = $this->__connect();
            $query = $driver->from($this->get_table())->where($params);
            if ($limit) {
                $query->limit($limit);
            }
            if ($offset) {
                $query->offset($offset);
            }
            if ($order) {
                $query->orderBy($order);
            }
            $data = $query->fetchAll();
            $models = array();
            foreach ($data as $row) {
                $models[] = $this->fetch_model($row);
            }

            return $models;
        }

        /**
         *
         * @param string $sql
         * @param array $data
         *
         * @return array
         */
        public function all_with_condition($sql = ' 1 = 1 ', $data = array())
        {
            $driver = $this->__connect();
            $data = $driver->from($this->get_table())->where($sql, $data)->fetchAll();

            $models = array();
            foreach ($data as $row) {
                $models[] = $this->fetch_model($row);
            }

            return $models;
        }

        /**
         * @param $data
         *
         * @return mixed
         */
        private function fetch_model($data)
        {
            $class = get_class($this);

            $model = new $class;
            $model->import($data);
            $model->exist = true;

            return $model;
        }

        /**
         * Delete the model
         */
        function delete()
        {
            $driver = $this->__connect();
            $driver->delete($this->get_table(), $this->id);
        }

        /**
         * @return string
         */
        function get_table()
        {
            global $wpdb;

            return $wpdb->prefix . $this->table;
        }

        public static function model($class_name = __CLASS__)
        {
            //cache
            if (!isset(self::$_models[$class_name])) {
                self::$_models[$class_name] = new $class_name();
            }
            return self::$_models[$class_name];
        }
    }
}