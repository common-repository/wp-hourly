<?php

if ( ! defined( 'WP_HOURLY_PATH' ) ) exit; // Exit if accessed directly


$wphProjectId = filter_var( $_GET['project'] , FILTER_VALIDATE_INT );

if ($wphProjectId) {

    $userCanEditProject = wphCurrentUserCanEditProject();
    $userCanEditTask = wphCurrentUserCanEditTask();
    
    $wph_project = wphGetProject($wphProjectId);

    $estimated_hours = $wph_project->estimated_hours;

    if ($estimated_hours == '') {
        $estimated_hours = __('NOT SET','wphourly');
    }

    $project_completion_rate = wphGetProjectCompletionRate($wph_project->id);

    //echo '<pre>'.print_r($wph_project, true).'</pre>';

    // check active / archive status
    $is_active_check = '';
    if($wph_project->status == 'active' ) {
        $is_active_check = 'checked';
    } else {
        $is_active_check = '';
    }

    // check billable status
    $is_billable_check = '';
    if($wph_project->is_billable == 1 ) {
        $is_billable_check = 'checked';
    } else {
        $is_billable_check = '';
    }


    // if(function_exists('wph_get_project_followers')) {
    
    //     $followers = wph_get_project_followers($wph_project->id);
    // }

    // if(function_exists('wph_get_project_unfollowers')) {
    
    //     $unfollowers = wph_get_project_unfollowers($wph_project->id);
    // }


    $project_tracked_hours = wphGetProjectHours($wph_project->id, WPH_BILLABLE);

    $project_deadline = $wph_project->deadline;
    if($project_deadline == '') {
        $project_deadline = __('NOT SET','wphourly');
    }
    $hide_hourly_rate = wphEditInPlace(
                                $type="number",
                                $size="small",
                                $class="mt-2 mb-0",
                                $id="wph-editable-project-rate",
                                $entityId=$wph_project->id,
                                $entity="project",
                                $update="hourlyRate",
                                $value=$wph_project->hourlyRate,
                                $label= __('Rate: ','wphourly'),
                                $style="",
                                $title = __('Override Hourly Rate (current source: ','wphourly') . wphGetProjectHourlyRateSource($wph_project->id) .')',
                                $placeholder = wphGetProjectHourlyRate($wph_project->id)
                            )    ;
    if(current_user_can('employee')){
        $hide_hourly_rate = '';
    }

    echo
    '<div class="container-fluid pl-0 pr-0">';

    echo '
      <div class="row">';

    echo '
            <div class="col-lg-5 mb-3">
                <div class="row">
                    '. ($userCanEditProject ? '<div class="col-md-3">
                        '.wphSwitchInput(
                            $entity = 'project',
                            $entityId = $wph_project->id,
                            $update = 'status',
                            $value = $wph_project->status,
                            $onLabel = __('Active','wphourly'),
                            $offLabel = __('Archived','wphourly'),
                            $id = 'wph-edit-project-status',
                            $class = '',
                            $style = "width: 100%",
                            $title = __("Edit project status", 'wphourly'),
                            $is_ajax = "yes"
                        ).'</div>' : '').'
                    <div class="col-md-9 pl-0">
                        '.($userCanEditProject ? wphEditInPlace(
                            $type="text",
                            $size="",
                            $class="",
                            $id="wph-editable-project-title",
                            $entityId=$wph_project->id,
                            $entity="project",
                            $update="title",
                            $value=$wph_project->title,
                            $label="",
                            $style="width:100%;text-transform: uppercase;",
                            $title = __('Edit project title', 'wphourly'),
                            $placeholder = __('Project title', 'wphourly')
                        ) : '<h5 style="margin-left: 16px">'.$wph_project->title.'</h5>').'
                    </div>
                </div>

                 <div class="progress bg-warning">
                    <div class="progress-bar bg-success" role="progressbar" style="width: '.$project_completion_rate.'%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="col-12" id="col-followers">';
                        
                do_action('insert-follower-thumbnails', $wph_project->id);

                echo '</div>

            </div>


             <div class="col-lg-7">

                 <div class="row">
                     <div class="col-lg-12">

                        <div class="row">    
                            '. ($userCanEditProject
                                ? '<div class="col-md-4">
                                        '.wphGetSelectDropdown(
                                            $curren_selection = $wph_project->client_id,
                                            $type = 'customer',
                                            $show_selection = true,
                                            $gravatar_size = 30,
                                            $search_key = 'project-owner',
                                            $entityId = $wph_project->id,
                                            $entity = 'project',
                                            $update = 'client_id',
                                            $is_ajax = true,
                                            $title = __('Select project client', 'wphourly'),
                                            $extra_class = ''
                                        ).'
                                    </div>
                                    <div class="col-md-3">    
                                        '.wphSwitchInput(
                                            $entity = 'project',
                                            $entityId = $wph_project->id,
                                            $update = 'is_billable',
                                            $value = $wph_project->is_billable,
                                            $onLabel = __('Billable','wphourly'),
                                            $offLabel = __('Non Billable','wphourly'),
                                            $id = 'wph-edit-project-billable-status',
                                            $class = '',
                                            $style = "width: 100%",
                                            $title = __("Is project billable?", 'wphourly'),
                                            $is_ajax = "yes"
                                        ).'
                                    </div>'
                                : '<div class="col-md-7">'.wphGetUserSelectDropdownNonEditable($wph_project->client_id, 30, 'customer').'</div>').'
                              <div class="col-lg-5">
                                <div class="input-group">
                                  <input type="text" data-search-target="searchable-tasks" class="form-control live-search-box has-shadow-hover" aria-label="Search" aria-describedby="inputGroup-sizing-default" placeholder="'.__('Search tasks','wphourly').'">
                                </div>
                            </div>

                         </div>

                 <div class="row no-gutters">
                    '.($userCanEditProject
                        ? '<div class="col-lg-2">
                             '. $hide_hourly_rate
                              .'
                            </div>
                            <div class="col-lg-3">
                                <p class="mt-2 mb-0" title="'.__('Created at ','wphourly').''.$wph_project->created_at.'">'.__('Created at: ','wphourly').'<b>'. substr($wph_project->created_at, 0, 10) .'</b></p>     
                            </div>
                            <div class="col-lg-3 pb-2" title="'.$project_tracked_hours.' tracked hours vs. '.$estimated_hours.' estimated hours">
                                '.wphEditInPlace(
                                    $type="number",
                                    $size="small",
                                    $class="mt-2",
                                    $id="wph-editable-project-estimates",
                                    $entityId=$wph_project->id,
                                    $entity="project",
                                    $update="estimated_hours",
                                    $value=$estimated_hours,
                                    $label="<b>".__('Tracked h: ','wphourly')."".$project_tracked_hours."</b> / ",
                                    $style="",
                                    $title = __('Project estimated hours', 'wphourly'),
                                    $placeholder = __('E.H.','wphourly')
                                ).'

                            </div>
                            <div class="col-lg-4">
                                <div class="row text-danger">
                                    <div class="col-md-4"><p class="mt-2 ml-2">DEADLINE: </p></div>
                                    <div class="col-md-8">
                                    '.wphEditInPlace(
                                        $type="date",
                                        $size="small",
                                        $class="mt-2 text-danger",
                                        $id="wph-editable-project-deadline",
                                        $entityId=$wph_project->id,
                                        $entity="project",
                                        $update="deadline",
                                        $value=$project_deadline,
                                        $label="",
                                        $style="font-weight: bold; width: 100%",
                                        $title = __('Edit project deadline', 'wphourly'),
                                        $placeholder = __('P.Dl.','wphorly')
                                    ).'
                                    </div>
                                </div>
                            </div>'
                        : '
                            <div class="col-lg-4 pb-2">
                                <p class="mt-2 mb-0"><strong>'. __('Created at:', 'wphourly') .'</strong> '.$wph_project->created_at.'</p>
                            </div>
                            <div class="col-lg-4">
                                <p class="mt-2 mb-0"><strong>'. __('Tracked hours:', 'wphourly') .'</strong> '.$project_tracked_hours.'/'.$estimated_hours.'</p>
                            </div>
                            <div class="col-lg-4">
                                <p class="mt-2 mb-0"><strong>' . __('Deadline:', 'wphourly') . '</strong> '.$project_deadline.'</p>
                            </div>
                        '
                    ).'
                 </div>

             </div>

        </div>
        </div>
    </div>';


    echo '
    <div class="row">';

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: PROJECT DESCRIPTION
    */

    $external_project_url = $wph_project->external_url;


    if($external_project_url != '') {
        $parse = parse_url($external_project_url);
        $external_project_favicon = "https://www.google.com/s2/favicons?sz=32&domain={$parse['host']}";
    }

    echo '
        <div class="col-md-3">
            <div id="wph-project-details"  class="card p-3 pt-4 task-panel-list-wrapper has-shadow-hover">
                <h5><b>'. __('PROJECT DETAILS', 'wphourly') . '</b></h5>
                <div id="wph-extrnal-project-url" class="mb-3">';
    if($external_project_url != '') {
        echo '<button class="btn btn-xs btn-block text-left"><a href="'.$external_project_url.'" target="_blank" class="text-dark text-decoration-none" ><img src="'.$external_project_favicon.'"> <small>'.__('View on ','wphourly').''.$parse['host'].'</small></a>'.($userCanEditProject ? '<a class="text-muted mt-1 float-right" data-toggle="collapse" href="#edit-project-external-link" aria-expanded="false" aria-controls="collapseExample"><i class="fas fa-cog"></i></a>' : '').'</button>';
    } else {
        echo '<button class="btn btn-xs btn-block text-left"><small>'. __('No external link set', 'wphourly') .'</small>'.($userCanEditProject ? '<a class="text-muted mt-1 float-right" data-toggle="collapse" href="#edit-project-external-link" aria-expanded="false" aria-controls="collapseExample"><i class="fas fa-cog"></i></a>' : '').'</button>';
    }

    echo ($userCanEditProject
        ? '<div class="collapse" id="edit-project-external-link">
            <div class="pt-3 pb-3">
                '.wphEditInPlace(
                    $type="url",
                    $size="small",
                    $class="",
                    $id="wph-editable-project-external-url",
                    $entityId=$wph_project->id,
                    $entity="project",
                    $update="external_url",
                    $value=$external_project_url,
                    $label="",
                    $style="width:100%",
                    $title = __('Edit external project URL', 'wphourly'),
                    $placeholder = __('ex.: https://asana.com','wphourly')
                ).'
            </div>
        </div>'
        : '');

    echo '
                </div> <!-- end external project -->
                <div id="wph-project-description-holder" class="text-justify">';
    if($wph_project->description == '') {
        echo '<p class="alert alert-warning">'. __('No project description just yet. Add one :)', 'wphourly') .'</p>';
    }

    echo ($userCanEditProject
        ? wphEditInPlace(
            $type="textarea",
            $size="",
            $class="",
            $id="wph-editable-project-description",
            $entityId=$wph_project->id,
            $entity="project",
            $update="description",
            $value=apply_filters('wph-project-description', $wph_project->description),
            $label="",
            $style="width:100%",
            $title = __('Edit project description', 'wphourly'),
            $placeholder = __('Click here to edit project description', 'wphourly')
        )
        :   '<div class="text-secondary form-control" style="height: 300px; overflow: auto; font-size: 13px; border-width: 0">'
                .nl2br(apply_filters('wph-project-description', $wph_project->description)).
            '</div>'
    );
    echo '</div>';

    if ($userCanEditProject) {
        echo '
            <div id="wph-project-delete" class="justify-text"' . ($wph_project->status == 'active' ? ' style="display:none"' : '') . '>
                <h6>Delete project (<i class="fa fa-sad-tear"></i> are you sure?...)</h6>
                <p class="mt-0 alert alert-danger">'. __('Deleting a project will permanently delete all the associated tasks and time records that follow along with it. If you had your money collected for those time records, then at least money wise, you need not worry, however, your reports will no longer show that time, if you or your clients will even need to consult the archives.', 'wphourly') . '</p>
                <form>
                    <div class="form-row">
                        <div class="form-group col-12">
                        <input data-toggle="tooltip" data-placement="top" title="" type="text" class="form-control-plaintext" id="wph-project-delete-approval" placeholder="'. __('Type DELETE PROJECT to approve', 'wphourly') .'" required="" data-original-title="'. __('Type the word DELETE PROJECT to approve deletion', 'wphourly') . '">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12">
                        <button type="submit" disabled class="btn btn-danger btn-sm btn-block" data-id="' . $wph_project->id . '" id="delete-project-button"><small>' . __('DELETE PROJECT', 'wphourly') .'</small></button>
                        </div>
                    </div>
                </form>
            </div>
        ';
    }

    echo '
            </div>
        </div>';


    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: GET AVAILABLE TASK STATUSES
    */
    $task_statuses = wphGetTaskStatuses();

    foreach( $task_statuses as $task_status ) {

        $task_column_color = '';

        if($task_status->status == 'backlog') {
            $task_column_color = 'text-warning';
        } elseif($task_status->status == 'todo') {
            $task_column_color = 'text-info';
        } elseif($task_status->status == 'done') {
            $task_column_color = 'text-success';
        } else {
            $task_column_color = 'text-secondary';
        }

        /*
        //    ---        SUPER WP HEROES
        //    ---        COMMENT: PRINT TASK STATUS COLUMNS AND TASKS
        */

        echo '
              <div class="col-md-3">
                  <div id="wph-tasks-'.$task_status->status.'"  class="card p-3 pt-4 task-panel-list-wrapper has-shadow-hover">
                      <h5>
                          <i class="fa fa-circle '.$task_column_color.'" ></i> <b>'.$task_status->description.'</b>';

        if ($task_status->status != 'done' && $userCanEditTask) {
            echo '<a class="float-right text-muted add-task has-action" '. ($wph_project->status == 'archived' ? 'style="display: none"' : '') .' data-project-id="'.$wphProjectId.'" data-status-id="'.$task_status->id.'" title="'.__('Add task','wphourly').'"><i class="fa fa-plus"></i></a>';
        }
        echo '
                      </h5>


                    <ul class="task-status-'.$task_status->status.' sortable ui-sortable m-0 p-0" data-update-task-status="'.$task_status->id.'" data-status-id="'.$task_status->id.'">';



        $project_tasks = wphGetProjectTasks($wphProjectId, $task_status->id);

        
        $shownTasks = 0;
        foreach( $project_tasks as $index => $task) {
            if (!wphCurrentUserCanViewTask($task->id)) {
                continue;
            }

            $shownTasks++;

            $task_estimated_h = "";
            if($task->estimated_hours == '') {
                $task_estimated_h = $task->estimated_hours;
            } else {
                $task_estimated_h = "";
            }

            $task_deadline = "";
            if($task->deadline == '') {
                $task_deadline = $task->deadline;
            } else {
                $task_deadline = "";
            }

            $hide_more = $shownTasks >= 20 ? 'style="display: none;"' : "";

            $external_task_url = $task->external_url;
            $external_task_favicon = '';
            if($external_task_url != '') {
                $parse = parse_url($external_task_url);
                $external_task_favicon = "https://www.google.com/s2/favicons?sz=16&domain={$parse['host']}";
            }

            $no_drag_class = '';
            if(current_user_can('customer')){
                $no_drag_class = 'ui-state-disabled wph-no-drag';
            }

            echo '
                            <li '.$hide_more.' data-task-count="'.$index.'" data-search-result="searchable-tasks" data-search-term="'.strtolower($task->title).'" data-task-id="'.$task->id.'" title="'.$task->title.'" class="searchable task-panel-task-item ui-sortable-handle task-item-status-'.$task_status->status.' '.$no_drag_class.'" data-show-all-result="task-item-status-'.$task_status->status.'" data-entity-id="'.$task->id.'" data-entity="task" data-update="status_id">
                                <div class="task-item-details">
                                    <div class="task-item-details-title">
                                        <div class="task-item-details-row">
                                            <div class="task-item-details-icon">
                                                <!-- <input class="facheck" id="check-25" type="checkbox">
                                                <label for="check-25" class="fas fa-check-square user-icon-comment py-2"></label> -->
                                                <a href="'.$external_task_url.'" target="_blank" class="has-action"><img class="" src="'.$external_task_favicon.'"></a>
                                            </div>
                                            <div class="task-item-details-detail">
                                                <h6 class="">
                                                    <a class="updates-tasks-title text-dark load-task task-title-container task-title-ellipsis" style="" data-task="'.$task->id.'" data-project="'.$wphProjectId.'">
                                                        '.mb_strimwidth($task->title, 0, 28, "...").'
                                                    </a>
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="task-item-details-row">';
                                            $task_hours = (float) wphGetTaskHours($task->id);
                                            $estimatedHours = '';
                                            if ($task->estimated_hours) {
                                                $estimatedHours = '<small class="est-hours est-task-hours">('.__('est.: ','wphourly').''.$task->estimated_hours.'h)</small>';
                                            }
                                            
                                            echo '<div class="task-item-details-icon">
                                                <i class="fa fa-clock '.wphGetClockStyle($task_hours, (float) $task->estimated_hours).'"></i>
                                            </div>
                                            <div class="task-item-details-detail pt-0">
                                                <p title="'.__('Number of tracked hours vs. estimated hours','wphourly').'">
                                                    <small class="est-hours rt-hours">'.__('R.T.: ','wphourly').''.$task_hours.'h</small>' . $estimatedHours . '
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-item-details-timer">
                                        <div class="task-item-details-start-stop">';
                                            echo '<div class="task-item-details-member">
                                                <div class="wph-usr-ava-wrp">'
                                                    .($userCanEditTask ? wphGetSelectDropdown(
                                                    $curren_selection = $task->assignee_id,
                                                    $type = 'employee',
                                                    $show_selection = false,
                                                    $gravatar_size = 30,
                                                    $search_key = 'task-'.$task->id.'-assignee',
                                                    $entityId = $task->id,
                                                    $entity = 'task',
                                                    $update = 'assignee_id',
                                                    $is_ajax = true,
                                                    $title = __('Change task assignee', 'wphourly'),
                                                    $extra_class = ''
                                                ) : get_avatar($task->assignee_id, 30, '', '', ['class' => ['wph-selected-user-avatar']])).'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>';
        } // end task
        echo ' 
                    <li class="ui-sortable-placeholder task-panel-task-item task-item-status-'.$task_status->status.' ui-sortable-handle" style="visibility:visible; height: 1px"></li> 
                </ul>';
        if(count($project_tasks) > 20) {
            echo '
                    <button class="wph-show-all btn btn-sm btn-secondary" data-show-all-target="task-item-status-'.$task_status->status.'">'. __('Show / Hide All', 'wphourly') .'</button>';
        }
        echo '    
            </div>
        </div>';
    } // end statuses

    echo '
    </div>';


    // Followers modal

    do_action('insert-follower-modal', $wph_project->id);

    ///


    echo '
    <div id="wph-task-holder" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header pb-0 border-bottom-0">
                    <h5 class="modal-title" id="add-task-modal-title">'. __('Add task', 'wphourly') .'</h5>
                    <ul class="nav nav-tabs w-100" id="task-tabs" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link text-secondary active" id="task-details-tab" data-toggle="tab" href="#task-details-content" role="tab" aria-controls="task-details-content" aria-selected="true"><b>'.__('Task Details','wphourly').'</b></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link text-secondary" id="task-time-records-tab" data-toggle="tab" data-is-ajax="yes" data-load-url="'.WP_HOURLY_URI.'templates/wph-time-record-tpl.php" data-parameters="" data-target="#task-time-records-content" href="#task-time-records-content" role="tab" aria-controls="task-time-records-content" aria-selected="false"><b>'. __('Time Records', 'wphourly') .'</b></a>
                      </li>
                      '.(wphCurrentUserCanAddManualTime() ? '<li class="nav-item">
                        <a class="nav-link text-secondary" id="task-manual-time-tab" data-toggle="tab" href="#task-manual-time-content" role="tab" aria-controls="task-manual-time-content" aria-selected="false"><b>'. __('Add Manual Time', 'wphourly') .'</b></a>
                      </li>' : '').'
                      '.($userCanEditTask ? '<li class="nav-item">
                        <a class="nav-link text-secondary" id="task-settings-tab" data-toggle="tab" href="#task-settings-content" role="tab" aria-controls="task-settings-content" aria-selected="false"><b>'.__('Settings','wphourly').'</b></a>
                      </li>' : '');

//                      $args = array(
//                        'task_id' => $wph_task->id,
//                      );
//                      do_action( 'wph_add_task_modal_tab', $args);

                    echo '
                    </ul>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body" id="task-details"></div>
                <div class="modal-footer d-none"></div>
            </div>
        </div>
    </div>';


    echo '
    </div> <!-- container -->';



    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: print an error if trying to access badly
    */

} else {
    echo '<div class="alert alert-danger" role="alert">'. __('Could not retrieve project data!', 'wphourly') .'</div>';
    exit;
}

