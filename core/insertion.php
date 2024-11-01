<?php

add_action('wp_ajax_wph_live_edit', 'wphLiveEdit');

/**
 * POST PARAMS:
 * `entity`: string - one of project, task, timeRecord
 * `fields`: [string] - fields to save
 * [string]: any - individual field
 */
function wphLiveEdit()
{
    if (!current_user_can('administrator') && !(current_user_can('employee') && wphCurrentUserCanEditProject())) {
        die(json_encode([
            'success' => false,
            'errors' => [__('Action not allowed', 'wphourly')],
        ]));
    }

    $validEntities = ['project', 'task', 'timeRecord'];
    $entity = filter_input(INPUT_POST, 'entity', FILTER_SANITIZE_STRING);
    if (!in_array($entity, $validEntities)) {
        die(json_encode([
            'success' => false,
            'errors' => [__("Invalid entity `{$entity}`. Must be one of " . join(', ', $validEntities), 'wphourly')],
        ]));
    }

    $errors = [];
    $submittedFields = [];
    $fieldsToSave = filter_input(INPUT_POST, 'fields', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $ucEntity = ucfirst($entity);
    $id = null;
    foreach ($fieldsToSave as $fieldToSave) {
        if ($fieldToSave == 'id') {
            $id = wphGetLiveEditIdField();
            continue;
        }
        
        $ucFieldToSave = ucfirst(wphSnakeCaseToPascalCase($fieldToSave));
        $fieldCallback = "wphGetLiveEdit{$ucEntity}{$ucFieldToSave}Field";
        if (!function_exists($fieldCallback)) {
            $errors[] = __("Invalid field {$fieldToSave} for {$entity} entity", 'wphourly');
            continue;
        }
        $filteredValue = call_user_func($fieldCallback);
        if ($filteredValue['error']) {
            $errors[] = $filteredValue['error'];

            continue;
        }

        $submittedFields[$fieldToSave] = $filteredValue['value'];
    }

    if (!empty($errors)) {
        die(json_encode(['success' => false, 'errors' => $errors]));
    }

    $entityHandler = "wphLiveEditSave{$ucEntity}";
    $result = call_user_func($entityHandler, $id, $submittedFields);

    die(json_encode($result));
}

// general
function wphGetLiveEditIdField()
{
    return filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

function wphGetLiveEditTitleField()
{
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    if (!$title || !is_string($title)) {
        return ['error' => __('Title is required', 'wphourly')];
    }

    return ['value' => $title, 'error' => ''];
}

function wphGetLiveEditDescriptionField()
{
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    return [
        'value' => $description,
        'error' => '',
    ];
}

function wphIsDateValid($date)
{
    $dateArr = explode('-', $date);
    if (count($dateArr) == 3) {
        return checkdate($dateArr[1], $dateArr[2], $dateArr[0]);
    }

    return false;
}

// projects
function wphGetLiveEditProjectTitleField()
{
    return wphGetLiveEditTitleField();
}

function wphGetLiveEditProjectClientIdField()
{
    $clientId = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
    if (!$clientId) {
        return ['error' => __('Client is required', 'wphourly')];
    }

    if (!user_can($clientId, 'customer')) {
        return ['error' => __('Selected user is not a client', 'wphourly')];
    }

    return [
        'value' => $clientId,
        'error' => '',
    ];
}

function wphGetLiveEditProjectCodeField()
{
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);

    return [
        'value' => $code,
        'error' => '',
    ];
}

function wphGetLiveEditProjectExternalUrlField()
{

    if($_POST['external_url'] == '') {
        $externalUrl = '';
    }else{
        $externalUrl = filter_input(INPUT_POST, 'external_url', FILTER_VALIDATE_URL);    
        
        if (!$externalUrl) {
            return ['error' => __('Invalid external url', 'wphourly')];
        }    
    }

    return [
        'value' => $externalUrl,
        'error' => '',
    ];

}

function wphGetLiveEditProjectDescriptionField()
{
    return wphGetLiveEditDescriptionField();
}

function wphGetLiveEditProjectHourlyRateField()
{
    $hourlyRate = filter_input(INPUT_POST, 'hourlyRate', FILTER_SANITIZE_STRING);
    if ($hourlyRate && !filter_var($hourlyRate, FILTER_VALIDATE_INT)) {
        return ['error' => __('Hourly rate field must be a whole number', 'wphourly')];
    }

    return [
        'value' => $hourlyRate ? $hourlyRate : null,
        'error' => '',
    ];
}

function wphGetLiveEditProjectIsBillableField()
{
    $isBillable = filter_input(INPUT_POST, 'is_billable', FILTER_VALIDATE_INT);

    return [
        'value' => $isBillable ? 1 : 0,
        'error' => '',
    ];
}

function wphGetLiveEditProjectDeadlineField()
{
    $deadline = trim(filter_input(INPUT_POST, 'deadline', FILTER_SANITIZE_STRING));

    if (!$deadline) {
        return [
            'value' => null,
            'error' => '',
        ];
    }

    if (!wphIsDateValid($deadline)) {
        return [
            'error' => __('Deadline needs to be a valid date', 'wphourly'),
        ];
    }

    return [
        'value' => $deadline,
        'error' => '',
    ];
}

function wphGetLiveEditProjectEstimatedHoursField()
{
    $estimatedHours = filter_input(INPUT_POST, 'estimated_hours', FILTER_SANITIZE_NUMBER_INT);
    if ($estimatedHours && !filter_var($estimatedHours, FILTER_VALIDATE_INT)) {
        return ['error' => __('Estimated hours field must be a whole number', 'wphourly')];
    }

    return [
        'value' => (int) $estimatedHours,
        'error' => '',
    ];
}

function wphGetLiveEditProjectStatusField()
{
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
    if (!in_array($status, ['active', 'archived'])) {
        return ['error' => __('Status must be one of `active` or `archived`', 'wphourly')];
    }

    return [
        'value' => $status,
        'error' => '',
    ];
}

// tasks
function wphGetLiveEditTaskTitleField()
{
    return wphGetLiveEditTitleField();
}

function wphGetLiveEditTaskDescriptionField()
{
    return wphGetLiveEditDescriptionField();
}

function wphGetLiveEditTaskClientIdField()
{
    $clientId = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
    if (!$clientId) {
        return ['error' => __('Invalid client', 'wphourly')];
    }

    if (!user_can($clientId, 'customer')) {
        return ['error' => __('Selected user is not a client', 'wphourly')];
    }

    return [
        'value' => $clientId,
        'error' => '',
    ];
}

function wphGetLiveEditTaskAssigneeIdField()
{
    $assigneeId = filter_input(INPUT_POST, 'assignee_id', FILTER_VALIDATE_INT);
    if (!$assigneeId) {
        return ['error' => __('Invalid assignee', 'wphourly')];
    }

    if (!wphIsEmployeeUser($assigneeId)) {
        return ['error' => __('Selected user is not an employee', 'wphourly')];
    }

    return [
        'value' => $assigneeId,
        'error' => '',
    ];
}

function wphGetLiveEditTaskExternalUrlField()
{
    if($_POST['external_url'] == '') {
        $externalUrl = '';
    }else{
        $externalUrl = filter_input(INPUT_POST, 'external_url', FILTER_VALIDATE_URL);    
        
        if (!$externalUrl) {
            return ['error' => __('Invalid external url', 'wphourly')];
        }    
    }

    return [
        'value' => $externalUrl,
        'error' => '',
    ];
}

function wphGetLiveEditTaskProjectIdField()
{
    $projectId = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_STRING);
    if ($projectId && !filter_var($projectId, FILTER_VALIDATE_INT)) {
        return ['error' => __('Invalid project', 'wphourly')];
    }

    if (!$projectId) {
        return [
            'value' => null,
            'error' => '',
        ];
    }

    global $wpdb;
    $project = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
    if (!$project) {
        return ['error' => __('Invalid project', 'wphourly')];
    }

    return [
        'value' => (int) $projectId,
        'error' => '',
    ];
}

function wphGetLiveEditTaskStatusIdField()
{
    $statusId = filter_input(INPUT_POST, 'status_id', FILTER_VALIDATE_INT);
    if (!$statusId) {
        return ['error' => __('Invalid status', 'wphourly')];
    }

    global $wpdb;
    $statuses = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}wph_status");
    if (!in_array($statusId, $statuses)) {
        return ['error' => __('Invalid status', 'wphourly')];
    }

    return [
        'value' => $statusId,
        'error' => '',
    ];
}

function wphGetLiveEditTaskHourlyRateField()
{
    $hourlyRate = filter_input(INPUT_POST, 'hourlyRate', FILTER_SANITIZE_STRING);
    if ($hourlyRate && !filter_var($hourlyRate, FILTER_VALIDATE_INT)) {
        return ['error' => __('Hourly rate field must be a whole number', 'wphourly')];
    }

    return [
        'value' => $hourlyRate ? $hourlyRate : null,
        'error' => '',
    ];
}

function wphGetLiveEditTaskIsBillableField()
{
    $isBillable = filter_input(INPUT_POST, 'is_billable', FILTER_VALIDATE_INT);

    return [
        'value' => $isBillable ? 1 : 0,
        'error' => '',
    ];
}

function wphGetLiveEditTaskDeadlineField()
{
    $deadline = trim(filter_input(INPUT_POST, 'deadline', FILTER_SANITIZE_STRING));

    if (!$deadline) {
        return [
            'value' => null,
            'error' => '',
        ];
    }

    if (!wphIsDateValid($deadline)) {
        return [
            'error' => __('Deadline needs to be a valid date', 'wphourly'),
        ];
    }

    return [
        'value' => $deadline,
        'error' => '',
    ];
}

function wphGetLiveEditTaskEstimatedHoursField()
{
    $estimatedHours = filter_input(INPUT_POST, 'estimated_hours', FILTER_SANITIZE_NUMBER_INT);
    if ($estimatedHours && !filter_var($estimatedHours, FILTER_VALIDATE_INT)) {
        return ['error' => __('Estimated hours field must be a whole number', 'wphourly')];
    }

    return [
        'value' => (int) $estimatedHours,
        'error' => '',
    ];
}

// time records
function wphGetLiveEditTimeRecordTitleField()
{
    return wphGetLiveEditTitleField();
}

function wphGetLiveEditTimeRecordHoursField()
{
    $hours = filter_input(INPUT_POST, 'hours', FILTER_VALIDATE_FLOAT);
    if (!$hours || $hours < 0.1) {
        return ['error' => __('Hours field must be a float number. Minimum value is 0.1', 'wphourly')];
    }

    return [
        'value' => $hours,
        'error' => '',
    ];
}

function wphGetLiveEditTimeRecordAssigneeIdField()
{
    $assigneeId = filter_input(INPUT_POST, 'assignee_id', FILTER_VALIDATE_INT);
    if (!$assigneeId) {
        return ['error' => __('Invalid assignee', 'wphourly')];
    }

    if (!wphIsEmployeeUser($assigneeId)) {
        return ['error' => __('Selected user is not an employee', 'wphourly')];
    }

    return [
        'value' => $assigneeId,
        'error' => '',
    ];
}

function wphGetLiveEditTimeRecordTaskIdField()
{
    $taskId = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    if (!$taskId) {
        return ['error' => __('Invalid task', 'wphourly')];
    }

    global $wpdb;
    $task = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
    if (!$task) {
        return ['error' => __('Invalid task', 'wphourly')];
    }

    return [
        'value' => (int) $taskId,
        'error' => '',
    ];
}

function wphGetLiveEditTimeRecordTimestampField()
{
    $datetime = trim(filter_input(INPUT_POST, 'timestamp', FILTER_SANITIZE_STRING));
    if (!$datetime) {
        return ['error' => __('Invalid date for the time record', 'wphourly')];
    }

    try {
        $date = new \DateTime($datetime);
    } catch (\Exception $e) {
        return ['error' => __('Invalid date for the time record', 'wphourly')];
    }

    return [
        'value' => $date->getTimestamp(),
        'error' => '',
    ];
}

function wphGetLiveEditTimeRecordClientIdField()
{
    $clientId = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
    if (!$clientId) {
        return ['error' => __('Invalid client', 'wphourly')];
    }

    if (!user_can($clientId, 'customer')) {
        return ['error' => __('Selected user is not a client', 'wphourly')];
    }

    return [
        'value' => $clientId,
        'error' => '',
    ];
}

function wphGetLiveEditTimeRecordIsPaidField()
{
    $isPaid = filter_input(INPUT_POST, 'is_paid', FILTER_VALIDATE_INT);

    return [
        'value' => $isPaid ? 1 : 0,
        'error' => '',
    ];
}

function wphGetLiveEditTimeRecordIsPaidOutField()
{
    $isPaidOut = filter_input(INPUT_POST, 'is_paid_out', FILTER_VALIDATE_INT);

    return [
        'value' => $isPaidOut ? 1 : 0,
        'error' => '',
    ];
}

function wphGetLiveEditTimeRecordTrTypeField()
{
    $trType = filter_input(INPUT_POST, 'tr_type', FILTER_SANITIZE_STRING);

    if ($trType != 'manual') {
        return ['error' => __('Invalid time record type', 'wphourly')];
    }

    return [
        'value' => $trType,
        'error' => '',
    ];
}


// save handlers
function wphLiveEditValidateRequiredFields($requiredFields, $providedFields)
{
    $errors = [];
    foreach ($requiredFields as $requiredKey => $requiredError) {
        if (!in_array($requiredKey, $providedFields)) {
            $errors[] = $requiredError;
        }
    }

    if (count($errors)) {
        return $errors;
    }

    return true;
}

function wphLiveEditSaveProject($id, $parameters)
{
    if ($id) {
        return wphLiveEditSaveProjectUpdate($id, $parameters);
    }

    return wphLiveEditSaveProjectNew($parameters);
}

function wphLiveEditSaveProjectUpdate($id, $parameters)
{
    global $wpdb;
    $project = $wpdb->get_var("SELECT 1 FROM {$wpdb->prefix}wph_projects WHERE id = {$id}");
    if (!$project) {
        return [
            'success' => false,
            'errors' => [__('Invalid project to update', 'wphourly')],
        ];
    }

    $oldProject = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_projects WHERE id = {$id}");
    $updateResult = $wpdb->update(
        "{$wpdb->prefix}wph_projects",
        $parameters,
        ['id' => $id]
    );

    if ($updateResult === false) {
        return [
            'success' => false,
            'errors' => [__('There was an error while updating the project', 'wphourly')],
        ];
    }

    // only update task billable if project is non billable (cannot have billable tasks under non billable projects)
    $oldBillable = $oldProject->is_billable;
    if (array_key_exists('is_billable', $parameters) && !$parameters['is_billable'] && $oldBillable != $parameters['is_billable']) {
        $tasks = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}wph_tasks WHERE project_id = {$id} AND is_billable = 1");
        foreach ($tasks as $task) {
            $wpdb->update("{$wpdb->prefix}wph_tasks", ['is_billable' => 0], ['id' => $task]);
            wphHandleTaskBillableUpdate($task, false);
        }
    }

    // also do do the updates related to hourly rate change (unpaid time records)
    $tasks = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}wph_tasks WHERE project_id = {$id} AND is_billable = 1");
    foreach ($tasks as $taskId) {
        $hourlyRate = wphGetTaskHourlyRate($taskId);

        if ($hourlyRate) {
            $query = "
                UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
                INNER JOIN {$wpdb->prefix}wph_tasks t ON (t.id = utr.taskId)
                SET utr.hourlyRate = {$hourlyRate}
                WHERE
                    utr.projectId = {$id} AND
                    t.hourlyRate IS NULL
            ";
            $wpdb->query($query);
        }
    }

    return ['success' => true, 'errors' => []];
}

function wphLiveEditSaveProjectNew($parameters)
{
    $requiredFields = [
        'title' => __('Title is required', 'wphourly'),
        'client_id' => __('Client is required', 'wphourly'),
        'status' => __('Status is required', 'wphourly'),
    ];
    $validationResult = wphLiveEditValidateRequiredFields($requiredFields, array_keys($parameters));
    if ($validationResult !== true) {
        return [
            'success' => false,
            'errors' => $validationResult,
        ];
    }

    global $wpdb;
    $insertResult = $wpdb->insert(
        "{$wpdb->prefix}wph_projects",
        $parameters
    );

    $project_id = $wpdb->insert_id;


    $insertFollowers = apply_filters('insert-follower', $parameters['client_id'], $project_id);
    $insertFollower = apply_filters('insert-follower', get_current_user_id(), $project_id);



    if (!$insertResult) {
        return [
            'success' => false,
            'errors' => [__('There was an error while adding the project', 'wphourly')],
        ];
    }

    if(!$insertFollowers) {
        return [
            'success' => false,
            'errors' => $insertFollowers,
        ];
    }

    return [
        'id' => $project_id,
        'success' => true,
        'errors' => [],
    ];
}

function wphLiveEditSaveTask($id, $parameters)
{
    if ($id) {
        return wphLiveEditSaveTaskUpdate($id, $parameters);
    }

    return wphLiveEditSaveTaskNew($parameters);
}

function wphLiveEditSaveTaskUpdate($id, $parameters)
{
    global $wpdb;
    $task = $wpdb->get_var("SELECT 1 FROM {$wpdb->prefix}wph_tasks WHERE id = {$id}");
    if (!$task) {
        return [
            'success' => false,
            'errors' => [__('Invalid task to update', 'wphourly')],
        ];
    }

    $errors = [];
    $isProjectDefined = array_key_exists('project_id', $parameters);
    $isClientDefined = array_key_exists('client_id', $parameters);
//    if ($isProjectDefined || $isClientDefined) {
//        if ($isProjectDefined && $isClientDefined) {
//            $projectClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_projects WHERE id = {$parameters['project_id']}}");
//            if ($projectClient != $parameters['client_id']) {
//                $errors[] = __('Selected project is not owned by the specified client', 'wphourly');
//            }
//        } elseif ($isProjectDefined) {
//            $projectClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_projects WHERE id = {$parameters['project_id']}}");
//            $taskClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$id}}");
//            if ($projectClient != $taskClient) {
//                $errors[] = __('Selected project is not owned by the specified client', 'wphourly');
//            }
//        } elseif ($isClientDefined) {
//            $projectId = $wpdb->get_var("SELECT project_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$id}");
//            if ($projectId) {
//                $projectClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
//                if ($projectClient != $parameters['client_id']) {
//                    $errors[] = __('Selected project is not owned by the specified client', 'wphourly');
//                }
//            }
//        }
//    }

    if ($isProjectDefined) {
        wphChangeTaskProject($id, $parameters['project_id']);
    }

    $updateBillable = false;
    if (array_key_exists('is_billable', $parameters)) {
        if ($isProjectDefined) {
            $projectId = $parameters['project_id'];
        } else {
            $projectId = $wpdb->query("SELECT project_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$id}");
        }

        if ($projectId) {
            $projectBillable = $wpdb->query("SELECT is_billable FROM {$wpdb->prefix}wph_projects WHERE id = {$projectId}");
            if (!$projectBillable && $parameters['is_billable']) {
                $errors[] = __('Cannot mark a task as billable if the project is not billable', 'wphourly');
            }
        }
        $updateBillable = true;
    }

    if (count($errors)) {
        return [
            'success' => false,
            'errors' => $errors,
        ];
    }

    $oldTask = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_tasks WHERE id = {$id}");
    $updateResult = $wpdb->update(
        "{$wpdb->prefix}wph_tasks",
        $parameters,
        ['id' => $id]
    );

    if ($updateResult === false) {
        return [
            'success' => false,
            'errors' => [__('There was an error while updating the task', 'wphourly')],
        ];
    }

    if ($updateBillable && $oldTask->is_billable != $parameters['is_billable']) {
        wphHandleTaskBillableUpdate($id, (bool) $parameters['is_billable']);
    }

    // also do something for hourly rate change (unpaid time records)
    if (isset($parameters['hourlyRate']) && $oldTask->hourlyRate != $parameters['hourlyRate']) {
        $hourlyRate = wphGetTaskHourlyRate($id);
        $query = "
            UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
            SET utr.hourlyRate = {$hourlyRate}
            WHERE utr.taskId = {$id}
        ";
        $wpdb->query($query);
    }

    return ['success' => true, 'errors' => []];
}

function wphLiveEditSaveTaskNew($parameters)
{
    $requiredFields = [
        'title' => __('Title is required', 'wphourly'),
        'client_id' => __('Client is required', 'wphourly'),
        'status_id' => __('Status is required', 'wphourly'),
    ];
    $validationResult = wphLiveEditValidateRequiredFields($requiredFields, array_keys($parameters));
    if ($validationResult !== true) {
        return [
            'success' => false,
            'errors' => $validationResult,
        ];
    }

    global $wpdb;
    $insertResult = $wpdb->insert(
        "{$wpdb->prefix}wph_tasks",
        $parameters
    );


    $insertFollowers = apply_filters('insert-follower', $parameters['assignee_id'], $parameters['project_id']);


    if (!$insertFollowers) {
        return [
            'success' => false,
            'errors' => [__('There was an error while adding the followers', 'wphourly')],
        ];
    }

    if (!$insertResult) {
        return [
            'success' => false,
            'errors' => [__('There was an error while adding the ysdk', 'wphourly')],
        ];
    }

    return [
        'id' => $wpdb->insert_id,
        'success' => true,
        'errors' => [],
    ];
}

function wphLiveEditSaveTimeRecord($id, $parameters)
{
    if ($id) {
        return wphLiveEditSaveTimeRecordUpdate($id, $parameters);
    }

    return wphLiveEditSaveTimeRecordNew($parameters);
}

function wphLiveEditSaveTimeRecordUpdate($id, $parameters)
{
    global $wpdb;
    $timeRecord = $wpdb->get_var("SELECT 1 FROM {$wpdb->prefix}wph_time_records WHERE id = {$id}");
    if (!$timeRecord) {
        return [
            'success' => false,
            'errors' => [__('Invalid time record to update', 'wphourly')],
        ];
    }

    $errors = [];
    $isTaskDefined = array_key_exists('task_id', $parameters);
    $isClientDefined = array_key_exists('client_id', $parameters);
    if ($isTaskDefined || $isClientDefined) {
        if ($isTaskDefined && $isClientDefined) {
            $taskClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$parameters['task_id']}}");
            if ($taskClient != $parameters['client_id']) {
                $errors[] = __('Selected task is not owned by the specified client', 'wphourly');
            }
        } elseif ($isTaskDefined) {
            $taskClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$parameters['task_id']}}");
            $timeRecordClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_time_records WHERE id = {$id}}");
            if ($taskClient != $timeRecordClient) {
                $errors[] = __('Selected project is not owned by the specified client', 'wphourly');
            }
        } elseif ($isClientDefined) {
            $taskId = $wpdb->get_var("SELECT task_id FROM {$wpdb->prefix}wph_time_records WHERE id = {$id}");
            if ($taskId) {
                $taskClient = $wpdb->get_var("SELECT client_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$taskId}");
                if ($taskClient != $parameters['client_id']) {
                    $errors[] = __('Selected task is not owned by the specified client', 'wphourly');
                }
            }
        }
    }

    if (count($errors)) {
        return [
            'success' => false,
            'errors' => $errors,
        ];
    }

    $updateResult = $wpdb->update(
        "{$wpdb->prefix}wph_tasks",
        $parameters,
        ['id' => $id]
    );

    if ($updateResult === false) {
        return [
            'success' => false,
            'errors' => [__('There was an error while updating the task', 'wphourly')],
        ];
    }

    return ['success' => true, 'errors' => []];
}

function wphLiveEditSaveTimeRecordNew($parameters)
{
    $requiredFields = [
        'title' => __('Title is required', 'wphourly'),
        'client_id' => __('Client is required', 'wphourly'),
        'assignee_id' => __('Assignee is required', 'wphourly'),
        'hours' => __('Amount of hours is required', 'wphourly'),
        'timestamp' => __('Time record date is required', 'wphourly'),
    ];
    $validationResult = wphLiveEditValidateRequiredFields($requiredFields, array_keys($parameters));
    if ($validationResult !== true) {
        return [
            'success' => false,
            'errors' => $validationResult,
        ];
    }

    global $wpdb;
    $insertResult = $wpdb->insert(
        "{$wpdb->prefix}wph_time_records",
        $parameters
    );

    if (!$insertResult) {
        return [
            'success' => false,
            'errors' => [__('There was an error while adding the time record', 'wphourly')],
        ];
    }

    do_action('wph-after-time-record-created', $wpdb->insert_id);

    return [
        'id' => $wpdb->insert_id,
        'success' => true,
        'errors' => [],
    ];
}

add_action('wph-after-time-record-created', 'wphGenerateTimeRecordUnpaidEntry');
function wphGenerateTimeRecordUnpaidEntry($timeRecordId)
{
    global $wpdb;
    $timeRecord = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_time_records WHERE id = {$timeRecordId}");
    if (!$timeRecord || $timeRecord->is_paid) {
        return;
    }

    if ($timeRecord->task_id && !wphIsTaskBillable($timeRecord->task_id)) {
        return;
    }

    $project = null;
    if ($timeRecord->task_id) {
        $hourlyRate = wphGetTaskHourlyRate($timeRecord->task_id);
        $project = $wpdb->get_var("SELECT project_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$timeRecord->task_id}");
    } else {
        $hourlyRate = wphGetClientHourlyRate($timeRecord->client_id);
    }
    $wpdb->insert(
        "{$wpdb->prefix}wph_unpaid_time_records",
        [
            'recordId' => $timeRecordId,
            'taskId' => $timeRecord->task_id,
            'projectId' => $project,
            'clientId' => $timeRecord->client_id,
            'hours' => $timeRecord->hours,
            'hourlyRate' => $hourlyRate,
            'createdAt' => (new \DateTime("@{$timeRecord->timestamp}"))->format('Y-m-d H:i:s'),
        ]
    );
}

function wphUpdateLiveEditTimeRecordUnpaidEntry($timeRecordId)
{
    global $wpdb;
    $timeRecord = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wph_time_records WHERE id = {$timeRecordId}");
    if (!$timeRecord) {
        return;
    }

    if ($timeRecord->is_paid) {
        $wpdb->delete("{$wpdb->prefix}wph_unpaid_time_records", ['recordId' => $timeRecordId]);

        return;
    }

    $project = null;
    if ($timeRecord->task_id) {
        $hourlyRate = wphGetTaskHourlyRate($timeRecord->task_id);
        $project = $wpdb->get_var("SELECT project_id FROM {$wpdb->prefix}wph_tasks WHERE id = {$timeRecord->task_id}");
    } else {
        $hourlyRate = wphGetClientHourlyRate($timeRecord->client_id);
    }
    $wpdb->update(
        "{$wpdb->prefix}wph_unpaid_time_records",
        [
            'taskId' => $timeRecord->task_id,
            'projectId' => $project,
            'clientId' => $timeRecord->client_id,
            'hours' => $timeRecord->hours,
            'hourlyRate' => $hourlyRate,
            'createdAt' => (new \DateTime("@{$timeRecord->timestamp}"))->format('Y-m-d H:i:s'),
        ],
        [
            'recordId' => $timeRecordId,
        ]
    );
}
