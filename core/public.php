<?php

declare(strict_types=1);

define('WPH_BILLABLE', 1);
define('WPH_NON_BILLABLE', 2);
define('WPH_BILLABLE_NON_BILLABLE', 3);

function wphGetClockStyle($progress, $total)
{
    if (!$total) {
        return 'text-secondary';
    }

    $ratio = $progress / $total;
    if ($ratio <= 0.8) {
        return 'text-success';
    } elseif ($ratio <= 1) {
        return 'text-warning';
    }

    return 'text-danger';
}

// client related
function wphGetClientProjects($clientId, $status)
{
    global $wpdb;

    return $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}wph_projects
        WHERE
            status = '{$status}' AND
            client_id = {$clientId}
    ");
}

function wphGetClientActiveProjects($clientId)
{
    return wphGetClientProjects($clientId, WPH_PROJECT_STATUS_ACTIVE);
}

function wphGetClientArchivedProjects($clientId)
{
    return wphGetClientProjects($clientId, WPH_PROJECT_STATUS_ARCHIVED);
}

function wphGetClientRate($clientId)
{
    return wphGetClientHourlyRate($clientId);
}

function wphGetClientHourlyRateSource($clientId)
{
    $customerRate = get_user_meta($clientId, 'wph_hourly_rate', true);

    return is_numeric($customerRate) ? __('client rate','wphourly') : __('global rate','wphourly');
}

function wphGetClientUnpaidHours($clientId, DateTimeInterface $from = null, DateTimeImmutable $to = null)
{
    if (is_null($to)) {
        $to = new DateTime();
    }

    if (is_null($from)) {
        // all time
        $from = new DateTime('@0');
    }

    global $wpdb;

    $records = $wpdb->get_results("
        SELECT
            utr.taskId taskId,
            utr.taskTitle taskName,
            utr.projectId projectId,
            utr.projectTitle projectName,
            utr.hours hours,
            SUM(utr.hours) totalHours,
            SUM(utr.hours * utr.hourlyRate) cost
        FROM {$wpdb->prefix}wph_unpaid_time_records utr
        WHERE
            utr.clientId = {$clientId} AND
            utr.createdAt BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'
    ");

    if (empty($records)) {
        return [
            'unpaidHours' => [],
            'totalHours' => 0,
            'totalCost' => 0,
        ];
    }

    return [
        'unpaidHours' => array_map(
            function ($record) {
                return [
                    'taskId' => $record->taskId,
                    'taskName' => $record->taskName,
                    'projectId' => $record->projectId,
                    'projectName' => $record->projectName,
                    'hours' => $record->hours,
                ];
            },
            $records
        ),
        'totalHours' => $records[0]->totalHours,
        'totalCost' => $records[0]->cost,
    ];
}

// project related

function wphGetProject($projectId)
{
    global $wpdb;

      return $wpdb->get_row("
        SELECT * FROM {$wpdb->prefix}wph_projects
        WHERE
            id = '{$projectId}'
    ");
}


function wphGetProjects($status = '')
{
    global $wpdb;

    $statusFilter = $status ? "WHERE status = '{$status}'" : '';
    return $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}wph_projects
        {$statusFilter}
    ");
}


function wphGetProjectStatus($projectId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT status FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
}

function wphGetProjectHourlyRate($projectId)
{
    global $wpdb;

    $project = $wpdb->get_row("SELECT hourlyRate, client_id FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
    if (!$project) {
        return null;
    }

    return $project->hourlyRate ? $project->hourlyRate : wphGetClientHourlyRate($project->client_id);
}

function wphGetProjectHourlyRateSource($projectId)
{
    global $wpdb;

    $project = $wpdb->get_row("SELECT hourlyRate, client_id FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
    if (!$project) {
        return '';
    }

    return $project->hourlyRate ? __('project rate','wphourly') : wphGetClientHourlyRateSource($project->client_id);
}

function wphGetProjectClientId($projectId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
}

function wphGetProjectHours($projectId, $type = WPH_BILLABLE_NON_BILLABLE, $my = false)
{
    global $wpdb;

    $myCondition = '';
    if($my) {
        $user = get_current_user_id();
        $myCondition = " AND tr.assignee_id = $user";
    }



    switch ($type) {
        case WPH_BILLABLE:
            $billableCondition = 'AND tasks.is_billable != 0';
            break;
        case WPH_NON_BILLABLE:
            $billableCondition = 'AND tasks.is_billable = 0';
            break;
        default:
            $billableCondition = '';
            break;
    }

    return (float) $wpdb->get_var("
        SELECT SUM(tr.hours)
        FROM {$wpdb->prefix}wph_projects projects
        INNER JOIN {$wpdb->prefix}wph_tasks tasks ON (projects.id = tasks.project_id)
        INNER JOIN {$wpdb->prefix}wph_time_records tr ON (tr.task_id = tasks.id)
        WHERE projects.id = {$projectId} {$billableCondition} {$myCondition}
    ");
}

function wphGetProjectHoursStatus($projectId, $isPaid = false)
{
    global $wpdb;
    $isPaid = (int) $isPaid;

    return $wpdb->get_var("
        SELECT SUM(tr.hours)
        FROM {$wpdb->prefix}wph_projects projects
        INNER JOIN {$wpdb->prefix}wph_tasks tasks ON (projects.id = tasks.project_id)
        INNER JOIN {$wpdb->prefix}wph_time_records tr ON (tr.task_id = tasks.id)
        WHERE
            projects.id = {$projectId} AND
            tr.is_paid = {$isPaid}
    ");
}

function wphGetProjectMembers($projectId)
{
    global $wpdb;

    return $wpdb->get_col("
        SELECT task.assignee_id
        FROM {$wpdb->prefix}wph_projects project
        INNER JOIN {$wpdb->prefix}wph_tasks task ON (task.project_id = project.id)
        WHERE project.id = {$projectId}
    ");
}

function wphGetProjectDeadline($projectId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT deadline FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
}

function wphGetProjectCompletionRate($projectId)
{
    global $wpdb;

    $result = $wpdb->get_row("
        SELECT
            SUM(IF(task.status_id = 3, 1, 0)) completedTasks,
            COUNT(*) totalTasks
        FROM {$wpdb->prefix}wph_tasks task
        WHERE task.project_id = {$projectId}
    ");

    if (!$result || $result->totalTasks == 0) {
        return null;
    }

    return round(($result->completedTasks / $result->totalTasks) * 100, 2);
}

function wphGetMyProjectTasks($projectId, $taskStatus = 0, $user) {
    global $wpdb;

    $myCondition = " AND task.assignee_id = $user";
    $statusCondition = $taskStatus ? "AND task.status_id = {$taskStatus}" : '';
    return $wpdb->get_results("
        SELECT task.*
        FROM {$wpdb->prefix}wph_tasks task
        INNER JOIN {$wpdb->prefix}wph_projects project ON (project.id = task.project_id)
        WHERE project.id = {$projectId} {$statusCondition} {$myCondition}   
    ");
}
function wphGetProjectTasks($projectId, $taskStatus = 0)
{
    global $wpdb;

    $statusCondition = $taskStatus ? "AND task.status_id = {$taskStatus}" : '';
    return $wpdb->get_results("
        SELECT task.*
        FROM {$wpdb->prefix}wph_tasks task
        INNER JOIN {$wpdb->prefix}wph_projects project ON (project.id = task.project_id)
        WHERE project.id = {$projectId} {$statusCondition}
    ");
}

//task related

function wphGetTask($taskId)
{
    global $wpdb;

    return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
}

function wphGetTaskTitle($taskId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT title FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
}

function wphGetTaskProject($taskId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT project_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
}

function wphGetTaskStatuses()
{
    global $wpdb;

    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wph_status");
}

function wphGetTaskClientId($taskId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
}

function wphGetTaskStatus($taskId)
{
    global $wpdb;

    return $wpdb->get_var("
        SELECT
            taskStatus.status status,
            taskStatus.description title
        FROM {$wpdb->prefix}wph_tasks tasks
        INNER JOIN {$wpdb->prefix}wph_status taskStatus ON (taskStatus.id = tasks.status_id)
        WHERE tasks.id = {$taskId}
    ");
}

function wphGetTasks($status = '')
{
    global $wpdb;

    $statusFilter = $status ? "WHERE status_id = '{$status}'" : '';
    return $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}wph_tasks
        {$statusFilter}
    ");
}

function wphGetTasksByProject($project_id)
{
    global $wpdb;

    return $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}wph_tasks
        WHERE project_id = '$project_id'
    ");
}

function wphGetTaskHourlyRate($taskId)
{
    global $wpdb;

    $task = $wpdb->get_row("SELECT hourlyRate, project_id, client_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
    if (!$task) {
        return null;
    }

    return $task->hourlyRate
        ? $task->hourlyRate
        : ($task->project_id
            ? wphGetProjectHourlyRate($task->project_id)
            : wphGetClientHourlyRate($task->client_id)
        );
}

function wphGetTaskHourlyRateSource($taskId)
{
    global $wpdb;

    $task = $wpdb->get_row("SELECT hourlyRate, project_id, client_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
    if (!$task) {
        return '';
    }

    return $task->hourlyRate
        ? 'task source'
        : ($task->project_id
            ? wphGetProjectHourlyRateSource($task->project_id)
            : wphGetClientHourlyRateSource($task->client_id)
        );
}

function wphGetTaskHours($taskId, $type = WPH_BILLABLE_NON_BILLABLE, $me = false)
{
    global $wpdb;

    $myCondition = '';
    if($me) {
        $user = get_current_user_id();
        $myCondition = " AND tr.assignee_id = $user";
    }

    $joinProjects = "LEFT JOIN {$wpdb->prefix}wph_projects project ON (task.project_id = project.id)";
    switch ($type) {
        case WPH_BILLABLE:
            $billableCondition = 'AND ((project.id IS NOT NULL AND project.is_billable != 0) OR task.is_billable != 0)';
            break;
        case WPH_NON_BILLABLE:
            $billableCondition = 'AND ((project.id IS NOT NULL AND project.is_billable = 0) OR task.is_billable = 0)';
            break;
        default:
            $billableCondition = '';
            $joinProjects = '';
            break;
    }

    return $wpdb->get_var("
        SELECT SUM(tr.hours)
        FROM {$wpdb->prefix}wph_tasks task
        {$joinProjects}
        INNER JOIN {$wpdb->prefix}wph_time_records tr ON (task.id = tr.task_id)
        WHERE task.id = {$taskId} {$billableCondition} {$myCondition}
    ");
}

function wphGetTaskHoursStatus($taskId, $isPaid = false)
{
    global $wpdb;
    $isPaid = (int) $isPaid;

    return $wpdb->get_var("
        SELECT SUM(tr.hours)
        FROM {$wpdb->prefix}wph_tasks task
        INNER JOIN {$wpdb->prefix}wph_time_records tr ON (task.id = tr.task_id)
        WHERE
            task.id = {$taskId} AND
            tr.is_paid = {$isPaid}
    ");
}

function wphGetTaskAssignee($taskId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT task.assignee_id FROM {$wpdb->prefix}wph_tasks task WHERE task.id = {$taskId}");
}

function wphGetTaskDeadline($taskId)
{
    global $wpdb;

    return $wpdb->get_var("SELECT deadline FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
}

function wphIsTaskBillable($taskId)
{
    global $wpdb;

    return (bool) $wpdb->get_var("SELECT is_billable FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
}

function wphGetTaskTimeRecords($taskId)
{

    global $wpdb;

    return $wpdb->get_results("
        SELECT tr.*
        FROM {$wpdb->prefix}wph_time_records tr
        INNER JOIN {$wpdb->prefix}wph_tasks task ON (tr.task_id = task.id)
        WHERE task.id = {$taskId}
        ORDER BY tr.created_at DESC
    ");
}

function wphGetTotalTrackedTime()
{
    global $wpdb;

    return $wpdb->get_var("
        SELECT SUM(hours) FROM `{$wpdb->prefix}wph_time_records`
    ");
}

// employee related
function wphGetEmployeeProjects($employeeId, $all = true)
{
    global $wpdb;
    if(!$all) {
        return $wpdb->get_results("
        SELECT DISTINCT project.*
        FROM {$wpdb->prefix}wph_projects project
        INNER JOIN {$wpdb->prefix}wph_tasks task ON (task.project_id = project.id)
        WHERE task.assignee_id = {$employeeId} AND task.status_id = 2
    ");
    }
    return $wpdb->get_results("
        SELECT DISTINCT project.*
        FROM {$wpdb->prefix}wph_projects project
        INNER JOIN {$wpdb->prefix}wph_tasks task ON (task.project_id = project.id)
        WHERE task.assignee_id = {$employeeId}
    ");
}

function wphGetEmployeeTasks($employeeId)
{
    global $wpdb;

    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wph_tasks WHERE assignee_id = {$employeeId}");
}

function wphGetEmployeeWorkedHours($employeeId, DateTimeInterface $from = null, DateTimeInterface $to = null)
{
    global $wpdb;

    if (is_null($to)) {
        $to = new DateTime();
    }

    if (is_null($from)) {
        $from = new DateTime('@0');
    }

    return $wpdb->get_var("
        SELECT SUM(tr.hours)
        FROM {$wpdb->prefix}wph_time_records tr
        WHERE
            tr.assignee_id = {$employeeId} AND
            tr.created_at BETWEEN '{$from->format('Y-m-d H:i:s')}' AND '{$to->format('Y-m-d H:i:s')}'
    ");
}

function wphGetHoursTarget($employeeId)
{
    return get_user_meta($employeeId, 'wph_hours_target', true);
}


/*
//    ---        SUPER WP HEROES                                               
//    ---        COMMENT: INPUTS
*/

function wphSwitchInput($entity, $entityId, $update, $value, $onLabel, $offLabel, $id, $class, $style, $title, $is_ajax) {

  if($value == '1' || $value == 'active') {
    $is_active_check = 'checked';
  } else {
    $is_active_check = '';
  }

  if($title != '') {
      $title = 'data-toggle="tooltip" data-placement="top" title="'.$title.'"';
  }

  if($is_ajax == true) {
      $ajax = 'data-is-ajax="yes"';
  } else {
    $ajax = 'data-is-ajax="no"';
  }

  $input = '
  <label class="switch '.$class.'" style="'.$style.'" '.$title.'  id="witch-holder'.$id.'">
    <i class="fas fa-spinner fa-spin"></i>
    <input name="'.$update.'" value="'.$value.'" id="'.$id.'" class="switch-input" ' . $is_active_check . ' type="checkbox" data-id="'.$entityId.'" data-entity="'.$entity.'"  data-update="'.$update.'" />
    <span class="switch-label" data-on="'.$onLabel.'" data-off="'.$offLabel.'" '.$ajax.'>
      <span class="switch-handle"></span>
    </span> 
  </label>';

  return $input;
}

function wphEditInPlace($type, $size, $class, $id, $entityId, $entity, $update, $value, $label, $style, $title, $placeholder) {

  // type: text, number, date, password... any simple input field..not selects or radio
  // size: default empty; options: small
  // class: extra css classes
  // id: css id
  // entityId: project, task or time record id
  // entity: project, task, timeRecord
  // update: database cel header name, ex: title

  if($type == 'text' || $type == 'url' ||  $type == 'date' || $type == 'number') {

    $step = '';
    if($type=="number") {
      $step = 'step="0.1"';
    }


    $input =
    '<label class="eip-holder eip-holder-'.$size.' '.$class.'" style="'.$style.'">
      <i class="fas fa-edit text-muted"></i>
      <i class="fas fa-spinner fa-spin"></i>
      <i class="fa fa-exclamation-triangle text-danger has-action" title="'. __('View browser console.log for errors','wphourly').'"></i>
      '.$label.'<input  data-toggle="tooltip" data-placement="top" title="'.$title.'" placeholder="'.$placeholder.'" data-entity-id="'.$entityId.'" data-entity="'.$entity.'"  data-update="'.$update.'" type="'.$type.'" '.$step.' id="'.$id.'" class="form-control  eip eip-'.$size.'" value="'. $value .'" style="'.$style.'">
    </label>';
  }
  if($type == 'url') {

    $step = '';
    if($type=="number") {
      $step = 'step="0.1"';
    }


    $input =
    '<label class="eip-holder eip-holder-'.$size.' '.$class.'" style="'.$style.'">
      <i class="fas fa-edit text-muted"></i>
      <i class="fas fa-spinner fa-spin"></i>
      <i class="fa fa-exclamation-triangle text-danger has-action" data-toggle="tooltip" data-placement="top" title="'.__('URL should contain the full path with http / https','wphourly').'" ></i>
      '.$label.'<input  data-toggle="tooltip" data-placement="top" title="'.$title.'" placeholder="'.$placeholder.'" data-entity-id="'.$entityId.'" data-entity="'.$entity.'"  data-update="'.$update.'" type="'.$type.'" '.$step.' id="'.$id.'" class="form-control  eip eip-'.$size.'" value="'. $value .'" style="'.$style.'">
    </label>';
  }


  if($type == 'textarea') {

    $height = '';
    if($size == 'small') {
      $height = "height: 150px!important;";
    } else {
      $height = 'height: 300px!important;';
    }

    if($value == '' ) {
      // $value = $placeholder;
      $value = '';
    }

    if($title != '') {
      $title = 'data-toggle="tooltip" data-placement="top" title="'.$title.'"';
    }

    $input = '
    <label class="eip-holder eip-holder-'.$size.' eip-holder-textarea  '.$class.'" style="'.$style.'">
      <i class="fas fa-edit text-muted"></i>
      <i class="fas fa-spinner fa-spin"></i>
      <i class="fa fa-exclamation-triangle text-danger has-action" title="'.__('View browser console.log for errors','wphourly').'"></i>
      '.$label.'
      <'.$type.' '.$title.' type="textarea" data-entity-id="'.$entityId.'" data-entity="'.$entity.'" data-update="'.$update.'" placeholder="'.$placeholder.'" class="form-control  eip eip-'.$size.' text-secondary" id="'.$id.'" style="'.$height.''.$style.'">'. $value .'</'.$type.'>
    </label>';
  }



  return $input;

}


function wphSearchForFollowers($project_id) {

    echo '<input class="form-control" data-project-id="'. $project_id .'" type="text" name="" id="search_followers" />';
}


function wphGetSelectDropdown($curren_selection, $type, $show_selection, $gravatar_size, $search_key, $entityId, $entity, $update, $is_ajax, $title, $extra_class)
{
    if($show_selection == false) {
        $show_selection = 'd-none';
    } else {
        $show_selection = 'ml-2';
    }

    if($type =='customer' || $type == 'employee') {
      $args = array(
          'role__in'   => $type,
          'meta_query'=>
           array(

              array(

                  'relation' => 'AND',

              array(
                  'key' => 'wph-user-status',
                  'value' => 'active',
                  'compare' => "="
              )

            )
         )
      );

      $results = get_users( $args );
      if ($type == 'employee' && esc_attr(get_option('wph_treat_admins_as_employees')) == 1) {
          // admins are considered active
          $admins = get_users(['role' => 'administrator']);
          array_push($results, ...$admins);
      }

      usort($results, function ($userA, $userB) {
          return $userA->display_name > $userB->display_name;
      });

      $current_selected_item_name = $curren_selection ? get_userdata($curren_selection)->display_name : '';
    }

    if($type =='projects') {
      $results = wphGetProjects('active');
      $current_selected_item_name = $curren_selection ? wphGetProject($curren_selection)->title : '';
    }

    $entityIdData = '';
    if($entityId != '') {
      $entityIdData = 'data-entity-id="'.$entityId.'"';
    }

    $entityData = '';
    if($entity != '') {
      $entityData = 'data-entity="'.$entity.'"';
    }

    $updateData = '';
    if($update != '') {
      $updateData = 'data-update="'.$update.'"';
    }

    if($is_ajax == true) {
      $isAjaxData = 'data-is-ajax="yes"';
    } else {
      $isAjaxData = 'data-is-ajax="no"';
    }

    if($title != '') {
      $title = 'data-toggle="tooltip" data-placement="top" title="'.$title.'"';
    }

    $select = '<div class="dropdown show wph-user-select p-1  sip-holder '.$extra_class.'">
    
      <i class="fas fa-edit text-muted d-none"></i>
      <i class="fas fa-spinner fa-spin"></i>
      <i class="fa fa-exclamation-triangle text-danger has-action" title="'.__('View browser console.log for errors','wphourly').'" ></i>
      <input '.$entityIdData.' '.$entityData.' '.$updateData.' '.$isAjaxData.' type="hidden" name="" class="wph-selected-user sip" id="wph-selected-'.$search_key.'" value="'.$curren_selection.'" />
      <a class="dropdown-toggle text-decoration-none" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" '.$title.' >';
            if($type =='customer' || $type == 'employee') {
              $select .= get_avatar($curren_selection, $gravatar_size, '', '', array( 'class' => array( 'wph-selected-user-avatar' ) ));
            }
            $select .= '<small class="'.$show_selection.' text-dark font-weight-bold">'.__('SELECT','wphourly').' '.strtoupper($type).': <span class="wph-selected-user-nicename">'.$current_selected_item_name.'</span></small>
      </a>

      <div class="has-shadow-hover dropdown-menu dropdown-menu-right p-1" aria-labelledby="dropdownMenuLink">
        <input type="search" data-search-target="'.$search_key.'" placeholder="'.__('search','wphourly').'" class="m-1 live-search-box wph-user-search" >
        <ul>';
        $counter = 0;
        foreach ($results as $result) {
            if ($counter++ <= 9 ) {
                $hide_more = "";
            } else {
                $hide_more = 'display: none;';
            }

            if ($type =='customer' || $type == 'employee') {
              $select .= '
              <li style="cursor: pointer; '.$hide_more.'" data-show-all-result="'.$search_key.'" data-search-result="'.$search_key.'" data-search-term="'.strtolower($result->display_name).'" class="searchable dropdown-item px-1 m-0 wph-update-user-select" data-select-user-id="'.$result->ID.'" data-select-user-nicename="'.$result->display_name.'" data-select-user-avatar="'.get_avatar_url( $result->ID).'" >
                  '.get_avatar( $result->ID, 30).' 
                  <small>'.esc_html( $result->display_name ) . (user_can($result->ID, 'administrator') ? ' (admin)' : '') .'</small>
              </li>';
            }
            if($type =='projects') {
              $select .= '
              <li style="cursor: pointer; '.$hide_more.'" data-show-all-result="'.$search_key.'" data-search-result="'.$search_key.'" data-search-term="'.strtolower($result->title).'" class="searchable dropdown-item px-1 m-0 wph-update-user-select" data-select-user-id="'.$result->id.'" data-select-user-nicename="'.$result->title.'" data-select-user-avatar="" >
                  <small>'.esc_html( $result->title ).'</small>
              </li>';
            }
        }


        $select .= '
        </ul>
      </div>
    </div>';
    return $select;
}

/*
//    ---        SUPER WP HEROES                                               
//    ---        COMMENT: USER PERMISSIONS
*/

function wphCurrentUserCanEditProject()
{
    if (current_user_can('administrator')) {
        return true;
    }

    if (current_user_can('customer')) {
        return false;
    }

    if (!current_user_can('employee')) {
        return false;
    }

    return (
        get_option('wph_allow_employees_to_edit_project') == 1 ||
        get_user_meta(get_current_user_id(), 'wph_allow_employees_to_edit_project', true) == 1
    );
}

function wphCurrentUserCanViewProject($projectId)
{
    if (current_user_can('administrator')) {
        return true;
    }

    if (current_user_can('customer')) {
        return wphCurrentUserIsClientOfProject($projectId);
    }

    if (!current_user_can('employee')) {
        return false;
    }

    if (wphCurrentEmployeeHasTaskInProject($projectId)) {
        return true;
    }

    if (metadata_exists('user', get_current_user_id(), 'wph_allow_employee_to_view_all_projects')) {
        return get_user_meta(get_current_user_id(), 'wph_allow_employee_to_view_all_projects', true) == 1;
    }

    return get_option('wph_allow_employees_to_view_all_projects') == 1;
}

function wphCurrentUserCanViewTask($taskId)
{
    if (current_user_can('administrator')) {
        return true;
    }

    if (current_user_can('customer')) {
        return wphCurrentUserIsClientOfTask($taskId);
    }

    if (!current_user_can('employee')) {
        return false;
    }

    if (wphCurrentUserIsTaskAssignee($taskId)) {
        return true;
    }

    if (metadata_exists('user', get_current_user_id(), 'wph_allow_employee_to_view_all_tasks')) {
        return get_user_meta(get_current_user_id(), 'wph_allow_employee_to_view_all_tasks', true) == 1;
    }

    return get_option('wph_allow_employees_to_view_all_tasks') == 1;
}

function wphCurrentUserIsClientOfProject($projectId)
{
    global $wpdb;

    $query = sprintf(
        "SELECT COUNT(*) FROM %swph_projects WHERE id = %d AND client_id = %d",
        $wpdb->prefix,
        $projectId,
        get_current_user_id()
    );

    return $wpdb->get_var($query) != 0;
}

function wphCurrentUserIsClientOftask($taskId)
{
    global $wpdb;

    $query = sprintf(
        "SELECT COUNT(*) FROM %swph_tasks WHERE id = %d AND client_id = %d",
        $wpdb->prefix,
        $taskId,
        get_current_user_id()
    );

    return $wpdb->get_var($query) != 0;
}

function wphCurrentUserIsTaskAssignee($taskId)
{
    global $wpdb;

    $query = sprintf(
        "SELECT COUNT(*) FROM %swph_tasks WHERE id = %d AND assignee_id = %d",
        $wpdb->prefix,
        $taskId,
        get_current_user_id()
    );

    return $wpdb->get_var($query) != 0;
}

function wphCurrentUserCanEditTask()
{
    if (current_user_can('administrator')) {
        return true;
    }

    if (current_user_can('customer')) {
        return false;
    }

    if (!current_user_can('employee')) {
        return false;
    }

    if (metadata_exists('user', get_current_user_id(), 'wph_allow_employees_to_edit_task')) {
        return get_user_meta(get_current_user_id(), 'wph_allow_employees_to_edit_task', true) == 1;
    }

    return get_option('wph_allow_employees_to_edit_task') == 1;
}

function wphCurrentUserCanAddManualTime()
{
    if (current_user_can('administrator')) {
        return true;
    }

    if (current_user_can('customer')) {
        return false;
    }

    if (!current_user_can('employee')) {
        return false;
    }

    if (metadata_exists('user', get_current_user_id(), 'wph_allow_employees_to_add_manual_time')) {
        return get_user_meta(get_current_user_id(), 'wph_allow_employees_to_add_manual_time', true) == 1;
    }

    return get_option('wph_allow_employees_to_add_manual_time') == 1;
}


function wphCurrentEmployeeHasTaskInProject($projectId)
{
    global $wpdb;

    $query = sprintf(
        "SELECT COUNT(*) FROM %swph_tasks WHERE assignee_id = %d AND project_id = %d",
        $wpdb->prefix,
        get_current_user_id(),
        $projectId
    );

    return $wpdb->get_var($query) != 0;
}

/*
//    ---        SUPER WP HEROES                                               
//    ---        COMMENT: MISC
*/

function wphPrintDocument($elementId)
{
    $wpHourlyUrl = esc_url(plugins_url('',  dirname(__FILE__)));
    wp_register_script('wph-plugin-url', false);
    wp_enqueue_script('wph-plugin-url' );
    wp_add_inline_script('wph-plugin-url', 'var wpHourlyPluginUrl = "' . $wpHourlyUrl . '"');
    echo '<a href="javascript: void(0);" id="print_this" class=" pull-right float-right"><i class="fa fa-print"></i> ' . __('Print this document', 'wphourly') . '</a>';
    wp_enqueue_script('printme', $wpHourlyUrl.'/assets/js/print.js', ['jquery', 'wph-plugin-url']);
    wp_register_script('wph-printme-init', false, ['printme']);
    wp_enqueue_script('wph-printme-init' );
    wp_add_inline_script(
        'wph-printme-init',
        'jQuery("#print_this").click( function(){ jQuery( "#' . $elementId . '" ).print(); return false;});'
    );
}
