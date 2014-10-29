<?php

/**
 * Author: Hoang Ngo
 */
class IG_Post_Model extends IG_Model
{
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

    private function perform_update()
    {
        //build the insert args
        $args = array(
            'post_type' => $this->table
        );
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
                                $taxs[] = $term->tern_name;
                            } else {
                                var_dump($term);
                            }
                        } else {
                            $taxs[] = $tax;
                        }
                    }
                    //now we got the ids, assign post to this tax
                    wp_set_object_terms($id, $taxs, $relation['key']);
                }
            }

            return $id;
        }
    }

    private function perform_insert()
    {
        //build the insert args
        $args = array(
            'post_type' => $this->table
        );
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
                                $taxs[] = $term->tern_name;
                            } else {
                                var_dump($term);
                            }
                        } else {
                            $taxs[] = $tax;
                        }
                    }
                    //now we got the ids, assign post to this tax
                    wp_set_object_terms($id, $taxs, $relation['key']);
                }
            }
            //return the id
            return $id;
        }
    }

    private function finish_save($saved)
    {
        $this->id = $saved;
        $this->exist = true;
        $this->after_save();
        //loaded the data
        $model = self::find($saved);
        $this->import($model->export());
        return $this;
    }

    public function before_save()
    {

    }

    public function after_save()
    {

    }

    ///////////////////////////////////////////////////////////////
    public static function find($id)
    {
        //first we need to get the post
        $post = get_post($id);
        if (!is_object($post)) return null;
        $class = get_called_class();
        $model = new $class;
        foreach ($model->get_mapped() as $key => $val) {
            $model->$key = $post->$val;
        }
        //relations
        foreach ($model->get_relations() as $key => $val) {
            $prop = $val['map'];
            if ($val['type'] == 'meta') {
                $model->$prop = get_post_meta($model->id, $val['key'], true);
            } else {
                $ts = wp_get_object_terms($model->id, $val['key']);
                $model->$prop = array();
                foreach ($ts as $t) {
                    array_push($model->$prop, $t->name);
                }
            }
        }
        $model->set_exist(true);
        return apply_filters('ig_post_model_find', $model, $class, $id);
    }

    public static function all()
    {
        //for faster, getting the wpdb
        global $wpdb;
        $sql = "SELECT ID FROM " . $wpdb->posts . " WHERE post_type=%s";
        $ids = $wpdb->get_col($wpdb->prepare($sql, self::table_name()));
        //gatehr all ids, now the time has come
        $models = array();
        foreach ($ids as $id) {
            $models[] = self::find($id);
        }
        return $models;
    }

    public static function all_with_condition($args = array())
    {
        //get only need to get ids
        $args['fields'] = 'ids';
        $args['post_type'] = self::table_name();
        $query = new WP_Query($args);

        $data = array();

        foreach ($query->posts as $post_id) {
            $model = self::find($post_id);
            if ($model) {
                $data[] = $model;
            }
        }
        wp_reset_query();
        return $data;
    }

    public static function count()
    {
        global $wpdb;
        $sql = "SELECT count(ID) FROM {$wpdb->posts} WHERE post_type=%s AND post_status=%s";
        $total = $wpdb->get_var($wpdb->prepare($sql, self::table_name(), 'publish'));
        return $total;
    }

    private static function table_name()
    {
        $class = get_called_class();
        $empty = new $class;
        return $empty->get_table();
    }

    public function get_raw()
    {
        $post = get_post($this->id);
        return $post;
    }
}