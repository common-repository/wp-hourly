<?php

$taskId = filter_input(INPUT_GET, 'task', FILTER_VALIDATE_INT);
$clientId = wphGetTaskClientId($taskId);
if (!$taskId || !$clientId) {
    echo 'There was an error!';

    return;
}
$userCanEditTask = wphCurrentUserCanEditTask();

$wph_task = wphGetTask($taskId);

$external_task_url = $wph_task->external_url;
if($external_task_url != '') {
    $parse = parse_url($external_task_url);
    $external_task_favicon = "https://www.google.com/s2/favicons?sz=64&domain={$parse['host']}";
}



    echo '
    <div class="tab-content">';

      echo '
      <div class="tab-pane active" id="task-details-content" role="tabpanel" aria-labelledby="task-details-content">
              <div class="card mt-0 p-2" style="width: 100%;max-width: 100%;">
                  <div>

                       <div class="form-row h-100">
                          <div class="form-group my-auto col-md-1 pr-0">
                            '.($userCanEditTask ? wphGetSelectDropdown(
                                $curren_selection = $wph_task->assignee_id,
                                $type = 'employee',
                                $show_selection = false,
                                $gravatar_size = 47,
                                $search_key = 'task-assignee',
                                $entityId = $wph_task->id,
                                $entity = 'task',
                                $update = 'assignee_id',
                                $is_ajax = true,
                                $title = __('Change task assignee','wphourly'),
                                $extra_class = ''
                            ) : get_avatar($wph_task->assignee_id, 30, '', '', ['class' => ['wph-selected-user-avatar']])).'
                          </div>
                          <div class="form-group my-auto col-md-9">
                              '.($userCanEditTask ? wphEditInPlace(
                                      $type="text",
                                      $size="",
                                      $class="form-control-lg",
                                      $id="form-task-title",
                                      $entityId=$wph_task->id,
                                      $entity="task",
                                      $update="title",
                                      $value=$wph_task->title,
                                      $label="",
                                      $style="width:100%;text-transform: uppercase;",
                                    $title = __('Edit task title', 'wphourly'),
                                    $placeholder = __('Task title','wphourly')
                                  ) : '<h5>'.$wph_task->title.'</h5>').'
                        </div>
                        <div class="col-md-1 my-auto text-center">';
                        if($external_task_url != '') {
                            echo '<a data-toggle="tooltip" data-placement="top" title="'.__('View external task link','wphourly').'"  href="'.$external_task_url.'" target="_blank" class="text-muted"><img class="mt-1" style="height: 35px" src="'.$external_task_favicon.'" /></a>';
                        } else {
                            echo '<span data-toggle="tooltip" data-placement="top" title="'.__('No external link set','wphourly').'"  target="_blank" class="text-muted"><i class="fa fa-2x fa-link"></i></span>';
                        }
                        echo '</div>
                        <div class="col-md-1 my-auto text-center">
                            '.($userCanEditTask ? '<a class="text-muted" data-toggle="collapse" href="#edit-task-details" aria-expanded="false" aria-controls="collapseExample"><i class="fas fa-2x fa-cog"></i></a>' : '').'
                        </div>
                      </div>


                  
                      <div class="collapse mt-3" id="edit-task-details" style="">
                      <div class="form-row">
                            '.($userCanEditTask
                                ? '<div class="form-group col-md-4">
                                        '.wphEditInPlace(
                                            $type="url",
                                            $size="small",
                                            $class="",
                                            $id="form-task-ex-link",
                                            $entityId=$wph_task->id,
                                            $entity="task",
                                            $update="external_url",
                                            $external_task_url,
                                            $label="",
                                            $style="width:100%",
                                            $title = __('Edit external task URL', 'wphourly'),
                                            $placeholder = __('External task URL', 'wphourly')
                                        ).'
                                    </div>

                                    <div class="form-group col-md-2">
                                            '.wphSwitchInput(
                                                $entity = 'task',
                                                $entityId = $wph_task->id,
                                                $update = 'is_billable',
                                                $value = $wph_task->is_billable,
                                                $onLabel = __('Billable','wphourly'),
                                                $offLabel = __('Non Billable','wphourly'),
                                                $id = 'task-billable-status',
                                                $class = 'mb-0',
                                                $style = "width: 100%",
                                                $title = __('Edit task billing status','wphourly'),
                                                $is_ajax = "yes"
                                            ).'
                                    </div>

                                    <div class="form-group col-md-2">
                                        '.wphEditInPlace(
                                            $type="number",
                                            $size="small",
                                            $class="",
                                            $id="form-task-hourly-rate",
                                            $entityId=$wph_task->id,
                                            $entity="task",
                                            $update="hourlyRate",
                                            $value = $wph_task->hourlyRate,
                                            $label="",
                                            $style="width:100%",
                                            $title = __('Override Hourly Rate (current source: ','wphourly'). wphGetTaskHourlyRateSource($wph_task->id) .')',
                                            $placeholder = wphGetTaskHourlyRate($wph_task->id)
                                        ).'
                                    </div>
                                    <div class="form-group col-md-2">
                                            '.wphEditInPlace(
                                                $type="number",
                                                $size="small",
                                                $class="",
                                                $id="form-task-estimated-hours",
                                                $entityId=$wph_task->id,
                                                $entity="task",
                                                $update="estimated_hours",
                                                $value=$wph_task->estimated_hours,
                                                $label="",
                                                $style="width:100%;",
                                                $title = __('Task estimated hours', 'wphourly'),
                                                $placeholder = __('ex. 100','wphourly')
                                            ).'
                                        </div>
                                    <div class="form-group col-md-2">
                                            '.wphEditInPlace(
                                                $type="date",
                                                $size="small",
                                                $class="",
                                                $id="form-task-deadline",
                                                $entityId=$wph_task->id,
                                                $entity="task",
                                                $update="deadline",
                                                $value= substr($wph_task->deadline,0, 10),
                                                $label="",
                                                $style="width:100%;",
                                                $title = __('Task deadline date ','wphourly').$wph_task->deadline,
                                                $placeholder = ''
                                            ).'
                                    </div>'
                                : ''
                            ).'
                      </div>
                    </div>

                      <div class="form-group">
                        ';

                        if ($userCanEditTask) {
                            echo wphEditInPlace(
                                $type="textarea",
                                $size="",
                                $class="mt-3",
                                $id="form-task-description",
                                $entityId=$wph_task->id,
                                $entity="task",
                                $update="description",
                                $value=apply_filters('wph-task-description', $wph_task->description),
                                $label="",
                                $style="",
                                $title = __('Edit task description', 'wphourly'),
                                $placeholder = __('Click here to edit the task description', 'wphourly')
                            );
                        } else {
                            echo '<div class="text-secondary form-control" style="height: 300px; overflow: auto; font-size: 13px; border-width: 0">'
                                .nl2br(apply_filters('wph-task-description', $wph_task->description)).
                            '</div>';
                        }

                      echo '</div>';
                      $args = array(
                        'task_id' => $wph_task->id,
                        'assignee_id' => $wph_task->assignee_id
                      );
                      do_action( 'wph_after_task_description_form', $args);
            echo '          
            </div>
        </div> <!-- end card -->
      </div> <!-- end task details card -->';

      echo '<div class="tab-pane" id="task-time-records-content" role="tabpanel" aria-labelledby="task-time-records-content">';

      if (current_user_can('administrator')) {
            echo '<div class="card mt-0 p-2" style="width: 100%;max-width: 100%;">
                <div class="row h-100">
                    <div class="col-md-12">
                        <label class="checkbox-inline" data-toggle="tooltip" data-placement="top" title="'.__('Full screen title','wphourly').'">
                            <input type="checkbox" id="select-all-time-records" value=""><small>'. __('Select all time records at once', 'wphourly') .'</small>
                        </label>
                        <select id="tr-selection-action">
                            <option value="" disabled selected>'. __('Choose an action', 'wphourly') .'</option>
                            <option value="paid-in">'. __('Mark paid in', 'wphourly') .'</option>
                            <option value="paid-out">'. __('Mark paid out', 'wphourly') .'</option>
                            <option value="unpaid-in">'. __('Mark unpaid in', 'wphourly') .'</option>
                            <option value="unpaid-out">'. __('Mark unpaid out', 'wphourly') .'</option>
                            <option value="delete">'. __('Delete', 'wphourly') .'</option>
                        </select>
                        <button type="submit" id="tr-selection-go" disabled class="btn btn-primary btn-sm"><small>'.__('GO','wphourly').'</small></button>
                    </div>
                    <div class="col-md-3 my-auto" style="display:none;">
                        <select>
                            <option>'. __('Assign to another task', 'wphourly') .'</option>
                        </select>
                    </div>
                    <div class="col-md-2 my-auto" style="display:none;">
                    <label class="switch  mb-0" style="width: 100%" data-toggle="tooltip" data-placement="top" title="'. __('Mark time records as paid in by your client', 'wphourly') .'">
                        <input class="switch-input" type="checkbox" name="is_paid" value="1">
                        <span class="switch-label" data-on="Paid In" data-off="'. __('Not Paid In', 'wphourly') .'"></span> 
                        <span class="switch-handle"></span> 
                    </label>
                    </div>
                    <div class="col-md-3 my-auto" style="display:none;">
                    <label class="switch  mb-0" style="width: 100%" data-toggle="tooltip" data-placement="top" title="'. __('Mark time records as paid out to your employees', 'wphourly') .'">
                        <input class="switch-input" type="checkbox" name="is_paid_out" value="1">
                        <span class="switch-label" data-on="Paid Out" data-off="'. __('Not Paid Out', 'wphourly') .'"></span> 
                        <span class="switch-handle"></span> 
                    </label>
                    </div>
                    <div class="col-md-4 my-auto" style="display:none;">
                            <form class="form-inline">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                    <input data-toggle="tooltip" data-placement="top" title="'. __('Type the word DELETE to approve deletion of selected time records', 'wphourly') . '" type="text" class="form-control-plaintext" id="wph-tr-delete-approval" placeholder="'. __('Type DELETE to approve', 'wphourly') . '" required>
                                  </div>
                                  <div class="form-group col-md-6">
                                    <button type="submit" class="btn btn-danger btn-block btn-sm"><small>DELETE</small></button>
                                  </div>
                              </div>

                          </form>
                    </div>

                </div>
            </div>';
        }


        echo '<div class="row" id="wph-time-records">';

              $time_records = wphGetTaskTimeRecords($taskId);
              //echo '<pre>'.print_r($time_records, true).'</pre>';
              $date = '';

              // group time records by hour
              $groupedTrs = [];
              foreach ($time_records as $tr) {
                  $day = date('Y-m-d', $tr->timestamp);
                  if (!isset($groupedTrs[$day])) {
                      $groupedTrs[$day] = [
                          'timestamp' => $tr->timestamp,
                          'byHour' => [],
                      ];
                  }

                  // ex: 11 AM
                  $hour = date('g a', $tr->timestamp);
                  if (!isset($groupedTrs[$day]['byHour'][$hour])) {
                      $groupedTrs[$day]['byHour'][$hour] = [];
                  }

                  $groupedTrs[$day]['byHour'][$hour][] = $tr;
              }

            $hourlyBreakdown = '';
            $hourIndex = 0;
            foreach ($groupedTrs as $day => $dayTrs) {
                echo '<div class="col-md-12"><h5 class="mt-5"><small><b>'.$day.'</b></small></h5><hr /></div>';
                foreach ($dayTrs['byHour'] as $hour => $hourTrs) {
                    $tr = $hourTrs[0];
                    $is_paid_in = $tr->is_paid;
                    if($is_paid_in == 1) {
                        $is_paid_in = 'bg-success';
                    } else {
                        $is_paid_in = 'bg-danger';
                    }

                    $is_paid_out = $tr->is_paid_out;
                    if($is_paid_out == 1) {
                        $is_paid_out = 'bg-success';
                    } else {
                        $is_paid_out = 'bg-danger';
                    }

                    $tr_type = $tr->tr_type;

                    if($tr_type == 'manual') {
                        $tr_type_class = 'wph-time-record-manual-time bg-warning id';
                        // todo: import into the plugin
                        $tr_screenshot = 'https://superwpheroes.io/wp-content/plugins/wp-hourly/assets/img/app-icon.png';
                        $tr_manual_time_badge = '<span class="badge badge-info" style="display: none;" data-toggle="tooltip" data-placement="top" title="MANUAL TIME">M.T.</span>';
                    } else {
                        $tr_type_class = 'wph-time-record-tracker-time bg-light';
                        if (!function_exists('SWPH\WpHourly\Tracker\timeRecordScreenshot')) {
                            $tr_screenshot = WP_HOURLY_URI.'assets/img/screenshot-expired.png';
                        } else {
                            $tr_screenshot = SWPH\WpHourly\Tracker\timeRecordScreenshot($tr->id);
                        }
                        $tr_manual_time_badge = '';
                    }

                    /*
                    //    ---        SUPER WP HEROES
                    //    ---        COMMENT: group TR by date
                    */

                    $tr_date = substr($tr->created_at, 0, 10);

                    // current becomes previous
                    $date = $tr_date;

                    $avatar_url = get_avatar_url($tr->assignee_id);

                    $assignee = get_userdata($tr->assignee_id);

                    $hourIndex++;
                    echo '
                        <div class="col-md-3 hourly-breakdown-trigger" data-index="' . $hourIndex . '">
                            <div class="card wph-time-record-holder  '.$tr_type_class.' p-0" id="tr-id-'.$tr->id.'">
                                <div class="wph-time-record-thumbnail" ' . ($tr_type == 'manual' ? 'style="background-image: url(' . $tr_screenshot . ')"' : 'data-bg-image="' . $tr_screenshot . '"') . '>
                                    ' . ($tr_type == 'manual' ? '' : '<div class="time-record-holder">
                                        <div class="text-center bg-light text-muted">
                                            <i class="fa fa-spinner fa-spin fa-4x"></i>
                                        </div>
                                    </div>') . '
                                    <div class="ml-1 paid-status">
                                        <span class="badge bg-info text-white trs-hour-group" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title="" data-original-title="'. __('Click for details', 'wphourly') .'"><i class="fa fa-link"></i> ' . (count($hourTrs) > 1 ? '+ ' . (count($hourTrs) - 1) . ' '.__('more','wphourly').'' : __('1 record','wphourly')) . '</span>
                                        <span class="badge bg-danger text-white" style="display: none;" data-toggle="tooltip" data-placement="top" title="" data-original-title="'. __('Time record is Paid In by your client', 'wphourly') .'">'.__('P.I.','wphourly').'</span>
                                        <span class="badge bg-success text-white" style="display: none;" data-toggle="tooltip" data-placement="top" title="" data-original-title="'. __('Time record is not Paid Out to your employee', 'wphourly') .'">'.__('P.O.','wphourly').'</span>
                                        '.$tr_manual_time_badge;
//                                    if ($tr_type == 'tracker' && strpos($tr_screenshot, 'screenshot-expired.png') === false) {
//                                        echo '
//                                            <a href="'.$tr_screenshot.'" target="_blank"><span class="badge bg-light" data-toggle="tooltip" data-placement="top" title="View Screenshot" data-original-title="View Screenshot"><i class="fa fa-link"></i></span></a>
//                                        ';
//                                    }
                                    echo '
                                    </div>
                                    <span data-toggle="tooltip" data-placement="top" data-original-title="'. __('There is at least one time record selected for this hour', 'wphourly').'" data-index="' . $hourIndex . '" class="selected-tr-warning dashicons dashicons-warning"></span>
                                    <span class="wph-tr-assignee" id="wph-tr-assignee-'.$tr->assignee_id.'">
                                        <img alt="" src="'.$avatar_url.'" srcset="'.$avatar_url.' 2x" class="avatar avatar-35 photo p-1 bg-light" height="35" width="35" title="'.$assignee->display_name.'">
                                    </span>
                                </div>
                            </div>
                            <div class="wph-time-record-data">
                                <div class="text-center">
                                        <p>'.$hour.'</p>
                                </div>
                            </div>
                        </div>
                    ';

                    $hourlyBreakdown .= '
                        <div  class="row" style="display: none;" data-index="' . $hourIndex . '">
                            <div class="col-12">
                                <br/>
                                <h3>' . $wph_task->title . '</h3>
                                <h4>' . date('F j, Y, g a', $tr->timestamp) . '</h4>
                                '.($userCanEditTask ? '<label class="checkbox-inline">
                                    <input type="checkbox" class="hourly-select-all" data-index="' . $hourIndex . '" /> '.__('Select all','wphourly').'
                                </label>' : '').'
                            </div>
                    ';
                        foreach ($hourTrs as $hourTr) {
                            if ($hourTr->is_paid) {
                                $is_paid_in = 'bg-success';
                            } else {
                                $is_paid_in = 'bg-danger';
                            }

                            if ($hourTr->is_paid_out) {
                                $is_paid_out = 'bg-success';
                            } else {
                                $is_paid_out = 'bg-danger';
                            }

                            $tr_type = $hourTr->tr_type;

                            if($tr_type == 'manual') {
                                $tr_type_class = 'wph-time-record-manual-time bg-warning';
                                // todo: import into the plugin
                                $tr_screenshot = 'https://superwpheroes.io/wp-content/plugins/wp-hourly/assets/img/app-icon.png';
                                $tr_manual_time_badge = '<span class="badge badge-info" style="display: none;" data-toggle="tooltip" data-placement="top" title="'.__('MANUAL TIME','wphourly').'">'.__('M.T.','wphourly').'</span>';
                            } else {
                                $tr_type_class = 'wph-time-record-tracker-time bg-light';
                                if (!function_exists('SWPH\WpHourly\Tracker\timeRecordScreenshot')) {
                                    $tr_screenshot = WP_HOURLY_URI.'assets/img/screenshot-expired.png';
                                } else {
                                    $tr_screenshot = SWPH\WpHourly\Tracker\timeRecordScreenshot($hourTr->id);
                                }
                                $tr_manual_time_badge = '';
                            }

                            /*
                            //    ---        SUPER WP HEROES
                            //    ---        COMMENT: group TR by date
                            */

                            $tr_date = substr($hourTr->created_at, 0, 10);

                            // current becomes previous
                            $date = $tr_date;

                            $avatar_url = get_avatar_url($hourTr->assignee_id);

                            $assignee = get_userdata($hourTr->assignee_id);

                            $hourlyBreakdown .= '
                                <div class="col-lg-2">
                                    <div class="card wph-time-record-holder  '.$tr_type_class.' p-0">
                                        <a href="'. $tr_screenshot .'" target="_blank"  class="wph-time-record-thumbnail" ' . ($tr_type == 'manual' ? 'style="background-image: url(' . $tr_screenshot . ')"' : 'data-bg-image="' . $tr_screenshot . '"') . ' style="height: auto;">
                                            ' . ($tr_type == 'manual' ? '' : '<div class="time-record-holder">
                                                <div class="text-center bg-light text-muted">
                                                    <i class="fa fa-spinner fa-spin fa-4x"></i>
                                                </div>
                                            </div>') . '
                                            <div class="ml-1 paid-status">
                                                <span class="badge ' . $is_paid_in . ' text-white" data-toggle="tooltip" data-placement="top" title="" data-original-title="'.__('Time record is Paid In by your client','wphourly').'">'.__('P.I.','wphourly').'</span>
                                                <span class="badge ' . $is_paid_out . ' text-white" data-toggle="tooltip" data-placement="top" title="" data-original-title="'.__('Time record is not Paid Out to your employee','wphourly').'">'.__('P.O.','wphourly').'</span>
                                                '.$tr_manual_time_badge.'
                                            </div>
                                            <span class="wph-tr-assignee" id="wph-tr-assignee-'.$hourTr->assignee_id.'">
                                                <img alt="" src="'.$avatar_url.'" srcset="'.$avatar_url.' 2x" class="avatar avatar-35 photo p-1 bg-light" height="35" width="35" title="'.$assignee->display_name.'">
                                            </span>
                                        </a>
                                    </div>
                                    <div class="wph-time-record-data">
                                        <div class="text-center">
                                            <label class="checkbox-inline" data-toggle="tooltip" data-placement="top" title="'.$tr->title.'">
                                        '.($userCanEditTask ? '<input type="checkbox" class="time-record-breakdown-checkbox"  data-index="' . $hourIndex . '" value="'.$hourTr->id.'">' : '').'<small><small>'.$hourTr->hours.' h - '.date('Y-m-d H:i:s', $hourTr->timestamp).'</small></small>
                                    </label>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                    $hourlyBreakdown .= '</div>';
                }
            }

            echo '<div style="display: none; padding: 100px 70px 20px;" class="container-fluid" id="hourly-breakdown">
                <span class="hourly-breakdown-arrow dashicons dashicons-arrow-left-alt2" id="hourly-breakdown-left"></span>
                <span class="hourly-breakdown-arrow dashicons dashicons-arrow-right-alt2" id="hourly-breakdown-right"></span>
                <span class="dashicons dashicons-no-alt" id="hourly-breakdown-close"></span>
                ' . $hourlyBreakdown . '
            </div>';

echo '
        </div>


      </div> <!-- end task time records card -->';

      echo '<div class="tab-pane" id="task-manual-time-content" role="tabpanel" aria-labelledby="task-manual-time-content">';
     if (wphCurrentUserCanAddManualTime()) {
         echo '<div class="card mt-0 p-2" style="width: 100%;max-width: 100%;">
              <form class="form-inline add-time-record-validation" novalidate>
              <input type="hidden" id="form-time-record-add-task-id" value="' . $taskId . '" />
              <input type="hidden" id="form-time-record-add-client-id" value="' . $clientId . '" />
              <div class="form-row">
                    <div class="form-group col-md-1">
                    '.wphGetSelectDropdown(
                        $curren_selection = $wph_task->assignee_id,
                        $type = 'employee',
                        $show_selection = false,
                        $gravatar_size = 47,
                        $search_key = 'task-assignee-add-time',
                        $entityId = '',
                        $entity = '',
                        $update = '',
                        $is_ajax = false,
                        $title = __('Select employee for whom to add time', 'wphourly'),
                        $extra_class = ''
                    ).'
                  </div>

                  <div class="form-group col-md-3">
                    <input placeholder="1" data-toggle="tooltip" step="0.1" data-placement="top" title="'.__('Number of hours to add (ex.: 10)','wphourly').'" type="number" class="form-control-plaintext" id="form-time-record-add-hours"  required>
                    <div class="invalid-tooltip">
                        '.__('Invalid number of hours','wphourly').'
                    </div>
                  </div>
                  <div class="form-group col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="'.__('On date','wphourly').'" type="date" class="form-control-plaintext" id="form-time-record-add-date"  required>
                        <div class="invalid-tooltip">
                            '.__('Invalid date','wphourly').'
                        </div>
                    </div>
                  <div class="form-group col-md-3">
                    <input data-toggle="tooltip" data-placement="top" title="'.__('Time','wphourly').'" type="time" class="form-control-plaintext" id="form-time-record-add-time"  required>
                  </div>
                  <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-success btn-sm" id="form-time-record-add"><i class="fa fa-plus"></i> '.__('Add time','wphourly').'</button>
                  </div>

                  <div id="add-manual-time-response"></div>

              </div>

            </form>
        </div> <!-- end card -->';
    }

     echo '</div> <!-- end task manual time card -->';

      echo '<div class="tab-pane" id="task-settings-content" role="tabpanel" aria-labelledby="task-settings-content">';
      if ($userCanEditTask) {
        echo '<div class="card mt-0 p-2" style="width: 100%;max-width: 100%;">
                <div class="row" style="display: none;">
                        <div class="col-md-12">
                            <h6>'.__('Change Task Client','wphourly').'</h6>
                            <p class="mt-0 alert alert-warning">'. __('Changing the assigned Client for any task would re-assigne the tracked time to the newly selected client. Be super sure you know what you are doing here. Also, it will remove the project assigned to this task; you will have to select a new project below once you selected the new client as well.', 'wphourly') . '
                            </p>

                                <form class="">
                            <div class="form-row">
                                    <div class="form-group col-md-3">
                                            <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text p-0" id="client-110"><img alt="" src="https://secure.gravatar.com/avatar/741ddda8d50f0c022279491809639579?s=33&amp;d=mm&amp;r=g" srcset="https://secure.gravatar.com/avatar/741ddda8d50f0c022279491809639579?s=66&amp;d=mm&amp;r=g 2x" class="avatar avatar-33 photo" height="33" width="33"></span>
                                            </div>
                                            <select class="form-control" aria-label="Large" aria-describedby="client-110">
                                                <option value="Some Cient" selected="">'. __('Some Client', 'wphourly') .'</option>
                                            </select>
                                            </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <input data-toggle="tooltip" data-placement="top" title="'. __('Type the word CHANGE CLIENT to approve changing this task\'s client', 'wphourly') .'" type="text" class="form-control-plaintext" id="wph-tr-delete-approval" placeholder="'. __('Type CHANGE CLIENT to approve', 'wphourly') .'" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block"><small>'. __('CHANGE CLIENT', 'wphourly') .'</small></button>
                                </div>
                            </div>

                            </form>

                        </div>

                </div> <!-- end row -->

                <div class="row">
                        <div class="col-md-12">
                            <h6>'.__('Change Task Project','wphourly').'</h6>
                            <p class="mt-0 alert alert-warning d-none">'. __('You can change the project of a task for the same client with no problem. IF you changed the client above as well, make sure you do select the projects from the new client\'s project list below.', 'wphourly') .'</p>
                            <p class="mt-0 alert alert-warning">'. __('You can change the project of a task. HOWEVER, it is not recommended that you change the project if the task has time records that are in a pending order (in case WP Hourly Woocommerce plugin is used). Be aware that the hourly rate for unpaid time records is going to be updated accordingly.', 'wphourly') .'</p>

                                <form class="">
                            <div class="form-row">
                                    <div class="form-group col-md-3">
                                        '.wphGetSelectDropdown(
                                        $curren_selection = $wph_task->project_id,
                                        $type = 'projects',
                                        $show_selection = true,
                                        $gravatar_size = 47,
                                        $search_key = 'wph-task-project-id',
                                        $entityId = $wph_task->id,
                                        $entity = 'task',
                                        $update = 'project_id',
                                        $is_ajax = true,
                                        $title = __('Change task project', 'wphourly'),
                                        $extra_class = ''
                                    ).'
                                </div>
                                <div class="form-group col-md-3 d-none">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block"><small>'. __('SET NEW PROJECT', 'wphourly') .'</small></button>
                                </div>
                            </div>

                            </form>


                        </div>

                </div> <!-- end row -->

                <div class="row">
                        <div class="col-md-12">
                            <h6>'.__('Delete Task ( ','wphourly').'<i class="fa fa-sad-tear"></i>'.__(' are you sure?...)','wphourly').'</h6>
                            <p class="mt-0 alert alert-danger">'. __('Deleting a task will permanently delete all the associated time records that follow along with it. If you had your money collected for those time records, then at least money wise, you need not worry, however, your reports will no longer show that time, if you or your clients will even need to consult the archives.', 'wphourly') .'
                            </p>

                                <form class="">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <input data-toggle="tooltip" data-placement="top" title="'. __('Type the word DELETE TASK to approve deletion', 'wphourly') .'" type="text" class="form-control-plaintext" id="wph-task-delete-approval" placeholder="'. __('Type DELETE TASK to approve', 'wphourly') .'" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <button id="wph-delete-task-button" data-task-id="' . $wph_task->id . '" disabled class="btn btn-danger btn-sm btn-block"><small>'. __('DELETE TASK', 'wphourly') .'</small></button>
                                </div>
                            </div>

                            </form>


                        </div>

                </div> <!-- end row -->

            </div> <!-- end card -->';
        }

      echo '</div> <!-- end task advanced settings card -->';


      $args = array(
        'task_id' => $wph_task->id,
      );
      do_action( 'wph_add_task_modal_tab_pane', $args);

    echo '
    </div>';


?>

