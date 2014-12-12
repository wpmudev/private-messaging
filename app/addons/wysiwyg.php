<?php

/**
 * Author: WPMU DEV
 * Name: WYISWYG
 * Description: Adds a WYSIWYG editor to the message composer.
 */
if (!class_exists('MM_WYSIWYG')) {
    class MM_WYSIWYG extends IG_Request
    {
        public function __construct()
        {
            add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
            add_action('admin_enqueue_scripts', array(&$this, 'scripts'));
            add_action('wp_footer', array(&$this, 'footer_scripts'));
            add_action('admin_footer', array(&$this, 'footer_scripts'));
        }

        function footer_scripts()
        {
            if (!class_exists('Mobile_Detect')) {
                include_once dirname(__FILE__) . '/wysiwyg/Mobile_Detect.php';
            }
            $detect = new Mobile_Detect();
            //because the viewport of mobile, so we minimize the toolbars on mobile
            if ($detect->isMobile()) {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        function load_editor() {
                            if ($('.mm_wsysiwyg').size() > 0) {
                                $('.mm_wsysiwyg').sceditor({
                                    plugins: "bbcode",
                                    autoUpdate: true,
                                    autoExpand: true,
                                    width: '98%',
                                    height: '80%',
                                    resizeMinWidth: '50%',
                                    resizeMaxWidth: '100%',
                                    resizeMaxHeight: '100%',
                                    resizeMinHeight: '50%',
                                    emoticonsEnabled: true,
                                    toolbar: "bold,italic,underline,strike|left,center,right,justify",
                                    emoticonsRoot: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/'?>',
                                    style: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/jquery.sceditor.default.min.css'?>'
                                });
                            }
                        }
                        load_editor();
                        $('body').on('abc', function () {
                            load_editor();
                        });
                    })
                </script>
            <?php
            } else {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        function load_editor() {
                            if ($('.mm_wsysiwyg').size() > 0) {
                                var editors = $('.mm_wsysiwyg').sceditor({
                                    plugins: "xhtml",
                                    autoUpdate: true,
                                    width: '98%',
                                    resizeMinWidth: '-1',
                                    resizeMaxWidth: '100%',
                                    resizeMaxHeight: '100%',
                                    resizeMinHeight: '-1',
                                    readOnly: false,
                                    emoticonsEnabled: true,
                                    toolbar: "bold,italic,underline,strike|left,center,right,justify|font,size,color,removeformat|cut,copy,paste,pastetext|bulletlist,orderedlist,indent,outdent|link,unlink|date,time|emoticon",
                                    emoticonsRoot: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/'?>',
                                    style: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/jquery.sceditor.default.min.css'?>'
                                });
                            }
                        }
                        load_editor();

                        $('body').on('abc', function () {
                            load_editor();
                        });
                    })
                </script>
            <?php
            }
        }

        function scripts()
        {
            wp_register_script('mm_sceditor', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/jquery.sceditor.min.js', array('jquery'));
            wp_register_script('mm_sceditor_xhtml', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/plugins/bbcode.js', array('jquery', 'mm_sceditor'));
            //cause the adminbar needed from anywhere,so we bind it
            wp_enqueue_style('mm_sceditor', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/themes/default.min.css');

        }
    }
}
new MM_WYSIWYG();