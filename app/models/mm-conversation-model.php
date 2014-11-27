<?php

/**
 * Author: Hoang Ngo
 */
class MM_Conversation_Model extends IG_DB_Model_Ex
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
        $models = MM_Message_Model::model()->find_by_ids($this->index, false, false, 'ID DESC');

        return $models;
    }

    public static function get_conversation()
    {
        global $wpdb;
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;

        $total_pages = ceil(self::count_all() / $per_page);

        mmg()->global['conversation_total_pages'] = $total_pages;
        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();

        $query = $driver->from($model->get_table() . ' AS conversation')->disableSmartJoin()
            ->innerJoin($wpdb->postmeta . " AS con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id")
            ->innerJoin($wpdb->postmeta . " AS send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to'")
            ->where("CAST(send_to.meta_value AS UNSIGNED) = :user_id", array(
                ':user_id' => get_current_user_id()
            ))
            ->orderBy("conversation.date DESC")->limit($offset . ',' . $per_page);
        $ids = $query->fetchAll('id');
        $ids = array_filter(array_unique(array_keys($ids)));
        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date DESC');
        /* $sql = "SELECT conversation.id FROM {$wpdb->base_prefix}mm_conversation conversation
 INNER JOIN {$wpdb->prefix}postmeta con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id
 INNER JOIN {$wpdb->prefix}postmeta send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to' AND CAST(send_to.meta_value AS UNSIGNED) = %d
 ORDER BY conversation.date DESC LIMIT $offset,$per_page";

         $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id()));*/
        return $models;
    }

    public function get_last_message()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $id = array_pop($ids);

        $model = MM_Message_Model::model()->find($id);
        if (is_object($model)) {
            return $model;
        }
    }

    public function get_first_message()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $id = array_shift($ids);
        $model = MM_Message_Model::model()->find($id);
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
        $models = MM_Message_Model::model()->find_by_attributes(array(
            'conversation_id' => $this->id
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
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;
        $total_pages = ceil(self::count_all() / $per_page);
        mmg()->global['conversation_total_pages'] = $total_pages;

        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();


        $query = $driver->from($model->get_table() . ' AS conversation')->disableSmartJoin()
            ->innerJoin($wpdb->postmeta . " AS con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id")
            ->innerJoin($wpdb->postmeta . " AS send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to'")
            ->innerJoin($wpdb->postmeta . " AS mstat ON mstat.post_id = con_id.post_id AND mstat.meta_key='_status'")
            ->where("CAST(send_to.meta_value AS UNSIGNED) = :user_id AND mstat.meta_value=:status", array(
                ':user_id' => get_current_user_id(),
                ':status' => MM_Message_Model::UNREAD
            ))
            ->orderBy("conversation.date DESC")->limit($offset . ',' . $per_page);
        $ids = $query->fetchAll('id');
        $ids = array_filter(array_unique(array_keys($ids)));
        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date DESC');
        /*$sql = "SELECT conversation.id FROM {$wpdb->base_prefix}mm_conversation conversation
INNER JOIN {$wpdb->prefix}postmeta con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id
INNER JOIN {$wpdb->prefix}postmeta send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to' AND CAST(send_to.meta_value AS CHAR) = %d
INNER JOIN {$wpdb->prefix}postmeta mstat ON mstat.post_id = con_id.post_id AND mstat.meta_key='_status' AND mstat.meta_value=%s
ORDER BY conversation.date DESC LIMIT $offset,$per_page";

        $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id(), MM_Message_Model::UNREAD));
        if (empty($ids)) {
            return array();
        }

        $models = MM_Conversation_Model::model()->all_with_condition('id IN (' . implode(',', $ids) . ') ORDER BY date DESC');*/

        return $models;
    }

    public static function get_read()
    {
        global $wpdb;
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;
        $total_pages = ceil(self::count_all() / $per_page);
        mmg()->global['conversation_total_pages'] = $total_pages;

        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();


        $query = $driver->from($model->get_table() . ' AS conversation')->disableSmartJoin()
            ->innerJoin($wpdb->postmeta . " AS con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id")
            ->innerJoin($wpdb->postmeta . " AS send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to'")
            ->innerJoin($wpdb->postmeta . " AS mstat ON mstat.post_id = con_id.post_id AND mstat.meta_key='_status'")
            ->where("CAST(send_to.meta_value AS UNSIGNED) = :user_id AND mstat.meta_value=:status", array(
                ':user_id' => get_current_user_id(),
                ':status' => MM_Message_Model::READ
            ))
            ->orderBy("conversation.date DESC")->limit($offset . ',' . $per_page);
        $ids = $query->fetchAll('id');
        $ids = array_filter(array_unique(array_keys($ids)));
        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date DESC');

        /*    $sql = "SELECT conversation.id FROM {$wpdb->base_prefix}mm_conversation conversation
    INNER JOIN {$wpdb->prefix}postmeta con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id
    INNER JOIN {$wpdb->prefix}postmeta send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to' AND CAST(send_to.meta_value AS CHAR) = %d
    INNER JOIN {$wpdb->prefix}postmeta mstat ON mstat.post_id = con_id.post_id AND mstat.meta_key='_status' AND mstat.meta_value=%s
    ORDER BY conversation.date DESC LIMIT $offset,$per_page";

            $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id(), MM_Message_Model::READ));
            if (empty($ids)) {
                return array();
            }
            $models = MM_Conversation_Model::model()->all_with_condition('id IN (' . implode(',', $ids) . ') ORDER BY date DESC');*/

        return $models;
    }

    public static function get_sent()
    {
        global $wpdb;
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;
        $total_pages = ceil(self::count_all() / $per_page);
        mmg()->global['conversation_total_pages'] = $total_pages;

        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();

        $query = $driver->from($model->get_table() . ' AS conversation')->disableSmartJoin()
            ->innerJoin($wpdb->postmeta . " AS con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id")
            ->innerJoin($wpdb->posts . " AS posts ON posts.ID = con_id.post_id")
            ->where("CAST(posts.post_author AS UNSIGNED) = :user_id", array(
                ':user_id' => get_current_user_id()
            ))
            ->orderBy("conversation.date DESC")->limit($offset . ',' . $per_page);
        $ids = $query->fetchAll('id');
        $ids = array_filter(array_unique(array_keys($ids)));
        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date DESC');
        /* $sql = "SELECT conversation.id FROM {$wpdb->base_prefix}mm_conversation conversation
 WHERE conversation.from=%d
 ORDER BY conversation.date DESC LIMIT $offset,$per_page";

         $ids = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id(), MM_Message_Model::UNREAD));
         if (empty($ids)) {
             return array();
         }
         $models = MM_Conversation_Model::model()->all_with_condition('id IN (' . implode(',', $ids) . ') ORDER BY date DESC');*/

        return $models;
    }

    public function has_unread()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $models = MM_Message_Model::model()->all_with_condition(array(
            'post__in' => $ids,
            'post_status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_status',
                    'value' => MM_Message_Model::UNREAD,
                    'compare' => '=',
                ),
                array(
                    'key' => '_send_to',
                    'value' => get_current_user_id()
                )
            ),
        ));
        return count($models) > 0;
    }

    public function mark_as_read()
    {
        $ids = explode(',', $this->index);
        $ids = array_unique(array_filter($ids));
        $models = MM_Message_Model::model()->all_with_condition(array(
            'post__in' => $ids,
            'post_status' => 'publish',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_status',
                    'value' => MM_Message_Model::UNREAD,
                    'compare' => '=',
                ),
                array(
                    'key' => '_send_to',
                    'value' => get_current_user_id()
                )
            ),
        ));
        //mmg()->get_logger()->log(var_export($models,true));
        foreach ($models as $model) {
            $model->status = MM_Message_Model::READ;
            $model->save();
        }
    }

    public static function count_all()
    {
        if (wp_cache_get('mm_count_all') == false) {
            global $wpdb;
            $sql = "SELECT conversation.id FROM {$wpdb->base_prefix}mm_conversation conversation
INNER JOIN {$wpdb->prefix}postmeta con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id
INNER JOIN {$wpdb->prefix}postmeta send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to' AND CAST(send_to.meta_value AS CHAR) = %d
";
            $count = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id()));
            wp_cache_set('mm_count_all', count(array_unique($count)));
        }

        return wp_cache_get('mm_count_all');
    }

    public static function count_unread()
    {
        if (wp_cache_get('mm_count_unread') == false) {
            global $wpdb;
            $sql = "SELECT conversation.id FROM {$wpdb->base_prefix}mm_conversation conversation
INNER JOIN {$wpdb->prefix}postmeta con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id
INNER JOIN {$wpdb->prefix}postmeta send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to' AND CAST(send_to.meta_value AS CHAR) = %d
INNER JOIN {$wpdb->prefix}postmeta mstat ON mstat.post_id = con_id.post_id AND mstat.meta_key='_status' AND mstat.meta_value=%s";

            $count = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id(), MM_Message_Model::UNREAD));
            wp_cache_set('mm_count_unread', count(array_unique($count)));
        }

        return wp_cache_get('mm_count_unread');
    }

    public static function count_read()
    {
        if (wp_cache_get('mm_count_read') == false) {
            global $wpdb;
            $sql = "SELECT count(conversation.id) FROM {$wpdb->base_prefix}mm_conversation conversation
INNER JOIN {$wpdb->prefix}postmeta con_id ON con_id.meta_key='_conversation_id' AND CAST(con_id.meta_value as UNSIGNED)=conversation.id
INNER JOIN {$wpdb->prefix}postmeta send_to ON send_to.post_id = con_id.post_id AND send_to.meta_key='_send_to' AND CAST(send_to.meta_value AS CHAR) = %d
INNER JOIN {$wpdb->prefix}postmeta mstat ON mstat.post_id = con_id.post_id AND mstat.meta_key='_status' AND mstat.meta_value=%s
GROUP BY conversation.id";

            $count = $wpdb->get_col($wpdb->prepare($sql, get_current_user_id(), MM_Message_Model::READ));
            wp_cache_set('mm_count_read', count($count));
        }

        return wp_cache_get('mm_count_read');
    }

    public static function search($query)
    {
        $ms = MM_Message_Model::model()->all_with_condition(array(
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
        if (empty($ms)) {
            return array();
        }

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

    public static function model($class_name = __CLASS__)
    {
        return parent::model($class_name);
    }
}