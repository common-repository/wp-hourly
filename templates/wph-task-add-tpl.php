<?php

$projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
$clientId = wphGetProjectClientId($projectId);
$statusId = filter_input(INPUT_GET, 'status_id', FILTER_VALIDATE_INT);
if (!$projectId || !$clientId || !$statusId) {
    echo "There was an error!";

    return;
}

/*echo '
    <ul class="nav nav-tabs" id="task-tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="task-details-tab" data-toggle="tab" href="#task-details-content" role="tab" aria-controls="task-details-content" aria-selected="true"><b>Task Details</b></a>
      </li>
    </ul>';*/


echo '
    <div class="tab-content">';

echo '
    <div class="tab-pane py-3 active" id="task-details-content" role="tabpanel" aria-labelledby="task-details-content">
        <div class="card mt-0 p-2" style="width: 100%;max-width: 100%;">
            <form class="add-task-validation" novalidate>
                <input type="hidden" id="task-add-project-id" value="'.$projectId.'" />
                <input type="hidden" id="task-add-client-id" value="'.$clientId.'" />
                <input type="hidden" id="task-add-status-id" value="'.$statusId.'" />
                
               <div class="form-row h-100">
                  <div class="form-group my-auto col-md-1 pr-0">
                    '.wphGetSelectDropdown(
                      $curren_selection = 0,
                      $type = 'employee',
                      $show_selection = false,
                      $gravatar_size = 47,
                      $search_key = 'task-assignee',
                      $entityId = 0,
                      $entity = 'task',
                      $update = 'assignee_id',
                      $is_ajax = false,
                      $title = __('Change task assignee', 'wphourly'),
                      $extra_class = ''
                    ).'
                    <div class="invalid-tooltip">
                          '.__('Select customer','wphourly').'
                    </div>
    
                  </div>
                  <div class="form-group my-auto col-md-10">
                        
                        <div class="input-group input-group-lg">
                          <input type="text" class="form-control form-control-lg" id="form-task-title" placeholder="'.__('Task title','wphourly').'" required>
                          <div class="invalid-tooltip">
                            '.__('Please provide a task title.','wphourly').'
                          </div>
                        </div>
                    </div>
                    <div class="col-md-1 my-auto text-center">
                        <a class="text-muted" data-toggle="collapse" href="#edit-task-details" aria-expanded="false" aria-controls="collapseExample"><i class="fas fa-2x fa-cog"></i></a>
                    </div>
              </div>
    
    
              
                <div class="collapse mt-3" id="edit-task-details" style="">
                  <div class="form-row">
    
                      <div class="form-group col-md-4">
                        <input type="url" class="form-control form-control-sm" id="form-task-ex-link" placeholder="'.__('External URL (ex.: asana.com project link)','wphourly').'">
                      </div>
    
                      <div class="form-group col-md-2">
                            <label class=" d-none switch  mb-0" style="width: 100%">
                                <input class="switch-input" type="checkbox" name="is_billable" value="1">
                                <span class="switch-label" data-on="'.__('Billable','wphourly').'" data-off="'.__('Non billable','wphourly').'"></span> 
                                <span class="switch-handle"></span> 
                            </label>
                            '.wphSwitchInput(
                                $entity = 'task',
                                $entityId = '',
                                $update = 'is_billable',
                                $value = 1,
                                $onLabel = __('Billable','wphourly'),
                                $offLabel = __('Non Billable','wphourly'),
                                $id = 'task-billable-status',
                                $class = 'mb-0',
                                $style = "width: 100%",
                                $title = __("Set task billing status", 'wphourly'),
                                $is_ajax = false
                            ).'
                      </div>
    
                      <div class="form-group col-md-2">
                        <input type="number" class="form-control form-control-sm" id="form-task-hourly-rate" data-toggle="tooltip" data-placement="top" title="'. __('Override Global & Project Hourly Rate?', 'wphourly') .'" placeholder="Rate">
                      </div>
                      <div class="form-group col-md-2">
                            <input type="number"  data-toggle="tooltip" data-placement="top" title="'. __('Task estimated hours', 'wphourly') .'" class="form-control form-control-sm" id="form-task-estimated-hours" placeholder="Est. H.">
                        </div>
                      <div class="form-group col-md-2">
                        <input type="date" data-toggle="tooltip" data-placement="top" title="'. __('Project Deadline', 'wphourly') .'" class="form-control form-control-sm" id="form-task-deadline" placeholder="Deadline">
                      </div>
                  </div>
                </div>
    
                  <div class="form-group">
                    <textarea class="form-control mt-3" rows="5" id="form-task-description" placeholder="'. __('Task description', 'wphourly') .'"></textarea>
                  </div>
    
                  <div class="form-group" data-task-id="">
                    <button class="btn btn-success btn-sm float-right" id="add-task-button">'. __('Add Task', 'wphourly') .'</button>
                  </div>
        </form>
    </div> <!-- end card -->
    </div> <!-- end task details card -->';

echo '</div>';
