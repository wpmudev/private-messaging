<div class="ig-container">
    <?php foreach ($models as $model): ?>
        <div class="modal" id="igu-modal-<?php echo $model->id ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?php echo $model->name ?></h4>
                    </div>
                    <div class="modal-body sample-pop" style="max-height:450px;overflow-y:scroll">
                        <?php
                        $file = $model->file;
                        //check does this file exist

                        $file_url = '';
                        $show_image = false;

                        if ($file) {
                            $file_url = wp_get_attachment_url($file);
                            $mime = explode('/', get_post_mime_type($file));
                            if (array_shift($mime) == 'image') {
                                $show_image = true;
                            }
                        }
                        if ($show_image) {
                            echo '<img src="' . $file_url . '"/>';
                        } elseif ($file) {
                            //show meta
                            ?>
                            <ul class="list-group">
                                <li class="list-group-item upload-item">
                                    <i class="glyphicon glyphicon-floppy-disk"></i>
                                    <?php _e('Size', je()->domain) ?>:
                                    <strong><?php
                                        $tfile = get_attached_file($file);
                                        //check does this files has deleted
                                        if ($tfile) {
                                            $f = filesize(get_attached_file($file));
                                            echo $f;
                                        } else {
                                            echo __("N/A", je()->domain);
                                        }
                                        ?></strong>
                                </li>
                                <li class="list-group-item upload-item">
                                    <i class="glyphicon glyphicon-file"></i>
                                    <?php _e('Type', je()->domain) ?>:
                                    <strong><?php echo ucwords(get_post_mime_type($file)) ?></strong>
                                </li>
                            </ul>
                        <?php
                        } else {
                            ?>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <i class="glyphicon glyphicon-link"></i>
                                    <strong><?php _e('Link', je()->domain) ?></strong>:
                                    <?php echo $model->url ?>
                                </li>
                                <div class="clearfix"></div>
                            </ul>
                        <?php
                        }?>
                    </div>
                    <div class="modal-footer">
                        <?php if ($model->url): ?>
                            <a class="btn btn-info" rel="nofollow"
                               href="<?php echo esc_attr($model->url) ?>" target="_blank">
                                <?php _e("Visit Link", je()->domain) ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($model->file): ?>
                            <a href="<?php echo $file_url ?>" download
                               class="btn btn-info"><?php _e('Download File', je()->domain) ?></a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-default attachment-close" data-dismiss="modal">Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('.igu-media-info a').leanModal({
            closeButton: '.attachment-close',
            top: '1%',
            width: '90%'
        });
    })
</script>