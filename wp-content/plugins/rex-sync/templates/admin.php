<?php
use Rex\Sync\Loader;

$settings = Loader::get_settings();

?>
<div class="wrap rsc-wrap">
    <h1 class="rsc__title"><?php _e('Rex Sync WordPress', 'rex-sync') ?></h1>

    <?php
    \Rex\Sync\Helper::display_errors(Loader::$errors);
    \Rex\Sync\Helper::display_messages(Loader::$messages);
    ?>

    <div class="rsc__content">
        <div class="container-fluid rsc-settings">
            <div class="row">
                <div class="col-8">
                    <form method="post">
                        <?php wp_nonce_field('rsc-settings', 'rsc-settings-nonce') ?>
                        <p>&nbsp;</p>
                        <h3><?php _e('Rex API', 'rex-sync') ?></h3>

                        <table>
                            <tr>
                                <th valign="top" style="width:200px;"><?php _e('User Login', 'rex-sync') ?></th>
                                <td valign="top">
                                    <input type="text" name="rsc[user_login]" class="widefat" value="<?php esc_attr_e($settings['user_login']) ?>">
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('User Password', 'rex-sync') ?></th>
                                <td valign="top">
                                    <input type="password" name="rsc[user_password]" class="widefat" value="<?php esc_attr_e($settings['user_password']) ?>">
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Webhook URL', 'rex-sync') ?></th>
                                <td valign="top">
                                    <strong><?php esc_html_e( Loader::get_webhook_url() ) ?></strong>
                                    <br/>
                                    From Rex Dashboard, go to Settings -> Webhooks -> Add New Webhook.
                                    <br/>
                                    Use webhook format: Changes Details (ID only).
                                    <br/>
                                    Listings will be downloaded automatically in 3 minutes after the webhook fired.
                                </td>
                            </tr>

                            <tr>
                                <th></th>
                                <td>
                                    <p>&nbsp;</p>
                                    <button type="submit" class="button-primary"><?php _e('Save settings', 'rex-sync') ?></button>
                                </td>
                            </tr>
                        </table>


                    </form>
                </div>
                <div class="col-2"></div>
            </div>
        </div>
    </div>
</div>