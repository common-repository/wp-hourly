<?php


DEFINE('WPH_TASK_STATUS_BACKLOG', 1);
DEFINE('WPH_TASK_STATUS_TODO', 2);
DEFINE('WPH_TASK_STATUS_DONE', 3);

function wphHandleTaskBillableUpdate($taskId, $isBillable)
{
    if ($isBillable) {
        // insert into unpaid hours table
        wphInsertUnpaidTimeRecordsByTaskId($taskId);
    } else {
        // remove all from unpaid hours table
        wphRemoveUnpaidTimeRecordsByTaskId($taskId);
    }
}

function wphRemoveUnpaidTimeRecordsByTaskId($taskId)
{
    global $wpdb;

    $wpdb->query("DELETE FROM {$wpdb->prefix}wph_unpaid_time_records WHERE taskId = {$taskId} AND isProcessing = 0");
}

function wphInsertUnpaidTimeRecordsByTaskId($taskId)
{
    global $wpdb;

    $globalHourlyRate = wphGetDefaultHourlyRate();
    $query = "
        INSERT INTO {$wpdb->prefix}wph_unpaid_time_records
        (
            recordId,
            taskId,
            projectId,
            clientId,
            hours,
            isProcessing,
            createdAt,
            hourlyRate
        )
        SELECT
            tr.id recordId,
            task.id taskId,
            project.id projectId,
            client.id clientId,
            tr.hours hours,
            0 isProcessing,
            tr.created_at createdAt,
            CASE
                WHEN task.hourlyRate IS NOT NULL AND task.hourlyRate != '' THEN task.hourlyRate
                WHEN project.hourlyRate IS NOT NULL AND project.hourlyRate != '' THEN project.hourlyRate
                WHEN chr.meta_value IS NOT NULL AND chr.meta_value != '' THEN chr.meta_value
                ELSE {$globalHourlyRate}
            END hourlyRate
        FROM {$wpdb->prefix}wph_time_records tr
        INNER JOIN {$wpdb->prefix}wph_tasks task ON (tr.task_id = task.id)
        LEFT JOIN {$wpdb->prefix}wph_projects project ON (project.id = task.project_id)
        LEFT JOIN {$wpdb->prefix}wph_unpaid_time_records utr ON (tr.wp_post_id = utr.recordId)
        INNER JOIN {$wpdb->prefix}users client ON (client.id = COALESCE(tr.client_id, task.client_id, project.client_id))
        LEFT JOIN {$wpdb->prefix}usermeta chr ON (client.id = chr.user_id AND chr.meta_key = 'wph_hourly_rate')
        WHERE
            task.id = {$taskId} AND
            tr.is_paid = 0 AND
            utr.id IS NULL
    ";
    $wpdb->query($query);
}
