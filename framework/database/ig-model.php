<?php
/**
 * Author: Hoang Ngo
 */
if (!class_exists('IG_Model')) {
    class IG_Model
    {
        /**
         * @var string
         * This is a footprint for where we store the model, post type name, option name,etc
         */
        protected $table;

        /**
         * @var array
         * Validation rules
         */
        protected $rules = array();

        /**
         * @var array
         */
        protected $errors = array();

        /**
         * @var bool
         * Check for this model is new or already exist
         */
        public $exist = false;

        /**
         * @var array
         * Relations of this model to taxonomoy, meta
         */
        protected $relations = array();

        /**
         * @var array
         * This model attributes with native wordpress post attribute
         */
        protected $mapped = array();

        /**
         * This function will do the validate and return true / false
         */
        public function validate()
        {
            $this->before_validate();
            require_once __DIR__ . '/gump.class.php';
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
         * Logic to run before validate
         */
        protected function before_validate()
        {

        }

        /**
         * Logic to run after model validate
         */
        protected function after_validate()
        {
            return true;
        }

        public function get_error($field)
        {
            if (isset($this->errors[$field]))
                return $this->errors[$field];
        }

        public function set_error($field, $message)
        {
            $this->errors[$field] = $message;
        }

        public function has_error($field)
        {
            return isset($this->errors[$field]);
        }

        /**
         * @param array $data
         * Import from an array to model
         */
        public function import($data = array())
        {
            foreach ($data as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->$key = $val;
                }
            }
        }

        /**
         * @return array
         * Export model data to array
         */
        public function export()
        {
            $data = array();
            $ref_class = new ReflectionClass(get_called_class());
            $system_prop = array('exist', 'errors', 'table', 'rules', 'relations', 'mapped', 'toolbox', 'pk');
            foreach ($ref_class->getProperties() as $prop) {
                if ($prop->class == get_called_class() && !in_array($prop->name, $system_prop)) {
                    $data[$prop->name] = $this->{$prop->name};
                }
            }

            return $data;
        }

        /**
         * @param $exist
         */
        public function set_exist($exist)
        {
            $this->exist = $exist;
        }

        /**
         * @return array
         */
        public function get_mapped()
        {
            return $this->mapped;
        }

        /**
         * @return array
         */
        public function get_relations()
        {
            return $this->relations;
        }

        public function get_table()
        {
            return $this->table;
        }

        public function get_errors()
        {
            return $this->errors;
        }
    }
}