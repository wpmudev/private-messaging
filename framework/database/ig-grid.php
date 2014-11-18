<?php
/**
 * Author: Worpdress, extended Hoang Ngo
 */
if (!class_exists('IG_Grid')) {
    class IG_Grid extends WP_List_Table
    {
        public $model;
        protected $post_type;
        protected $per_page;
        protected $edit_page_url;

        public function __construct($model, $per_page = 20, $edit_page_url)
        {
            $this->model = $model;
            $this->post_type = $model->get_table();
            $this->per_page = $per_page;
            $this->edit_page_url = $edit_page_url;

            $pt_object = get_post_type_object($this->post_type);
            parent::__construct(array(
                'singular' => $pt_object->labels->singular_name, //Singular label
                'plural' => $pt_object->labels->name, //plural label, also this well be one of the table css class
                'ajax' => false //We won't support Ajax for this table
            ));
        }

        public function get_columns()
        {
            //columns will base on the $model
            $columns = array('cb' => '<input type="checkbox" />',);
            foreach ($this->model->get_mapped() as $key => $val) {
                $columns[$key] = __(ucwords(str_replace('_', ' ', $key)), 'ig_framework');
            }
            $columns['ig_action'] = '';
            return $columns;
        }

        public function get_bulk_actions()
        {
            return array(
                'delete' => __('Delete', 'ig_framework'),
            );
        }

        public function process_bulk_action()
        {

            // security check!
            if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

                $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
                $action = 'bulk-' . $this->_args['plural'];

                if (!wp_verify_nonce($nonce, $action))
                    wp_die('Nope! Security check failed!');

            }

            $action = $this->current_action();

            switch ($action) {
                case 'delete':
                    $class = get_class($this->model);
                    if (isset($_POST[$class])) {
                        foreach ($_POST[$class] as $id) {
                            $model = $class::model()->find($id);
                            if (is_object($model)) {
                                $model->delete();
                            }
                        }
                    }
                    break;

            }

            return;
        }


        public function get_hidden_columns()
        {
            return array('id');
        }

        public function get_sortable_columns()
        {
            $columns = array();
            foreach ($this->model->get_mapped() as $key => $val) {
                $columns[$key] = $val;
            }
            return $columns;
        }

        function display_rows()
        {
            //Get the records registered in the prepare_items method
            $records = $this->items;
            list($columns, $hidden) = $this->get_column_info();

            if (!empty($records)) {
                foreach ($records as $rec) {
                    echo '<tr>';
                    foreach ($columns as $column_name => $column_display_name) {
                        if ($column_name == 'cb') {
                            echo sprintf(
                                '<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-%d">Select %s</label>' .
                                '<input type="checkbox" name="%s[]" value="%d" />',
                                $rec->id,
                                $this->_args['singular'],
                                get_class($this->model),
                                $rec->id
                            );
                            echo '</th>';
                        } elseif ($column_name == 'ig_action') {
                            $td = '<td><a class="btn btn-sm btn-default" href="' . $this->edit_page_url . '&id=' . $rec->id . '">' . __('Edit', 'ig_framework') . '</a> | <a  data-model="' . @array_pop(explode('\\', get_class($this->model))) . '" class="btn btn-sm btn-danger ig-delete-btn" href="#' . $rec->id . '">' . __('Delete', 'ig_framework') . '</a> </td>';
                            echo $td;
                        } else {
                            //Style attributes for each col
                            $class = "class='$column_name column-$column_name'";
                            $style = "";
                            if (in_array($column_name, $hidden)) $style = ' style="display:none;"';
                            $attributes = $class . $style;

                            //display the column content, if we have the function for display, continue
                            echo '<td ' . $attributes . '>';
                            if (!method_exists($this, 'column_col_' . $column_name)) {
                                if (property_exists($rec, $column_name))
                                    echo $rec->$column_name;
                            } else {
                                $func_name = 'column_col_' . $column_name;
                                echo $this->$func_name($rec);
                            }
                            echo '</td>';
                        }
                    }
                    echo '</tr>';
                }
            }
        }

        function prepare_items()
        {
            $this->process_bulk_action();
            global $wpdb, $_wp_column_headers;
            $screen = get_current_screen();

            $args = array();
            $class = get_class($this->model);
            //getting items
            $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
            $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
            if (!empty($orderby) & !empty($order)) {
                $args['orderby'] = $orderby;
                $args['order'] = $order;
            }
            $totalitems = count($class::all());
            $perpage = $this->per_page;
            $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
            if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
                $paged = 1;
            }
            $totalpages = ceil($totalitems / $perpage);
            //getting the items for this page
            $args['pagination'] = true;
            $args['posts_per_page'] = $this->per_page;
            $args['paged'] = $paged;
            //update the ordering

            $this->items = $class::all_with_condition($args);

            /* -- Register the pagination -- */
            $this->set_pagination_args(array(
                "total_items" => $totalitems,
                "total_pages" => $totalpages,
                "per_page" => $perpage,
            ));
            //The pagination links are automatically built according to those parameters

            /* -- Register the Columns -- */
            $columns = $this->get_columns();
            $hidden = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);
        }

        /**
         * Override the core function to using bootstrap
         */
        function get_table_classes1()
        {
            return array('table', 'table-striped', 'table-bordered', 'table-hover', 'table-condensed');
        }

        function display1()
        {
            extract($this->_args);

            $this->display_tablenav('top');

            ?>
            <br/>
            <table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
                <thead>
                <tr>
                    <?php $this->print_column_headers(); ?>
                </tr>
                </thead>

                <tbody id="the-list"<?php if ($singular) echo " data-wp-lists='list:$singular'"; ?>>
                <?php $this->display_rows_or_placeholder(); ?>
                </tbody>
            </table>
        <?php
        }
    }
}