<?php
/* @var $wpdb wpdb */
require_once NEWSLETTER_INCLUDES_DIR . '/controls.php';
$controls = new NewsletterControls();
$module = NewsletterEmails::instance();

// Always required
$email = Newsletter::instance()->get_email($_GET['id'], ARRAY_A);
if (empty($email['query'])) {
    $email['query'] = "select * from " . NEWSLETTER_USERS_TABLE . " where status='C'";
}

if (empty($email)) {
    echo 'Wrong email identifier';
    return;
}

$email_id = $email['id'];

$composer = isset($email['options']['composer']);

if ($composer) {
    wp_enqueue_style('tnpc-style', plugins_url('/tnp-composer/_css/newsletter-builder.css', __FILE__));
}

// Preferences conversions
if (!$controls->is_action()) {
    if (!isset($email['options']['lists'])) {

        $options_profile = get_option('newsletter_profile');

        if (empty($controls->data['preferences_status_operator'])) {
            $email['options']['lists_operator'] = 'or';
        } else {
            $email['options']['lists_operator'] = 'and';
        }
        $controls->data['options_lists'] = array();
        $controls->data['options_lists_exclude'] = array();

        if (!empty($email['preferences'])) {
            $preferences = explode(',', $email['preferences']);
            $value = empty($email['options']['preferences_status']) ? 'on' : 'off';

            foreach ($preferences as $x) {
                if ($value == 'on') {
                    $controls->data['options_lists'][] = $x;
                } else {
                    $controls->data['options_lists_exclude'][] = $x;
                }
            }
        }
    }
}

if (!$controls->is_action()) {
    $controls->data = $email;

    foreach ($email['options'] as $name => $value) {
        $controls->data['options_' . $name] = $value;
    }
}

if ($controls->is_action('change-private')) {
    $data = array();
    $data['private'] = $controls->data['private'] ? 0 : 1;
    $data['id'] = $email['id'];
    $email = Newsletter::instance()->save_email($data, ARRAY_A);
    $controls->add_message_saved();

    $controls->data = $email;
    foreach ($email['options'] as $name => $value) {
        $controls->data['options_' . $name] = $value;
    }
}

if ($controls->is_action('test') || $controls->is_action('save') || $controls->is_action('send') || $controls->is_action('editor')) {


    // If we were editing with visual editor (==0), we must read the extra <body> content
    if (!empty($controls->data['message'])) {
        $controls->data['message'] = str_ireplace('<script', '<noscript', $controls->data['message']);
        $controls->data['message'] = str_ireplace('</script', '</noscript', $controls->data['message']);
    }

    if ($email['editor'] == 0) {
        if (!empty($controls->data['message'])) {
            $x = strpos($email['message'], '<body');
            if ($x !== false) {
                $x = strpos($email['message'], '>', $x);
                $email['message'] = substr($email['message'], 0, $x + 1) . $controls->data['message'] . '</body></html>';
            } else {
                $email['message'] = '<html><body>' . $controls->data['message'] . '</body></html>';
            }
        }
    } else {
        $email['message'] = $controls->data['message'];
    }
    $email['message_text'] = str_ireplace('<script', '', $controls->data['message_text']);
    $email['subject'] = $controls->data['subject'];
    $email['track'] = $controls->data['track'];
    $email['private'] = $controls->data['private'];

    // Reset the options
    $email['options'] = array();
    if ($composer)
        $email['options']['composer'] = true;

    foreach ($controls->data as $name => $value) {
        if (strpos($name, 'options_') === 0) {
            $email['options'][substr($name, 8)] = $value;
        }
    }

    //var_dump($email);
    // Before send, we build the query to extract subscriber, so the delivery engine does not
    // have to worry about the email parameters
    if ($email['options']['status'] == 'S') {
        $query = "select * from " . NEWSLETTER_USERS_TABLE . " where status='S'";
    } else {
        $query = "select * from " . NEWSLETTER_USERS_TABLE . " where status='C'";
    }

    if ($email['options']['wp_users'] == '1') {
        $query .= " and wp_user_id<>0";
    }

    $list_where = array();
    if (isset($email['options']['lists']) && count($email['options']['lists'])) {
        foreach ($email['options']['lists'] as $list) {
            $list = (int) $list;
            $list_where[] = 'list_' . $list . '=1';
        }
    }

    if (!empty($list_where)) {
        if (isset($email['options']['lists_operator']) && $email['options']['lists_operator'] == 'and') {
            $query .= ' and (' . implode(' and ', $list_where) . ')';
        } else {
            $query .= ' and (' . implode(' or ', $list_where) . ')';
        }
    }

    $list_where = array();
    if (isset($email['options']['lists_exclude']) && count($email['options']['lists_exclude'])) {
        foreach ($email['options']['lists_exclude'] as $list) {
            $list = (int) $list;
            $list_where[] = 'list_' . $list . '=0';
        }
    }
    if (!empty($list_where)) {
        $query .= ' and (' . implode(' or ', $list_where) . ')';
    }

    if (isset($email['options']['sex'])) {
        $sex = $email['options']['sex'];
        if (is_array($sex) && count($sex)) {
            $query .= " and sex in (";
            foreach ($sex as $x) {
                $query .= "'" . esc_sql((string) $x) . "', ";
            }
            $query = substr($query, 0, -2);
            $query .= ")";
        }
    }

    $e = Newsletter::instance()->save_email($email);

    $query = apply_filters('newsletter_emails_email_query', $query, $e);

    $email['query'] = $query;
    if ($email['status'] == 'sent') {
        $email['total'] = $email['sent'];
    } else {
        $email['total'] = $wpdb->get_var(str_replace('*', 'count(*)', $query));
    }
    if ($controls->is_action('send') && $controls->data['send_on'] < time()) {
        $controls->data['send_on'] = time();
    }
    $email['send_on'] = $controls->data['send_on'];

    if ($controls->is_action('editor')) {
        $email['editor'] = $email['editor'] == 0 ? 1 : 0;
    }

    // Cleans up of tag
    $email['message'] = NewsletterModule::clean_url_tags($email['message']);

    //$email = apply_filters('newsletter_emails_pre_save', $email);
    //$module->logger->fatal($email);

    $res = Newsletter::instance()->save_email($email);
    if ($res === false) {
        $controls->errors = 'Unable to save. Try to deactivate and reactivate the plugin may be the database is out of sync.';
    }

    $controls->data['message'] = $email['message'];

    $controls->add_message_saved();
}

if ($controls->is_action('send')) {
    // Todo subject check
    if ($email['subject'] == '') {
        $controls->errors = __('A subject is required to send', 'newsletter');
    } else {
        $wpdb->update(NEWSLETTER_EMAILS_TABLE, array('status' => 'sending'), array('id' => $email_id));
        $email['status'] = 'sending';
        $controls->messages .= __('Now sending, see the progress on newsletter list', 'newsletter');
    }
}

if ($controls->is_action('pause')) {
    $wpdb->update(NEWSLETTER_EMAILS_TABLE, array('status' => 'paused'), array('id' => $email_id));
    $email['status'] = 'paused';
}

if ($controls->is_action('continue')) {
    $wpdb->update(NEWSLETTER_EMAILS_TABLE, array('status' => 'sending'), array('id' => $email_id));
    $email['status'] = 'sending';
}

if ($controls->is_action('abort')) {
    $wpdb->query("update " . NEWSLETTER_EMAILS_TABLE . " set last_id=0, sent=0, status='new' where id=" . $email_id);
    $email['status'] = 'new';
    $email['sent'] = 0;
    $email['last_id'] = 0;
    $controls->messages = __('Delivery definitively cancelled', 'newsletter');
}

if ($controls->is_action('test')) {
    if ($email['subject'] == '') {
        $controls->errors = __('A subject is required to send', 'newsletter');
    } else {
        $users = NewsletterUsers::instance()->get_test_users();
        if (count($users) == 0) {
            $controls->messages = '<strong>' . __('There are no test subscribers to send to', 'newsletter') . '</strong>';
        } else {
            Newsletter::instance()->send(Newsletter::instance()->get_email($email_id), $users);
            $controls->messages = __('Test newsletter sent to:', 'newsletter');
            foreach ($users as &$user) {
                $controls->messages .= ' ' . $user->email;
            }
            $controls->messages .= '.';
        }

        $controls->messages .= '<br>';
        $controls->messages .= '<a href="https://www.thenewsletterplugin.com/plugins/newsletter/subscribers-module#test" target="_blank">' .
                __('Read more about test subscribers', 'newsletter') . '</a>.';
    }
}

if ($email['editor'] == 0) {
    $controls->data['message'] = $module->extract_body($controls->data['message']);
}

if (isset($controls->data['options_status']) && $controls->data['options_status'] == 'S') {
    $controls->warnings[] = __('This newsletter will be sent to not confirmed subscribers.', 'newsletter');
}

/*
  $host = parse_url(home_url(), PHP_URL_HOST);
  $parts = array_reverse(explode('.', $host));
  $host = $parts[1] . '.' . $parts[0];

  $re = '/["\'](https?:\/\/[^\/\s]+\/\S+\.(jpg|png|gif))["\']/i';
  preg_match_all($re, $controls->data['message'], $matches);
  $images = array();
  if (isset($matches[1])) {
  //echo 'Ci sono immagini';
  //var_dump($matches[1]);
  foreach ($matches[1] as $url) {
  $h = parse_url($url, PHP_URL_HOST);
  $p = array_reverse(explode('.', $h));
  $h = $p[1] . '.' . $p[0];
  if ($h == $host)
  continue;
  $images[] = $url;
  }
  }

  if ($images) {
  //$controls->warnings[] = __('Message body contains images from external domains.', 'newsletter') . ' <a href="">' . __('Read more', 'newsletter') . '</a>';
  }
 */
/*
  if ($images) {
  $upload = wp_upload_dir();
  $dir = $upload['basedir'] . '/newsletter/' . $email['id'];
  $baseurl = $upload['baseurl'] . '/newsletter/' . $email['id'];

  // Cannot work on systems with forced relative paths
  if (strpos($baseurl, 'http') === 0) {
  wp_mkdir_p($dir);
  foreach ($images as $url) {
  $file = basename(parse_url($url, PHP_URL_PATH));
  $file = sanitize_file_name($file);
  if (copy($url, $dir . '/' . $file)) {
  $controls->data['message'] = str_replace($url, $baseurl . '/' . $file, $controls->data['message']);
  }
  }
  }
  }
 */
?>

<div class="wrap tnp-emails tnp-emails-edit" id="tnp-wrap">

    <?php include NEWSLETTER_DIR . '/tnp-header.php'; ?>

    <div id="tnp-heading">

        <h2><?php _e('Edit Newsletter', 'newsletter') ?></h2>

    </div>

    <div id="tnp-body">



        <form method="post" action="" id="newsletter-form">
            <?php $controls->init(array('cookie_name' => 'newsletter_emails_edit_tab')); ?>

            <div class="tnp-submit">

                <?php $controls->button_back('?page=newsletter_emails_index') ?>
                <?php if ($email['status'] != 'sending' && $email['status'] != 'sent') $controls->button_save(); ?>
                <?php if ($email['status'] != 'sending' && $email['status'] != 'sent') $controls->button('test', __('Test', 'newsletter')); ?>

                <?php if ($email['status'] == 'new') $controls->button_confirm('send', __('Send', 'newsletter'), __('Start real delivery?', 'newsletter')); ?>
                <?php if ($email['status'] == 'sending') $controls->button_confirm('pause', __('Pause', 'newsletter'), __('Pause the delivery?', 'newsletter')); ?>
                <?php if ($email['status'] == 'paused') $controls->button_confirm('continue', __('Continue', 'newsletter'), 'Continue the delivery?'); ?>
                <?php if ($email['status'] == 'paused') $controls->button_confirm('abort', __('Stop', 'newsletter'), __('This totally stop the delivery, ok?', 'newsletter')); ?>
                <?php if ($email['status'] != 'sending' && $email['status'] != 'sent') $controls->button('editor', __('Switch editor')); ?>
                <?php //if ($images) $controls->button_confirm('import', __('Import images', 'newsletter'), 'Proceed?')  ?>
            </div>

            <?php $controls->text('subject', 70, 'Subject'); ?>

            <div id="tabs">
                <ul>
                    <li><a href="#tabs-a"><?php _e('Message', 'newsletter') ?></a></li>
                    <li><a href="#tabs-b"><?php _e('Message (textual)', 'newsletter') ?></a></li>
                    <li><a href="#tabs-c"><?php _e('Targeting', 'newsletter') ?></a></li>
                    <li><a href="#tabs-d"><?php _e('Other', 'newsletter') ?></a></li>
                    <li><a href="#tabs-status"><?php _e('Status', 'newsletter') ?></a></li>
                </ul>


                <div id="tabs-a">

                    <?php
                    if ($email['editor'] == 0) {
                        if ($composer) {
                            include __DIR__ . '/edit-composer.php';
                        } else {
                            include __DIR__ . '/edit-editor.php';
                        }
                    } else {
                        include __DIR__ . '/edit-html.php';
                    }
                    ?>

                </div>


                <div id="tabs-b">
                    <?php if (Newsletter::instance()->options['phpmailer'] == 0) { ?>
                        <p class="tnp-tab-warning">The text part is sent only when Newsletter manages directly the sending process. <a href="admin.php?page=newsletter_main_main" target="_blank">See the main settings</a>.</p>
                    <?php } ?>
                    <p>
                        This is the textual version of your newsletter. If you empty it, only an HTML version will be sent but
                        is an anti-spam best practice to include a text only version.
                    </p>

                    <?php $controls->textarea_fixed('message_text', '100%', '500'); ?>
                </div>


                <div id="tabs-c">
                    <p>
                        <?php $controls->panel_help('https://www.thenewsletterplugin.com/documentation/newsletter-targeting') ?>
                    </p>

                    <p>
                        <?php _e('Leaving all multichoice options unselected is like to select all them', 'newsletter'); ?>
                    </p>
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Lists', 'newsletter') ?></th>
                            <td>
                                <?php
                                $lists = $controls->get_list_options();
                                ?>
                                <?php $controls->select('options_lists_operator', array('or' => __('Match at least one of', 'newsletter'), 'and' => __('Match all of', 'newsletter'))); ?>

                                <?php $controls->select2('options_lists', $lists, null, true, null, __('All', 'newsletter')); ?>

                                <p><?php _e('must not in one of', 'newsletter') ?></p>

                                <?php $controls->select2('options_lists_exclude', $lists, null, true, null, __('None', 'newsletter')); ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Gender', 'newsletter') ?></th>
                            <td>
                                <?php $controls->checkboxes_group('options_sex', array('f' => 'Women', 'm' => 'Men', 'n' => 'Not specified')); ?>
                            </td>
                        </tr>


                        <tr>
                            <th><?php _e('Status', 'newsletter') ?></th>
                            <td>
                                <?php $controls->select('options_status', array('C' => __('Confirmed', 'newsletter'), 'S' => __('Not confirmed', 'newsletter'))); ?>

                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Only to subscribers linked to WP users', 'newsletter') ?></th>
                            <td>
                                <?php $controls->yesno('options_wp_users'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e('Approximated subscribers count', 'newsletter') ?>
                            </th>
                            <td>
                                <?php
                                if ($email['status'] != 'sent') {
                                    echo $wpdb->get_var(str_replace('*', 'count(*)', $email['query']));
                                } else {
                                    echo $email['sent'];
                                }
                                ?>
                                <p class="description">
                                    <?php _e('Save to update if on targeting filters have been changed', 'newsletter') ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <?php do_action('newsletter_emails_edit_target', $module->get_email($email_id), $controls) ?>
                </div>


                <div id="tabs-d">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Keep private', 'newsletter') ?></th>
                            <td>
                                <?php $controls->yesno('private'); ?>
                                <?php if ($email['status'] == 'sent') { ?>
                                    <?php $controls->button('change-private', __('Toggle')) ?>
                                <?php } ?>
                                <p class="description">
                                    <?php _e('Hide/show from public sent newsletter list.', 'newsletter') ?>
                                    <?php _e('Required', 'newsletter') ?>: <a href="" target="_blank">Newsletter Archive Extension</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Track clicks and message opening', 'newsletter') ?></th>
                            <td>
                                <?php $controls->yesno('track'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Send on', 'newsletter') ?></th>
                            <td>
                                <?php $controls->datetime('send_on'); ?> (now: <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format')); ?>)
                            </td>
                        </tr>
                    </table>

                    <?php do_action('newsletter_emails_edit_other', $module->get_email($email_id), $controls) ?>
                </div>

                <div id="tabs-status">
                    <table class="form-table">
                        <tr>
                            <th>Email status</th>
                            <td><?php echo esc_html($email['status']); ?></td>
                        </tr>
                        <tr>
                            <th>Messages sent</th>
                            <td><?php echo $email['sent']; ?> of <?php echo $email['total']; ?></td>
                        </tr>
                        <tr>
                            <th>Query (tech)</th>
                            <td><?php echo esc_html($email['query']); ?></td>
                        </tr>
                        <tr>
                            <th>Token (tech)</th>
                            <td><?php echo esc_html($email['token']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

        </form>
    </div>

    <?php include NEWSLETTER_DIR . '/tnp-footer.php'; ?>

</div>
