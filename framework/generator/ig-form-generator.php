<?php

/* Author: Hoang Ngo */
if (!class_exists('IG_Form_Generator')) {
    class IG_Form_Generator
    {
        public $model;

        public function __construct($model)
        {
            $this->model = $model;
        }

        public function op_generate()
        {
            $data = $this->model->export();
            $form = '<?php $form = new IG_Active_Form($model);' . PHP_EOL;
            $form .= '$form->open(array("attributes"=>array("class"=>"form-horizontal")));?>' . PHP_EOL;
            foreach ($data as $key => $val) {
                $field = '<div class="form-group <?php echo $model->has_error("%s")?"has-error":null ?>">
                            <?php $form->label("%s",array("text"=>"%s","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->%s("%s",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-%s"><?php $form->error("%s") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>';
                $field = sprintf($field, $key, $key, ucwords(str_replace('_', ' ', $key)), 'text', $key, $key, $key);

                $form .= $field . PHP_EOL;
            }
            $form .= '<?php $form->close();?>';

            //store the form
            file_put_contents(dirname(__DIR__) . '/runtime/' . array_pop(explode('\\', get_class($this->model))) . '_form.php', $form);
        }

        public function generate()
        {
            //getting the attributes
            $data = $this->model->get_mapped();

            $input_rules = array(
                'post_title' => 'text',
                'post_content' => 'text_area',
                'post_status' => 'select',
            );
            $form = '<?php $form = new IG_Active_Form($model);' . PHP_EOL;
            $form .= '$form->open(array("attributes"=>array("class"=>"form-horizontal")));?>' . PHP_EOL;
            foreach ($data as $key => $val) {
                $input = isset($input_rules[$val]) ? $input_rules[$val] : 'text';
                if ($input == 'select') {
                    $field = '<div class="form-group <?php echo $model->has_error("%s")?"has-error":null ?>">
                            <?php $form->label("%s",array("text"=>"%s","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->%s("%s",array("attributes"=>array("class"=>"form-control"),"data"=>array("publish"=>"Publish","draft"=>"Draft"))) ?>
                            <span class="help-block m-b-none error-%s"><?php $form->error("%s") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>';
                    $field = sprintf($field, $key, $key, ucwords(str_replace('_', ' ', $key)), $input, $key, $key, $key);
                } else {
                    $field = '<div class="form-group <?php echo $model->has_error("%s")?"has-error":null ?>">
                            <?php $form->label("%s",array("text"=>"%s","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->%s("%s",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-%s"><?php $form->error("%s") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>';
                    $field = sprintf($field, $key, $key, ucwords(str_replace('_', ' ', $key)), $input, $key, $key, $key);
                }

                $form .= $field . PHP_EOL;
            }

            //we allso need the relatations too
            foreach ($this->model->get_relations() as $val) {
                $key = $val['map'];
                if ($val['type'] == 'meta') {
                    $field = '<div class="form-group <?php echo $model->has_error("%s")?"has-error":null ?>">
                            <?php $form->label("%s",array("text"=>"%s","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("%s",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-%s"><?php $form->error("%s") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>';
                    $field = sprintf($field, $key, $key, ucwords(str_replace('_', ' ', $key)), $key, $key, $key);
                } elseif ($val['type'] == 'taxonomy') {
                    $field = '<div class="form-group <?php echo $model->has_error("%s")?"has-error":null ?>">
                            <?php $form->label("%s",array("text"=>"%s","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->select("%s",array("attributes"=>array("class"=>"form-control"),"data"=>%s)) ?>
                           <span class="help-block m-b-none error-%s"><?php $form->error("%s") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>';
                    //getting the data
                    $taxonomy = $val['key'];
                    $terms = ' array_combine(wp_list_pluck(get_terms("' . $taxonomy . '", array("hide_empty" => "false")), "term_id"), wp_list_pluck(get_terms("' . $taxonomy . '", array("hide_empty" => "false")), "name"))';

                    $field = sprintf($field, $key, $key, ucwords(str_replace('_', ' ', $key)), $key, $terms, $key, $key);
                } elseif ($val['type'] == 'meta_array') {
                    $fields = explode('|', $val['map']);
                    $html = '';
                    foreach ($fields as $v) {
                        $h = '<div class="form-group <?php echo $model->has_error("%s")?"has-error":null ?>">
                            <?php $form->label("%s",array("text"=>"%s","attributes"=>array("class"=>"col-lg-2 control-label"))) ?>
                          <div class="col-lg-10">
                            <?php $form->text("%s",array("attributes"=>array("class"=>"form-control"))) ?>
                           <span class="help-block m-b-none error-%s"><?php $form->error("%s") ?></span>
                          </div>
                          <div class="clearfix"></div>
                        </div>';
                        $h = sprintf($h, $v, $v, ucwords(str_replace('_', ' ', $v)), $v, $v, $v);
                        $html .= $h;
                    }
                    $field = $html;
                }

                $form .= $field . PHP_EOL;
            }
            $form .= '<?php $form->close();?>';

            //store the form
            file_put_contents(dirname(__DIR__) . '/runtime/' . array_pop(explode('\\', get_class($this->model))) . '_form.php', $form);
        }
    }
}