<div class="tab-pane active">
    <div class="page-header" style="margin-top: 0">
        <h3> <?php _e("General Options", mmg()->domain) ?></h3>
    </div>

    <?php $form = new IG_Active_Form($model);
    $form->open(array("attributes" => array("class" => "form-horizontal")));?>
    <div class="form-group <?php echo $model->has_error("enable_receipt") ? "has-error" : null ?>">
        <?php $form->label("enable_receipt", array("text" => __("Enable Message Receipt", mmg()->domain), "attributes" => array("class" => "col-lg-2 control-label"))) ?>
        <div class="col-lg-10">
            <div class="checkbox">
                <label>
                    <?php
                    $form->hidden('enable_receipt', array('value' => 0));
                    $form->checkbox("enable_receipt", array("attributes" => array("class" => "", "value" => 1))) ?>
                    <?php _e("Tick this box to enable email notifications of read messages.", mmg()->domain) ?>
                </label>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="form-group <?php echo $model->has_error("user_receipt") ? "has-error" : null ?>">
        <?php $form->label("user_receipt", array("text" => __("Allow the user to disable read message receipts?", mmg()->domain), "attributes" => array("class" => "col-lg-2 control-label"))) ?>
        <div class="col-lg-10">
            <div class="checkbox">
                <label>
                    <?php
                    $form->hidden('user_receipt', array('value' => 0));
                    $form->checkbox("user_receipt", array("attributes" => array("class" => "", "value" => 1))) ?>
                    <?php _e("This will allow the user to enable or disable read receipts.", mmg()->domain) ?>
                </label>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="page-header" style="margin-top: 0">
        <h4><?php _e('Create Page', mmg()->domain) ?></h4>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label"><?php _e('Inbox Page', mmg()->domain) ?></label>

        <div class="col-md-9">
            <div class="row">
                <div class="col-md-6">
                    <?php
                    $form->select('inbox_page', array(
                        'data' => array_combine(wp_list_pluck(get_pages(), 'ID'), wp_list_pluck(get_pages(), 'post_title')),
                        'attributes' => array('class' => 'form-control'),
                        'nameless' => __('--Choose--', mmg()->domain)
                    ));
                    ?>
                </div>
                <div class="col-md-6">
                    <button type="button" data-id="inbox"
                            class="button button-primary mm-create-page"><?php _e('Create Page', mmg()->domain) ?></button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php wp_nonce_field('mm_settings','_mmnonce') ?>
    <div class="page-header" style="margin-top: 0">
        <h4><?php _e('Add-ons', mmg()->domain) ?></h4>
    </div>
    <div class="alert alert-success plugin-status hide">

    </div>
    <?php $tbl = new MM_AddOn_Table();
    $tbl->prepare_items();
    $tbl->display();
    ?>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary"><?php _e("Save Changes", mmg()->domain) ?></button>
        </div>
    </div>
    <?php $form->close(); ?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.mm-plugin').click(function (e) {
            var that = $(this);
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                data: {
                    action: 'mm_plugin_action',
                    id: $(this).data('id')
                },
                beforeSend:function(){
                    that.find('.loader-ani').removeClass('hide');
                },
                success: function (data) {
                    that.find('.loader-ani').addClass('hide');
                    $('.plugin-status').html(data.noty);
                    $('.plugin-status').removeClass('hide');
                    that.text(data.text);
                }
            })
        });
        $('.mm-create-page').click(function (e) {
            var that = $(this);
            $.ajax({
                type: 'POST',
                data: {
                    m_type: $(this).data('id'),
                    action: 'mm_create_message_page'
                },
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                beforeSend: function () {
                    that.attr('disabled', 'disabled').text('<?php echo esc_js(__('Creating...',mmg()->domain)) ?>');
                },
                success: function (data) {
                    var element = that.parent().parent().find('select').first();
                    $.get("<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>", function (html) {
                        html = $(html);
                        var clone = html.find('select[name="' + element.attr('name') + '"]');
                        element.replaceWith(clone);
                        that.removeAttr('disabled').text('<?php echo esc_js(__('Create Page',mmg()->domain)) ?>');
                    })
                }
            })
        })
    })
</script>