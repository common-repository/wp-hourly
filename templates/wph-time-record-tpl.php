<?php

	if(
		( isset($_GET['id']) && !empty($_GET['id']) ) ||
		( isset($_GET['title']) && !empty($_GET['title']) ) ||
		( isset($_GET['hours']) && !empty($_GET['hours']) ) ||
		( isset($_GET['assignee_id']) && !empty($_GET['assignee_id']) ) ||
		( isset($_GET['task_id']) && !empty($_GET['task_id']) ) ||
		( isset($_GET['timestamp']) && !empty($_GET['timestamp']) ) ||
		( isset($_GET['created_at']) && !empty($_GET['created_at']) ) ||
		( isset($_GET['screenshot']) && !empty($_GET['screenshot']) ) ||
		( isset($_GET['client_id']) && !empty($_GET['client_id']) ) ||
		( isset($_GET['is_paid']) && !empty($_GET['is_paid']) ) ||
		( isset($_GET['is_paid_out']) && !empty($_GET['is_paid_out']) ) ||
		( isset($_GET['tr_type']) && !empty($_GET['tr_type']) ) ||
		( isset($_GET['avatar_url']) && !empty($_GET['avatar_url']) ) ||
		( isset($_GET['asignee_nicename']) && !empty($_GET['asignee_nicename']) )
	) {

    $id = $_GET['id'];
    $title = urldecode($_GET['title']);
    $hours = $_GET['hours'];
    $assignee_id = $_GET['assignee_id'];
    $task_id = $_GET['task_id'];
    $timestamp = $_GET['timestamp'];
    $created_at = urldecode($_GET['created_at']);
    $screenshot = $_GET['screenshot'];
    $client_id = $_GET['client_id'];
    $is_paid_in = $_GET['is_paid'];
    $is_paid_out = $_GET['is_paid_out'];
    $tr_type = $_GET['tr_type'];
    $avatar_url = urldecode($_GET['avatar_url']);
    $asignee_nicename = $_GET['asignee_nicename'];

    if (strpos($screenshot, 'screenshot-expired.png') !== false) {
	    $expired = true;
	} else {
		$expired = false;
	}

    if($tr_type == 'manual') {
		$tr_type_class = 'wph-time-record-manual-time bg-warning';
		$tr_namual_time_badge = '<span class="badge badge-info" data-toggle="tooltip" data-placement="top" title="'.__('MANUAL TIME','wphourly').'">'.__('M.T.','wphourly').'</span>';
	} else {
		$tr_type_class = 'wph-time-record-tracker-time bg-light';
		$tr_namual_time_badge = '';
	}

	echo '
	<div class="card wph-time-record-holder  '.$tr_type_class.' p-0" id="tr-id-'.$id.'">
		<div class="wph-time-record-thumbnail" style="background-image: url('.$screenshot.');">
			<div class="ml-1 paid-status">
				<span class="badge bg-danger text-white" data-toggle="tooltip" data-placement="top" title="" data-original-title="'. __('Time record is Paid In by your client', 'wphourly') .'">'.__('P.I.','wphourly').'</span>
				<span class="badge bg-success text-white" data-toggle="tooltip" data-placement="top" title="" data-original-title="'. __('Time record is not Paid Out to your employee', 'wphourly') .'">'.__('P.O.','wphourly').'</span>
				'.$tr_namual_time_badge;
				if($tr_type == 'tracker' && $expired == false ) {
					echo '
					<a href="'.$screenshot.'" target="_blank"><span class="badge bg-light" data-toggle="tooltip" data-placement="top" title="'.__('View Screenshot','wphourly').'" data-original-title="'.__('View Screenshot','wphourly').'"><i class="fa fa-link"></i></span></a>';
				}
			echo '
			</div>
			<span class="wph-tr-assignee" id="wph-tr-assignee-'.$assignee_id.'">
				<img alt="" src="'.$avatar_url.'" srcset="'.$avatar_url.' 2x" class="avatar avatar-35 photo p-1 bg-light" height="35" width="35" title="'.$asignee_nicename.'">
			</span>
		</div>
	</div>
	<div class="wph-time-record-data">
		<div class="">
			<label class="checkbox-inline" data-toggle="tooltip" data-placement="top" title="'.$title.'" data-original-title="'.$title.'">
	      		<input type="checkbox" name="selected-tr-'.$id.'" id="selected-tr-'.$id.'" value=""><small><small>'.$hours.' h - '.$created_at.'</small></small>
	      	</label>
		</div>
	</div>';

} else {
    echo '<div class="alert alert-danger">'. __('Could not load time record. Request is broken.', 'wphourly') .'</div>';
}
