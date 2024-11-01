<?php


$wph_projects = wphGetProjects('active');

$statusId = filter_input(INPUT_GET, 'status_id', FILTER_VALIDATE_INT);

echo '
<div id="wph-add-task-holder" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" style="display:none">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header pb-0 border-bottom-0">
                <h5 class="modal-title" id="add-task-modal-title-menu">'.__('Add task','wphourly').'</h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="task-details-menu">';
            
            
            
            echo '
    <div class="tab-content">';

echo '
    <div class="tab-pane py-3 active" id="task-details-content-menu" role="tabpanel" aria-labelledby="task-details-content">
        <div class="card mt-0 p-2" style="width: 100%;max-width: 100%;">
            <form class="add-task-modal-validation" novalidate>
                <input type="hidden" id="task-add-project-id-menu" value="" />
                
               <div class="form-row h-100">

                  <div class="form-group my-auto col-md-1 pr-0 wph-user-select-container" data-toggle="tooltip" data-placement="top" title="'.__('Select member to asign this task to','wphourly').'">
                    '.wphGetSelectDropdown(
                      $curren_selection = 0,
                      $type = 'employee',
                      $show_selection = false,
                      $gravatar_size = 47,
                      $search_key = 'task-assignee-menu',
                      $entityId = 0,
                      $entity = 'task',
                      $update = 'assignee_id',
                      $is_ajax = false,
                      $title = __('Change task assignee','wphourly'),
                      $extra_class = ''
                    ).'
                    <div class="invalid-feedback">
                        
                    </div>
                    
    
                  </div>
                  <div class="form-group my-auto col-md-10">
                        
                        <div class="input-group input-group-lg">
    
                          <input type="text" class="form-control form-control-lg" id="form-task-title-menu" placeholder="'.__('Task title','wphourly').'" required>
                          <div class="invalid-tooltip">
                            '.__('Please provide a task title.','wphourly').'
                          </div>
                        </div>
                    </div>
                    <div class="col-md-1 my-auto text-center">
                        <a class="text-muted" data-toggle="collapse" href="#edit-task-details-menu" aria-expanded="false" aria-controls="collapseExample"><i class="fas fa-2x fa-cog"></i></a>
                    </div>
              </div>
              <div class="form-group my-auto col-md-12 m-0 p-0">
                              
              <div class="wph-form-col-select-wrp input-group-lg mt-4">
                <div class="wph-form-col-select input-group">';

                echo wphGetSelectDropdown(
                  $curren_selection = '',
                  $type = 'projects',
                  $show_selection = true,
                  $gravatar_size = '',
                  $search_key = '',
                  $entityId = 'form-select-project',
                  $entity = 'project',
                  $update = '',
                  $is_ajax = false,
                  $title = '',
                  $extra_class = 'form-select-project'
                );

                // echo '<select class="select2 form-control form-control-lg" id="form-select-project" required data-toggle="tooltip" data-placement="top" title="'.__('Select the Project for the Task to be added to','wphourly').'">
                //     <option value="" selected>'.__('Select project','wphourly').'</option>';

                //     if(count($wph_projects) > 0) {
                //         foreach($wph_projects as $p) {
                          
                //           echo "<option value='$p->id'>$p->title</option>";
                          
                //         }
                //     }

                echo '</select>
                  <div class="invalid-tooltip">
                    '.__('Please Select a project.','wphourly').'
                  </div>
                </div>';

                  if(count($wph_projects) > 0) {
                    foreach($wph_projects as $p) {
                        echo '<input style="display: none" type="hidden" id="project_id_'.$p->id.'" value="'.$p->client_id.'" />';
                    }
                  }

                echo '
                <div class="wph-form-col-select input-group">
                  <select class="form-control form-control-lg" id="task-add-status-id-menu" required data-toggle="tooltip" data-placement="top" title="'.__('Select the Stage for the Task to be added to','wphourly').'">
                      <option value="" default>'.__('Select task stage','wphourly').'</option>
                      <option value="1">'.__('Backlog','wphourly').'</option>
                      <option value="2" selected>'.__('To Do','wphourly').'</option>
                      <option value="3">'.__('Done','wphourly').'</option>
                  </select>
                  <div class="invalid-tooltip">
                    '.__('Please Select task stage.','wphourly').'
                  </div>
                </div>  
              </div>
          </div>
    
    
              
                <div class="collapse mt-3" id="edit-task-details-menu" style="">
                  <div class="form-row">
    
                      <div class="form-group col-md-4">
                        <input type="url" class="form-control form-control-sm" data-toggle="tooltip" data-placement="top" title="'.__('URL should contain the full path with http / https','wphourly').'" id="form-task-ex-link-menu" placeholder="'.__('External URL (ex.: asana.com project link)','wphourly').'">
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
                                $onLabel = 'Billable',
                                $offLabel = 'Non Billable',
                                $id = 'task-billable-status',
                                $class = 'mb-0',
                                $style = "width: 100%",
                                $title = "Set task billing status",
                                $is_ajax = false
                            ).'
                      </div>
    
                      <div class="form-group col-md-2">
                        <input type="number" class="form-control form-control-sm" id="form-task-hourly-rate-menu" data-toggle="tooltip" data-placement="top" title="'.__('Override Global & Project Hourly Rate?','wphourly').'" placeholder="Rate">
                      </div>
                      <div class="form-group col-md-2">
                            <input type="number"  data-toggle="tooltip" data-placement="top" title="'.__('Task estimated hours','wphourly').'" class="form-control form-control-sm" id="form-task-estimated-hours-menu" placeholder="'.__('Est. H.','wphourly').'">
                        </div>
                      <div class="form-group col-md-2">
                        <input type="date" data-toggle="tooltip" data-placement="top" title="'.__('Project Deadline','wphourly').'" class="form-control form-control-sm" id="form-task-deadline-menu" placeholder="'.__('Deadline','wphourly').'">
                      </div>
                  </div>
                </div>
    
                  <div class="form-group">
                    <textarea class="form-control mt-3" rows="5" id="form-task-description-menu" placeholder="'.__('Task description','wphourly').'"></textarea>
                  </div>
    
                  <div class="form-group" data-task-id="">
                    <button class="btn btn-success btn-sm float-right" id="add-task-button-menu">'.__('Add Task','wphourly').'</button>
                  </div>
        </form>
    </div> <!-- end card -->
    </div> <!-- end task details card -->';

echo '</div>';
            
            
            
            
           echo ' </div>';
            

            echo '<div class="modal-footer d-none"></div>
        </div>
    </div>
</div>';