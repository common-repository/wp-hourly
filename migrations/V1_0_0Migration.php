<?php

final class V1_0_0Migration implements MigrationInterface
{
    const VERSION = '1.0.0';

    public function run()
    {
        global $wpdb;

        $query = " 
            CREATE OR REPLACE VIEW {$wpdb->prefix}unpaid_hours AS
            SELECT
                timeRecord.id recordId,
                unpaid.meta_key metakey,
                unpaid.meta_value metavalue,
                task.id taskId,
                task.post_title task,
                hours.meta_value hours,
                project.id projectId,
                project.post_title project,
                client.id clientId,
                client.user_nicename customer,
                IF (timeRecordDate.meta_value, timeRecordDate.meta_value, UNIX_TIMESTAMP(timeRecord.post_date)) `date`
            FROM {$wpdb->prefix}posts timeRecord
            INNER JOIN {$wpdb->prefix}postmeta unpaid ON (unpaid.post_id = timeRecord.id AND unpaid.meta_key = 'is_paid' and unpaid.meta_value = 0)
            INNER JOIN {$wpdb->prefix}postmeta hours ON (hours.post_id = timeRecord.id AND hours.meta_key = 'tr_hours' AND hours.meta_value > 0)
            INNER JOIN {$wpdb->prefix}postmeta timeRecordClient ON (timeRecordClient.post_id = timeRecord.id AND timeRecordClient.meta_key = 'time_record_client')
            INNER JOIN {$wpdb->prefix}users client ON (client.id = timeRecordClient.meta_value)
            LEFT JOIN {$wpdb->prefix}postmeta timeRecordTask ON (timeRecordTask.post_id = timeRecord.id AND timeRecordTask.meta_key = 'time_record_task')
            LEFT JOIN {$wpdb->prefix}posts task ON (task.id = timeRecordTask.meta_value)
            LEFT JOIN {$wpdb->prefix}postmeta taskProject ON (taskProject.post_id = task.id AND taskProject.meta_key = 'task_project')
            LEFT JOIN {$wpdb->prefix}posts project ON (project.id = taskProject.meta_value)
            LEFT JOIN {$wpdb->prefix}postmeta timeRecordDate ON (timeRecordDate.post_id = timeRecord.id AND timeRecordDate.meta_key = 'tr_timestamp')
            WHERE
                timeRecord.post_type = 'time_record'
        ";
        $wpdb->query($query);

        $query = "
            CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wph_status (
                id int(2) unsigned NOT NULL AUTO_INCREMENT,
                `status` varchar(255) NOT NULL DEFAULT '',
                description varchar(255) DEFAULT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
        ";
        $wpdb->query($query);

        $existingStatus = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wph_status WHERE `status` = 'draft'");
        if (!$existingStatus) {
            $statuses = [
                ["status" => "draft"],
                ["status" => "ready"],
                ["status" => "closed"],
                ["status" => "completed"],
            ];

            array_map(function ($status) use ($wpdb) {
                $wpdb->insert("{$wpdb->prefix}wph_status", $status);
            }, $statuses);
        }

        $query = "
            CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wph_projects (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL DEFAULT '',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                client_id bigint(20) unsigned NOT NULL,
                code varchar(255) DEFAULT NULL,
                description longtext,
                status_id int(2) unsigned NOT NULL,
                PRIMARY KEY (id),
                KEY client_id (client_id),
                KEY status_id (status_id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
        ";
        $wpdb->query($query);

        $query = "
            CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wph_tasks (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL DEFAULT '',
                description longtext,
                project_id int(11) unsigned DEFAULT NULL,
                client_id bigint(20) unsigned DEFAULT NULL,
                assignee_id bigint(20) unsigned DEFAULT NULL,
                status_id int(2) unsigned NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY (client_id),
                KEY (status_id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
        ";
        $wpdb->query($query);

        $query = "
            CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wph_time_records (
                id int(20) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL DEFAULT '',
                hours double(4,2) NOT NULL,
                assignee_id bigint(20) unsigned DEFAULT NULL,
                task_id int(11) unsigned DEFAULT NULL,
                timestamp bigint(20) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                is_paid tinyint(1) DEFAULT '0',
                screenshot varchar(255) DEFAULT NULL,
                client_id bigint(20) unsigned DEFAULT NULL,
                PRIMARY KEY (id),
                KEY assignee_id (assignee_id),
                KEY task_id (task_id),
                KEY client_id (client_id)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
        ";
        $wpdb->query($query);
    }

    public function getVersionNumber()
    {
        return self::VERSION;
    }
}
