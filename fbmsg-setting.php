<?php
if (!current_user_can('activate_plugins')) {
    die('The account you\'re logged in to doesn\'t have permission to access this page.');
}

function fbmsg_has_valid_nonce() {
    $nonce_actions = array('fbmsg_settings');
    $nonce_form_prefix = 'fbmsg-form_nonce_';
    $nonce_action_prefix = 'fbmsg-wpnonce_';
    foreach ($nonce_actions as $key => $value) {
        if (isset($_POST[$nonce_form_prefix.$value])) {
            check_admin_referer($nonce_action_prefix.$value, $nonce_form_prefix.$value);
            return true;
        }
    }
    return false;
}

if (!empty($_POST)) {
    $nonce_result_check = fbmsg_has_valid_nonce();
    if ($nonce_result_check === false) {
        die('Unable to save changes. Make sure you are accessing this page from the Wordpress dashboard.');
    }
}

// Post fields that require verification.
$valid_fields = array(
    'fbmsg_page' => array(
        'key_name' => 'fbmsg_page',
        'length' => 1000
    ),
    'fbmsg_timeline' => array(
        'key_name' => 'fbmsg_timeline',
        'values' => array(true, false)
    ),
    'fbmsg_events' => array(
        'key_name' => 'fbmsg_events',
        'values' => array(true, false)
    ),
    'fbmsg_pos' => array(
        'key_name' => 'fbmsg_pos',
        'values' => array('right', 'left')
    ));

// Check POST fields and remove bad input.
foreach ($valid_fields as $key) {

    if (isset($_POST[$key['key_name']]) ) {

        // SANITIZE first
        $_POST[$key['key_name']] = trim(sanitize_text_field($_POST[$key['key_name']]));

        // Validate
        if ($key['regexp']) {
            if (!preg_match($key['regexp'], $_POST[$key['key_name']])) {
                unset($_POST[$key['key_name']]);
            }

        } else if ($key['type'] == 'int') {
            if (!intval($_POST[$key['key_name']])) {
                unset($_POST[$key['key_name']]);
            }

        } else if ($key['length'] > 0) {
            if (strlen($_POST[$key['key_name']]) > $key['length']) {
                unset($_POST[$key['key_name']]);
            }

        } else {
            $valid = false;
            $vals = $key['values'];
            foreach ($vals as $val) {
                if ($_POST[$key['key_name']] == $val) {
                    $valid = true;
                }
            }
            if (!$valid) {
                unset($_POST[$key['key_name']]);
            }
        }
    }
}

if (isset($_POST['submit'])) {
    foreach (fbmsg_options() as $opt) {
        update_option($opt, $_POST[$opt]);
    }
    update_option('fbmsg_version', FBMSG_VERSION);
}

$fbmsg_title     =  esc_attr(get_option('fbmsg_title'));
$fbmsg_page      =  esc_attr(get_option('fbmsg_page'));
$fbmsg_timeline  =  esc_attr(get_option('fbmsg_timeline'));
$fbmsg_events    =  esc_attr(get_option('fbmsg_events'));
$fbmsg_pos       =  esc_attr(get_option('fbmsg_pos'));
?>

<style>
.version {
  position: absolute;
  top: 6px;
  right: 16px;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  display: inline-block;
  margin: 20px 0 0;
  padding: 6px 10px;
  font-size: 12px;
  line-height: 14px;
  color: #FFF;
  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
  white-space: nowrap;
  vertical-align: baseline;
  background-color: #999;
}
</style>
<span class="version"><?php echo fbmsg_i('Version: %s', esc_html(FBMSG_VERSION)); ?></span>
<div class="fbmsg-setting">
    <h1><?php echo fbmsg_i('Facebook Messenger Settings'); ?></h1>

    <!-- Configuration form -->
    <form method="POST" enctype="multipart/form-data">
    <?php wp_nonce_field('fbmsg-wpnonce_fbmsg_settings', 'fbmsg-form_nonce_fbmsg_settings'); ?>
    <table class="form-table">
        <tr>
            <th scope="row" valign="top"><?php echo fbmsg_i('Title'); ?></th>
            <td>
                <input type="text" name="fbmsg_title" value="<?php echo $fbmsg_title; ?>" style="width:100%"/><br>
                <small>Live Chat title</small>
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php echo fbmsg_i('Facebook Page'); ?></th>
            <td>
                <input type="text" name="fbmsg_page" value="<?php echo $fbmsg_page; ?>" style="width:100%"/><br>
                <small>This must be a <b>Facebook Page</b>. Groups and Profiles are not accepted.<br>To enable messaging on your Facebook page go to https://www.facebook.com/YOUR_PAGE_NAME/settings/?tab=settings&on=messages&view</small>
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php echo fbmsg_i('Show Timeline Tab'); ?></th>
            <td>
                <input type="checkbox" name="fbmsg_timeline" value="true" <?php if($fbmsg_timeline) {echo 'checked="checked"';}?> />
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php echo fbmsg_i('Show Events Tab'); ?></th>
            <td>
                <input type="checkbox" name="fbmsg_events" value="true" <?php if($fbmsg_events) {echo 'checked="checked"';}?> />
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php echo fbmsg_i('Badge Position'); ?></th>
            <td>
                <select name="fbmsg_pos">
                    <option value="right" <?php selected('right', $fbmsg_pos); ?>><?php echo fbmsg_i('Right'); ?></option>
                    <option value="left" <?php selected('left', $fbmsg_pos); ?>><?php echo fbmsg_i('Left'); ?></option>
                </select>
            </td>
        </tr>
    </table>
    <p class="submit" style="text-align: left">
        <input name="submit" type="submit" value="Save" class="button-primary button" tabindex="4">
    </p>
    </form>
    <hr>
    <b>Feel free to try our other widgets powered by <a href="https://widgetpack.com/">Widget Pack</a>.</b>
</div>