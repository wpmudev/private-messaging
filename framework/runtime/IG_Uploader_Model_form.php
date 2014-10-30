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
<div class="form-group <?php echo $model->has_error("name")?"has-error":null ?>">
                            <?php $form->label("name",array("text"=>"Name","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("name",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-name"><?php $form->error("name") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("attach_to")?"has-error":null ?>">
                            <?php $form->label("attach_to",array("text"=>"Attach To","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("attach_to",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-attach_to"><?php $form->error("attach_to") ?></span>
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
<div class="form-group <?php echo $model->has_error("post_status")?"has-error":null ?>">
                            <?php $form->label("post_status",array("text"=>"Post Status","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->select("post_status",array("attributes"=>array("class"=>"form-control"),"data"=>array("publish"=>"Publish","draft"=>"Draft"))) ?>
                            <span class="help-block m-b-none error-post_status"><?php $form->error("post_status") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("url")?"has-error":null ?>">
                            <?php $form->label("url",array("text"=>"Url","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("url",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-url"><?php $form->error("url") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<div class="form-group <?php echo $model->has_error("file")?"has-error":null ?>">
                            <?php $form->label("file",array("text"=>"File","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("file",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-file"><?php $form->error("file") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>
<?php $form->close();?>