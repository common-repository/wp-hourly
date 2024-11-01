<?php

if ( ! defined( 'WP_HOURLY_PATH' ) ) exit; // Exit if accessed directly

$all_projects = add_query_arg(
                array(
                    'page'=>'wph-dashboard',
                    'projects'=>'all'
               ), admin_url('admin.php'));

$active_projects = add_query_arg(
                array(
                    'page'=>'wph-dashboard'
               ), admin_url('admin.php'));

$archived_projects = add_query_arg(
                array(
                    'page'=>'wph-dashboard',
                    'projects'=>'archived'
               ), admin_url('admin.php'));

$all_projects_active = "";
$active_projects_active = "";
$archived_projects_active = "";

if( isset($_GET['projects']) ) {

    if($_GET['projects'] == 'all') {
        $all_projects_active = "active";
        $active_projects_active = "text-dark";
        $archived_projects_active = "text-dark";

        $wph_projects = wphGetProjects();
    }
    if($_GET['projects'] == 'archived') {
        $all_projects_active = "text-dark";
        $active_projects_active = "text-dark";
        $archived_projects_active = "active";

        $wph_projects = wphGetProjects('archived');
    }
} else {
    $all_projects_active = "text-dark";
    $active_projects_active = "active";
    $archived_projects_active = "text-dark";

    $wph_projects = wphGetProjects('active');
}


echo
'<div class="container-fluid pl-0 pr-0">
    <div class="row updates-tasks-title h-100">
        <div class="col-md-2 my-auto">
            <h1 class="m-0 p-0"><b>'.__('PROJECTS','wphourly').'</b></h1>
        </div>
        <div class="col-md-5 my-auto">
            <ul class="nav">
              <li class="nav-item m-0">
                <a class="nav-link '.$all_projects_active.'" href="'.$all_projects.'">'.__('All Projects','wphourly').'</a>
              </li>
              <li class="nav-item m-0">
                <a class="nav-link '.$active_projects_active.'" href="'.$active_projects.'"><span class="fas fa-circle text-success"></span> '.__('Active Projects','wphourly').'</a>
              </li>
              <li class="nav-item m-0">
                <a class="nav-link '.$archived_projects_active.'" href="'.$archived_projects.'"><span class="fas fa-circle text-warning"></span> '.__('Archived Projects','wphourly').'</a>
              </li>
            </ul>
            
        </div>
        <div class="col-md-1 my-auto">
            '.(wphCurrentUserCanEditProject() ? '<i class="fas fa-plus-circle has-action" id="wph-open-add-project-modal" data-toggle="tooltip" data-placement="bottom" title="'.__('Add new project','wphourly').'"></i>' : '').'
        </div>
        <div class="col-md-4 my-auto">
            <div class="input-group">
                <input type="text" class="has-shadow-hover form-control live-search-box" data-search-target="projects" placeholder="'.__('Search projects','wphourly').'" aria-label="Search" aria-describedby="inputGroup-sizing-default">
            </div>
        </div>
    </div>';

    echo '
    <div class="row">';


    if(count($wph_projects) > 0 ) {
        foreach($wph_projects as $wph_project) {
            if (!wphCurrentUserCanViewProject($wph_project->id)) {
                continue;
            }

            $url = add_query_arg(
                    array(
                        'page'=>'wph-dashboard',
                        'project'=>$wph_project->id
                   ), admin_url('admin.php'));

            $project_completion_rate = wphGetProjectCompletionRate($wph_project->id);

            echo
            '<div class="col-lg-3 col-md-6 mt-2 searchable"  data-search-result="projects" data-search-term="'.strtolower($wph_project->title).'">
                        
                <div class="card p-3 has-shadow-hover">


                      <div class="row">
                          <div class="col-md-12 mt-1">';

                          if($wph_project->status == "active") {
                            echo '<p class="company-name mb-0 font-weight-bold"><i class="fas fa-circle text-success"></i> '.substr($wph_project->title,0 ,20).'...</p>';
                        } else {
                            echo '<p class="company-name mb-0 font-weight-bold"><i class="fas fa-circle text-warning"></i> '.substr($wph_project->title,0 ,20).'...</p>';
                        }

                        echo '
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mt-3 mb-3">
                              <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: '.$project_completion_rate.'%" aria-valuenow="'.$project_completion_rate.'" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>';

                    $deadline = __('Not set','wphourly');
                    if( !empty($wph_project->deadline)) {
                        $deadline = $wph_project->deadline;
                    }

                    echo '
                    <div class="row">
                          <div class="col-md-8">
                              <p class="deadline-projects mb-0 font-weight-bold">'.__('deadline:','wphourly').'<span class="date-deadline-projects"> '.$deadline.'</span></p>
                          </div>
                          <div class="col-md-4 text-right">
                              <p class="date-deadline-projects"><i class="fas fa-dollar-sign"></i> '.__('Rate:','wphourly').'  ' . wphGetProjectHourlyRate($wph_project->id) . ' '.__('/ h','wphourly').' </p>
                          </div>
                    </div>

                    <div class="row no-gutters">
                        <div class="col-4">';
                            echo '<a href="'.$url.'" class="btn btn-sm">'.__('View','wphourly').'</a>';
                        echo '
                        </div>
                        <div class="col-2">
                            '.get_avatar( $wph_project->client_id, 30).'
                        </div>
                        <div class="col-2">';
                            if($wph_project->is_billable == 1) {
                                echo '<i class="fas fa-2x fa-money-check-alt text-success" data-toggle="tooltip" data-placement="top" title="'. __('Billable Project', 'wphourly') . '"></i>';
                            } else {
                                echo '<i class="fas fa-2x fa-money-check-alt text-danger" data-toggle="tooltip" data-placement="top" title="' . __('NON-Billable Project', 'wphourly') . '"></i>';
                            }

                        $projectHours = wphGetProjectHours($wph_project->id, WPH_BILLABLE);
                        echo '
                        </div>
                        <div class="col-4">
                            <i class="fas fa-2x fa-stopwatch '.wphGetClockStyle((float) $projectHours, (float) $wph_project->estimated_hours).'" data-toggle="tooltip" data-placement="top" title="'.__('Tracked Hours','wphourly').'"></i> <small>'.$projectHours.'</small>
                        </div>
                    </div>
                    
                </div>
              </div>';

              //exit;
        } // end projects loop

    } else {

        $current_project_status = '';
        if(empty($_GET['projects'])) {
          $current_project_status = 'active';
        } else {
          $current_project_status = $_GET['projects'];
        }
        echo '
        <div class="col-md-12">
            <div class="mt-5 alert alert-info">'. __('There are no projects with the status: ', 'wphourly') .'<b>'.$current_project_status.'</b></div>
        </div>';

    } // end projects count check

    echo
    '</div>';

echo
'</div> <!-- end container -->';

echo '
<div id="wph-add-project-holder" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">'. __('Add new project', 'wphourly') . '</h5>
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
                            <input type="number" step="0.1" data-toggle="tooltip" data-placement="top" title="'.__('Project estimated hours','wphourly').'" class="form-control form-control-lg" id="form-project-estimated-hours" placeholder="'.__('Est. Hours','wphourly').'">
                          </div>
                      </div>

                      <div class="form-row">
                          <div class="form-group col-md-8">
                            <input type="url" class="form-control form-control-sm" id="form-project-ex-link" placeholder="'.__('External URL (ex.: asana.com project link)','wphourly').'">
                          </div>
                          <div class="form-group col-md-4">
                            <input type="date" data-toggle="tooltip" data-placement="top" title="'.__('Project Deadline','wphourly').'" class="form-control form-control-sm" id="form-project-deadline" placeholder="'.__('Deadline','wphourly').'">
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
                                    $title = __("Select client", 'wphourly'),
                                    $extra_class = ''
                                  ).'
                                <div class="invalid-tooltip">
                                    '.__('Please select a customer.','wphourly').'
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
                                    $title = __("Select billing status", 'wphourly'),
                                    $is_ajax = false
                                  ).'
                        </div>
                        <div class="form-group col-md-2">
                                <input type="number" step="0.1" class="form-control" id="form-project-hourly-rate" data-toggle="tooltip" data-placement="top" title="'. __('Override Global Hourly Rate?', 'wphourly') .'" placeholder="'.__('H.R.','wphourly').'">
                        </div>
                      </div>

                      <div class="form-group">
                        <textarea class="form-control" rows="5" id="form-project-description" placeholder="'.__('Project description','wphourly').'"></textarea>
                      </div>
                      <div class="form-group text-right">
                          <input type="hidden" name="wp-admin-url" value="'.admin_url().'" />
                          <i class="fa fa-spinner fa-spin d-none" id=""></i>
                          <button id="add-project-button" type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i>'. __('ADD PROJECT', 'wphourly') . '</button>
                      </div>
                </form>
            </div>
        </div>
    </div>
</div>';
