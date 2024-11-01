<?php

function wph_run_report($userId = '', $projectId = '', $viewType = '', $startDate = '', $endDate = '')
{
    return  wph_return_display($userId, $projectId, strtolower($viewType), $startDate, $endDate);
}


/* --------------------------------------------------------------------------------------------------------------------------------------- */

/* DISPLAY RESULTS */

function wph_return_display($userId, $projectId, $viewType, $startDate, $endDate)
{
    $report = '';
    global $wpdb;

    $viewType = strtolower($viewType);
    if ($viewType == 'tasks-table') {
        wph_generate_timesheet($userId, $startDate, $endDate, $projectId);
    } elseif ($viewType == 'unpaid') {
        $projectsCondition = $projectId ? " AND project.id = {$projectId}" : '';
        $user = get_userdata($userId);
        $start = strtotime(date("Y-m-d 00:00:00", strtotime($startDate)));
        $end = strtotime(date("Y-m-d 23:59:59", strtotime($endDate)));
        $roles = array_map(function ($role) {
            return strtolower($role);
        }, empty($user) ? [] : $user->roles);
        $isCustomer = in_array('customer', $roles);
        $totalCostClass = '';
        if ($isCustomer) {
            $userCondition = "tr.client_id = {$userId} AND tr.is_paid = 0 AND task.is_billable = 1";
            $clientHourlyRate = wphGetClientHourlyRate($userId);
        } else {
            $userCondition = "tr.assignee_id = {$userId} AND tr.is_paid_out = 0";
            $clientHourlyRate = 0;
            $totalCostClass = 'hidden';
        }
        $query = "
            SELECT
                tr.id id,
                tr.hours hours,
                tr.title title,
                tr.assignee_id assignee_id,
                CASE
                    WHEN task.hourlyRate IS NOT NULL AND task.hourlyRate != '' THEN task.hourlyRate
                    WHEN project.hourlyRate IS NOT NULL AND project.hourlyRate != '' THEN project.hourlyRate
                    ELSE c.meta_value
                END cost
            FROM {$wpdb->prefix}wph_time_records tr
            LEFT JOIN {$wpdb->prefix}wph_tasks task ON (task.id = tr.task_id)
            LEFT JOIN {$wpdb->prefix}wph_projects project ON (project.id = task.project_id)
            LEFT JOIN {$wpdb->prefix}usermeta c ON (tr.client_id = c.user_id AND c.meta_key = 'wph_hourly_rate')
            WHERE
                {$userCondition}
                {$projectsCondition}
                AND tr.`timestamp` >= '{$start}' AND tr.`timestamp` <= '{$end}'
        ";

        $results = $wpdb->get_results($query);

        $totalHours = 0;
        $totalCost = 0;
        $rows = join('', array_map(function ($timeRecord) use (&$totalHours, &$totalCost, &$clientHourlyRate) {
            $totalHours += $timeRecord->hours;
            $timeRecordCost = $timeRecord->hours * $clientHourlyRate;
            $totalCost += $timeRecordCost;
            return sprintf(
                "<tr>
                    <td><input type='checkbox' class='unpaid-time-record' checked name='tr_id[]' value='%d' data-hours='%s' data-cost='%s' /></td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s h %s</td>
                </tr>",
                $timeRecord->id,
                $timeRecord->hours,
                $timeRecordCost,
                get_avatar($timeRecord->assignee_id, 32),
                function_exists('\SWPH\WpHourly\Tracker\screenshotPathToUrl') && !empty($timeRecord->screenshot)
                    ? sprintf(
                        '<img src="%s" alt="%s" width="150" />',
                        \SWPH\WpHourly\Tracker\screenshotPathToUrl($timeRecord->screenshot),
                        $timeRecord->title
                    )
                    : '-',
                $timeRecord->title,
                $timeRecord->hours,
                wphHoursInTimeFormat($timeRecord->hours)
            );
        }, $results));

        $table = "
            <table class='wp-list-table widefat'>
                <tr>
                    <th></th>
                    <th>".__('Assignee','wphourly')."</th>
                    <th>".__('Screenshot','wphourly')."</th>
                    <th>".__('Title','wphourly')."</th>
                    <th>".__('Hours','wphourly')."</th>
                </tr>
                {$rows}
            </table>
        ";

        $totalHours = number_format($totalHours, 2);
        $markType = $isCustomer ? 'paid-in' : 'paid-out';
        $totals = "
            <div class='mark-paid-container' style='margin-top: 20px;'>
                <label>".__('Total hours to mark as paid:','wphourly')." <strong><span id='total-marked-unpaid-hours'>{$totalHours}</span>/<span id='total-unpaid-hours'>{$totalHours}</span></strong></label>
                <label class='{$totalCostClass}'>".__('Total cost for marked hours:','wphourly')." <strong><span id='total-marked-unpaid-hours-cost'>{$totalCost}</span>/<span id='total-unpaid-hours-cost'>{$totalCost}</span></strong></label>
                <input type='hidden' id='mark-type' value='{$markType}' />
                <button id='mark-unpaid-hours'>".__('Mark as paid','wphourly')."</button>
            </div>
        ";
        echo "
            {$table}
            {$totals}
        ";
    } elseif ($viewType == 'tasks-table-unpaid-out') {
        wph_generate_timesheet($userId, $startDate, $endDate, $projectId);
    } elseif ($viewType == 'number-of-tasks') {
        $report .= $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wph_tasks WHERE project_id = {$projectId}");
    } elseif ($viewType == 'number-of-completed-tasks') {
        $report .= $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wph_tasks WHERE project_id = {$projectId} AND status_id = 3");
    }

    return $report;
}

function wphMarkTimeRecordsAsPaidFromForm()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $records = filter_input(INPUT_POST, 'records', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
    $type = filter_input(INPUT_POST, 'mark-type');

    wphMarkTimeRecordsAsPaid($records, $type);

    wp_die();
}
add_action('wp_ajax_mark-time-records-as-paid', 'wphMarkTimeRecordsAsPaidFromForm');

function wphMarkTimeRecordsAsPaid($records, $type)
{
    if (!is_array($records) || empty($records)) {
        return false;
    }

    global $wpdb;
    $wpdb->query(sprintf("
        UPDATE %swph_time_records tr
        SET %s=1
        WHERE tr.id IN (%s)
    ", $wpdb->prefix, $type == 'paid-in' ? 'is_paid' : 'is_paid_out', join(',', $records)));

    if ($type == 'paid-in') {
        $wpdb->query(sprintf("
            DELETE utr FROM %swph_unpaid_time_records utr
            INNER JOIN %swph_time_records tr ON (utr.recordId = tr.id)
            WHERE tr.id IN (%s)
        ", $wpdb->prefix, $wpdb->prefix, join(',', $records)));
    }

    return true;

//    add logging
//    $user_id = get_current_user_id();
//    $str = $_POST['recordsId'];
//    $clientId = $_POST['clientId'];
//    $records = explode(",",$str);
//    foreach ($records as $post_id) {
//        update_post_meta($post_id, 'is_paid', 1);
//    }
//    $log = fopen($_SERVER['DOCUMENT_ROOT']."/wp-content/themes/NEGOSOL/_framework/lib/ns-agency-manager/core/widgets/unpaid-hours/logs/marked-as-paid/".$clientId."-".date('Y-m-d h:i:s')."-by-".$user_id.".txt", "w") or die('could not write to log file');
//    $txt = $str."\n";
//    fwrite($log, $txt);
//    fclose($myfile);
}


function wphMarkTimeRecordsAsUnpaid($records, $type)
{
    if (!is_array($records) || empty($records)) {
        return false;
    }

    global $wpdb;
    $wpdb->query(sprintf("
        UPDATE %swph_time_records tr
        SET %s=0
        WHERE tr.id IN (%s)
    ", $wpdb->prefix, $type == 'paid-in' ? 'is_paid' : 'is_paid_out', join(',', $records)));

    if ($type == 'paid-in') {
        $query = sprintf("
            INSERT INTO {$wpdb->prefix}wph_unpaid_time_records (
                recordId,
                taskId,
                projectId,
                clientId,
                hours,
                hourlyRate,
                isProcessing,
                createdAt
            )
            SELECT
                tr.id,
                tr.task_id,
                t.project_id,
                tr.client_id,
                tr.hours,
                CASE 
                    WHEN (t.hourlyRate IS NOT NULL AND t.hourlyRate > 0) THEN t.hourlyRate
                    WHEN (p.hourlyRate IS NOT NULL AND p.hourlyRate > 0) THEN p.hourlyRate
                    WHEN (cr.meta_value IS NOT NULL AND cr.meta_value > 0) THEN cr.meta_value
                    ELSE gr.option_value
                END as hr,
                0,
                tr.created_at
            FROM {$wpdb->prefix}wph_time_records tr
            LEFT JOIN {$wpdb->prefix}wph_unpaid_time_records utr ON (utr.recordId = tr.id)
            LEFT JOIN {$wpdb->prefix}wph_tasks t ON (t.id = tr.task_id)
            LEFT JOIN {$wpdb->prefix}wph_projects p ON (p.id = t.project_id)
            LEFT JOIN {$wpdb->prefix}usermeta cr ON (tr.client_id = cr.user_id AND cr.meta_key = 'wph_hourly_rate')
            LEFT JOIN {$wpdb->prefix}options gr ON (gr.option_name = 'wph_hourly_rate')
            WHERE
                tr.id IN (%s) AND
                utr.id IS NULL
        ", join(',', $records));
//        echo $query;
        $wpdb->query($query);
    }

    return true;
}

add_action('wp_ajax_wph_get_projects_dropdown', 'wph_get_projects_dropdown_callback');
function wph_get_projects_dropdown_callback()
{
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

    $user = get_userdata($userId);
    $roles = array_map(function ($role) {
        return strtolower($role);
    }, empty($user) ? [] : $user->roles);

    global $wpdb;
    if (in_array('customer', $roles)) {
        $query = "
                SELECT
                    p.id id,
                    p.title title
                FROM {$wpdb->prefix}wph_projects p
                WHERE p.client_id = {$userId}
            ";
    } else if (in_array('employee', $roles)) {
        $query = "
                SELECT
                    p.id id,
                    p.title title
                FROM {$wpdb->prefix}wph_projects p
                INNER JOIN {$wpdb->prefix}wph_tasks t ON (t.project_id = p.id)
                WHERE t.assignee_id = {$userId}
                GROUP BY p.id
            ";
    }

    $projects = $wpdb->get_results($query);
    $projects_dropdown = '';
    $projects_dropdown .= '<select name="select_project_list" id="select_project_list">';
    $projects_dropdown .= '<option value="">' . __('Select All Projects', 'wphourly') . '</option>';
    foreach ($projects as $project) {
        $projects_dropdown .= '<option value="' . $project->id . '">' . $project->title . '</option>';
    }
    $projects_dropdown .= "</select>";

    echo $projects_dropdown;
    die();
}
