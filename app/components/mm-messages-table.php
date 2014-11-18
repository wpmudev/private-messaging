<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Author: hoangngo
 */
class MM_Messages_Table extends WP_List_Table
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @access public
     * @param array $args The array of arguments.
     */
    public function __construct($args = array())
    {
        parent::__construct(array_merge(array(
            'plural' => 'messages',
            'autoescape' => false,
        ), $args));
    }

    function get_table_classes()
    {
        return array('widefat', 'fixed', $this->_args['plural'], 'table', 'table-bordered');
    }

    function get_columns()
    {
        return $columns = array(
            'col_name' => __('Last Message', mmg()->domain),
            'col_users' => __('Users', mmg()->domain),
            'col_count' => __("Total Messages", mmg()->domain),
        );
    }


    /*function column_default($item, $column_name)
    {
        $value = '';
        switch ($column_name) {
            default        :
                $value = $item[$column_name];
                break;
        }
        return $value;
    }*/

    public function column_col_name(MM_Conversation_Model $item)
    {
        $message = $item->get_last_message();
        return sprintf('<p><strong><a href="%s">%s</a></strong></p><p>%s</p>', admin_url('admin.php?page=mm_view&id='.$item->id), $message->subject, mmg()->mb_word_wrap(strip_tags($message->content)));
    }

    public function column_col_users(MM_Conversation_Model $item)
    {
        $ids = $item->user_index;
        $ids = array_filter(array_unique(explode(',', $ids)));
        $users = array();
        foreach ($ids as $id) {
            $user = get_user_by('id', $id);
            $users[] = $user->user_login;
        }
        return implode(',', $users);
    }

    public function column_col_count(MM_Conversation_Model $item)
    {
        return $item->count;
    }

    function prepare_items()
    {
        global $wpdb;

        $totals = $wpdb->get_var('SELECT COUNT(id) from ' . $wpdb->prefix . 'mm_conversation');

        //How many to display per page?
        $perpage = 10;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
        //Page Number
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }
        $offset = ($this->get_pagenum() - 1) * $perpage;
        //How many pages do we have in total?
        $totalpages = ceil($totals / $perpage);
        //adjust the query to take pagination into account
        /* -- Register the pagination -- */
        $this->set_pagination_args(array(
            "total_items" => $totals,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ));
        //The pagination links are automatically built according to those parameters

        /* — Register the Columns — */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        if (isset($_GET['s']) && !empty($_GET['s'])) {
            if (isset($_GET['s'])) {
                $sql = "SELECT _conv.id FROM wp_posts posts
INNER JOIN wp_postmeta conv_id ON posts.ID = conv_id.post_id AND conv_id.meta_key='_conversation_id'
INNER JOIN wp_postmeta send_to ON posts.ID = send_to.post_id AND send_to.meta_key='_send_to'
INNER JOIN wp_mm_conversation conv ON conv.id = conv_id.meta_value
INNER JOIN wp_users ON wp_users.ID=posts.post_author OR wp_users.ID = send_to.meta_value
WHERE (posts.post_title LIKE %s OR posts.post_content LIKE %s OR wp_users.user_login LIKE %s) AND posts.post_status='publish'";
                $totals = $wpdb->get_var($wpdb->prepare(str_replace('_conv.id', 'COUNT(DISTINCT(conv.id))', $sql), "%$_GET[s]%", "%$_GET[s]%", "%$_GET[s]%"));
                $ids = $wpdb->get_col($wpdb->prepare(str_replace('_conv.id', 'DISTINCT(conv.id)', $sql), "%$_GET[s]%", "%$_GET[s]%", "%$_GET[s]%"));
                $totalpages = ceil($totals / $perpage);
                $this->set_pagination_args(array(
                    "total_items" => $totals,
                    "total_pages" => $totalpages,
                    "per_page" => $perpage,
                ));

                $this->items = MM_Conversation_Model::model()->all_with_condition('id IN (' . implode(',', $ids) . ') LIMIT ' . $offset . ',' . $perpage, array(//':ids' => implode(',', $ids)
                ));

            }
        } else {
            $this->items = MM_Conversation_Model::model()->all_with_condition(' LIMIT ' . $offset . ',' . $perpage);
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];
        ?>
        <form method="get" action="<?php echo admin_url('admin.php') ?>">
            <input type="hidden" name="page" value="mm_main">
            <?php $this->search_box(__("Search", mmg()->domain), 'mm_conv_search'); ?>
        </form>
        <div class="clearfix" style="height:20px"></div>

        <table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <?php $this->print_column_headers(false); ?>
            </tr>
            </tfoot>

            <tbody id="the-list"<?php
            if ($singular) {
                echo " data-wp-lists='list:$singular'";
            } ?>>
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>
        </table>
        <?php
        $this->display_tablenav('bottom');
    }
}