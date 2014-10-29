<?php

/**
 * Author: WPMU DEV
 * Name: WYISWYG
 * Description: WYISWYG flavor for message content
 */
class MM_WYSIWYG extends IG_Request
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
        add_action('wp_footer', array(&$this, 'footer_scripts'));
    }

    function footer_scripts()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                if ($('.mm_wsysiwyg').size() > 0) {
                    $('.mm_wsysiwyg').sceditor({
                        plugins: "xhtml",
                        autoUpdate: true,
                        width: '100%',
                        emoticonsEnabled: true,
                        toolbar: "bold,italic,underline,strike|left,center,right,justify|font,size,color,removeformat|cut,copy,paste,pastetext|bulletlist,orderedlist,indent,outdent|link,unlink|date,time|emoticon",
                        emoticonsRoot: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/'?>',
                        style: '<?php echo mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/jquery.sceditor.default.min.css'?>'
                    });
                }
            })
        </script>
    <?php
    }

    function scripts()
    {
        wp_enqueue_script('mm_sceditor', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/jquery.sceditor.min.js', array('jquery'));
        wp_enqueue_script('mm_sceditor_xhtml', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/plugins/xhtml.js', array('jquery','mm_sceditor'));
        wp_enqueue_style('mm_sceditor', mmg()->plugin_url . 'app/addons/wysiwyg/sceditor/minified/themes/default.min.css');
    }
}

new MM_WYSIWYG();