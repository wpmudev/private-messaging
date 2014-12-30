<?php if (is_array($models) && count($models)): ?>
    <div class="file-view-port">
        <?php foreach ($models as $model): ?>
            <?php $this->render_partial(apply_filters('igu_icon_file_template', '_single_icon'), array(
                'model' => $model
            )) ?>
            <?php
            $this->footer_model[] = $model;
            add_action('wp_footer', array(&$this, 'footer_modal')) ?>
        <?php endforeach; ?>
    </div>
    <script type="text/javascript">
        jQuery(function ($) {
            $('.igu-media-icon').mouseenter(function () {
                $(this).find('.igu-media-info').removeClass('hide');
            }).mouseleave(function () {
                $(this).find('.igu-media-info').addClass('hide');
            });
            $('.igu-media-info a').click(function (e) {
                e.preventDefault();
            })
        })
    </script>
    <div class="clearfix"></div>
<?php else: ?>
    <p class="no-file"><?php _e("No sample file.", ig_uploader()->domain) ?></p>
<?php endif; ?>