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

        function text()
        {
            $string = array(
                'Bold' => __("Bold", mmg()->domain),
                'Italic' => __("Italic", mmg()->domain),
                'Underline' => __("Underline", mmg()->domain),
                'Strikethrough' => __("Strikethrough", mmg()->domain),
                'Subscript' => __("Subscript", mmg()->domain),
                'Superscript' => __("Superscript", mmg()->domain),
                'Align left' => __("Align left", mmg()->domain),
                'Center' => __("Center", mmg()->domain),
                'Align right' => __("Align right", mmg()->domain),
                'Justify' => __("Justify", mmg()->domain),
                'Font Name' => __("Font Name", mmg()->domain),
                'Font Size' => __("Font Size", mmg()->domain),
                'Font Color' => __("Font Color", mmg()->domain),
                'Remove Formatting' => __("Remove Formatting", mmg()->domain),
                'Cut' => __("Cut", mmg()->domain),
                'Your browser does not allow the cut command. Please use the keyboard shortcut Ctrl/Cmd-X' => __("Your browser does not allow the cut command. Please use the keyboard shortcut Ctrl/Cmd-X", mmg()->domain),
                'Copy' => __("Copy", mmg()->domain),
                'Your browser does not allow the copy command. Please use the keyboard shortcut Ctrl/Cmd-C' => __("Your browser does not allow the copy command. Please use the keyboard shortcut Ctrl/Cmd-C", mmg()->domain),
                'Paste' => __("Paste", mmg()->domain),
                'Your browser does not allow the paste command. Please use the keyboard shortcut Ctrl/Cmd-V' => __("Your browser does not allow the paste command. Please use the keyboard shortcut Ctrl/Cmd-V", mmg()->domain),
                'Paste your text inside the following box' => __("Paste your text inside the following box", mmg()->domain),
                'Paste Text' => __("Paste Text", mmg()->domain),
                'Bullet list' => __("Bullet list", mmg()->domain),
                'Numbered list' => __("Numbered list", mmg()->domain),
                'Undo' => __("Undo", mmg()->domain),
                'Redo' => __("Redo", mmg()->domain),
                'Rows' => __("Rows", mmg()->domain),
                'Cols' => __("Cols", mmg()->domain),
                'Insert a table' => __("Insert a table", mmg()->domain),
                'Insert a horizontal rule' => __("Insert a horizontal rule", mmg()->domain),
                'Code' => __("Code", mmg()->domain),
                'Width (optional)' => __("Width (optional)", mmg()->domain),
                'Height (optional)' => __("Height (optional)", mmg()->domain),
                'Insert an image' => __("Insert an image", mmg()->domain),
                'E-mail' => __("E-mail", mmg()->domain),
                'Insert an email' => __("Insert an email", mmg()->domain),
                'URL' => __("URL", mmg()->domain),
                'Insert a link' => __("Insert a link", mmg()->domain),
                'Unlink' => __("Unlink", mmg()->domain),
                'More' => __("More", mmg()->domain),
                'Insert an emoticon' => __("Insert an emoticon", mmg()->domain),
                'Video URL' => __("Video URL", mmg()->domain),
                'Insert' => __("Insert", mmg()->domain),
                'Insert a YouTube video' => __("Insert a YouTube video", mmg()->domain),
                'Insert current date' => __("Insert current date", mmg()->domain),
                'Insert current time' => __("Insert current time", mmg()->domain),
                'Print' => __("Print", mmg()->domain),
                'View source' => __("View source", mmg()->domain),
                'Description (optional)' => __("Description (optional)", mmg()->domain),
                'Enter the image URL' => __("Enter the image URL", mmg()->domain),
                'Enter the e-mail address' => __("Enter the e-mail address", mmg()->domain),
                'Enter the displayed text' => __("Enter the displayed text", mmg()->domain),
                'Enter URL' => __("Enter URL", mmg()->domain),
                'Enter the YouTube video URL or ID' => __("Enter the YouTube video URL or ID", mmg()->domain),
                'Insert a Quote' => __("Insert a Quote", mmg()->domain),
                'Invalid YouTube video' => __("Invalid YouTube video", mmg()->domain),
                'dateFormat' => __("dateFormat", mmg()->domain),
            );

            return $string;
        }

        function footer_scripts()
        {
            $locale = str_replace('_', '-', get_locale());
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
                                    style: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/jquery.sceditor.default.min.css'?>',
                                    locale: '<?php echo $locale ?>'
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
                                    style: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/jquery.sceditor.default.min.css'?>',
                                    locale: '<?php echo $locale ?>'
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
            //load language
            $string = $this->text();
            $string = json_encode($string);
            //generate locale js for sceditor
            $translate = json_encode($string);
            $locale = explode('_', get_locale());
            //generate js file
            $template = '(function ($) {
	\'use strict\';

	$.sceditor.locale["' . strtolower($locale[0]) . '"] ={{json}};
})(jQuery);';
            $template = str_replace('{{json}}',$string,$template);

            if (mmg()->can_compress()) {
                $runtime_path = mmg()->plugin_path . 'framework/runtime';
                //write it
                $file_path = $runtime_path . '/sceditor-translate.js';
                file_put_contents($file_path, $template);
                //convert to url
                $url = mmg()->plugin_url . 'framework/runtime/sceditor-translate.js';
                wp_register_script('mm_sceditor_translate', $url);
            }

            wp_register_script('mm_sceditor_xhtml', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/plugins/bbcode.js', array('jquery', 'mm_sceditor'));
            //cause the adminbar needed from anywhere,so we bind it
            wp_enqueue_style('mm_sceditor', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/themes/default.min.css');
        }
    }
}
new MM_WYSIWYG();