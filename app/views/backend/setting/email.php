<div class="tab-pane active">
    <div class="page-header">
        <h3><?php _e("Email Settings", mmg()->domain) ?></h3>
    </div>

    <?php $form = new IG_Active_Form($model);
    $data = stripslashes_deep($model->export());
    $model->import($data);
    $form->open(array("attributes" => array("class" => "form-horizontal")));?>

    <div class="form-group <?php echo $model->has_error("noti_subject") ? "has-error" : null ?>">
        <?php $form->label("noti_subject", array("text" => "Subject", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
        <div class="col-lg-10">
            <?php $form->text_area("noti_subject", array("attributes" => array("class" => "form-control", "style" => "height:50px"))) ?>
            <span
                class="help-block m-b-none error-noti_subject"><?php $form->error("noti_subject") ?></span>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="form-group <?php echo $model->has_error("noti_content") ? "has-error" : null ?>">
        <?php $form->label("noti_content", array("text" => "Content", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
        <div class="col-lg-10">
            <?php $form->text_area("noti_content", array("attributes" => array("class" => "form-control", "style" => "height:50px"))) ?>
            <span
                class="help-block m-b-none error-noti_content"><?php $form->error("noti_content") ?></span>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="form-group <?php echo $model->has_error("receipt_subject") ? "has-error" : null ?>">
        <?php $form->label("receipt_subject", array("text" => "Receipt Subject", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
        <div class="col-lg-10">
            <?php $form->text_area("receipt_subject", array("attributes" => array("class" => "form-control", "style" => "height:50px"))) ?>
            <span
                class="help-block m-b-none error-receipt_subject"><?php $form->error("receipt_subject") ?></span>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="form-group <?php echo $model->has_error("receipt_content") ? "has-error" : null ?>">
        <?php $form->label("receipt_content", array("text" => "Receipt Content", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
        <div class="col-lg-10">
            <?php $form->text_area("receipt_content", array("attributes" => array("class" => "form-control", "style" => "height:50px"))) ?>
            <span
                class="help-block m-b-none error-receipt_content"><?php $form->error("receipt_content") ?></span>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="form-group <?php echo $model->has_error("per_page") ? "has-error" : null ?>">
        <?php $form->label("per_page", array("text" => "Per Page", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
        <div class="col-lg-10">
            <?php $form->text("per_page", array("attributes" => array("class" => "form-control"))) ?>
            <span
                class="help-block m-b-none error-per_page"><?php $form->error("per_page") ?></span>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php wp_nonce_field('mm_settings','_mmnonce') ?>
    <div class="row">
        <div class="col-md-10 col-md-offset-2">
            <button type="submit" class="btn btn-primary"><?php _e("Save Changes", mmg()->domain) ?></button>
        </div>
    </div>
    <?php $form->close(); ?>
</div>