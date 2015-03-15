<div class="ig-container">
    <?php
    //empty modal for fields work only
    if (!isset($model)) {
        $model = new IG_Uploader_Model();
    }
    $form = new IG_Active_Form($model);
    $form->open(array("attributes" => array("class" => "igu-upload-form", "style" => "min-width:60%;max-width:304px")));?>
    <?php if ($model->exist) {
        $form->hidden('id');
    }?>
    <div style="margin-bottom: 0" class="form-group <?php echo $model->has_error("file") ? "has-error" : null ?>">
        <button type="button"
                class="btn btn-default upload_image_button btn-xs"><?php _e("Choose File", ig_uploader()->domain) ?></button>
        <span class="file-upload-name"></span>
        <?php $form->hidden('file', array('attributes' => array('id' => 'attachment'))) ?>
        <?php if ($model->exist && $model->file) : ?>
            <span
                class="help-block m-b-none"><?php _e("File attached, upload new file will replace the current file.", ig_uploader()->domain) ?></span>
        <?php endif; ?>
        <span class="help-block m-b-none error-file"><?php $form->error("file") ?></span>

        <div class="clearfix"></div>
    </div>
    <div style="margin-bottom: 0" class="form-group <?php echo $model->has_error("url") ? "has-error" : null ?>">
        <?php $form->label("url", array("text" => __("Url", ig_uploader()->domain), "attributes" => array("class" => "control-label hidden-xs hidden-sm"))) ?>
        <?php $form->text("url", array("attributes" => array("class" => "form-control input-sm", "placeholder" => __("Url", ig_uploader()->domain)))) ?>
        <span class="help-block m-b-none error-url"><?php $form->error("url") ?></span>

        <div class="clearfix"></div>
    </div>
    <div style="margin-bottom: 0" class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
        <?php $form->label("content", array("text" => __("Content", ig_uploader()->domain), "attributes" => array("class" => "control-label hidden-xs hidden-sm"))) ?>
        <?php $form->text_area("content", array("attributes" => array("class" => "form-control input-sm", "style" => "height:80px", "placeholder" => __("Content", ig_uploader()->domain)))) ?>
        <span class="help-block m-b-none error-content"><?php $form->error("content") ?></span>

        <div class="clearfix"></div>
    </div>
    <?php echo wp_nonce_field('igu_uploading') ?>
    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-default btn-sm igu-close-uploader"
                    type="button"><?php _e("Cancel", ig_uploader()->domain) ?></button>
            <button class="btn btn-primary btn-sm" type="submit"><?php _e("Submit", ig_uploader()->domain) ?></button>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php $form->close(); ?>
</div>