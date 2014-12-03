<br/>
<div class="row">
    <div class="col-md-6 no-padding">
        <div class="mm_login_form">
            <div class="page-header">
                <h3><?php _e("Sign in", mmg()->domain) ?></h3>
            </div>
            <?php mm_login_form(); ?>
        </div>
    </div>
    <div class="col-md-6 no-padding">
        <div class="mm_sign_up">
            <div class="page-header">
                <h3><?php _e("Sign Up", mmg()->domain) ?></h3>
            </div>
            <p><?php _e("Sign up to become a registered member of the site", mmg()->domain) ?></p>
            <a href="<?php echo wp_registration_url(); ?>" class="btn btn-primary mm_signup_btn"><?php _e("Create Account", mmg()->domain) ?></a>
        </div>
    </div>
    <div class="clearfix"></div>
</div>