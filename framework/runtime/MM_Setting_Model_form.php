<?php $form = new IG_Active_Form($model);
$form->open(array("attributes"=>array("class"=>"form-horizontal")));?>
<div class="form-group <?php echo $model->has_error("noti_subject")?"has-error":null ?>">
                            <?php $form->label("noti_subject",array("text"=>"Noti Subject","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("noti_subject",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-noti_subject"><?php $form->error("noti_subject") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("noti_content")?"has-error":null ?>">
                            <?php $form->label("noti_content",array("text"=>"Noti Content","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("noti_content",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-noti_content"><?php $form->error("noti_content") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("enable_receipt")?"has-error":null ?>">
                            <?php $form->label("enable_receipt",array("text"=>"Enable Receipt","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("enable_receipt",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-enable_receipt"><?php $form->error("enable_receipt") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("user_receipt")?"has-error":null ?>">
                            <?php $form->label("user_receipt",array("text"=>"User Receipt","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("user_receipt",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-user_receipt"><?php $form->error("user_receipt") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("per_page")?"has-error":null ?>">
                            <?php $form->label("per_page",array("text"=>"Per Page","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("per_page",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-per_page"><?php $form->error("per_page") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<?php $form->close();?>