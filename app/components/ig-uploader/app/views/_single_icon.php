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
<div class="igu-media-file-land igu-media-icon" style="margin-right: 1%;width: 32.3%;padding: 0" id="igu-media-file-<?php echo $model->id ?>">
    <div class="well well-sm">
        <div class="row no-margin">
            <div class="col-md-12 col-sm-12 col-xs-12 no-padding">
                <div class="igu-media-file-thumbnail hidden-xs hidden-sm <?php echo $color ?>">
                    <?php echo $model->mime_to_icon() ?>
                </div>
                <div class="igu-media-file-meta">
                    <h5><?php echo ig_uploader()->trim_text($model->name, 17) ?></h5>

                    <p class="text-muted small"><?php echo get_the_date(null, $model->id) ?></p>

                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="igu-media-info hide">
        <a href="#igu-modal-<?php echo $model->id ?>"><i class="fa fa-search-plus fa-2x"></i></a>
    </div>
</div>