<?php

/*
//	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//	***
//	***		SUPER WP HEROES                                               
//	***		FUNCTION NAME: STERGE ASTA E DOAR O NOTA: VEZI IN wp-hourly.php AM PUS LA FINAL UN HOOK CARE TREBE MUTA IN PRO CA SA AISEZE NOTIFICARI
//	***		DESCRIPTION: 
//  ***		CALLED ON: 
//  ***
//  ***		TO DO: -
//	***
//	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
*/

if ( ! defined( 'WP_HOURLY_PATH' ) ) exit; // Exit if accessed directly

$user = get_current_user_id();


$wph_projects = wphGetEmployeeProjects($user, false);

echo
'<h1 class=""><b>MY WORKSPACE</b></h1>

<div class="container-fluid my-tasks my-workspace pl-0 pr-0">';

	if(count($wph_projects) > 0 ) {

		do_action( 'wph_my_workspace_top_section' );

		echo '<div class="row"> ';
    		echo '<div class="col-3">';

    			do_action( 'wph_my_workspace_before_search_bar' );

	    		echo '<div class="input-group mb-3 mt-3">
	                <input type="text" class="has-shadow-hover form-control live-search-box" data-search-target="projects-tasks" placeholder="'.__('Search projects & tasks','wphourly').'" aria-label="Search" aria-describedby="inputGroup-sizing-default">
	            </div>';

	            do_action( 'wph_my_workspace_after_search_bar' );
	            do_action( 'wph_my_workspace_before_projects_list' );

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

	            		echo '<div 
		            			class="card p-3 mt-0 mb-3 has-shadow-hover"
		            			data-project="'.$wph_project->id.'"
		            			data-search-result="projects-tasks"
			                    data-search-term="'.strtolower($wph_project->title).'"
		                    >
									<div class="row">
				                        <div class="col-md-12 mt-1">';

				                        if($wph_project->status == "active") {
				                            echo '<p class="company-name mb-0 font-weight-bold"><i class="fas fa-circle text-success"></i> '.substr($wph_project->title,0 ,27).'...</p>';
				                        } else {
				                            echo '<p class="company-name mb-0 font-weight-bold"><i class="fas fa-circle text-warning"></i> '.substr($wph_project->title,0 ,27).'...</p>';
				                        }

				                        echo '
				                        </div>
				                    </div>

				                    <div class="row">
				                        <div class="col-md-12 mt-3 mb-3">
				                            <div class="progress">
				                                <div class="progress-bar bg-success" role="progressbar" style="width: '.$project_completion_rate.'%" aria-valuenow="'.$project_completion_rate.'" aria-valuemin="0" aria-valuemax="100">
				                              	</div>
				                            </div>
				                        </div>
				                    </div>';

				                    $deadline = "Not set";
				                    if( !empty($wph_project->deadline)) {
				                        $deadline = $wph_project->deadline;
				                    }

									$project_tasks = wphGetmyProjectTasks($wph_project->id, 2, get_current_user_id());

				                    echo '
				                    <div class="row">
				                        <div class="col-md-8">
				                            <p class="deadline-projects mb-0 font-weight-bold">'.__('deadline:','wphourly').'<span class="date-deadline-projects"> '.$deadline.'</span></p>
				                        </div>
				                        <div class="col-md-4 text-right">
				                            <p class="date-deadline-projects" title="'.__('Billable hours tracked','wphourly').'"><i class="fas fa-clock"></i> ' . wphGetProjectHours($wph_project->id, WPH_BILLABLE_NON_BILLABLE, true) . ' hours </p>
				                        </div>
				                    </div>

				                    <div class="row no-gutters">
				                        <div class="col-6">
				                        	<a href="'.$url.'" class="btn btn-sm">'.__('View Project','wphourly').'</a>
				                        </div>
				                        <div class="col-6">
				                            <a href="javascript: void(0)" data-project="'.$wph_project->id.'" class="btn btn-sm btn-block wph-show-workspace-project-tasks">View <b>'.count($project_tasks).'</b> '.__('Tasks','wphourly').'</a>
				                        </div>
				                    </div>
				            </div>';
					} // end projects loop

					do_action( 'wph_my_workspace_after_projects_list' );

			echo '</div> <!-- col-md-3 -->';

			echo '<div class="col-md-4">';
				echo '<div class="task-list mb-3 p-3 has-shadow-hover">';

				do_action( 'wph_my_workspace_before_task_list' );

                    if(count($wph_projects) > 0 ) {
			        	
			        	$i = 0;

			        	foreach($wph_projects as $wph_project) {

			        		$hide = "";

				        	if($i == 0) {
				        		$hide = "";
				        	} else {
				        		$hide = "hidden d-none";
				        	}

				        	
				            if (!wphCurrentUserCanViewProject($wph_project->id)) {
				                continue;
				            }

			            	$url = add_query_arg(
			                    array(
			                        'page'=>'wph-dashboard',
			                        'project'=>$wph_project->id
			                   ), admin_url('admin.php'));

			            
                    
                   			echo '<h5 data-search-result="projects-tasks" data-search-term="just hide 9465 24y i4g 5929649 524t" class="searchable project-title mt-1 '.$hide.'" data-project="'.$wph_project->id.'">
            					<i class="fa fa-circle text-success"></i> <b>'.$wph_project->title.'</b>
            				</h5>
            				<hr data-search-result="projects-tasks" data-search-term="just hide 9465 24y i4g 5929649 524t" class="searchable '.$hide.' project-title" data-project="'.$wph_project->id.'" />';

                   			echo '<ul class="task-status-todo sortable-DISABLE ui-sortable-DISABLE m-0 p-0" data-update-task-status="2" data-status-id="2">';



					        $project_tasks = wphGetMyProjectTasks($wph_project->id, 2, get_current_user_id());

					        
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

					            $userCanEditTask = 0; //hack

								

					            echo '
		                            <li '.$hide_more.' 
		                            	data-task-count="'.$index.'" 
		                            	data-search-result="project-stasks"
		                            	data-search-term="'.strtolower($task->title).'"
		                            	data-project="'.$wph_project->id.'"
		                            	data-task="'.$task->id.'"
		                            	data-task-id="'.$task->id.'"
		                            	title="'.$task->title.'"
		                            	data-show-all-result="task-item-status-todo"
		                            	data-entity-id="'.$task->id.'"
		                            	data-entity="task"
		                            	data-update="status_id"
		                            	class="tsk-li searchable task-panel-task-item ui-sortable-handle task-item-status-todo '.$hide.'"
		                            >


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
					                                 	<a 
					                                 		class="updates-tasks-title text-dark load-task task-title-container task-title-ellipsis" 
					                                 		style=""
					                                 		data-task="'.$task->id.'"
					                                 		data-project="'.$wph_project->id.'">
					                                 		'.mb_strimwidth($task->title, 0, 35, "...").'
					                                 	</a>
					                                 </h6>
		                            			</div>
		                            		</div>
		                            		<div class="task-item-details-row">';
		                            			$task_hours = (float) wphGetTaskHours($task->id, WPH_BILLABLE_NON_BILLABLE, true);
									            $estimatedHours = '';
									            if ($task->estimated_hours) {
									                $estimatedHours = '<small class="est-hours est-task-hours">('.__('est.:','wphourly').' '.$task->estimated_hours.''.__('h','wphourly').')</small>';
									            }
												if(get_current_user_id() != $task->assignee_id) $message = __('His time:','wphourly');
												else $message = __('My time:','wphourly');


		                            			echo '<div class="task-item-details-icon">
			                            			<i class="fa fa-clock '.wphGetClockStyle($task_hours, (float) $task->estimated_hours).'"></i>
			                            		</div>
			                            		<div class="task-item-details-detail pt-0">
			                            			<p  title="'.__('Number of tracked hours vs. estimated hours','wphourly').'">
					                                     <small class="est-hours rt-hours">'.$message.' <span id="hoursTask-'. $task->id .'">'. $task_hours .'</span>'.__('h','wphourly').'</small>' . $estimatedHours . '
					                                </p>
		                            			</div>
		                            		</div>
		                            	</div>
		                            	<div class="task-item-details-timer">
		                            		<div class="task-item-details-start-stop">';
		                            		
		                            		$wph_print_time_trk = do_action( 'wph_before_select_task_assignee_in_list', $task->id );
	                                		echo $wph_print_time_trk;
	                                		
	                            		
		                            		echo '<div class="task-item-details-member">
		                            			<div class="wph-usr-ava-wrp">'.($userCanEditTask ? wphGetSelectDropdown(
	                                            $curren_selection = $task->assignee_id,
	                                            $type = 'employee',
	                                            $show_selection = false,
	                                            $gravatar_size = 30,
	                                            $search_key = 'task-'.$task->id.'-assignee',
	                                            $entityId = $task->id,
	                                            $entity = 'task',
	                                            $update = 'assignee_id',
	                                            $is_ajax = true,
	                                            $title = __('Change task assignee','wphourly')
	                                        ) : get_avatar($task->assignee_id, 30, '', '', ['class' => ['wph-selected-user-avatar']])).'
		                            			</div>
	                            			</div>
		                            	</div>
		                            </div>

	                                <hr />
	                            </li>';
	                            $i++;
	                            $hide = "";
				        } // end task
				        echo ' 
	                    <li class="ui-sortable-placeholder task-panel-task-item task-item-status-todo ui-sortable-handle" style="visibility:visible; height: 1px"></li> 
	                </ul>';
	                do_action( 'wph_my_workspace_after_task_list' );
	                
                   		}
                   		echo '</div>';

                   	echo '</div> <!-- col-md-4 -->';
                   	}	


                   	echo '<div class="col-md-5">';
                   		echo '<div class="card has-shadow-hover wph-my-workspace-sidebar m-0 p-3">';
                   			do_action( 'wph_my_workspace_sidebar' );
                   		echo '</div>';
                   	echo '</div> <!-- col-md-5 -->';

        echo '</div>'; // row

        do_action( 'wph_my_workspace_bottom_section' );

	echo '
    <div id="wph-task-holder" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header pb-0 border-bottom-0">
                    <h5 class="modal-title" id="add-task-modal-title">'.__('Add task','wphourly').'</h5>
                    <ul class="nav nav-tabs w-100" id="task-tabs" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link text-secondary active" id="task-details-tab" data-toggle="tab" href="#task-details-content" role="tab" aria-controls="task-details-content" aria-selected="true"><b>'.__('Task Details','wphourly').'</b></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link text-secondary" id="task-time-records-tab" data-toggle="tab" data-is-ajax="yes" data-load-url="'.WP_HOURLY_URI.'templates/wph-time-record-tpl.php" data-parameters="" data-target="#task-time-records-content" href="#task-time-records-content" role="tab" aria-controls="task-time-records-content" aria-selected="false"><b>'.__('Time Records','wphourly').'</b></a>
                      </li>
                      '.(wphCurrentUserCanAddManualTime() ? '<li class="nav-item">
                        <a class="nav-link text-secondary" id="task-manual-time-tab" data-toggle="tab" href="#task-manual-time-content" role="tab" aria-controls="task-manual-time-content" aria-selected="false"><b>'.__('Add Manual Time','wphourly').'</b></a>
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

    ?>

	<script>

	jQuery(document).ready( function(){
		jQuery(".wph-show-workspace-project-tasks").click( function() {
			var project = jQuery(this).data("project");
			jQuery(".task-panel-task-item, h5.project-title, hr.project-title").addClass("hidden d-none");
			jQuery('li[data-project="'+project+'"], h5[data-project="'+project+'"], hr[data-project="'+project+'"]').removeClass("hidden d-none");

		});
	});

	</script>


<?php

} else { // end prject if statement check
    echo '
    <div class="row">
        <div class="col-md-12">
            <div class="mt-5 alert alert-info">'.__('You do not have any tasks marked as','wphourly'). '<b>'.__(' TO DO ','wphourly').'</b>'.__('on any ','wphourly').' <b>'.__('Active','wphourly').'</b> '.__('projects','wphourly').'</div>
        </div>
    </div>';

} // end projects count check


echo '</div> <!-- end container -->';





?>