<?php

function wphSnakeCaseToPascalCase(string $string): string
{
    return str_replace('_', '', ucwords($string, '_'));
}


add_action('show_user_profile', 'wph_user_profile_fields');
add_action('edit_user_profile', 'wph_user_profile_fields');
function wph_user_profile_fields($user)
{
    wp_enqueue_media(); ?>

    <h2><?php _e("WP Hourly User Settings", "wphourly"); ?></h2>

    <table class="form-table wph-table">
        <tr>
            <th><label for="wph-user-profile-image"><?php _e('Profile Image', 'wphourly'); ?></label></th>
            <td>
                <?php $userProfilePicture = esc_attr(get_the_author_meta('wph-user-profile-image', $user->ID));
                $pictureData = wp_get_attachment_image_src($userProfilePicture);
                $src = $pictureData
                    ? $pictureData[0]
                    : '';
                ?>
                <span id="wph-user-profile-image-preview">
                    <?php if ($src) {
                        echo "<img src=\"{$src}\" style=\"max-width: 400px; max-height: 400px\" /><br/>";
                    } ?>
                </span>
                <input type="button" class="button button-secondary" value="Choose a profile image" id="wph-user-profile-image-upload-button" />
                <input type="button" class="button button-link-delete" value="Remove profile image" id="wph-user-profile-image-remove-button" />
                <input type="hidden" name="wph-user-profile-image" id="wph-user-profile-image" value="<?php echo $userProfilePicture; ?>" />
            </td>
        </tr>

        <?php if(!empty(array_intersect(['customer', 'employee'], $user->roles))) { ?>
      
        <tr>
            <th><label for="wph-user-status"><?php _e("User status", 'wphourly'); ?></label></th>
            <td>
                <?php
                $checked = '';
                if (esc_attr(get_the_author_meta('wph-user-status', $user->ID)) == 'active') {
                    $checked = 'checked';
                }

                echo '<label class="switch" style="width: 150px">
                    <input class="switch-input" ' . $checked . ' type="checkbox" name="wph-user-status" value="active" />
                    <span class="switch-label" data-on="'. __('Active','wphourly').'" data-off="'. __('Inactive','wphourly').'"></span>
                    <span class="switch-handle"></span>
                </label>'; ?>
            </td>
        </tr>
        <?php } ?>

        <?php if(in_array('customer', $user->roles)) { ?>
            <?php
                $hourlyRate = get_option('wph_hourly_rate', ''); 
                if($hourlyRate =='') {
                    $hourlyRate = "NOT SET - please visit the WP Hourly Settings page to set up a Global Hourly Rate.";
                }
            ?>
            <?php $hourlyRate = get_the_author_meta('wph_hourly_rate', $user->ID); ?>
             <tr>
                <th><label for="wph_hourly_rate"><?php _e('Customer Hourly rate','wphourly');?> <span class="dashicons dashicons-info" title="<?php _e('Leave empty to use the Global Hourly rate.', 'wphourly'); ?>"></span></label></th>
                <td>
                    <input type="text" name="wph_hourly_rate" id="wph_hourly_rate" placeholder="ex.: 100" value="<?php echo $hourlyRate; ?>">
                </td>
            </tr>
        <?php } ?>
        <?php if(in_array('employee', $user->roles)) { ?>
        <tr>
            <th><label for="wph_allow_employee_to_view_all_projects"><?php _e("Can this Employee view acces any Project or just Projects they are part of?", 'hourly'); ?></label></th>
            <td>
                <?php
                $checked = '';
                if (esc_attr(get_the_author_meta('wph_allow_employee_to_view_all_projects', $user->ID)) == 1) {
                    $checked = 'checked';
                }

                echo '<label class="switch" style="width: 150px">
                    <input class="switch-input" ' . $checked . ' type="checkbox" name="wph_allow_employee_to_view_all_projects" value="1" />
                    <span class="switch-label" data-on="'. __('Yes','wphourly').'" data-off="'. __('No','wphourly').'"></span>
                    <span class="switch-handle"></span>
                </label>'; ?>
            </td>
        </tr>
        <tr>
            <th><label for="wph_allow_employee_to_view_all_tasks"><?php _e("Can this Employee view any Task or just its own Tasks?"); ?></label></th>
            <td>
                <?php
                $checked = '';
                if (esc_attr(get_the_author_meta('wph_allow_employee_to_view_all_tasks', $user->ID)) == 1) {
                    $checked = 'checked';
                }

                echo '<label class="switch" style="width: 150px">
                    <input class="switch-input" ' . $checked . ' type="checkbox" name="wph_allow_employee_to_view_all_tasks" value="1" />
                    <span class="switch-label" data-on="'. __('Yes','wphourly').'" data-off="'. __('No','wphourly').'"></span>
                    <span class="switch-handle"></span>
                </label>'; ?>
            </td>
        </tr>
        <tr>
            <th><label for="wph_allow_employees_to_edit_project"><?php _e("Can this Employee edit Projects?",'wphourly'); ?></label></th>
            <td>
                <?php
                $checked = '';
                if (esc_attr(get_the_author_meta('wph_allow_employees_to_edit_project', $user->ID)) == 1) {
                    $checked = 'checked';
                }

                echo '<label class="switch" style="width: 150px">
                    <input class="switch-input" ' . $checked . ' type="checkbox" name="wph_allow_employees_to_edit_project" value="1" />
                    <span class="switch-label" data-on="'. __('Yes','wphourly').'" data-off="'. __('No','wphourly').'"></span>
                    <span class="switch-handle"></span>
                </label>'; ?>
            </td>
        </tr>
        <tr>
            <th><label for="wph_allow_employees_to_edit_task"><?php _e("Can this Employee edit any Task or just Tasks that are assigned to them?",'wphourly'); ?></label></th>
            <td>
                <?php
                $checked = '';
                if (esc_attr(get_the_author_meta('wph_allow_employees_to_edit_task', $user->ID)) == 1) {
                    $checked = 'checked';
                }

                echo '<label class="switch" style="width: 150px">
                    <input class="switch-input" ' . $checked . ' type="checkbox" name="wph_allow_employees_to_edit_task" value="1" />
                    <span class="switch-label" data-on="'. __('Yes','wphourly').'" data-off="'. __('No','wphourly').'"></span>
                    <span class="switch-handle"></span>
                </label>'; ?>
            </td>
        </tr>
        <tr>
            <th><label for="wph_allow_employees_to_add_manual_time"><?php _e("Can this Employee add Manual Time on tasks that are assigned to them?",'wphourly'); ?></label></th>
            <td>
                <?php
                $checked = '';
                if (esc_attr(get_the_author_meta('wph_allow_employees_to_add_manual_time', $user->ID)) == 1) {
                    $checked = 'checked';
                }

                echo '<label class="switch" style="width: 150px">
                    <input class="switch-input" ' . $checked . ' type="checkbox" name="wph_allow_employees_to_add_manual_time" value="1" />
                    <span class="switch-label" data-on="'. __('Yes','wphourly').'" data-off="'. __('No','wphourly').'"></span>
                    <span class="switch-handle"></span>
                </label>'; ?>
            </td>
        </tr>
        <?php $target = get_the_author_meta('wph_daily_target', $user->ID); ?>
        <tr>
            <th><label for="wph_daily_target"><?php _e('Daily hours target','wphourly');?> <span class="dashicons dashicons-info" title="<?php _e('Set a Daily Hours target you expect this Employee to track working.','wphourly');?>"></span></label></th>
            <td>
                <input type="text" placeholder="ex.: 8" name="wph_daily_target" id="wph_daily_target" value="<?php echo $target; ?>">
            </td>
        </tr>
    
        <?php } ?>

    </table>
<?php }

add_action('user_new_form', 'wph_new_user_profile_fields');
function wph_new_user_profile_fields()
{
    ?> <h2><?php _e("WP Hourly User Settings", "wphourly"); ?></h2>

    <table class="form-table wph-table">
        <tr>
            <th><label for="wph-user-status"><?php _e("User status", 'wphourly'); ?></label></th>
            <td>
                <?php echo '<label class="switch" style="width: 150px">
                    <input class="switch-input" checked type="checkbox" name="wph-user-status" value="active" />
                    <span class="switch-label" data-on="'. __('Active','wphourly').'" data-off="'. __('Inactive','wphourly').'"></span>
                    <span class="switch-handle"></span>
                </label>'; ?>
            </td>
        </tr>
    </table> <?php
}

/*
//    ---        SUPER WP HEROES
//    ---        COMMENT: save the user fields
*/
add_action('personal_options_update', 'wph_save_user_profile_fields');
add_action('edit_user_profile_update', 'wph_save_user_profile_fields');
add_action('user_register', 'wph_save_user_profile_fields');
function wph_save_user_profile_fields($userId)
{
    if (!current_user_can('edit_user', $userId)) {
        return false;
    }

    $profileImage = filter_input(INPUT_POST, 'wph-user-profile-image', FILTER_VALIDATE_INT);
    update_user_meta($userId, 'wph-user-profile-image', $profileImage);

    $user = get_userdata($userId);
    if (!empty(array_intersect(['customer', 'employee'], $user->roles))) {
        $status = filter_input(INPUT_POST, 'wph-user-status', FILTER_SANITIZE_STRING);
        update_user_meta($userId, 'wph-user-status', $status == 'active' ? $status : 'inactive');

    }

    if (in_array('employee', $user->roles)) {
        $targetDaily = filter_input(INPUT_POST, 'wph_daily_target', FILTER_SANITIZE_STRING);
        update_user_meta($userId, 'wph_daily_target', $targetDaily);

        $canViewAllProjects = filter_input(INPUT_POST, 'wph_allow_employee_to_view_all_projects', FILTER_SANITIZE_STRING);
        update_user_meta($userId, 'wph_allow_employee_to_view_all_projects', $canViewAllProjects == '1' ? $canViewAllProjects : '0');

        $canViewAllTasks = filter_input(INPUT_POST, 'wph_allow_employee_to_view_all_tasks', FILTER_SANITIZE_STRING);
        update_user_meta($userId, 'wph_allow_employee_to_view_all_tasks', $canViewAllTasks == '1' ? $canViewAllTasks : '0');

        $canEditProjects = filter_input(INPUT_POST, 'wph_allow_employees_to_edit_project', FILTER_SANITIZE_STRING);
        update_user_meta($userId, 'wph_allow_employees_to_edit_project', $canEditProjects == '1' ? $canEditProjects : '0');

        $canEditTasks = filter_input(INPUT_POST, 'wph_allow_employees_to_edit_task', FILTER_SANITIZE_STRING);
        update_user_meta($userId, 'wph_allow_employees_to_edit_task', $canEditTasks == '1' ? $canEditTasks : '0');

        $canAddManualTime = filter_input(INPUT_POST, 'wph_allow_employees_to_add_manual_time', FILTER_SANITIZE_STRING);
        update_user_meta($userId, 'wph_allow_employees_to_add_manual_time', $canAddManualTime == '1' ? $canAddManualTime : '0');
    }

    $hourlyRate = filter_input(INPUT_POST, 'wph_hourly_rate', FILTER_SANITIZE_NUMBER_FLOAT);

    $oldHourlyRate = get_user_meta($userId, 'wph_hourly_rate');
    update_user_meta($userId, 'wph_hourly_rate', is_numeric($hourlyRate) ? $hourlyRate : '');

    if ($oldHourlyRate == $hourlyRate) {
        return;
    }

    do_action('wph-client-hourly-rate-updated', $userId, $oldHourlyRate, $hourlyRate);
}

function wphUpdateHourlyRateForUnpaidTimeRecords($userId, $oldRate, $newRate)
{
    global $wpdb;

    $query = "
        UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
        LEFT JOIN {$wpdb->prefix}wph_tasks t ON (utr.taskId = t.id)
        LEFT JOIN {$wpdb->prefix}wph_projects p ON (utr.projectId = p.id)
        SET utr.hourlyRate = '{$newRate}'
        WHERE
            utr.isProcessing = 0 AND
            utr.clientId = {$userId} AND 
            (p.hourlyRate IS NULL OR p.hourlyRate = '') AND
            (t.hourlyRate IS NULL OR t.hourlyRate = '')
    ";
    $wpdb->query($query);
}
add_action('wph-client-hourly-rate-updated', 'wphUpdateHourlyRateForUnpaidTimeRecords', 10, 3);






/*
//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//    ***
//    ***        SUPER WP HEROES
//    ***        FUNCTION NAME: wph_get_client_hourly_rate
//    ***        DESCRIPTION:
//  ***        CALLED ON:
//  ***
//  ***        TO DO: -
//    ***
//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
*/



function wphGetClientHourlyRate($userId) {
    $customerRate = get_user_meta($userId, 'wph_hourly_rate', true);

    return is_numeric($customerRate) ? $customerRate : wphGetDefaultHourlyRate();
}

function wphGetDefaultHourlyRate()
{
    return get_option('wph_hourly_rate', 0);
}


// ADMIN SCRIPTS

//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    *******
//    *******
//    *******
//    *******
//    *******        SUPER WP HEROES
//    *******        SECTION NAME: ADMIN SCRIPTS
//    *******        DESCRIPTION: print out scripts in the admin section
//    *******
//    *******
//    *******
//    *******
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************


//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//    ***
//    ***        SUPER WP HEROES
//    ***        FUNCTION NAME: wwp_admin_scripts
//    ***        DESCRIPTION: general function to print out all admin scripts in the footer of the wp admin dashboard
//  ***        CALLED ON: wp admin on needed pages
//  ***
//  ***        TO DO: -
//    ***
//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


add_action('admin_print_scripts', 'wph_admin_scripts', 20, 3);
function wph_admin_scripts() {
    global $post;

    if(isset($post->post_type) && !empty($post->post_type)) {
        if ($post->post_type == 'task') {
            $complete = '';
            $label = '';
            if ($post->post_status == 'completed') {
                $complete = ' selected="selected"';
                $label = '<span id="post-status-display"> Completed</span>';
            }

            $script = '
                jQuery(document).ready(function(){
                    jQuery("select#post_status").append(\'<option value="completed" ' . $complete . '>Completed</option>\');
                    jQuery(document).on("click", ".edit-post-status", function() {
                        jQuery(".misc-pub-section label span").html(\' ' . $label . ' \'); 
                    });
                    jQuery(document).on("change","input#title", function () {
                        if (this.value.match(/[^a-zA-Z0-9 ]/g)) {
                            this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, "");
                        }
                    });
                });
            ';

            wp_register_script('task-status', false, ['jquery']);
            wp_enqueue_script('task-status' );
            wp_add_inline_script('task-status', $script);
        } // END COMPLETED TASK STATUS SUPPORT
    } // end post types
}

function wphIsJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) ? true : false;
}

function wphIsEmployeeUser($userId = false)
{
    if (!$userId) {
        $userId = get_current_user_id();
    }

    return (
        user_can($userId, 'employee') ||
        (esc_attr(get_option('wph_treat_admins_as_employees')) == 1 && user_can($userId, 'administrator'))
    );
}

function wphIsClientUser($userId = false)
{
    if (!$userId) {
        $userId = get_current_user_id();
    }

    return user_can($userId, 'customer');
}

function wphShowAdminBarToEmployees($show)
{
    if (wphIsEmployeeUser()) {
        $show = true;
    }

    return $show;
}
add_filter('show_admin_bar', 'wphShowAdminBarToEmployees', 20, 1);

function wphHoursInTimeFormat($hours)
{
    if (!is_numeric($hours) || get_option('wph_show_hours_in_time_format') != 1) {
        return '';
    }

    $fullHours = floor($hours);

    return sprintf(
        '(%s:%s)',
        str_pad(number_format($fullHours, 0, '', ''), 2, '0', STR_PAD_LEFT),
        str_pad(number_format(($hours - $fullHours) * 60, 0, '', ''), 2, 0, STR_PAD_LEFT)
    );
}

add_filter('manage_users_columns', 'wphAddUsersTableColumns');
function wphAddUsersTableColumns($columns)
{
    $columns['hourly_rate'] = __('Hourly Rate', 'wphourly');

    return $columns;
}

add_filter('manage_users_custom_column', 'wphNewModifyUserTableRow', 10, 3);
function wphNewModifyUserTableRow($val, $column, $userId)
{
    switch ($column) {
        case 'hourly_rate':
            if (!user_can($userId, 'customer')) {
                return __('Not a customer', 'wphourly');
            }

            $rate = get_user_meta($userId, 'wph_hourly_rate', true);
//            $currency = get_option('woocommerce_currency');
            if($rate) {
//                return '<b>'.$currency. ' ' .$client_rate.'</b>';
                return '<b>' . apply_filters('wph-currency-for-user', 'USD', $userId) . ' ' . $rate . '</b>';
            } else {
                return 'Not set';
            }
            break;
        default:
    }

    return $val;
}


function wphGetLoader($identifier = '', $hidden = true)
{
    if (!$identifier) {
        $identifier = microtime(true);
    }
    $style = $hidden ? "display: none" : '';
    return '<div id="' . $identifier . '" style="' . $style . '" class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>';
}

add_action('wp_ajax_wph_load_task_add_form', 'wphLoadTaskAddForm');








function wphLoadTaskAddForm()
{
    require_once(plugin_dir_path( __FILE__ ) . '../templates/wph-task-add-tpl.php');

    wp_die();
}

add_action('wp_ajax_wph_load_task_edit_form', 'wphLoadTaskEditForm');
function wphLoadTaskEditForm()
{
    require_once(plugin_dir_path( __FILE__ ) . '../templates/wph-task-tpl.php');

    wp_die();
}


add_action('wp_ajax_wph_delete_project', function() {
    if (!current_user_can('administrator')) {
        die(json_encode([
            'success' => false,
            'message' => __('Unauthorized', 'wphourly'),
        ]));
    }

    $projectId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$projectId) {
        die(json_encode([
            'success' => false,
            'message' => __('Invalid project', 'wphourly'),
        ]));
    }
    global $wpdb;
    /*CHECK IF SOMEONE IS WORKING on a task for this project */
    // $projectId -- project id,
    // $tasksProject -- fetch tasks for this project id 
    $check_db_for_active_tasks = $wpdb->get_results("SELECT id FROM `{$wpdb->prefix}wph_tasks` inner JOIN `{$wpdb->prefix}usermeta` on (`{$wpdb->prefix}wph_tasks`.id = `{$wpdb->prefix}usermeta`.meta_value AND `{$wpdb->prefix}usermeta`.meta_key = 'wph_active_task' ) WHERE `{$wpdb->prefix}wph_tasks`.`project_id` = {$projectId}");

    if(!empty($check_db_for_active_tasks)){
        die('working');
    }
    if (!wphRemoveArchivedProject($projectId)) {
        die(json_encode([
            'success' => false,
            'message' => __('There was an error', 'wphourly'),
        ]));
    }

    die(json_encode([
        'success' => true,
    ]));
});

function wphRemoveArchivedProject($projectId)
{
    if (!current_user_can('administrator')) {
        return false;
    }

    $project = wphGetProject($projectId);
    if (!$project || $project->status != 'archived') {
        return false;
    }

    global $wpdb;

    // first remove the tasks
    $tasks = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}wph_tasks WHERE project_id = {$projectId}");
    foreach ($tasks as $task) {
        wphRemoveTask($task);
    }

    // the the project
    return (false !== $wpdb->delete("{$wpdb->prefix}wph_projects", ['id' => $projectId]));
}

function wphRemoveTask($taskId)
{

    if (!current_user_can('administrator')) {
        return false;
    }

    $task = wphGetTask($taskId);
    if (!$task) {
        return false;
    }

    global $wpdb;

    $canRemoveTask = apply_filters('wph-can-task-be-removed', true);
    if (!$canRemoveTask) {
        return false;
    }

    do_action('wph-before-remove-task', $taskId);

    


    // delete from unpaid tr
    $wpdb->delete("{$wpdb->prefix}wph_unpaid_time_records", ['taskId' => $taskId]);
    //delete from tr
    $wpdb->delete("{$wpdb->prefix}wph_time_records", ['task_id' => $taskId]);

    //delete task
    return (false !== $wpdb->delete("{$wpdb->prefix}wph_tasks", ['id' => $taskId]));

}

add_action('wp_ajax_wph-remove-task', function() {

    $id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
  
    if (!$id) {
        die(json_encode(['success' => false, 'error' => __('Invalid task id','wphourly')]));
    }
    global $wpdb; 
    $check_db_for_active_tasks = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}usermeta` WHERE `meta_key` = 'wph_active_task' AND `meta_value` = '".$id."'");
    
    if(!empty($check_db_for_active_tasks)){
        die(json_encode(['success' => false, 'error' => __('Someone is working','wphourly')]));
    }

    if (!wphRemoveTask($id)) {
        die(json_encode(['success' => false, 'error' => __('The task could not be removed','wphourly')]));
    }


    die(json_encode(['success' => true, 'error' => '']));


});


add_action('wp_ajax_wph_paid-in', 'wphBulkHandleMarkTimeRecordsPaidIn');
function wphBulkHandleMarkTimeRecordsPaidIn()
{
    if (!current_user_can('administrator')) {
        die(json_encode([
            'success' => false,
            'error' => __('Unauthorized','wphourly'),
        ]));
    }

    $records = filter_input(
        INPUT_POST,
        'time-records',
        FILTER_VALIDATE_INT,
        FILTER_REQUIRE_ARRAY
    );

    die(json_encode(['success' => wphMarkTimeRecordsAsPaid($records, 'paid-in')]));
}

add_action('wp_ajax_wph_paid-out', 'wphBulkHandleMarkTimeRecordsPaidOut');
function wphBulkHandleMarkTimeRecordsPaidOut()
{
    if (!current_user_can('administrator')) {
        die(json_encode([
            'success' => false,
            'error' => __('Unauthorized','wphourly'),
        ]));
    }

    $records = filter_input(
        INPUT_POST,
        'time-records',
        FILTER_VALIDATE_INT,
        FILTER_REQUIRE_ARRAY
    );

    die(json_encode(['success' => wphMarkTimeRecordsAsPaid($records, 'paid-out')]));
}

add_action('wp_ajax_wph_unpaid-in', 'wphBulkHandleMarkTimeRecordsUnpaidIn');
function wphBulkHandleMarkTimeRecordsUnpaidIn()
{
    if (!current_user_can('administrator')) {
        die(json_encode([
            'success' => false,
            'error' => __('Unauthorized','wphourly'),
        ]));
    }

    $records = filter_input(
        INPUT_POST,
        'time-records',
        FILTER_VALIDATE_INT,
        FILTER_REQUIRE_ARRAY
    );

    die(json_encode(['success' => wphMarkTimeRecordsAsUnpaid($records, 'paid-in')]));
}

add_action('wp_ajax_wph_unpaid-out', 'wphBulkHandleMarkTimeRecordsUnpaidOut');
function wphBulkHandleMarkTimeRecordsUnpaidOut()
{
    if (!current_user_can('administrator')) {
        die(json_encode([
            'success' => false,
            'error' => __('Unauthorized','wphourly'),
        ]));
    }

    $records = filter_input(
        INPUT_POST,
        'time-records',
        FILTER_VALIDATE_INT,
        FILTER_REQUIRE_ARRAY
    );

    die(json_encode(['success' => wphMarkTimeRecordsAsUnpaid($records, 'paid-out')]));
}

add_action('wp_ajax_wph_delete', 'wphBulkHandleTimeRecordsDelete');
function wphBulkHandleTimeRecordsDelete()
{
    if (!current_user_can('administrator')) {
        die(json_encode([
            'success' => false,
            'error' => __('Unauthorized','wphourly'),
        ]));
    }

    $records = filter_input(
        INPUT_POST,
        'time-records',
        FILTER_VALIDATE_INT,
        FILTER_REQUIRE_ARRAY
    );

    if (!apply_filters('wph-can-delete-time-records', true, $records)) {
        die(json_encode([
            'success' => false,
            'error' => __('Could not delete the time records','wphourly'),
        ]));
    }

    do_action('wph-before-bulk-delete-time-records', $records);

    global $wpdb;

    if (false === $wpdb->query(sprintf("DELETE FROM {$wpdb->prefix}wph_unpaid_time_records WHERE recordId IN (%s)", join(',', $records)))) {
        die(json_encode([
            'success' => false,
            'error' => __('Error while deleting unpaid time records','wphourly'),
        ]));
    }

    if (false === $wpdb->query(sprintf("DELETE FROM {$wpdb->prefix}wph_time_records WHERE id IN (%s)", join(',', $records)))) {
        die(json_encode([
            'success' => false,
            'error' => __('Error while deleting time records','wphourly'),
        ]));
    }

    die(json_encode(['success' => true]));
}

function wphChangeTaskProject($taskId, $projectId)
{
    global $wpdb;

    $query = "
        UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
        INNER JOIN {$wpdb->prefix}wph_projects np ON (np.id = {$projectId})
        SET
            utr.projectId = np.id,
            utr.clientId = np.client_id
        WHERE utr.taskId = {$taskId}
    ";
    $wpdb->query($query);

    $query = "
        UPDATE {$wpdb->prefix}wph_time_records tr
        INNER JOIN {$wpdb->prefix}wph_tasks t ON (t.id = tr.task_id)
        INNER JOIN {$wpdb->prefix}wph_projects op ON (op.id = t.project_id)
        INNER JOIN {$wpdb->prefix}wph_projects np ON (np.id = {$projectId})
        SET
            tr.title = REPLACE(tr.title, CONCAT('project ', op.title, ' (', op.id, ')'), CONCAT('project ', np.title, ' (', np.id, ')')),
            tr.client_id = np.client_id
        WHERE tr.task_id = {$taskId}
    ";
    $wpdb->query($query);

    $query = "
        UPDATE {$wpdb->prefix}wph_tasks t
        INNER JOIN {$wpdb->prefix}wph_projects np ON (np.id = {$projectId})
        SET
            t.project_id = np.id,
            t.client_id = np.client_id
        WHERE t.id = {$taskId}
    ";
    $wpdb->query($query);

    // also update hourly rate
    $hourlyRate = wphGetTaskHourlyRate($taskId);
    $query = "
        UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
        SET utr.hourlyRate = {$hourlyRate}
        WHERE utr.taskId = {$taskId}
    ";
    $wpdb->query($query);

    $isProjectBillable = $wpdb->get_var("SELECT is_billable FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
    if (!$isProjectBillable) {
        $wpdb->query("UPDATE {$wpdb->prefix}wph_tasks SET is_billable = 0 WHERE id = {$taskId}");
        wphRemoveUnpaidTimeRecordsByTaskId($taskId);
    }
}

//add_action( 'show_user_profile', 'wphRenderUploadImageField' );
//add_action( 'edit_user_profile', 'wphRenderUploadImageField' );
//add_action( 'user_new_form', 'wphRenderUploadImageField' );
function wphRenderUploadImageField( $user ) {

    $screen = get_current_screen();

    $screens = array('user-edit','profile');

    if(in_array($screen->base,$screens))
    {
        $profile_pic = ($user!=='add-new-user') ? get_user_meta($user->ID, 'wph_profile_image_id', true): false;

        if( !empty($profile_pic) ){
            $image = wp_get_attachment_image_src( $profile_pic, 'thumbnail' );

        } ?>

        <table class="form-table wph-profile-upload-options">
            <tr>
                <th>
                    <label for="image"><?php _e('Profile Image', 'wphourly') ?></label>
                </th>
                <td>
                    <input type="button" data-id="wph_profile_image_id" data-src="wph-img" class="button wph-image" name="wph-image" id="wph-image" value="Upload" />
                    <input type="hidden" class="button" name="wph_profile_image_id" id="wph_profile_image_id" value="<?php echo !empty($profile_pic) ? $profile_pic : ''; ?>" />
                    <img id="wph-img" src="<?php echo !empty($profile_pic) ? $image[0] : ''; ?>" style="<?php echo  empty($profile_pic) ? 'display:none;' :'' ?> max-width: 100px; max-height: 100px;" />
                </td>
            </tr>
        </table><?php
    }
}

function wphProfileUploadImageUpdate($user_id){
    if( current_user_can('edit_users') ){
        $profile_pic = empty($_POST['wph_profile_image_id']) ? '' : $_POST['wph_profile_image_id'];
        update_user_meta($user_id, 'wph_profile_image_id', $profile_pic);
    }
}
add_action('profile_update', 'wphProfileUploadImageUpdate');
add_action('user_register', 'wphProfileUploadImageUpdate');

add_filter('get_avatar_url', 'wphOverrideAvatarUrl', 10, 3);
function wphOverrideAvatarUrl($url, $id_or_email, $args)
{
    if (is_numeric($id_or_email)) {
        $user = get_user_by('ID', $id_or_email);
    } elseif ($id_or_email instanceof WP_Comment) {
        $user = get_user_by( 'email', $id_or_email->comment_author_email );
    } elseif ($id_or_email instanceof WP_User) {
        $user = $id_or_email;
    } elseif ($id_or_email instanceof WP_Post) {
        $user = get_user_by( 'email', $id_or_email->post_author );
    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if (!$user) {
        return $url;
    }

    $meta = get_user_meta($user->ID, 'wph-user-profile-image', true);

    if ($meta){
        $imageData = wp_get_attachment_image_src($meta, [$args['width'], $args['height']]);
        if ($imageData) {
            $url = $imageData[0];
        }
    }

    return $url;
}

function wphGetUserSelectDropdownNonEditable($userId, $gravatar_size, $type)
{
    $user = get_userdata($userId);
    if (!$user) {
        return '';
    }

    $select = '<div class="dropdown show wph-user-select p-1  sip-holder">';
    $select .= get_avatar($userId, $gravatar_size, '', '', array( 'class' => array( 'wph-selected-user-avatar' ) ));
    $select .= '<small style="font-size: 12px; padding-left: 16px;" class="text-dark font-weight-bold">'.strtoupper($type).': <span class="wph-selected-user-nicename">'.$user->display_name.'</span></small></div>';

    return $select;
}
