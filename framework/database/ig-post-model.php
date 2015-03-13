<?php

/**
 * This class is use for extend only. This is a modal design for a worpdress post type
 * Support CRUD, validation and query
 * @author: Hoang Ngo
 * @package: Database
 */
if (!class_exists('IG_Post_Model')) {
    class IG_Post_Model extends IG_Model
    {
        private static $_models = array();

        public $wp_post;

        /**
         * Default value of Wordpress post
         * @var array
         */
        protected $defaults = array(
            'post_status' => 'publish',
        );


        /**
         * This will auto detect if the current modal is new record or exist,
         * and fire an update/insert action.
         * @param bool $before_save
         * @param bool $after_save
         * @return bool|IG_Post_Model
         */
        public function save($before_save = true, $after_save = true)
        {
            if ($before_save)
                $this->before_save();
            if ($this->exist) {
                $saved = $this->perform_update();
            } else {
                $saved = $this->perform_insert();
            }
            //todo clear the cache
            $this->clear_caches();
            if ($saved) {
                return $this->finish_save($saved, $after_save);
            }

            return false;
        }

        /**
         * When this action fired, it will delete all the meta key of the post type,
         * and then delete itself
         */
        public function delete()
        {
            //clean up all the meta
            foreach ($this->relations as $relation) {
                if ($relation['type'] == 'meta') {
                    delete_post_meta($this->id, $relation['key']);
                }
            }
            //remove this
            wp_delete_post($this->id, true);
        }

        /**
         * @return int|WP_Error
         */
        private function perform_update()
        {
            //build the insert args
            $args = array(
                'post_type' => $this->table
            );
            //combine the default with $args
            $args = array_merge($args, $this->defaults);
            foreach ($this->mapped as $key => $val) {
                $args[$val] = $this->$key;
            }
            //insert post
            $id = wp_update_post($args);
            if ($id) {
                //parse the relations
                foreach ($this->relations as $relation) {
                    $prop = $relation['map'];
                    if ($relation['type'] == 'meta') {
                        update_post_meta($id, $relation['key'], $this->$prop);
                    } elseif ($relation['type'] == 'taxonomy') {
                        /**
                         * This process will assign this post to taxonomy, if taxonomy not exist, create it
                         * The property should be array of name,slug or id
                         */
                        $taxs = array();
                        //the input should be array of string or id
                        if (!is_array($this->$prop)) {
                            $this->$prop = array_filter(explode(',', $this->$prop));
                        }
                        $t = null;

                        foreach ($this->$prop as $tax) {
                            if (filter_var($tax, FILTER_VALIDATE_INT)) {
                                //include to the tax array
                                $term = get_term($tax, $relation['key']);
                                if (is_object($term)) {
                                    $taxs[] = $term->name;
                                } else {
                                    var_dump($term);
                                }
                            } else {
                                $taxs[] = $tax;
                            }
                        }
                        //now we got the ids, assign post to this tax
                        wp_set_object_terms($id, $taxs, $relation['key']);
                    } elseif ($relation['type'] == 'meta_array') {
                        $data = array();
                        $format = explode('|', $relation['array_key']);
                        $fields = explode('|', $relation['map']);
                        $map = array_combine($format, $fields);
                        foreach ($map as $key => $val) {
                            $data[$key] = $this->$val;
                        }

                        if ($relation['format'] == 'json') {
                            json_encode($data);
                        }
                        update_post_meta($this->id, $relation['key'], $data);
                    }
                }

                return $id;
            }
        }

        function find_by_slug($slug, $status = 'publish')
        {
            $posts = new WP_Query(array(
                'name' => $slug,
                'post_type' => $this->table,
                'post_status' => $status
            ));
            wp_reset_query();
            $model = null;
            if ($posts->post_count > 0) {
                $post = $posts->posts[0];
                $model = $this->find($post);
                return $model;
            }
            return null;
        }

        /**
         * @return int|WP_Error
         */
        private function perform_insert()
        {
            //build the insert args
            $args = array(
                'post_type' => $this->table
            );
            //combine the default with $args
            $args = array_merge($args, $this->defaults);

            foreach ($this->mapped as $key => $val) {
                $args[$val] = $this->$key;
            }
            //insert post
            $id = wp_insert_post($args);
            if (!is_wp_error($id)) {
                //parse the relations
                foreach ($this->relations as $relation) {
                    $prop = $relation['map'];
                    if ($relation['type'] == 'meta') {
                        update_post_meta($id, $relation['key'], $this->$prop);
                    } elseif ($relation['type'] == 'taxonomy') {
                        /**
                         * This process will assign this post to taxonomy, if taxonomy not exist, create it
                         * The property should be array of name,slug or id
                         */
                        $taxs = array();
                        //the input should be array of string or id
                        if (!is_array($this->$prop)) {
                            $this->$prop = array_filter(explode(',', $this->$prop));
                        }
                        $t = null;

                        foreach ($this->$prop as $tax) {
                            if (filter_var($tax, FILTER_VALIDATE_INT)) {
                                //include to the tax array
                                $term = get_term($tax, $relation['key']);
                                if (is_object($term)) {
                                    $taxs[] = $term->name;
                                } else {
                                    var_dump($term);
                                }
                            } else {
                                $taxs[] = $tax;
                            }
                        }
                        //now we got the ids, assign post to this tax
                        wp_set_object_terms($id, $taxs, $relation['key']);
                    } elseif ($relation['type'] == 'meta_array') {
                        $data = array();
                        $format = explode('|', $relation['array_key']);
                        $fields = explode('|', $relation['map']);
                        $map = array_combine($format, $fields);
                        foreach ($map as $key => $val) {
                            $data[$key] = $this->$val;
                        }

                        if ($relation['format'] == 'json') {
                            $data = json_encode($data);
                        }
                        update_post_meta($id, $relation['key'], $data);
                    }
                }

                //return the id
                return $id;
            }
        }

        /**
         * @param $saved
         *
         * @return $this
         */
        private function finish_save($saved, $after_save)
        {
            $this->id = $saved;
            $this->exist = true;
            if ($after_save)
                $this->after_save();
            //loaded the data
            $model = $this->find($saved);
            $this->import($model->export());

            return $this;
        }

        /**
         * Addition actions before saving a model, eg: update date create
         */
        protected function before_save()
        {
            do_action($this->get_table() . '_before_save', $this);
        }

        /**
         * Addition actions after saving a model, eg another dependency of this model
         */
        protected function after_save()
        {
            do_action($this->get_table() . '_after_save', $this);
        }

        public static function model($class_name = __CLASS__)
        {
            //cache
            if (!isset(self::$_models[$class_name])) {
                self::$_models[$class_name] = new $class_name();
            }
            return self::$_models[$class_name];
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
            //$cache_id = $this->cache_prefix() . '_' . $id;

            /*$model = get_transient($cache_id);
            if (is_object($model)) {
                return apply_filters($this->get_table() . '_model_find', $model, get_class($this), $id);
            }*/

            //first we need to get the post
            $post = get_post($id);
            if (!is_object($post)) {
                return null;
            }
            $class = get_class($this);
            $model = new $class;
            foreach ($model->get_mapped() as $key => $val) {
                $model->$key = $post->$val;
            }
            //relations
            foreach ($model->get_relations() as $key => $val) {
                $prop = $val['map'];
                if ($val['type'] == 'meta') {
                    $model->$prop = get_post_meta($model->id, $val['key'], true);
                } elseif ($val['type'] == 'taxonomy') {
                    $ts = wp_get_object_terms($model->id, $val['key']);
                    $model->$prop = array();
                    foreach ($ts as $t) {
                        array_push($model->$prop, $t->name);
                    }
                } elseif ($val['type'] == 'meta_array') {
                    $data = get_post_meta($model->id, $val['key'], true);
                    //check for serialize
                    if (!is_array($data)) {
                        $data = maybe_unserialize($data);
                    }
                    if (!is_array($data)) {
                        $data = json_decode($data, true);
                    }
                    if (!is_array($data)) {
                        break;
                    }
                    $format = explode('|', $val['array_key']);
                    $fields = explode('|', $val['map']);
                    $map = array_combine($format, $fields);
                    foreach ($data as $k => $v) {
                        $p = isset($map[$k]) ? $map[$k] : null;
                        if ($p) {
                            $model->$p = $v;
                        }
                    }
                }
            }
            $model->set_exist(true);
            $model->wp_post = get_post($model->id);
            // set_transient($cache_id, $model, 60 * 60 * 12);

            return apply_filters($this->get_table() . '_model_find', $model, $class, $id);
        }

        public function find_one_by_attributes($params, $order = false)
        {
            $query = array(
                'post_type' => $this->table,
                'fields' => 'ids'
            );
            $meta_query = array();
            $tax_query = array();

            foreach ($params as $key => $val) {
                if (isset($this->mapped[$key])) {
                    $query[$this->mapped[$key]] = $val;
                } else {
                    $re = $this->_relation($key);
                    if ($re['type'] == 'meta') {
                        $meta_query[] = array(
                            'key' => $re['key'],
                            'value' => $val,
                            'compare' => '=',
                        );
                    } else {
                        $tax_query[] = array(
                            'taxonomy' => $re['key'],
                            'field' => 'slug',
                            'terms' => $val,
                        );
                    }
                }
            }

            $query['meta_query'] = $meta_query;
            $query['tax_query'] = $tax_query;

            if ($order) {
                $order = explode(' ', $order);
                if (count($order) == 2) {
                    $query['orderby'] = $order[0];
                    $query['order'] = $order[1];
                } else {
                    $query['orderby'] = $order;
                }
            }

            $query['posts_per_page'] = 1;
            $query['paged'] = 1;
            $query = new WP_Query(apply_filters($this->table . 'find_one_by_attributes', $query, $params));

            wp_reset_query();
            $model = null;
            if ($query->post_count > 0) {
                $post = $query->posts[0];
                $model = $this->find($post);
            }
            return $model;
        }

        public function find_by_attributes($params, $paged = false, $order = false)
        {
            $query = array(
                'post_type' => $this->table,
                'fields' => 'ids'
            );
            $meta_query = array();
            $tax_query = array();

            foreach ($params as $key => $val) {
                if (isset($this->mapped[$key])) {
                    $post_field = $this->mapped[$key];
                    if ($post_field == 'post_author') {
                        $post_field = 'author';
                    }
                    $query[$post_field] = $val;
                } else {
                    $re = $this->_relation($key);
                    if ($re['type'] == 'meta') {
                        $meta_query[] = array(
                            'key' => $re['key'],
                            'value' => $val,
                            'compare' => '=',
                        );
                    } else {
                        $tax_query[] = array(
                            'taxonomy' => $re['key'],
                            'field' => 'slug',
                            'terms' => $val,
                        );
                    }
                }
            }

            $query['meta_query'] = $meta_query;
            $query['tax_query'] = $tax_query;
            if ($paged) {
                $query['paged'] = $paged;
            } else {
                $query['nopaging'] = 1;
            }

            if ($order) {
                $order = explode(' ', $order);
                if (count($order) == 2) {
                    $query['orderby'] = $order[0];
                    $query['order'] = $order[1];
                } else {
                    $query['orderby'] = $order;
                }
            }

            $query = new WP_Query(apply_filters($this->table . 'find_by_attributes', $query, $params));
            wp_reset_query();
            $models = array();
            foreach ($query->posts as $post_id) {
                $model = $this->find($post_id);
                if ($model) {
                    $models[] = $model;
                }
            }
            return $models;
        }

        public function find_by_ids($ids, $paged = false, $limit = false, $order = false)
        {
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }

            if ($paged == false && $order == false) {
                $models = array();
                foreach ($ids as $post_id) {
                    $model = $this->find($post_id);
                    if ($model) {
                        $models[] = $model;
                    }
                }
                return $models;
            }

            //use wp_query for order and paging
            $params = array(
                'post_type' => $this->table,
                'fields' => 'ids',
                'post__in' => $ids
            );
            if ($paged) {
                $params['paged'] = $paged;
            } else {
                $params['nopaging'] = 1;
            }
            if ($limit) {
                $params['posts_per_page'] = $limit;
            }

            if ($order) {
                $order = explode(' ', $order);
                if (count($order) == 2) {
                    $params['orderby'] = $order[0];
                    $params['order'] = $order[1];
                } else {
                    $params['orderby'] = $order;
                }
            }

            $query = new WP_Query(apply_filters($this->table . 'find_all_by_ids', $params));
            wp_reset_query();

            $models = array();
            foreach ($query->posts as $post_id) {
                $model = $this->find($post_id);
                if ($model) {
                    $models[] = $model;
                }
            }
            return $models;
        }

        private function  _relation($key)
        {
            foreach ($this->relations as $re) {
                if ($re['map'] == $key) {
                    return $re;
                }
            }
            return false;
        }

        /**
         * Query through wp_posts table and return the data
         * @return array
         */
        public function all()
        {
            //for faster, getting the wpdb
            global $wpdb;
            $sql = "SELECT ID FROM " . $wpdb->posts . " WHERE post_type=%s";
            $ids = $wpdb->get_col($wpdb->prepare($sql, $this->get_table()));
            //gatehr all ids, now the time has come
            $models = array();
            foreach ($ids as $id) {
                $models[] = $this->find($id);
            }

            return apply_filters($this->get_table() . '_result_all', $models);
        }

        /**
         * This function will query the wp_posts table, with parameters.
         * The parameters is refer to WP_Query parameters http://codex.wordpress.org/Class_Reference/WP_Query
         *
         * @param array $args
         *
         * @return array
         */
        public function all_with_condition($args = array(), &$instance = null)
        {
            //get only need to get ids
            $args['fields'] = 'ids';
            $args['post_type'] = $this->get_table();

            //build the cache id
            $cache_id = $this->cache_prefix() . substr(md5('-all_with_condition-' . $this->multi_implode($args, '-')), 0, 8);
            $cache = get_transient($cache_id);
            if ($cache) {
                if (!is_null($instance)) {
                    $instance->global['wp_query'] = $cache['query'];
                }
                return $cache['data'];
            }

            $query = new WP_Query($args);

            $data = array();

            foreach ($query->posts as $post_id) {
                $model = $this->find($post_id);
                if ($model) {
                    $data[] = $model;
                }
            }
            wp_reset_query();
            if (!is_null($instance)) {
                $instance->global['wp_query'] = $query;;
            }

            set_transient($cache_id, array(
                'data' => $data,
                'query' => $query
            ), 60 * 60 * 12);
            return $data;
        }

        function clear_caches()
        {
            global $wpdb;
            $sql = "SELECT option_name FROM " . $wpdb->options . " WHERE option_name LIKE %s";
            $caches_key = $wpdb->get_col($wpdb->prepare($sql, '%' . $this->get_table() . '-cache-%'), 0);
            foreach ($caches_key as $key) {
                delete_option($key);
            }
        }

        function cache_prefix()
        {
            return $this->get_table() . '-cache-';
        }

        /**
         * @return mixed
         */
        public function count()
        {
            global $wpdb;
            $sql = "SELECT count(ID) FROM {$wpdb->posts} WHERE post_type=%s AND post_status=%s";
            $total = $wpdb->get_var($wpdb->prepare($sql, $this->get_table(), 'publish'));

            return $total;
        }

        /**
         * Retuning the raw wp_posts behind this model
         * @return null|WP_Post
         */
        public function get_raw()
        {
            $post = get_post($this->id);

            return $post;
        }
    }
}