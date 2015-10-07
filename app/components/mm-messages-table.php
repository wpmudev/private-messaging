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
        return array('widefat', 'fixed', $this->_args['plural'], 'table', 'table-bordered', 'mm-messages-table');
    }

    function get_columns()
    {
        return $columns = array(
            'col_name' => __('Conversation', mmg()->domain),
            'col_last_message' => __('Last Message', mmg()->domain),
            'col_started' => __('Started', mmg()->domain),
            'col_last_activated' => __("Last Active", mmg()->domain),
            'col_action' => ''
        );
    }

    public function column_col_action(MM_Conversation_Model $item)
    {
        $text = sprintf('<a class="button button-small" href="%s"><i class="fa fa-eye"></i> ' . __("View", mmg()->domain) . '</a>&nbsp;
                <a class="button button-small lock-conv" data-type="' . ($item->is_lock() ? 'unlock' : 'lock') . '" data-id="' . $item->id . '" href="#">%s</a>&nbsp;

                <form method="post" style="display: inline" class="delete-message-frm">
                                                <input type="hidden" name="id" value="'.$item->id.'">
                                                <input type="hidden" name="action" value="mm_delete_user_conversation">

                                                <button type="submit" class="button button-small"><i
                                                        class="fa fa-trash"></i> '.__("Delete", mmg()->domain).'</button>
                                            </form>
                ',

            admin_url('admin.php?page=mm_view&id=' . $item->id), ($item->is_lock()) ? '<i class="fa fa-unlock"></i> ' . __("Unlock", mmg()->domain) : '<i class="fa fa-lock"></i> ' . __("Lock", mmg()->domain));
        return $text;
    }

    public function column_col_name(MM_Conversation_Model $item)
    {
        $users = array();
        foreach (explode(',', $item->user_index) as $ui) {
            $users[] = MM_Message_Model::model()->get_name($ui);
        }

        $text = sprintf(__("%s have joined in this conversation, %d messages in total", mmg()->domain),
            implode(', ', $users), $item->message_count);

        return $text;
    }

    public function column_col_last_message(MM_Conversation_Model $item)
    {
        $message = $item->get_last_message();
        $first = $item->get_first_message();
        $text = sprintf('<strong>%s </strong><p >%s </p>',
            $first->subject,
            wp_trim_words(wpautop($message->content), apply_filters('mmg_backend_last_message_trim', 10))
        );
        return $text;
    }

    public function column_col_started(MM_Conversation_Model $item)
    {
        return date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->date_created));
    }

    public function column_col_last_activated(MM_Conversation_Model $item)
    {
        $message = $item->get_last_message();
        return date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($message->date));
    }

    function prepare_items()
    {
        global $wpdb;

        $totals = $wpdb->get_var($wpdb->prepare('SELECT COUNT(id) from ' . $wpdb->base_prefix . 'mm_conversation WHERE site_id =%d', get_current_blog_id()));

        //How many to display per page?
        $perpage = apply_filters('mmg_message_table_perpage', 10);
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
                $this->items = MM_Conversation_Model::search($_GET['s'], $perpage);
                $totals = mmg()->global['conversation_total_pages'];
                $totalpages = ceil($totals / $perpage);
                $this->set_pagination_args(array(
                    "total_items" => $totals,
                    "total_pages" => $totalpages,
                    "per_page" => $perpage,
                ));
            }
        } else {
            $this->items = MM_Conversation_Model::model()->find_all('site_id =%d', array(
                get_current_blog_id()
            ), $perpage, $offset);
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];
        ?>
        <form method="get" action="<?php echo admin_url('admin . php') ?>">
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