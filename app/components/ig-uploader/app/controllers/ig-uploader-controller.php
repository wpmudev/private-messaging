<?php
/**
 * Author: Hoang Ngo
 */
if (!class_exists('IG_Uploader_Controller')) {
    class IG_Uploader_Controller extends IG_Request
    {
        public $is_admin = false;
        public $can_upload = false;
        /**
         * @var array IG_Uploader_Model
         */
        public $footer_model;

        public function __construct($can_upload)
        {
            $this->can_upload = $can_upload;
            if (is_user_logged_in()) {
                if ($can_upload) {
                    add_action('wp_loaded', array(&$this, 'handler_upload'));
                    add_action('wp_ajax_igu_file_delete', array(&$this, 'delete_file'));
                    add_action('wp_ajax_iup_load_upload_form', array(&$this, 'load_upload_form'));
                    add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
                    add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
                }
            }
            add_filter('igu_single_file_template', array(&$this, 'single_file_template'));
        }

        function scripts()
        {
            wp_enqueue_media();
        }

        function single_file_template()
        {
            return '_single_file_land';
        }

        function load_upload_form()
        {
            if (!wp_verify_nonce(ig_uploader()->get('_wpnonce'), 'iup_load_upload_form')) {
                return;
            }
            $id = ig_uploader()->get('id');
            $model = null;
            if ($id !== null) {
                $model = IG_Uploader_Model::model()->find($id);
            }
            if (!is_object($model)) {
                $model = new IG_Uploader_Model();
            }

            $this->render_partial('_uploader_form', array(
                'model' => $model
            ));
            exit;
        }

        function delete_file()
        {
            if (!wp_verify_nonce(ig_uploader()->post('_wpnonce'), 'igu_file_delete')) {
                return;
            }

            $model = IG_Uploader_Model::model()->find(ig_uploader()->post('id', 0));
            if (is_object($model)) {
                $model->delete();
            }
            exit;
        }

        function handler_upload()
        {
            if (ig_uploader()->get('igu_uploading')) {
                if (!wp_verify_nonce(ig_uploader()->post('_wpnonce'), 'igu_uploading')) {
                    return;
                }
                $model = '';
                $id = ig_uploader()->post('IG_Uploader_Model[id]', 0);
                if ($id != 0) {
                    $model = IG_Uploader_Model::model()->find($id);
                }
                if (!is_object($model)) {
                    $model = new IG_Uploader_Model();
                }
                $model->import(ig_uploader()->post('IG_Uploader_Model'));
                if (!is_admin()) {
                    if (isset($_FILES['IG_Uploader_Model'])) {
                        $uploaded = $this->rearrange($_FILES['IG_Uploader_Model']);
                        if (!empty($uploaded['file']['name'])) {
                            $model->file_upload = $uploaded;
                        }
                    }
                }
                if ($model->validate()) {
                    $model->save();
                    wp_send_json(array(
                        'status' => 'success',
                        'html' => $this->render_single_file($model, true),
                        'id' => $model->id
                    ));
                } else {
                    wp_send_json(array(
                        'status' => 'fail',
                        'errors' => $model->get_errors()
                    ));
                }
                exit;
            }
        }

        public function upload_form($attribute, $target_model, $is_admin = false, $attributes = array())
        {
            if ($this->can_upload) {
                wp_enqueue_style('igu-uploader');
                wp_enqueue_script('webuipopover');
                wp_enqueue_style('webuipopover');
                wp_enqueue_media();

                $ids = $target_model->$attribute;
                $models = array();
                if (!is_array($ids)) {
                    $ids = explode(',', $ids);
                    $ids = array_filter(array_unique($ids));
                }
                if (!empty($ids)) {
                    $models = IG_Uploader_Model::model()->all_with_condition(array(
                        'status' => 'publish',
                        'post__in' => $ids
                    ));
                }

                //$models[]=IG_Uploader_Model::model()->find(8);

                $mode = IG_Uploader_Model::MODE_EXTEND;

                if ($mode == IG_Uploader_Model::MODE_LITE) {
                    $this->_lite_form();
                } else {
                    $this->_extend_form($models, $attribute, $target_model, $is_admin, $attributes);
                }
            }
        }

        function show_media($model, $attribute)
        {
            wp_enqueue_style('igu-uploader');
            wp_enqueue_script('ig-leanmodal');
            $ids = $model->$attribute;
            $models = array();
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
                $ids = array_filter(array_unique($ids));
            }
            if (!empty($ids)) {
                $models = IG_Uploader_Model::model()->all_with_condition(array(
                    'status' => 'publish',
                    'post__in' => $ids
                ));
            }
            $this->render('show_media', array(
                'models' => $models
            ));
        }

        public function render_single_file($model, $return = false)
        {
            if ($return) {
                return $this->render_partial(apply_filters('igu_single_file_template', '_single_file'), array(
                    'model' => $model
                ), false);
            }
            $this->render_partial(apply_filters('igu_single_file_template', '_single_file'), array(
                'model' => $model
            ));
        }

        public function _lite_form()
        {

        }

        public function _extend_form($models, $attribute, $target_model, $is_admin, $attributes = array())
        {
            if (!isset($attributes['c_id'])) {
                $c_id = uniqid();
            } else {
                $c_id = $attributes['c_id'];
            }
            //place a cookie with hash for the upload can know this come from the component
            wp_localize_script('igu-uploader', 'igu_uploader_' . $c_id, array(
                'title' => __("Upload Attachment", ig_uploader()->domain),
                'add_url' => admin_url('admin-ajax.php?action=iup_load_upload_form&is_admin=' . $is_admin . '&_wpnonce=' . wp_create_nonce('iup_load_upload_form')),
                'edit_url' => admin_url('admin-ajax.php?action=iup_load_upload_form&is_admin=' . $is_admin . '&_wpnonce=' . wp_create_nonce('iup_load_upload_form')) . '&id=',
                'instance' => '',
                'form_submit_url' => esc_url(add_query_arg('igu_uploading', 1)),
                'target_id' => $this->build_id($target_model, $attribute),
                'c_id' => $c_id,
                'ajax_url' => admin_url('admin-ajax.php'),
                'delete_nonce' => wp_create_nonce('igu_file_delete'),
            ));
            wp_enqueue_script('igu-uploader');
            $this->render('_extend_form', array(
                'models' => $models,
                'tmodel' => $target_model,
                'attribute' => $attribute,
                'target_id' => $this->build_id($target_model, $attribute),
                'is_admin' => $is_admin,
                'attributes' => $attributes,
                'c_id' => $c_id,
                'file_frame_title' => __('Please select a file', ig_uploader()->domain)
            ));
        }

        function rearrange($arr)
        {
            foreach ($arr as $key => $all) {
                foreach ($all as $i => $val) {
                    $new[$i][$key] = $val;
                }
            }

            return $new;
        }

        private function build_id($model, $attribute)
        {
            $class_name = get_class($model);

            return sanitize_title($class_name . '-' . $attribute);
        }

        function footer_modal()
        {
            $this->render('footer_modal', array(
                'models' => $this->footer_model
            ));
        }
    }
}