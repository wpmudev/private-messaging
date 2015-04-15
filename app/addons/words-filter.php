<?php

/**
 * Author: WPMU DEV
 * Name: Message filters
 * Description:
 */
if (!class_exists('MM_Words_Filter')) {
    include_once dirname(__FILE__) . '/words-filter/words-filter-model.php';

    class MM_Words_Filter extends IG_Request
    {
        public function __construct()
        {
            add_action('mm_setting_menu', array(&$this, 'setting_menu'));
            add_action('mm_setting_filter', array(&$this, 'setting_content'));
            add_action('wp_loaded', array(&$this, 'process_settings'));
            add_filter('mm_message_content', array(&$this, 'content'));
            add_filter('mm_message_subject', array(&$this, 'content'));
        }

        function content($content)
        {
            $content = $this->censorString($content);
            if (is_array($content)) {
                return $content['clean'];
            }
            return $content;
        }

        function process_settings()
        {
            if (!wp_verify_nonce(mmg()->post('_wpnonce'), 'mm_words_filter')) {
                return '';
            }

            if (!current_user_can('manage_options')) {
                return '';
            }
            $model = new Words_Filter_Model();
            $model->import(mmg()->post('Words_Filter_Model'));
            $model->save();
            $this->set_flash('setting_save', __("Your settings have been successfully updated.", mmg()->domain));
            $this->refresh();
        }

        function setting_menu()
        {
            ?>
            <li class="<?php echo mmg()->get('tab') == 'filter' ? 'active' : null ?>">
                <a href="<?php echo add_query_arg('tab', 'filter') ?>">
                    <i class="fa fa-filter"></i> <?php _e("Words Filter", mmg()->domain) ?></a>
            </li>
        <?php
        }

        function setting_content()
        {
            $model = new Words_Filter_Model();
            ?>
            <?php $form = new IG_Active_Form($model);
            $form->open(array("attributes" => array("class" => "form-horizontal")));?>
            <div class="form-group <?php echo $model->has_error("replacer") ? "has-error" : null ?>">
                <?php $form->label("replacer", array("text" => "Replacer", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
                <div class="col-lg-10">
                    <?php $form->text("replacer", array("attributes" => array("class" => "form-control"))) ?>
                    <span class="help-block m-b-none error-replacer"><?php $form->error("replacer") ?></span>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="form-group <?php echo $model->has_error("block_list") ? "has-error" : null ?>">
                <?php $form->label("block_list", array("text" => "Block List", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
                <div class="col-lg-10">
                    <?php $form->text_area("block_list", array("attributes" => array("class" => "form-control", "style" => "height:150px"))) ?>
                    <span class="help-block m-b-none error-block_list"><?php $form->error("block_list") ?></span>
                    <span class="help-block"><?php _e("One word each line", mmg()->domain) ?></span>
                </div>

                <div class="clearfix"></div>
            </div>
            <?php wp_nonce_field('mm_words_filter') ?>
            <div class="row">
                <div class="col-md-2 col-md-offset-2">
                    <button type="submit" class="btn btn-primary"><?php _e("Save Changes", mmg()->domain) ?></button>
                </div>
            </div>
            <?php $form->close();?>
        <?php
        }

        public function censorString($string)
        {
            $settings = new Words_Filter_Model();

            $badwords = $settings->block_list;
            $badwords = explode(PHP_EOL, $badwords);
            $badwords = array_map('trim', $badwords);

            $leet_replace = array();
            $leet_replace['a'] = '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|α|Δ|Λ|λ)';
            $leet_replace['b'] = '(b|b\.|b\-|8|\|3|ß|Β|β)';
            $leet_replace['c'] = '(c|c\.|c\-|Ç|ç|¢|€|<|\(|{|©)';
            $leet_replace['d'] = '(d|d\.|d\-|&part;|\|\)|Þ|þ|Ð|ð)';
            $leet_replace['e'] = '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|∑)';
            $leet_replace['f'] = '(f|f\.|f\-|ƒ)';
            $leet_replace['g'] = '(g|g\.|g\-|6|9)';
            $leet_replace['h'] = '(h|h\.|h\-|Η)';
            $leet_replace['i'] = '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï)';
            $leet_replace['j'] = '(j|j\.|j\-)';
            $leet_replace['k'] = '(k|k\.|k\-|Κ|κ)';
            $leet_replace['l'] = '(l|1\.|l\-|!|\||\]\[|]|£|∫|Ì|Í|Î|Ï)';
            $leet_replace['m'] = '(m|m\.|m\-)';
            $leet_replace['n'] = '(n|n\.|n\-|η|Ν|Π)';
            $leet_replace['o'] = '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø)';
            $leet_replace['p'] = '(p|p\.|p\-|ρ|Ρ|¶|þ)';
            $leet_replace['q'] = '(q|q\.|q\-)';
            $leet_replace['r'] = '(r|r\.|r\-|®)';
            $leet_replace['s'] = '(s|s\.|s\-|5|\$|§)';
            $leet_replace['t'] = '(t|t\.|t\-|Τ|τ)';
            $leet_replace['u'] = '(u|u\.|u\-|υ|µ)';
            $leet_replace['v'] = '(v|v\.|v\-|υ|ν)';
            $leet_replace['w'] = '(w|w\.|w\-|ω|ψ|Ψ)';
            $leet_replace['x'] = '(x|x\.|x\-|Χ|χ)';
            $leet_replace['y'] = '(y|y\.|y\-|¥|γ|ÿ|ý|Ÿ|Ý)';
            $leet_replace['z'] = '(z|z\.|z\-|Ζ)';

            // is $censorChar a single char?
            $isOneChar = (strlen($settings->replacer) === 1);
            for ($x = 0; $x < count($badwords); $x++) {
                $replacement[$x] = $isOneChar
                    ? str_repeat($settings->replacer, strlen($badwords[$x]))
                    : $this->randCensor($settings->replacer, strlen($badwords[$x]));
                $badwords[$x] = '/' . str_ireplace(array_keys($leet_replace), array_values($leet_replace), $badwords[$x]) . '/i';
            }

            $newstring = array();
            $newstring['orig'] = html_entity_decode($string);
            $newstring['clean'] = preg_replace($badwords, $replacement, $newstring['orig']);

            return $newstring;
        }

        public function randCensor($chars, $len)
        {
            mt_srand(); // useful for < PHP4.2
            $lastChar = strlen($chars) - 1;
            $randOld = -1;
            $out = '';
            // create $len chars
            for ($i = $len; $i > 0; $i--) {
                // generate random char - it must be different from previously generated
                while (($randNew = mt_rand(0, $lastChar)) === $randOld) {
                }
                $randOld = $randNew;
                $out .= $chars[$randNew];
            }
            return $out;
        }
    }

    new MM_Words_Filter();
}