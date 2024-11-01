<?php

$hourlyRate = get_option('wph_hourly_rate', '');

if($hourlyRate == '') {
  $hourlyRate = 'NOT SET - check the WP Hourly Settings page to set up a Global Hourly rate.';
}

echo '<div id="wph-add-project-holder" class="modal fade" tabindex="-1" role="dialog" style="display:none">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">'.__('Add new project','wphourly').'</h5>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        </div>
        <div class="modal-body" id="project-details">

            <form class="add-validation" novalidate>
                  <div class="form-group">
                    
                  </div>

                  <div class="form-row">
                      <div class="form-group col-md-8">
                        <input type="text" class="form-control form-control-lg" id="form-project-title" placeholder="'.__('Project title','wphourly').'" required>
                        <div class="invalid-tooltip">
                          '.__('Please provide a project title.','wphourly').'
                        </div>
                      </div>
                      <div class="form-group col-md-4">
                        <input type="number" step="0.1" data-toggle="tooltip" data-placement="top" title="'.__('Project estimated hours','wphourly').'" class="form-control form-control-lg" id="form-project-estimated-hours" placeholder="Est. Hours">
                      </div>
                  </div>

                  <div class="form-row">
                      <div class="form-group col-md-8">
                        <input type="url" class="form-control form-control-sm" id="form-project-ex-link" placeholder="'.__('External URL (ex.: asana.com project link)','wphourly').'">
                      </div>
                      <div class="form-group col-md-4">
                        <input type="date" data-toggle="tooltip" data-placement="top" title="Project Deadline" class="form-control form-control-sm" id="form-project-deadline" placeholder="'.__('Deadline','wphourly').'">
                      </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                            '.wphGetSelectDropdown(
                                $curren_selection = '',
                                $type = 'customer',
                                $show_selection = true,
                                $gravatar_size = 30,
                                $search_key = 'form-project-client-id',
                                $entityId = '',
                                $entity = '',
                                $update = '',
                                $is_ajax = false,
                                $title = __("Select client","wphourly"),
                                $extra_class = ''
                              ).'
                            <div class="invalid-tooltip">
                                  Please select a customer.
                            </div>
                    </div>
                    <div class="form-group col-md-4">
                              '.wphSwitchInput(
                                $entity = 'task',
                                $entityId = '',
                                $update = 'is_billable',
                                $value = 1,
                                $onLabel = __('Billable','wphourly'),
                                $offLabel = __('Non Billable','wphourly'),
                                $id = 'task-billable-status',
                                $class = 'mb-0',
                                $style = "width: 80%",
                                $title = __('Select billing status','wphourly'),
                                $is_ajax = false
                              ).'
                    </div>
                    <div class="form-group col-md-2">
                            <input type="number" step="0.1" class="form-control" id="form-project-hourly-rate" data-toggle="tooltip" data-placement="top" title="'.__("Override Global Hourly Rate? Currently: $hourlyRate","wphourly").'" placeholder="'.__("H.R.","wphourly").'" >
                    </div>
                  </div>

                  <div class="form-group">
                    <textarea class="form-control" rows="5" id="form-project-description" placeholder="'.__('Project description','wphourly').'"></textarea>
                  </div>
                  <div class="form-group text-right">
                      <input type="hidden" name"created_by" id="created_by" value="'.get_current_user_id().'" />
                      <input type="hidden" name="wp-admin-url" value="'.admin_url().'" />
                      <i class="fa fa-spinner fa-spin d-none" id=""></i>
                      <button id="add-project-button" type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> '.__('ADD PROJECT','wphourly').'</button>
                  </div>
            </form>
        </div>
    </div>
</div>
</div>';