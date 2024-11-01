<?php

function wph_client_reports_sc()
{
    $userId = get_current_user_id();
    if (!$userId || !user_can($userId, 'customer')) {
        return '';
    }


    global $wpdb;
    $projectsQuery = "
        SELECT id, title FROM {$wpdb->prefix}wph_projects WHERE client_id = {$userId}
    ";
    $projects = $wpdb->get_results($projectsQuery);
    $options = ['<option>' . __('ALL PROJECTS', 'wphourly') . '</option>'];
    foreach ($projects as $project) {
        $options[] = sprintf('<option value="%d">%s</option>', $project->id, $project->title);
    }

    $report = '';
    if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
        $projectId = filter_input(INPUT_POST, 'wph-reports-project', FILTER_VALIDATE_INT);
        if (!$projectId ||
            in_array($projectId, array_map(function ($project) { return $project->id; }, $projects))
        ) {
            $startDate = filter_input(INPUT_POST, 'reports_start_date');
            $endDate = filter_input(INPUT_POST, 'reports_end_date');
            ob_start();
            wph_generate_timesheet($userId, $startDate, $endDate, $projectId);
            $report = ob_get_contents();
            ob_end_clean();
        } else {
            $report = '<p>' . __('Leave empty or choose a valid project', 'wphourly') . '</p>';
        }
    }

    return '
        <div id="wph-client-reports">
            <div id="wph-client-reports-form" class="row">
                <div class="col-md-12">
                    <form method="post">
                        <select name="wph-reports-project">
                            ' . join('', $options) . '
                        </select>
                        <label for="reports_start_date">' . __('Start Date', 'wphourly') . '<input type="date" id="reports_start_date" name="reports_start_date" style="margin-right: 20px;" value="'.date('Y-m-d', strtotime('-1 month +1 day')).'"/></label>
                        <label for="reports_end_date">' . __('End Date', 'wphourly') . '<input type="date" id="reports_end_date" name="reports_end_date" style="margin-right: 20px;" value="'.date('Y-m-d').'"/></label>
                        <input type="submit" value="' . __('Report', 'wphourly') . '" />
                    </form>
                </div>
            </div>
            <div class="wph-client-reports-result" class="row">
                <div class="col-md-12">
                    ' . $report . '
                </div>
            </div>
        </div>
    ';
}
add_shortcode('wph-client-reports', 'wph_client_reports_sc');



add_shortcode('wph-client-projects', 'wphListClientProjectsSc');
function wphListClientProjectsSc()
{
    if (empty($_REQUEST['project']) && empty($_REQUEST['task']) && empty($_REQUEST['tr'])) {
        $user = wp_get_current_user();
        if (!$user->exists() || !user_can($user->ID, 'customer')) {
            return '';
        }

        if (!class_exists('ProjectListTable')) {
            require_once __DIR__ . '/project-list/ProjectListTable.php';
        }

        $projectList = new ProjectListTable($user->ID);
        $projectList->prepare_items();

        ob_start();
        echo '<div class="wph-project-list">';
        $projectList->display();
        echo '</div>';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    if (empty($_REQUEST['task']) && empty($_REQUEST['tr'])) {
        $projectId = $_REQUEST['project'];
        $details = do_shortcode('[wph-client-project id="' . $projectId . '"]');
        $content = <<<HTML
        <div class="wph-project-details">
            <a href="javascript: history.back()" class="wph-back-button">&larr; Projects</a>
            {$details}
        </div>
HTML;

        return $content;
    }
    if (empty($_REQUEST['tr'])) {
        $taskId = $_REQUEST['task'];
        $details = do_shortcode('[wph-client-task id="' . $taskId . '"]');
        $project = wphGetProject($_REQUEST['project']);
        $content = <<<HTML
        <div class="wph-project-details">
            <a href="javascript: history.back()" class="wph-back-button">&larr; {$project->title}</a>
            {$details}
        </div>
HTML;

        return $content;
    }

    $timeRecord = $_REQUEST['tr'];
    $details = do_shortcode('[wph-client-time-record id="' . $timeRecord . '"]');
    $task = wphGetTask($_REQUEST['task']);
    $content = <<<HTML
        <div class="wph-time-record-details">
            <a href="javascript: history.back()" class="wph-back-button">&larr; {$task->title}</a>
            {$details}
        </div>
HTML;

    return $content;
}

add_shortcode('wph-client-project', 'wwpListClientProjectSc');
function wwpListClientProjectSc($atts)
{
    $projectId = $atts['id'];
    $body_classes = get_body_class();

    
    $project = wphGetProject($projectId);
    if (!$project) {
        return '';
    }
    $user = wp_get_current_user();
    if (!$user->exists() || $user->ID != $project->client_id) {
        return '';
    }

    if (!class_exists('TaskListTable')) {
        require_once __DIR__ . '/project-list/TaskListTable.php';
    }

    $atts = shortcode_atts(['show-title' => true], $atts, 'wph-client-project');

    $numberOfTasks = wph_run_report('', $projectId, "number-of-tasks");
    $numberOfCompletedTasks = wph_run_report('', $projectId, "number-of-completed-tasks");

    //PROJECT PROGRESS BAR
    $projectProgress = 100;
    if ($numberOfTasks) {
        $projectProgress = round(($numberOfCompletedTasks * 100) / $numberOfTasks);
    }

    $result = '<div class="row">';
    $result .= '<div class="col-md-12">';
    $result .= '<div id="exTab1" class="row">';
    $result .= '<div class="col-md-12">';
    if ($atts['show-title']) {
        $result .= '<h2 class="black_txt">' . $project->title . '</h2>';
    }

    
    $result .= '<ul class="wph-nav-tabs" style="margin-left: 0; margin-bottom: 8px;">';
    $result .= '<li class="wph-nav-link active"><a href="#" data-toggle="tab" data-id="wph-tab-overview">'.__('Overview','wphourly').'</a></li>';
    $result .= '<li class="wph-nav-link"><a href="#" data-toggle="tab" data-id="wph-tab-tasks" id="show-tasks-tab">'.__('Tasks','wphourly').'</a></li>';
    $result .= '</ul>';
    
    $result .= '</div>';

    
    $result .= '<div class="wph-tab-content clearfix clear black_txt">';
    $result .= '<div class="col-md-12 wph-tab-pane active" data-tab="wph-tab-overview">';
    $result .= '<div class="row">';
    $result .= '<div class="col-md-6">';
    
    

    $result .= '<h4>Project Description</h4>';
    $result .= '<div>' . apply_filters('wph-project-description', $project->description) . '</div>';

    $result .= '</div>';
  
    $result .= '<div class="col-md-6">';
    
    
    $result .= '<h4>'.__('Project Details','wphourly').'</h4>';
    $result .= '<table>';
    $result .= '<tbody>';
    $result .= '<tr>';
    $result .= '<td rowspan= "1" colspan="2"><h6>'.__('PROGRESS','wphourly').'</h6><div title="' . $projectProgress . '% '.__('Completed','wphourly').'" style="cursor: pointer; background: #ecedac; width: 100%; display:block; height: 10px"><span style="background: #75c283; width: ' . $projectProgress . '%; display:block; height: 10px"></span></div><span>' . $projectProgress . '% '.__('completed','wphourly').'</span></td>';
    $result .= '</tr>';
    $result .= '<tr>';
    $result .= '<th style="font-size: 14px;">'.__('ID','wphourly').'</th>';
    $result .= '<td style="font-size: 14px;"><span class="pull-right"><b>' . $projectId . '</b></span></td>';
    $result .= '</tr>';

    global $wpdb;

    $query = "
        SELECT
            SUM(CAST(tr.hours AS DECIMAL(4, 1))) hours
        FROM {$wpdb->prefix}wph_projects p
        INNER JOIN {$wpdb->prefix}wph_tasks t ON (p.id = t.project_id)
        INNER JOIN {$wpdb->prefix}wph_time_records tr ON (t.id = tr.task_id)
        WHERE p.id = {$projectId}
        GROUP BY p.id
    ";
    $hours = $wpdb->get_var($query);
    $result .= '<tr>';
    $result .= '<th style="font-size: 14px;">'.__('Hours','wphourly').'</th>';
    $result .= '<td style="font-size: 14px;"><span class="pull-right"><b>' . ($hours ? $hours : '0.0') . '</b></span></td>';
    $result .= '</tr>';
    if (wphHasWooCommerce()) {
        $result .= '<tr>';
        $result .= '<th style="font-size: 14px;">'.__('Payment Status','wphourly').'</th>';

        global $wpdb;
        $hasUnpaid = $wpdb->get_var("SELECT 1 FROM {$wpdb->prefix}wph_unpaid_time_records WHERE projectId = {$projectId} LIMIT 1");
        if ($hasUnpaid > 0) {
            $status = '<span class="dashicons dashicons-cart" style="color: #ed4242;"></span>';
        } else {
            $status = '<span class="dashicons dashicons-cart" style="color: #75c283;"></span>';
        }
        $result .= '<td><span class="pull-right">' . $status . '</span></td>';
        $result .= '</tr>';
    }
    $result .= '</tbody>';
    $result .= '</table>';
    $result .= '</div>';
    $result .= '</div>';
    $result .= '</div>';
    $result .= '<div class="col-md-12 wph-tab-pane" data-tab="wph-tab-tasks" id="2a">';
    $result .= '<div class="wrap">';
    $tasksTable = new TaskListTable($projectId);
    $tasksTable->prepare_items();
    ob_start();
    $tasksTable->display();
    $result .= ob_get_contents();
    ob_end_clean();
    $result .= '</div>';
    $result .= '</div>';
    $result .= '</div>';
    $result .= '</div>';

    return $result;
}

add_shortcode('wph-client-task', 'wwpListClientTaskSc');
function wwpListClientTaskSc($atts)
{
    $taskId = $atts['id'];

    global $wpdb;

    $task = $wpdb->get_row("
        SELECT
            t.title title,
            t.description description,
            t.client_id clientId,
            t.assignee_id assigneeId,
            s.description status
        FROM {$wpdb->prefix}wph_tasks t
        INNER JOIN {$wpdb->prefix}wph_status s ON (t.status_id = s.id)
        WHERE t.id = {$taskId}
    ");
    if (!$task) {
        return '';
    }

    $user = wp_get_current_user();
    if (!$user->exists() || $user->ID != $task->clientId) {
        return '';
    }

    $atts = shortcode_atts(['show-title' => true], $atts);

    $result = '';
    $result .= '<div class="col-md-12">';
    $result .= '<div id="exTab1" class="row">';
    $result .= '<div class="col-md-12">';
    if ($atts['show-title']) {
        $result .= '<h4 class="black_txt">' . $task->title . '</h4>';
    }
    

    $result .= '<ul class="wph-nav-tabs" style="margin-left: 0; margin-bottom: 8px;">';
    $result .= '<li class="wph-nav-link active"><a href="#" data-toggle="tab" data-id="wph-tab-overview">'.__('Overview','wphourly').'</a></li>';
    $result .= '<li class="wph-nav-link"><a href="#" data-toggle="tab" data-id="wph-tab-timerecords">'.__('Time Records','wphourly').'</a></li>';
    $result .= '</ul>';

    $result .= '</div>';
    $result .= '<div class="wph-tab-content clearfix clear black_txt">';
    $result .= '<div class="col-md-12 wph-tab-pane active" id="1a" data-tab="wph-tab-overview">';

    $result .= '<div class="row">';
    $result .= '<div class="col-md-6">';
    $result .= '<h4>'.__('Task Description','wphourly').'</h4>';
    $result .= '<div>' . apply_filters('wph-task-description', $task->description) . '</div><br />';
    $result .= '</div>';

    $result .= '<div class="col-md-6">';
    $result .= '<h4>'.__('Task Details','wphourly').'</h4>';
    $result .= '<table>';
    $result .= '<tbody>';
    $showAssigneeToClients = esc_attr(get_option('wph_tracker_show_assignee_to_client')) == 1;
    if ($showAssigneeToClients) {
        $result .= '<tr>';
        $result .= '<th>'.__('Assignee','wphourly').'</th>';
        $result .= '<td><span class="pull-right"><b>' . get_avatar($task->assigneeId, 32) . '</b></span></td>';
        $result .= '</tr>';
    }

    $query = "
        SELECT SUM(tr.hours)
        FROM {$wpdb->prefix}wph_time_records tr
        WHERE task_id = {$taskId}
    ";
    $result .= '<tr>';
    $result .= '<th>'.__('Hours','wphourly').'</th>';
    $result .= '<td><span class="pull-right">' . $wpdb->get_var($query) . '</span></td>';
    $result .= '</tr>';
    $result .= '<tr>';
    $result .= '<th'.__('ID','wphourly').'></th>';
    $result .= '<td><span class="pull-right">' . $taskId . '</span></td>';
    $result .= '</tr>';
    $result .= '<tr>';
    $result .= '<th>'.__('Status','wphourly').'</th>';
    $result .= '<td><span class="pull-right">' . $task->status . '</span></td>';
    $result .= '</tr>';
    if (wphHasWooCommerce()) {
        $result .= '<tr>';
        $result .= '<th>'.__('Payment Status','wphourly').'</th>';
        $hasUnpaid = $wpdb->get_var("SELECT 1 FROM {$wpdb->prefix}wph_unpaid_time_records WHERE taskId = {$taskId}");
        if ($hasUnpaid > 0) {
            $status = '<span class="dashicons dashicons-cart" style="color: #ed4242;"></span>';
        } else {
            $status = '<span class="dashicons dashicons-cart" style="color: #75c283;"></span>';
        }
        $result .= '<td><span class="pull-right">' . $status . '</span></td>';
        $result .= '</tr>';
    }
    $result .= '</tbody>';
    $result .= '</table>';
    $result .= '</div>';
    $result .= '</div>';
    $result .= '</div>';
    $result .= '<div class="col-md-12 wph-tab-pane" id="2a" data-tab="wph-tab-timerecords">';

    if (!class_exists('TimeRecordListTable')) {
        require_once __DIR__ . '/project-list/TimeRecordListTable.php';
    }
    $trTable = new TimeRecordsListTable($taskId);

    $trTable->prepare_items();

    $result .= '<div class="wrap wph-time-records-container">';
    ob_start();
    $trTable->display();
    $result .= ob_get_contents();
    ob_end_clean();
    $result .= '</div>';

    $result .= '</div>';

    $result .= '</div>';
    $result .= '</div>';

    $result .= '';

    return $result;
}

add_shortcode('wph-client-time-record', 'wwpListClientTimeRecordSc');
function wwpListClientTimeRecordSc($atts)
{
    $timeRecordId = $atts['id'];

    $user = wp_get_current_user();
    global $wpdb;
    $timeRecord = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_time_records WHERE id = {$timeRecordId}");
    if (!$user->exists() || !$timeRecord || $user->ID != $timeRecord->client_id) {
        return '';
    }

    // $image = 'This time record was added manually';
    // if ($timeRecord->screenshot && function_exists('\SWPH\WpHourly\Tracker\screenshotPathToUrl')) {
    //     $image = '<img src="' . \SWPH\WpHourly\Tracker\screenshotPathToUrl($timeRecord->screenshot) . '" />';
    // }
    if ($timeRecord->tr_type == 'manual') {
        $image = __('This time record was added manually','wphourly');
    } else {
        if (!function_exists('SWPH\WpHourly\Tracker\timeRecordScreenshot')) {
            $src = WP_HOURLY_URI.'assets/img/screenshot-expired.png';
        } else {
            $src = SWPH\WpHourly\Tracker\timeRecordScreenshot($timeRecord->id);
        }

        $image = '<img src="' . $src . '" />';
    }
    $takenAt = $timeRecord->timestamp ? (new \DateTime('@' . $timeRecord->timestamp))->format('Y-m-d H:i:s') : $timeRecord->created_at;
//    $comments = do_shortcode('[wph-client-tr-comments id="' . $timeRecordId . '"]');
    $comments = '';
    $assigneeRow = '';
    $showAssigneeToClients = esc_attr(get_option('wph_tracker_show_assignee_to_client')) == 1;
    if ($showAssigneeToClients) {
        $assignee = get_avatar($timeRecord->assignee_id, 32);
        $assigneeRow = "
            <tr>
                <th>".__('Assignee','wphourly')."</th>
                <td>{$assignee}</td>
            </tr>
        ";
    }

    $statusRow = '';
    if (wphHasWooCommerce()) {
        $status = $timeRecord->is_paid == 0
            ? '<span class="dashicons dashicons-cart" style="color: #ed4242;"></span>'
            : '<span class="dashicons dashicons-cart" style="color: #75c283;"></span>';
        $statusRow = "
            <tr>
                <th>".__('Status','wphourly')."</th>
                <td>{$status}</td>
            </tr>
        ";
    }

    return "
        <div class='row'>
            <div class='col-md-12'>
                <h4>".__('Time Record Screenshot','wphourly')."</h4>
                <div>{$image}</div>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-12'>
                <h4>".__('Time Record Details','wphourly')."</h4>
                <table>
                    <tbody>
                        <tr>
                            <th>".__('ID','wphourly')."ID</th>
                            <td>{$timeRecord->title}</td>
                        </tr>
                        <tr>
                            <th>".__('Hours','wphourly')."Hours</th>
                            <td>{$timeRecord->hours}</td>
                        </tr>
                        <tr>
                            <th>".__('Taken at','wphourly')."Taken at</th>
                            <td>{$takenAt}</td>
                        </tr>
                        {$assigneeRow}
                        {$statusRow}
                    </tbody>
                </table>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-12'>
                {$comments}
            </div>
        </div>
    ";
}

//add_shortcode('wph-client-tr-comments', 'wwpTrComments');
//function wwpTrComments($atts)
//{
//    $user = wp_get_current_user();
//    if (!$user->exists() || $user->ID != get_post_meta($atts['id'], 'time_record_client', true)) {
//        return '';
//    }
//
//    $comments = get_comments(array('post_id' => $atts['id']));
//
//    $form = '';
//    $form .= '<table class="comments" style="width:100%">';
//    $form .= '<tbody>';
//
//    foreach ($comments as $comment) {
//        $form .= '<tr>';
//        $form .= '<td>';
//        $form .= '<div>';
//        $form .= get_avatar($comment->comment_author_email, 32) . ' Posted by ';
//        $form .= $comment->comment_author . ' at ' . $comment->comment_date;
//        $form .= '</div>';
//        $form .= '<blockquote>' . $comment->comment_content . '</blockquote>';
//        $form .= '</td>';
//        $form .= '</tr>';
//    }
//
//    $form .= '</tbody>';
//    $form .= '</table>';
//    $form .= '
//        <div class="comment-form">
//            <h4 class="comments-wrapper-heading">Add a comment</h4>
//            <form id="commentform" action="' . get_option("siteurl") . '/wp-comments-post.php" method="post" id="commentform" class="frame-form-black">
//                <div class="commentform-element">
//                    <label class="hide" for="comment">Message</label>
//                    <textarea id="comment" class="input-fields" placeholder="Message" name="comment" cols="40" rows="10"></textarea>
//                </div>
//    ';
//
//    $form .= '
//            <p class="submit-form">
//                <input name="submit" class="form-submit-button"  type="submit" id="submit-comment" value="Post comment">
//                <input type="hidden" name="comment_post_ID" value="' . $atts['id'] . '" id="comment_post_ID">
//                <input type="hidden" name="comment_parent" id="comment_parent" value="0">
//                <input type="hidden" name="wph_redirect_to" value="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '" />
//            </p>
//    ';
//
//    $form .= '
//            </form>
//        </div>
//    ';
//
//    return $form;
//}
//
//add_filter('comment_post_redirect', function ($location) {
//    if (isset($_POST['wph_redirect_to'])) {
//        return $_POST['wph_redirect_to'];
//    }
//
//    return $location;
//});
