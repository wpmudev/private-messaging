<?php

/**
 * Author: Hoang Ngo
 */
class MM_Conversation_Model extends IG_DB_Model
{
    public $table = 'mm_conversation';

    /**
     * @var String
     * Date this conversation created
     */
    public $date;

    /**
     * @var Int
     */
    public $count;

    /**
     * @var String
     * IDs of the messages from this conversation
     */
    public $index;
    /**
     * @var String
     * IDs of the users join in this conversation
     */
    public $user_index;

    /**
     * @var Int
     * ID of user who create this conversation
     */
    public $from;

    /**
     * @var
     */
    public $site_id;

    public function get_messages()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $models = MM_Message_Model::all_with_condition(array(
            'post__in' => $ids,
            'post_status' => 'publish',
            'nopaging' => true
        ));
        return $models;
    }

    public static function get_conversation()
    {
        global $wpdb;
        $sql = "SELECT conv.id FROM wp_posts posts
INNER JOIN wp_postmeta conv_id ON posts.ID = conv_id.post_id AND conv_id.meta_key='_conversation_id'
INNER JOIN wp_postmeta send_to ON posts.ID = send_to.post_id AND send_to.meta_key='_send_to'
INNER JOIN wp_mm_conversation conv ON conv.id = conv_id.meta_value
AND send_to.meta_value = %d ORDER BY conv.date DESC";
        $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id()));
        if (empty($ids)) {
            return array();
        }
        $models = self::all_with_condition('id IN (' . implode(',', $ids) . ') ORDER BY date DESC');
        return $models;
    }

    public function get_last_message()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $id = array_pop($ids);

        $model = MM_Message_Model::find($id);
        if (is_object($model)) {
            return $model;
        }
    }

    public function get_first_message()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $id = array_shift($ids);
        $model = MM_Message_Model::find($id);
        if (is_object($model)) {
            return $model;
        }
    }

    public function update_index($id)
    {
        $index = explode(',', $this->index);
        $index = array_filter($index);
        $index[] = $id;
        $this->index = implode(',', $index);

        //update users
        $messages = $this->get_messages();
        $ids = array();
        foreach ($messages as $m) {
            $ids[] = $m->send_from;
            $ids[] = $m->send_to;
        }
        $ids = array_filter(array_unique($ids));
        $this->user_index = implode(',', $ids);

        $this->save();
    }

    public function update_count()
    {
        $models = MM_Message_Model::all_with_condition(array(
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_conversation_id',
                    'value' => $this->id,
                    'compare' => '=',
                ),
            ),
        ));
        $this->count = count($models);

        $this->save();
    }

    public function get_users()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $users = get_users(array(
            'include' => $ids
        ));
        return $users;
    }

    public function before_save()
    {
        if (!$this->exist) {
            $this->date = date('Y-m-d H:i:s');
            $this->from = get_current_user_id();
            $this->site_id = get_current_blog_id();
        }
    }

    public function after_save()
    {
        wp_cache_delete('mm_count_all');
        wp_cache_delete('mm_count_read');
        wp_cache_delete('mm_count_unread');
    }

    public static function get_unread()
    {
        global $wpdb;
        $sql = "SELECT conv.id FROM wp_posts posts
INNER JOIN wp_postmeta conv_id ON posts.ID = conv_id.post_id AND conv_id.meta_key='_conversation_id'
INNER JOIN wp_postmeta send_to ON posts.ID = send_to.post_id AND send_to.meta_key='_send_to'
INNER JOIN wp_postmeta stat ON posts.ID = stat.post_id AND stat.meta_key='_status'
INNER JOIN wp_mm_conversation conv ON conv.id = conv_id.meta_value
AND send_to.meta_value = %d AND stat.meta_value=%s ORDER BY conv.date DESC";

        $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id(), MM_Message_Model::UNREAD));
        if (empty($ids)) {
            return array();
        }
        $models = self::all_with_condition('id IN (' . implode(',', $ids) . ') ORDER BY date DESC');
        return $models;
    }

    public static function get_read()
    {
        global $wpdb;
        $sql = "SELECT conv.id FROM wp_posts posts
INNER JOIN wp_postmeta conv_id ON posts.ID = conv_id.post_id AND conv_id.meta_key='_conversation_id'
INNER JOIN wp_postmeta send_to ON posts.ID = send_to.post_id AND send_to.meta_key='_send_to'
INNER JOIN wp_postmeta stat ON posts.ID = stat.post_id AND stat.meta_key='_status'
INNER JOIN wp_mm_conversation conv ON conv.id = conv_id.meta_value
AND send_to.meta_value = %d AND stat.meta_value=%s ORDER BY conv.date DESC";
        $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id(), MM_Message_Model::READ));
        if (empty($ids)) {
            return array();
        }
        $models = self::all_with_condition('id IN (' . implode(',', $ids) . ') ORDER BY date DESC');
        return $models;
    }

    public static function get_sent()
    {
        global $wpdb;
        $sql = "SELECT conv.id FROM wp_posts posts
INNER JOIN wp_postmeta conv_id ON posts.ID = conv_id.post_id AND conv_id.meta_key='_conversation_id'
INNER JOIN wp_mm_conversation conv ON conv.id = conv_id.meta_value
        AND post_author = %d ORDER BY conv.date DESC";
        $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id()));
        if (empty($ids)) {
            return array();
        }
        $models = self::all_with_condition('id IN (' . implode(',', $ids) . ') ORDER BY date DESC');
        return $models;
    }

    public function has_unread()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $models = MM_Message_Model::all_with_condition(array(
            'post__in' => $ids,
            'post_status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_status',
                    'value' => MM_Message_Model::UNREAD,
                    'compare' => '=',
                ),
            ),
        ));
        return count($models) > 0;
    }

    public function mark_as_read()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $models = MM_Message_Model::all_with_condition(array(
            'post__in' => $ids,
            'post_status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_status',
                    'value' => MM_Message_Model::UNREAD,
                    'compare' => '=',
                ),
            ),
        ));
        foreach ($models as $model) {
            $model->status = MM_Message_Model::READ;
            $model->save();
        }
    }

    public static function count_all()
    {
        if (wp_cache_get('mm_count_all') == false) {
            $m = new MM_Conversation_Model();
            wp_cache_set('mm_count_all', count($m->get_conversation()));
        }
        return wp_cache_get('mm_count_all');
    }

    public static function count_unread()
    {
        if (wp_cache_get('mm_count_unread') == false) {
            $m = new MM_Conversation_Model();
            $models = $m->get_conversation();
            $count = 0;
            foreach ($models as $model) {
                if ($model->has_unread())
                    $count++;
            }
            wp_cache_set('mm_count_unread', $count);
        }
        return wp_cache_get('mm_count_unread');
    }

    public static function count_read()
    {
        if (wp_cache_get('mm_count_read') == false) {
            $m = new MM_Conversation_Model();
            $models = $m->get_conversation();
            $count = 0;
            foreach ($models as $model) {
                if ($model->has_unread() == false)
                    $count++;
            }
            wp_cache_set('mm_count_read', $count);
        }
        return wp_cache_get('mm_count_read');
    }

    public static function search($query)
    {
        $ms = MM_Message_Model::all_with_condition(array(
            's' => $query,
            'status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_send_to',
                    'value' => get_current_user_id(),
                    'compare' => '=',
                ),
            ),
        ));
        if (empty($ms))
            return array();

        $ids = array();
        foreach ($ms as $m) {
            $ids[] = $m->conversation_id;
        }
        return self::all_with_condition('id IN (' . implode(',', $ids) . ')', array());
    }

    function get_users_in()
    {
        $ids = $this->user_index;
        $ids = array_filter(array_unique(explode(',', $ids)));
        $users = array();
        foreach ($ids as $id) {
            $user = get_user_by('id', $id);
            if (is_object($user)) {
                $users[] = $user;
            }
        }
        return $users;
    }

    function get_table()
    {
        global $wpdb;

        return $wpdb->base_prefix . $this->table;
    }
}