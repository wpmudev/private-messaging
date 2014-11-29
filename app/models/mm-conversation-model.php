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
    public $date_created;

    /**
     * @var Int
     */
    public $message_count;

    /**
     * @var String
     * IDs of the messages from this conversation
     */
    public $message_index;
    /**
     * @var String
     * IDs of the users join in this conversation
     */
    public $user_index;

    /**
     * @var Int
     * ID of user who create this conversation
     */
    public $send_from;

    /**
     * @var
     */
    public $site_id;

    public $status;

    public function get_messages()
    {
        $models = MM_Message_Model::model()->find_by_ids($this->message_index, false, false, 'ID DESC');

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

        //migrate the code
        self::upgrade();

        $query = $driver->from($model->get_table() . ' conversation')->disableSmartJoin()
            ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
           /* ->innerJoin($wpdb->postmeta . " meta ON meta.meta_key='_conversation_id' AND meta.meta_value=conversation.id")
            ->innerJoin($wpdb->postmeta . " send_to ON send_to.meta_key='_send_to' AND send_to.post_id=meta.post_id")*/
            ->where('mstat.user_id', get_current_user_id())
            ->where('mstat.status', array(MM_Message_Status_Model::STATUS_READ, MM_Message_Status_Model::STATUS_UNREAD))
           /* ->where("CAST(send_to.meta_value AS UNSIGNED)", get_current_user_id())*/
            ->orderBy("conversation.date_created DESC")->limit($offset . ',' . $per_page)->groupBy('conversation.id');

        $res = $query->execute();

        $ids = $res->fetchAll(PDO::FETCH_COLUMN, 0);

        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date_created DESC');
        return $models;
    }

    public static function get_archive()
    {
        global $wpdb;
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;

        $total_pages = ceil(self::count_all() / $per_page);

        mmg()->global['conversation_total_pages'] = $total_pages;
        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();

        //migrate the code
        self::upgrade();

        $query = $driver->from($model->get_table() . ' conversation')->disableSmartJoin()
            ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
            ->where('mstat.user_id', get_current_user_id())
            ->where('mstat.status', MM_Message_Status_Model::STATUS_ARCHIVE)
            ->orderBy("conversation.date_created DESC")->limit($offset . ',' . $per_page)->groupBy('conversation.id');

        $res = $query->execute();
        $ids = $res->fetchAll(PDO::FETCH_COLUMN, 0);

        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date_created DESC');
        return $models;
    }

    public function is_archive()
    {
        $status = $this->get_current_status();
        return $status->status == MM_Message_Status_Model::STATUS_ARCHIVE;
    }

    private static function upgrade()
    {
        if (!get_option('mm_upgrade_message_status')) {
            $models = MM_Conversation_Model::model()->find_all();
            foreach ($models as $model) {
                $model->status = MM_Message_Status_Model::STATUS_READ;
                $model->save();
            }
            update_option('mm_upgrade_message_status', 1);
        };
    }

    public function get_last_message()
    {
        $ids = explode(',', $this->message_index);
        $ids = array_unique(array_filter($ids));
        $id = array_pop($ids);

        $model = MM_Message_Model::model()->find($id);
        if (is_object($model)) {
            return $model;
        }
    }

    public function get_first_message()
    {
        $ids = explode(',', $this->message_index);
        $ids = array_unique(array_filter($ids));
        $id = array_shift($ids);
        $model = MM_Message_Model::model()->find($id);
        if (is_object($model)) {
            return $model;
        }
    }

    public function update_index($id)
    {
        $index = explode(',', $this->message_index);
        $index = array_filter($index);
        $index[] = $id;
        $this->message_index = implode(',', $index);

        //update users
        $messages = $this->get_messages();
        $ids = array();
        foreach ($messages as $m) {
            $ids[] = $m->send_from;
            $ids[] = $m->send_to;
        }
        $ids = array_filter(array_unique($ids));
        $this->user_index = implode(',', $ids);

        $models = MM_Message_Model::model()->find_by_attributes(array(
            'conversation_id' => $this->id
        ));
        $this->message_count = count($models);

        $this->save();
    }

    public function get_users()
    {
        $ids = explode(',', $this->message_index);
        $ids = array_unique(array_filter($ids));
        $users = get_users(array(
            'include' => $ids
        ));

        return $users;
    }

    public function before_save()
    {
        if (!$this->exist) {
            $this->date_created = date('Y-m-d H:i:s');
            $this->send_from = get_current_user_id();
            $this->site_id = get_current_blog_id();
        }
    }

    public function after_save()
    {
        wp_cache_delete('mm_count_all');
        wp_cache_delete('mm_count_read');
        wp_cache_delete('mm_count_unread');
        //each time this saving, we add a new status

    }

    public function get_current_status()
    {
        $model = MM_Message_Status_Model::model()->find_one_with_attributes(array(
            'conversation_id' => $this->id,
            'type' => MM_Message_Status_Model::TYPE_CONVERSATION,
            'user_id' => get_current_user_id()
        ), 'date_created DESC');
        if (is_object($model)) {
            return $model;
        }
        return false;
    }

    public static function get_unread()
    {
        global $wpdb;
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;
        $total_pages = ceil(self::count_unread() / $per_page);
        mmg()->global['conversation_total_pages'] = $total_pages;

        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();

        $query = $driver->from($model->get_table() . ' conversation')->select(array('conversation.id'))->disableSmartJoin()
            ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
            ->where('mstat.user_id', get_current_user_id())
            ->where('mstat.status', MM_Message_Status_Model::STATUS_UNREAD)
            ->where('mstat.type', MM_Message_Status_Model::TYPE_CONVERSATION)
            ->orderBy("conversation.date_created DESC")->limit($offset . ',' . $per_page)->groupBy('conversation.id');

        $res = $query->execute();
        $ids = $res->fetchAll(PDO::FETCH_COLUMN, 0);

        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date_created DESC');
        return $models;
    }

    public static function get_read()
    {
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;
        $total_pages = ceil(self::count_read() / $per_page);
        mmg()->global['conversation_total_pages'] = $total_pages;

        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();

        $query = $driver->from($model->get_table() . ' conversation')->disableSmartJoin()
            ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
            ->where('mstat.user_id', get_current_user_id())
            ->where('mstat.status', MM_Message_Status_Model::STATUS_READ)
            ->where('mstat.type', MM_Message_Status_Model::TYPE_CONVERSATION)
            ->orderBy("conversation.date_created DESC")->limit($offset . ',' . $per_page)->groupBy('conversation.id');
        $res = $query->execute();
        $ids = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date_created DESC');

        return $models;
    }

    public static function get_sent()
    {
        $per_page = mmg()->setting()->per_page;
        $paged = fRequest::get('mpaged', 'int', 1);

        $offset = ($paged - 1) * $per_page;
        $total_pages = ceil(self::count_all() / $per_page);
        mmg()->global['conversation_total_pages'] = $total_pages;

        $messages = MM_Message_Model::model()->find_by_attributes(array(
            'send_from' => get_current_user_id()
        ));
        $ids = array();
        foreach ($messages as $message) {
            $ids[] = $message->conversation_id;
        }
        $ids = array_unique(array_filter($ids));

        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();

        $query = $driver->from($model->get_table() . ' conversation')->disableSmartJoin()
            ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
            ->where('mstat.status', array(MM_Message_Status_Model::STATUS_READ, MM_Message_Status_Model::STATUS_UNREAD))
            ->where('conversation.id', $ids)
            ->orderBy("conversation.date_created DESC")->limit($offset . ',' . $per_page)->groupBy('conversation.id');
        $res = $query->execute();
        $ids = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        if (empty($ids)) {
            return array();
        }
        $models = $model->find_all_by_ids($ids, false, false, 'date_created DESC');
        return $models;
    }

    public function has_unread()
    {
        $model = $this->get_current_status();

        return $model->status == MM_Message_Status_Model::STATUS_UNREAD;
    }

    public function mark_as_read()
    {
        $model = $this->get_current_status();
        $model->status = MM_Message_Status_Model::STATUS_READ;
        $model->save();
    }

    public static function count_all()
    {
        if (wp_cache_get('mm_count_all') == false) {
            $model = new MM_Conversation_Model();
            $driver = $model->get_driver();

            $query = $driver->from($model->get_table() . ' conversation')->disableSmartJoin()
                ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
                ->where('mstat.status', array(MM_Message_Status_Model::STATUS_READ, MM_Message_Status_Model::STATUS_UNREAD))
                ->where('mstat.user_id=:user_id', array(':user_id' => get_current_user_id()))
                ->groupBy('conversation.id');
            $res = $query->execute();
            $count = $res->rowCount();

            wp_cache_set('mm_count_all', $count);
        }

        return wp_cache_get('mm_count_all');
    }

    public static function count_unread($no_cache = false)
    {
        if (wp_cache_get('mm_count_unread') == false || $no_cache == true) {
            $model = new MM_Conversation_Model();
            $driver = $model->get_driver();
            $query = $driver->from($model->get_table() . ' conversation')->select(array('conversation.id'))->disableSmartJoin()
                ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
                ->where('mstat.user_id', get_current_user_id())
                ->where('mstat.status', MM_Message_Status_Model::STATUS_UNREAD)
                ->where('mstat.type', MM_Message_Status_Model::TYPE_CONVERSATION)
                ->groupBy('conversation.id');
            $res = $query->execute();
            $count = $res->rowCount();

            wp_cache_set('mm_count_unread', $count);
        }

        return wp_cache_get('mm_count_unread');
    }

    public static function count_read($no_cache = false)
    {
        if (wp_cache_get('mm_count_read') == false || $no_cache == true) {
            $model = new MM_Conversation_Model();
            $driver = $model->get_driver();
            $query = $driver->from($model->get_table() . ' conversation')->select(array('conversation.id'))->disableSmartJoin()
                ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id=conversation.id')
                ->where('mstat.user_id', get_current_user_id())
                ->where('mstat.status', MM_Message_Status_Model::STATUS_READ)
                ->where('mstat.type', MM_Message_Status_Model::TYPE_CONVERSATION)
                ->groupBy('conversation.id');
            $res = $query->execute();
            $count = $res->rowCount();
            wp_cache_set('mm_count_read', $count);
        }

        return wp_cache_get('mm_count_read');
    }

    public static function search($s)
    {
        global $wpdb;
        $model = new MM_Conversation_Model();
        $driver = $model->get_driver();
        if (!empty($s)) {
            $query = $driver->from($model->get_table() . ' conversation')->disableSmartJoin()
                ->innerJoin(MM_Message_Status_Model::model()->get_table() . ' mstat ON mstat.conversation_id = conversation.id')
                ->innerJoin($wpdb->postmeta . " meta ON meta.meta_key='_conversation_id' AND meta.meta_value = conversation.id")
                ->innerJoin($wpdb->posts . " posts ON posts.ID = meta.post_id")
                ->innerJoin($wpdb->users . " users ON users.id = posts.post_author")
                ->where("mstat.user_id= ? AND (posts.post_title LIKE ? OR posts.post_content LIKE ? OR users.user_login LIKE ?)",
                    get_current_user_id(), "%$s%", "%$s%", "%$s%")
                ->groupBy('conversation.id');
            $res = $query->execute();

            $ids = $res->fetchAll(PDO::FETCH_COLUMN, 0);

            $ids = array_filter(array_unique($ids));
            if (empty($ids)) {
                return array();
            }
            $models = $model->find_all_by_ids($ids, false, false, 'date_created DESC');
            return $models;
        } else {
            return self::get_conversation();
        }
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