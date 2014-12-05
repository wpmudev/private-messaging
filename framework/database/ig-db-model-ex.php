<?php

/**
 * @author:Hoang Ngo
 */
if (!class_exists('IG_DB_Model_Ex')) {
    class IG_DB_Model_Ex extends IG_Model
    {
        public $id;

        /**
         * @var array Model indexer
         */
        private static $_models = array();

        public function __connect()
        {
            global $wpdb;
            return $wpdb;
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
            $data = stripslashes_deep($data);
            $wpdb = $this->__connect();
            $wpdb->show_errors();
            $wpdb->update($this->get_table(), $data, array(
                'id' => $this->id
            ));

            return $this->id;
        }

        /**
         * @return int|string
         */
        private function perform_insert()
        {
            $data = $this->export();
            $data = stripslashes_deep($data);
            $wpdb = $this->__connect();
            $wpdb->insert($this->get_table(), $data);
            $id = $wpdb->insert_id;
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
            $wpdb = $this->__connect();
            $sql = $wpdb->prepare("SELECT * FROM " . $this->get_table() . " WHERE id = %d", $id);
            $record = $wpdb->get_row($sql, ARRAY_A);
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
            $wpdb = $this->__connect();

            $sql = "SELECT * FROM " . $this->get_table() . " WHERE " . $condition;

            if ($order) {
                $sql .= " ORDER BY " . $order;
            }

            $sql = $wpdb->prepare($sql, $params);

            $data = $wpdb->get_row($sql);
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
            $wpdb = $this->__connect();
            $params = $this->esc_db_field($params);

            $sql = "SELECT * FROM " . $this->get_table();
            $where = array();
            foreach ($params as $key => $val) {
                $where[] = "$key = '" . esc_sql($val) . "'";
            }
            $sql .= " WHERE " . implode(' AND ', $where);

            if ($order) {
                $sql .= " ORDER BY " . $order;
            }

            $data = $wpdb->get_row($sql, ARRAY_A);

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
        public function find_all($condition = '', $params = array(), $limit = false, $offset = false, $order = false)
        {
            $wpdb = $this->__connect();

            if (!empty($condition)) {
                $sql = "SELECT * FROM " . $this->get_table() . " WHERE " . $condition;
            } else {
                $sql = "SELECT * FROM " . $this->get_table();
            }

            if ($order) {
                $sql .= " ORDER BY " . $order;
            }

            if ($limit && $offset) {
                $sql .= " LIMIT $offset,$limit";
            } elseif ($limit) {
                $sql .= " LIMIT $limit";
            }

            if (!empty($params)) {
                $sql = $wpdb->prepare($sql, $params);
            }
            $data = $wpdb->get_results($sql, ARRAY_A);

            $models = array();
            foreach ($data as $row) {
                $models[] = $this->fetch_model($row);
            }

            return $models;
        }

        public function find_all_by_ids($ids, $limit = false, $offset = false, $order = false)
        {
            $wpdb = $this->__connect();
            $sql = "SELECT * FROM " . $this->get_table() . " WHERE id IN (" . implode(',', $ids) . ")";
            if ($order) {
                $sql .= " ORDER BY " . $order;
            }
            if ($limit && $offset) {
                $sql .= " LIMIT $offset,$limit";
            } elseif ($limit) {
                $sql .= " LIMIT $limit";
            }

            $data = $wpdb->get_results($sql, ARRAY_A);

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
            $wpdb = $this->__connect();
            $params = $this->esc_db_field($params);

            $sql = "SELECT * FROM " . $this->get_table();
            $where = array();
            foreach ($params as $key => $val) {
                $where[] = "$key = '" . esc_sql($val) . "'";
            }
            $sql .= " WHERE " . implode(' AND ', $where);

            if ($order) {
                $sql .= " ORDER BY " . $order;
            }

            if ($limit && $offset) {
                $sql .= " LIMIT $offset,$limit";
            } elseif ($limit) {
                $sql .= " LIMIT $limit";
            }

            $data = $wpdb->get_results($sql, ARRAY_A);

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
            $wpdb = $this->__connect();
            $wpdb->delete($this->get_table(), array(
                'id' => $this->id
            ));
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