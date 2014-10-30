<?php $form = new IG_Active_Form($model);
$form->open(array("attributes"=>array("class"=>"form-horizontal")));?>
<div class="form-group <?php echo $model->has_error("id")?"has-error":null ?>">
                            <?php $form->label("id",array("text"=>"Id","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("id",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-id"><?php $form->error("id") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("subject")?"has-error":null ?>">
                            <?php $form->label("subject",array("text"=>"Subject","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("subject",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-subject"><?php $form->error("subject") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("status")?"has-error":null ?>">
                            <?php $form->label("status",array("text"=>"Status","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->select("status",array("attributes"=>array("class"=>"form-control"),"data"=>array("publish"=>"Publish","draft"=>"Draft"))) ?>
                            <span class="help-block m-b-none error-status"><?php $form->error("status") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("send_from")?"has-error":null ?>">
                            <?php $form->label("send_from",array("text"=>"Send From","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("send_from",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-send_from"><?php $form->error("send_from") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("reply_to")?"has-error":null ?>">
                            <?php $form->label("reply_to",array("text"=>"Reply To","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("reply_to",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-reply_to"><?php $form->error("reply_to") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("content")?"has-error":null ?>">
                            <?php $form->label("content",array("text"=>"Content","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text_area("content",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-content"><?php $form->error("content") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("date")?"has-error":null ?>">
                            <?php $form->label("date",array("text"=>"Date","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("date",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-date"><?php $form->error("date") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("send_to")?"has-error":null ?>">
                            <?php $form->label("send_to",array("text"=>"Send To","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("send_to",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-send_to"><?php $form->error("send_to") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("attachments")?"has-error":null ?>">
                            <?php $form->label("attachments",array("text"=>"Attachments","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("attachments",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-attachments"><?php $form->error("attachments") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<?php $form->close();?>