<?php
/**
 * Author: Hoang Ngo
 */
if (!class_exists('IG_Uploader_Controller')) {
    class IG_Uploader_Controller extends IG_Request
    {
        public function __construct()
        {
            add_action('wp', array(&$this, 'handler_upload'));
            add_action('wp_ajax_igu_file_delete', array(&$this, 'delete_file'));
            add_action('wp_footer', array(&$this, '_extend_form_upload'));
            add_action('wp_ajax_iup_load_upload_form', array(&$this, 'load_upload_form'));
        }

        function load_upload_form()
        {
            if (!wp_verify_nonce(fRequest::get('_wpnonce'), 'iup_load_upload_form'))
                return;
            $id = fRequest::get('id', 'int', 0);
            $model = IG_Uploader_Model::find($id);
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
            if (!wp_verify_nonce(fRequest::get('_wpnonce'), 'igu_file_delete'))
                return;

            $model = IG_Uploader_Model::find(fRequest::get('id', 'int', 0));
            if (is_object($model)) {
                $model->delete();
            }
            exit;
        }

        function handler_upload()
        {
            if (fRequest::get('igu_uploading')) {
                if (!wp_verify_nonce(fRequest::get('_wpnonce'), 'igu_uploading'))
                    return;
                $model = '';
                $id = fRequest::get('IG_Uploader_Model[id]', 'int', 0);
                if ($id != 0) {
                    $model = IG_Uploader_Model::find($id);
                }
                if (!is_object($model)) {
                    $model = new IG_Uploader_Model();
                }
                $model->import(fRequest::get('IG_Uploader_Model'));
                if (isset($_FILES['IG_Uploader_Model'])) {
                    $uploaded = $this->rearrange($_FILES['IG_Uploader_Model']);
                    if (!empty($uploaded['file']['name'])) {
                        $model->file_upload = $uploaded;
                    }
                }
                if ($model->validate()) {

                    $model->save();
                    fJSON::output(array(
                        'status' => 'success',
                        'html' => $this->render_single_file($model, true),
                        'id' => $model->id
                    ));
                } else {
                    fJSON::output(array(
                        'status' => 'fail',
                        'errors' => $model->get_errors()
                    ));
                }
                exit;
            }
        }

        public function upload_form($attribute, $target_model, $container)
        {
            wp_enqueue_style('igu-uploader');
            wp_enqueue_script('popoverasync');
            wp_enqueue_script('jquery-frame-transport');

            $ids = $target_model->$attribute;
            $models = array();
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
                $ids = array_filter(array_unique($ids));
            }
            if (!empty($ids)) {
                $models = IG_Uploader_Model::all_with_condition(array(
                    'status' => 'publish',
                    'post__in' => $ids
                ));
            }

            $mode = IG_Uploader_Model::MODE_EXTEND;

            if ($mode == IG_Uploader_Model::MODE_LITE) {
                $this->_lite_form();
            } else {
                $this->_extend_form($models, $attribute, $target_model, $container);
            }
        }

        public function render_single_file($model, $return = false)
        {
            if ($return) {
                return $this->render_partial('_single_file', array(
                    'model' => $model
                ), false);
            }
            $this->render_partial('_single_file', array(
                'model' => $model
            ));
        }

        public function _lite_form()
        {

        }

        public function _extend_form($models, $attribute, $target_model, $container)
        {
            $cid = uniqid();

            $this->render('_extend_form', array(
                'models' => $models,
                'tmodel' => $target_model,
                'attribute' => $attribute,
                'target_id' => $this->build_id($target_model, $attribute),
                'container' => $container
            ));
        }

        function _extend_form_upload()
        {
            ?>
            <div class="mmessage-container hide">
                <div id="igu_upload_form_container">
                    <div class="igu-loader" style="text-align: center;;width:100%">
                        <i class="fa fa-2x fa-circle-o-notch fa-spin"></i>
                    </div>
                </div>
            </div>
        <?php
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
    }
}