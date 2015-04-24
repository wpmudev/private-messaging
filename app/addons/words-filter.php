<?php

/**
 * Author: WPMU DEV
 * Name: Message filters
 * Description:
 * Icon:fa-user-secret
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
            add_action('wp_ajax_mmg_add_word', array(&$this, 'mmg_add_word'));
            add_action('wp_ajax_mm_test_regex', array(&$this, 'mm_test_regex'));
            add_action('wp_ajax_remove_badword', array(&$this, 'remove_badword'));
            add_action('wp_ajax_load_badword', array(&$this, 'load_badword'));
        }

        function load_badword()
        {
            if (!current_user_can('manage_options')) {
                return '';
            }
            $key = mmg()->post('key');
            if (strlen($key) == 0) {
                echo '';
                die;
            }
            $model = new Words_Filter_Model();
            if (isset($model->block_list[$key])) {
                wp_send_json($model->block_list[$key]);
            }
            echo '';
            die;
        }

        function remove_badword()
        {
            if (!current_user_can('manage_options')) {
                return '';
            }
            $key = mmg()->post('key');
            if (strlen($key) == 0) {
                return '';
            }
            $model = new Words_Filter_Model();
            unset($model->block_list[$key]);
            $model->save();
        }

        function mm_test_regex()
        {
            if (!current_user_can('manage_options')) {
                return '';
            }
            $regex = mmg()->post('pattern');
            $subject = mmg()->post('subject');

            if (empty($regex) || empty($subject)) {
                wp_send_json(__("Pattern and Subject can't be empty", mmg()->domain));
            }

            $regex = "/" . stripcslashes($regex) . "/i";
            if (preg_match($regex, $subject)) {
                wp_send_json(__("Your pattern matched the subject", mmg()->domain));
            } else {
                wp_send_json(__("Your pattern mismatched the subject", mmg()->domain));
            }
        }

        function mmg_add_word()
        {
            if (!current_user_can('manage_options')) {
                return '';
            }

            if (!wp_verify_nonce(mmg()->post('_wpnonce'), 'mmg_add_word')) {
                return '';
            }
            parse_str(mmg()->post('data'), $data);
            $rules = array(
                'word' => 'required'
            );
            $validate = GUMP::is_valid($data, $rules);
            if ($validate === true) {
                $model = new Words_Filter_Model();
                $type = !isset($data['is_regex']) ? 'text' : 'regex';
                if (strlen($data['key']) > 0) {
                    $model->block_list[$data['key']] = array(
                        'type' => $type,
                        'word' => $data['word'],
                        'replacer' => $data['replacer']
                    );
                } else {
                    $model->block_list[] = array(
                        'type' => $type,
                        'word' => $data['word'],
                        'replacer' => $data['replacer']
                    );
                }
                $model->save();
                wp_send_json(array(
                    'status' => 1,
                    'message' => sprintf(__("The word <strong>%s</strong> has been added to the block list", mmg()->domain), $data['word'])
                ));
            } else {
                wp_send_json(array(
                    'status' => 0,
                    'errors' => implode('<br/>', $validate)
                ));
            }

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
                <a href="<?php echo esc_url(add_query_arg('tab', 'filter')) ?>">
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
            <div class="page-header">
                <h4><?php _e("General Settings", mmg()->domain) ?></h4>
            </div>
            <div class="form-group <?php echo $model->has_error("replacer") ? "has-error" : null ?>">
                <?php $form->label("replacer", array("text" => "Replacer", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
                <div class="col-lg-10">
                    <?php $form->text("replacer", array("attributes" => array("class" => "form-control"))) ?>
                    <span class="help-block m-b-none error-replacer"><?php $form->error("replacer") ?></span>
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
            <div class="clearfix"></div>
            <br/>
            <div class="page-header">
                <h4><?php _e("Add new word", mmg()->domain) ?></h4>
            </div>
            <div class="alert alert-words-list hide">

            </div>
            <form class="form-horizontal" id="words-list-frm">
                <input type="hidden" name="key">

                <div class="form-group">
                    <label class="control-label col-lg-2"><?php _e("Word", mmg()->domain) ?></label>

                    <div class="col-lg-5">
                        <input name="word" type="text" class="form-control"/>
                        <span
                            class="help-block"><?php _e("Word to block, you can use regex for this", mmg()->domain) ?></span>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br/>

                <div class="form-group">
                    <label class="control-label col-lg-2"><?php _e("Replacer", mmg()->domain) ?></label>

                    <div class="col-lg-5">
                        <input name="replacer" type="text" class="form-control"/>
                        <span
                            class="help-block"><?php _e("Replacer for this word, if empty, use the global instead", mmg()->domain) ?></span>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br/>

                <div class="form-group">
                    <label class="control-label col-lg-2"><?php _e("Is Regex", mmg()->domain) ?></label>

                    <div class="col-lg-5">
                        <div class="checkbox">
                            <label>
                                <input name="is_regex" value="1" type="checkbox">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br/>

                <div class="form-group regex-group hide">
                    <label class="control-label col-lg-2"><?php _e("Regex Subject", mmg()->domain) ?></label>

                    <div class="row">
                        <div class="col-md-5">
                            <input name="subject" type="text" class="form-control"/>
                        </div>
                        <div class="col-md-2">
                            <button type="button"
                                    class="btn btn-default btn-sm test-regex"><?php _e("Test Regex") ?></button>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br/>

                <div class="row">
                    <div class="col-md-2 col-md-offset-2">
                        <button type="submit" class="btn btn-primary"><?php _e("Save", mmg()->domain) ?></button>
                    </div>
                </div>
            </form>
            <div class="clearfix"></div><br/>
            <div class="page-header">
                <h4><?php _e("Block list", mmg()->domain) ?></h4>
            </div>
            <table class="table" id="badword-list-table">
                <thead>
                <tr>
                    <th><?php _e("Block word", mmg()->domain) ?></th>
                    <th><?php _e("Is Regex", mmg()->domain) ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($model->block_list)): ?>
                <?php else: ?>
                    <?php foreach ($model->block_list as $key => $word): ?>
                        <tr>
                            <td><?php echo $word['word'] ?></td>
                            <td><?php echo $word['type'] == 'regex' ? __("Yes", mmg()->domain) : __("No", mmg()->domain) ?></td>
                            <th><a data-key="<?php echo $key ?>" class="edit_badword"
                                   href="#"><?php _e("Edit", mmg()->domain) ?></a> |
                                <a class="remove_badword" data-key="<?php echo $key ?>"
                                   href="#"><?php _e("Remove", mmg()->domain) ?></a></th>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#words-list-frm').submit(function () {
                        var that = $(this);
                        $.ajax({
                            type: 'POST',
                            data: {
                                action: 'mmg_add_word',
                                data: that.serialize(),
                                _wpnonce: '<?php echo wp_create_nonce('mmg_add_word') ?>'
                            },
                            url: ajaxurl,
                            beforeSend: function () {
                                that.find('button').attr('disabled', 'disabled');
                            },
                            success: function (data) {
                                that.find('button').removeAttr('disabled');
                                if (data.status == 0) {
                                    $('.alert-words-list').addClass('alert-danger').removeClass('hide').html(data.errors);
                                } else {
                                    $('.alert-words-list').addClass('alert-success').removeClass('hide').html(data.message);
                                    that.find(':input').val('');
                                    $('#badword-list-table').load("<?php echo $_SERVER['REQUEST_URI'] ?> #badword-list-table")
                                }
                            }
                        })
                        return false;
                    });
                    $('input[name="is_regex"]').click(function () {
                        var form = $(this).closest('form');
                        if ($(this).prop('checked') == true) {
                            $('.regex-group').removeClass('hide');
                        } else {
                            $('.regex-group').addClass('hide');
                        }
                    });
                    $('.test-regex').click(function () {
                        var form = $(this).closest('form');
                        $.ajax({
                            type: 'POST',
                            data: {
                                action: 'mm_test_regex',
                                pattern: form.find('input[name="word"]').first().val(),
                                subject: form.find('input[name="subject"]').first().val()
                            },
                            url: ajaxurl,
                            success: function (data) {
                                alert(data);
                            }
                        })
                    });
                    $('body').on('click', '.remove_badword', function (e) {
                        e.preventDefault();
                        var that = $(this);
                        if (confirm("Are you sure?")) {
                            $.ajax({
                                type: 'POST',
                                data: {
                                    action: 'remove_badword',
                                    key: that.data('key')
                                },
                                url: ajaxurl,
                                success: function () {
                                    $('#badword-list-table').load("<?php echo $_SERVER['REQUEST_URI'] ?> #badword-list-table")
                                }
                            })
                        }
                    });
                    $('body').on('click', '.edit_badword', function (e) {
                        e.preventDefault();
                        var that = $(this);
                        $.ajax({
                            type: 'POST',
                            data: {
                                action: 'load_badword',
                                key: that.data('key')
                            },
                            url: ajaxurl,
                            success: function (data) {
                                var form = $('#words-list-frm');
                                if (data != '') {
                                    form.find('input[name="key"]').val(that.data('key'));
                                    form.find('input[name="word"]').val(data.word);
                                    form.find('input[name="replacer"]').val(data.replacer);
                                    if (data.type == 'regex') {
                                        form.find('input[name="is_regex"]').prop('checked', true)
                                    }
                                }

                            }
                        })
                    })
                })
            </script>
        <?php
        }

        public function censorString($string)
        {
            $settings = new Words_Filter_Model();

            $block_list = $settings->block_list;
            $badwords = array();
            $regexs = array();
            foreach ($block_list as $bl) {
                if ($bl['type'] == 'text') {
                    $badwords[] = array(
                        'word' => trim($bl['word']),
                        'replacer' => $bl['replacer']
                    );
                } elseif ($bl['type'] == 'regex') {
                    $regexs[] = array(
                        'word' => "/" . $bl['word'] . "/i",
                        'replacer' => $bl['replacer']
                    );
                }
            }
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
            $newstring = array();
            $newstring['orig'] = html_entity_decode($string);
            $newstring['clean'] = $newstring['orig'];
            if (count($badwords)) {
                for ($x = 0; $x < count($badwords); $x++) {
                    if (empty($badwords[$x]['replacer'])) {
                        $replacement[$x] = $isOneChar
                            ? str_repeat($settings->replacer, strlen($badwords[$x]['word']))
                            : $this->randCensor($settings->replacer, strlen($badwords[$x]['word']));
                    } else {
                        $replacement[$x] = $badwords[$x]['replacer'];
                    }
                    $badwords[$x] = '/' . str_ireplace(array_keys($leet_replace), array_values($leet_replace), $badwords[$x]['word']) . '/i';
                }
                $newstring['clean'] = preg_replace($badwords, $replacement, $newstring['orig']);
            }
            //now we append the regex
            foreach ($regexs as $key => $val) {
                $replacer = !empty($val['replacer']) ? $val['replacer'] : $this->randCensor($settings->replacer, 4);
                $newstring['clean'] = preg_replace($val['word'], $replacer, $newstring['clean']);
            }
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