<?php

$wph_projects = wphGetProjects('active');

echo '<div id="wph-add-manual-time" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">'.__('Add manual time','wphourly').'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body" id="project-details">';

        echo '
            <div class="row">
            
                <div class="col-6">
                
            ';

            
            if(count($wph_projects) > 0) {

                echo '
                <select class="form-control form-control-lg" id="time-select-project">
                    <option value="0" selected>'.__('Select project','wphourly').'</option>';

                foreach($wph_projects as $p) {
                  
                    echo "<option value='$p->id'>$p->title</option>";
                  
                }
            }
            echo '</select>';
                
        echo 
            '</div> 
            <div class="col-6"></div>
        </div>';

        if (wphCurrentUserCanAddManualTime()) {
            echo '<div class="card mt-0 p-2" style="width: 100%;max-width: 100%;">
                 <form class="form-inline">
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
                           $title = __('Select employee for whom to add time','wphourly'),
                           $extra_class = ''
                       ).'
                     </div>
   
                     <div class="form-group col-md-3">
                       <input placeholder="1" data-toggle="tooltip" step="0.1" data-placement="top" title="'.__('Number of hours to add (ex.: 10)','wphourly').'" type="number" class="form-control-plaintext" id="form-time-record-add-hours"  required>
                     </div>
                     <div class="form-group col-md-3">
                       <input data-toggle="tooltip" data-placement="top" title="'.__('On date','wphourly').'" type="date" class="form-control-plaintext" id="form-time-record-add-date"  required>
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

       echo '</div>
    </div>
</div>
</div>';