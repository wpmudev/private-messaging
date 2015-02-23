<?php
/**
 * This class is use for inherit only
 *
 * @author: Hoang Ngo
 * @package: Database
 */
if (!class_exists('IG_Model')) {
    class IG_Model
    {

        /**
         * This variable this to be init from every class inherit,
         * this is physic table name, or a post type name
         *
         * @var string
         */
        protected $table;

        /**
         * This variable contain validate rules, example usage
         * array(
         * 'username'    => 'required|alpha_numeric|max_len,100|min_len,6',
         * 'password'    => 'required|max_len,100|min_len,6',
         * 'email'       => 'required|valid_email',
         * 'gender'      => 'required|exact_len,1|contains,m f',
         * 'credit_card' => 'required|valid_cc'
         * )
         * For more information, check the GUMP library https://github.com/Wixel/GUMP
         * @var array
         */
        protected $rules = array();

        /**
         * This variables contain all the errors after validate, data example
         * array(
         * 'username'=>'Your username is empty'
         * )
         * @var array
         */
        protected $errors = array();

        /**
         * This variable is use to check does this model is a new record, or an existing
         * @var bool
         */
        public $exist = false;

        /**
         * This is define the relations of this modal other,
         * only implement for post type for now. Example format
         * array(
         *  array(
         *      'type' => 'meta',
         *      'key' => '_status',
         *      'map' => 'status'
         *  ),
         *  array(
         *      'type' => 'taxonomy',
         *      'key' => 'category',
         *      'map' => 'category'
         *  ),
         * )
         * In meta concept, 'key' is the meta_key of a post type.
         * In taxonomy concept, key is the taxonomy name.
         * @var array
         */
        protected $relations = array();

        /**
         * This variabe is an guide to tell the modal how to map local variable to
         * native post type property. Only apply for post type modal. Example format:
         *
         * array(
         *    'id' => 'ID',
         *    'name' => 'post_title',
         *    'status' => 'post_status',
         *    'user_id' => 'post_author'
         * )
         * The key is this modal property, value is map to wp_posts table
         * @var array
         */
        protected $mapped = array();

        /**
         * This variable is virtual attributes, you can still use for validate, set/get/bind, store to db.
         * Store to db is only for post type
         *
         * @var array
         */
        protected $virtual_attributes = array();

        /**
         * This variable use by system only, to assign value to vattribute key
         * @var array
         */
        protected $virtual_data = array();

        /**
         * Getting the property value from model
         * Priority native property, virtual property
         * @param $key
         */
        public function __get($key)
        {
            if (property_exists(get_class($this), $key)) {
                return $this->$key;
            } elseif (in_array($key, $this->virtual_attributes)) {
                return isset($this->virtual_data[$key]) ? $this->virtual_data[$key] : null;
            }
        }

        /**
         * Setting property value, priority similar with __get
         * @param $key
         */
        public function __set($key, $value = null)
        {
            if (property_exists(get_class($this), $key)) {
                $this->$key = $value;
            } elseif (in_array($key, $this->virtual_attributes)) {
                $this->virtual_data[$key] = $value;
            }
        }

        /**
         * This function will validate the modal, and return a bool,
         * also, variable $errors will be fetched.
         * @return bool
         */
        public function validate()
        {
            $this->before_validate();
            $validator = new GUMP();
            $validated = $validator->is_valid($this->export(), $this->rules);
            if ($validated === true) {
                //check for addition validate
                $all_good = $this->after_validate();
                if ($all_good == true) {
                    return true;
                } else {
                    //$this->errors = $all_good;
                    return false;
                }
            } else {
                $this->errors = $validated;

                return false;
            }
        }

        /**
         * Addition logic to fire before validate
         */
        protected function before_validate()
        {
            do_action('ig_before_validate_' . $this->get_table(), $this);
        }

        /**
         * Addition logic to fire before validate.
         * Make sure this will return a bool or the validate will halt.
         * @return bool
         */
        protected function after_validate()
        {
            do_action('ig_after_validate_' . $this->get_table(), $this);

            return true;
        }

        /**
         * This will get error of a property, return null when no error.
         *
         * @param $field
         *
         * @return mixed
         */
        public function get_error($field)
        {
            if (isset($this->errors[$field])) {
                return $this->errors[$field];
            }
        }

        /**
         * Set error to a property.
         *
         * @param $field
         * @param $message
         */
        public function set_error($field, $message)
        {
            $this->errors[$field] = $message;
        }

        /**
         * Check does a property has error
         *
         * @param $field
         *
         * @return bool
         */
        public function has_error($field)
        {
            return isset($this->errors[$field]);
        }

        /**
         * Import an array data to modal properties
         *
         * @param array $data
         */
        public function import($data = array())
        {
            foreach ((array)$data as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->$key = $val;
                } elseif (in_array($key, $this->virtual_attributes)) {
                    $this->virtual_data[$key] = $val;
                }
            }
        }

        /**
         * Export modal properties to an array
         * @return array
         */
        public function export()
        {
            $data = array();
            $ref_class = new ReflectionClass(get_class($this));
            $system_prop = array('exist', 'errors', 'table', 'rules', 'relations', 'mapped', 'toolbox', 'pk');
            foreach ($ref_class->getProperties() as $prop) {
                if ($prop->class == get_class($this) && !in_array($prop->name, $system_prop)) {
                    $data[$prop->name] = $this->{$prop->name};
                }
            }
            //virtual attribute
            foreach ($this->virtual_attributes as $key) {
                $data[$key] = $this->$key;
            }

            return $data;
        }

        function fetch_array($data)
        {
            $class = get_class($this);
            $results = array();
            foreach ($data as $row) {
                $model = new $class;
                $model->import($row);
                $results[] = $model;
            }
            return $results;
        }

        /**
         * @param bool $exist
         */
        public function set_exist($exist)
        {
            $this->exist = $exist;
        }

        /**
         * Getting the mapped data of this modal
         * @return array
         */
        public function get_mapped()
        {
            return $this->mapped;
        }

        /**
         * Getting relations data of this modal
         * @return array
         */
        public function get_relations()
        {
            return $this->relations;
        }

        /**
         * Get the $table variable
         * @return string
         */
        public function get_table()
        {
            return $this->table;
        }

        /**
         * Get all errors of this modal
         * @return array
         */
        public function get_errors()
        {
            return $this->errors;
        }

        function multi_implode($array, $glue)
        {
            $ret = '';

            foreach ($array as $item) {
                if (is_array($item)) {
                    $ret .= $this->multi_implode($item, $glue) . $glue;
                } else {
                    $ret .= $item . $glue;
                }
            }

            $ret = substr($ret, 0, 0 - strlen($glue));

            return $ret;
        }
    }
}