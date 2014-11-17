<div class="igu-media-file border-fade" id="igu-media-file-<?php echo $model->id ?>" data-id="<?php echo $model->id ?>">

    <div class="igu-actions">
        <button data-id="<?php echo $model->id ?>"
                data-target="#igu-uploader-form-<?php echo $model->id ?>" type="button"
                class="btn btn-primary btn-xs igu-file-update">
            <i class="glyphicon glyphicon-pencil"></i>
        </button>
        <button data-id="<?php echo $model->id ?>" type="button" class="btn btn-danger btn-xs igu-file-delete">
            <i class="glyphicon glyphicon-trash"></i>
        </button>
    </div>
    <?php
    $colors = array(
        'igu-blue', 'igu-pink', 'igu-dark-blue', 'igu-green', 'igu-black',
        'igu-yellow', 'igu-purple', 'igu-grey', 'igu-green-alt', 'igu-red',
        'igu-marine',
    );
    $color = $colors[array_rand($colors)];
    ?>
    <div <?php echo !is_admin() ? 'style="font-size:3.5em;padding:17px 0 0 0"' : null ?>
        class="igu-file-icon <?php echo $color ?>">
        <?php echo $model->mime_to_icon() ?>
    </div>
    <div class="igu-file-meta">
        <h5><?php echo $model->name ?></h5>

        <p class="text-muted"><?php echo get_the_date(null, $model->id) ?></p>
    </div>
</div>
