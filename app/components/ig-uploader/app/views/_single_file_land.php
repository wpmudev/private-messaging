<?php
$colors = array(
    'igu-blue',
    'igu-pink',
    'igu-dark-blue',
    'igu-green',
    'igu-black',
    'igu-yellow',
    'igu-purple',
    'igu-grey',
    'igu-green-alt',
    'igu-red',
    'igu-marine',
);
$color = $colors[array_rand($colors)];
?>
<div class="igu-media-file-land" id="igu-media-file-<?php echo $model->id ?>" data-id="<?php echo $model->id ?>">
    <div class="well well-sm">
        <div class="row no-margin">
            <div class="col-md-10 col-sm-10 col-xs-10 no-padding">
                <div class="igu-media-file-thumbnail hidden-xs hidden-sm <?php echo $color ?>">
                    <?php echo $model->mime_to_icon() ?>
                </div>
                <div class="igu-media-file-meta">
                    <h5><?php echo wp_trim_words($model->name,6) ?></h5>

                    <p class="text-muted small"><?php echo get_the_date(null, $model->id) ?></p>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="col-md-2 col-sm-2 col-xs-2 no-padding">
                <div class="btn-group-vertical btn-group-xs pull-right" role="group">
                    <button data-id="<?php echo $model->id ?>"
                       data-target="#igu-uploader-form-<?php echo $model->id ?>" type="button"
                       class="igu-file-update btn btn-default btn-xs" data-anchor=".popover-anchor-<?php echo $model->id ?>">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <button data-id="<?php echo $model->id ?>" type="button"
                       class="igu-file-delete btn btn-xs btn-danger">
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>

</div>