<?php $model = new MM_Message_Model(); ?>
<div class="wrap">
    <div class="mmessage-container">
        <div class="page-header">
            <h2><?php echo sprintf(__("Migrate Tools, found %d conversations", mmg()->domain), count($data)) ?></h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <p>
                    <?php _e("Please click here to import the data to new messaging system", mmg()->domain) ?>
                    <button type="button"
                            class="btn btn-primary btn-sm mm_import"><?php _e("Import All", mmg()->domain) ?></button>
                </p>
                <p>
                    <?php _e("If the import go wrong, please click here to clean up", mmg()->domain) ?>
                    <button type="button"
                            class="btn btn-danger btn-sm mm_clean"><?php _e("Clean up", mmg()->domain) ?></button>
                </p>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.mm_import').click(function () {
            var that = $(this);
            $.ajax({
                type: 'POST',
                data: {
                    action: 'mm_import',
                    _wpnonce: '<?php echo wp_create_nonce('mm_import') ?>'
                },
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                beforeSend: function () {
                    that.attr('disabled', 'disabled');
                },
                success: function () {
                    location.reload();
                }
            })
        });
        $('.mm_clean').click(function () {
            var that = $(this);
            $.ajax({
                type: 'POST',
                data: {
                    action: 'mm_cleanup',
                    _wpnonce: '<?php echo wp_create_nonce('mm_cleanup') ?>'
                },
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                beforeSend: function () {
                    that.attr('disabled', 'disabled');
                },
                success: function () {
                    location.reload();
                }
            })
        })
    })
</script>
