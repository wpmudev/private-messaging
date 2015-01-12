<?php
/**
 * Author: Hoang Ngo
 */

if (!class_exists('IG_Uploader_Model')) {
    class IG_Uploader_Model extends IG_Post_Model
    {
        const MODE_LITE = 1, MODE_EXTEND = 2;

        /**
         * @var int
         */
        public $id;
        /**
         * @var string
         */
        public $name;
        /**
         * @var string
         */
        public $content;
        /**
         * @var string
         */
        public $post_status;
        /**
         * @var string
         */
        public $url;
        /**
         * @var int - ID of the wordpress attachment post type
         */
        public $file;
        /**
         * @var int
         * ID of post which link to this
         */
        public $attach_to;

        /**
         * @var int
         * Lite - only need to upload a file
         * Extend - url/file and content will be filled
         */
        public $mode;

        /**
         * @var mix
         * Temp data, just use to hold the $_FILE if exist
         */
        public $file_upload;

        protected $table = 'iup_media';

        protected $mapped = array(
            'id' => 'ID',
            'name' => 'post_title',
            'attach_to' => 'post_parent',
            'content' => 'post_content',
            'post_status' => 'post_status'
        );

        protected $relations = array(
            array(
                'type' => 'meta',
                'key' => '_url',
                'map' => 'url'
            ),
            array(
                'type' => 'meta',
                'key' => '_file',
                'map' => 'file'
            ),
        );

        public function __construct()
        {
            $this->mode = self::MODE_EXTEND;
        }

        public function before_validate()
        {
            if ($this->mode == self::MODE_LITE) {
                //todo
            } else {
                $this->rules = array(
                    'url' => 'valid_url'
                );
            }
        }

        public function after_save()
        {
            if (is_array($this->file_upload)) {
                //do the upload
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                $media_id = media_handle_sideload($this->file_upload['file'], $this->id, $this->content, array(
                    'post_status' => 'inherit'
                ));
                if (is_wp_error($media_id)) {
                    //todo log
                    //return $media_id;
                } else {
                    //all good, store the value now
                    update_post_meta($this->id, '_file', $media_id);
                }
            }
        }

        public function before_save()
        {
            $this->post_status = 'publish';

            if (is_array($this->file_upload) && isset($this->file_upload['file']['name']) && !empty($this->file_upload['file']['name'])) {
                $this->name = $this->file_upload['file']['name'];
            } elseif (filter_var($this->file, FILTER_VALIDATE_INT)) {
                //id passed
                $this->name = pathinfo(wp_get_attachment_url($this->file), PATHINFO_BASENAME);
            } else {
                $this->name = __('Link', ig_uploader()->domain);
            }
        }

        protected function after_validate()
        {
            if ($this->mode == self::MODE_EXTEND) {
                if (($this->exist && empty($this->file)) || !$this->exist) {
                    //we require neither url or file
                    if (empty($this->url) && empty($this->file)) {
                        $this->set_error('file', __("Url or File required", ig_uploader()->domain));
                        $this->set_error('url', __("Url or File required", ig_uploader()->domain));
                    }
                }
            }

            if (!empty($this->errors)) {
                return false;
            }
            return true;
        }

        function get_max_file_upload()
        {
            $max_upload = (int)(ini_get('upload_max_filesize'));
            $max_post = (int)(ini_get('post_max_size'));
            $memory_limit = (int)(ini_get('memory_limit'));
            $upload_mb = min($max_upload, $max_post, $memory_limit);

            return $upload_mb;
        }

        public function mime_to_icon($mime = '')
        {
            if (empty($mime)) {
                $mime = get_post_mime_type($this->file);
            }
            $type = explode('/', $mime);
            $type = array_shift($type);
            $image = '';
            switch ($type) {
                case 'image':
                    $image = '<i class="glyphicon glyphicon-picture"></i>';
                    break;
                case 'video':
                    $image = '<i class="glyphicon glyphicon-film"></i>';
                    break;
                case 'text':
                    $image = '<i class="glyphicon glyphicon-font"></i>';
                    break;
                case 'audio':
                    $image = '<i class="glyphicon glyphicon-volume-up"></i>';
                    break;
                case 'application':
                    $image = '<i class="glyphicon glyphicon-hdd"></i>';
                    break;
            }

            if (empty($image)) {
                if (!empty($this->url)) {
                    $image = '<i class="glyphicon glyphicon-globe"></i>';
                } else {
                    $image = '<i class="glyphicon glyphicon-file"></i>';
                }
            }

            return $image;
        }

        public static function model($class_name = __CLASS__)
        {
            return parent::model($class_name);
        }
    }
}