<?php

final class V1_2_0Migration implements MigrationInterface
{
    const VERSION = '1.2.0';

    public function run()
    {
        global $wpdb;

        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wph_status");
        $defaultTaskStatuses = ['backlog' => 'Backlog', 'todo' => 'To Do', 'done' => 'Done'];
        foreach ($defaultTaskStatuses as $status => $description) {
            $wpdb->insert(
                "{$wpdb->prefix}wph_status",
                [
                    'status' => $status,
                    'description' => $description,
                ]
            );
        }

        // tasks
        $query = "
            UPDATE {$wpdb->prefix}wph_tasks tasks
            SET tasks.status_id = CASE 
                WHEN tasks.status_id = 3 OR tasks.status_id = 4 THEN 3
                ELSE 2
            END
        ";
        $wpdb->query($query);

        $query = "
            ALTER TABLE {$wpdb->prefix}wph_tasks
            ADD `wp_post_id` BIGINT(20) UNSIGNED DEFAULT NULL,
            ADD `hourlyRate` INT(11) UNSIGNED DEFAULT NULL,
            ADD `is_billable` TINYINT(1) UNSIGNED DEFAULT 1,
            ADD `estimated_hours` INT(11) UNSIGNED DEFAULT NULL,
            ADD `external_url` TEXT DEFAULT NULL,
            ADD `deadline` DATE DEFAULT NULL,
            ADD KEY `wp_post_id` (`wp_post_id`)
        ";
        $wpdb->query($query);

        $query = "
            UPDATE {$wpdb->prefix}wph_tasks tasks
            INNER JOIN {$wpdb->prefix}postmeta pm ON (pm.meta_value = tasks.id AND pm.meta_key = 'backupId')
            SET tasks.wp_post_id = pm.post_id
        ";
        $wpdb->query($query);

        $query = "
            UPDATE {$wpdb->prefix}wph_tasks tasks
            INNER JOIN {$wpdb->prefix}postmeta product ON (product.post_id = tasks.wp_post_id AND product.meta_key = 'hourly_rate_product')
            INNER JOIN {$wpdb->prefix}postmeta pm ON (pm.post_id = product.post_id AND pm.meta_key = '_regular_price')
            SET tasks.hourlyRate = pm.meta_value
        ";
        $wpdb->query($query);

        // projects
        $query = "
            ALTER TABLE {$wpdb->prefix}wph_projects
            ADD `wp_post_id` BIGINT(20) UNSIGNED DEFAULT NULL,
            ADD `hourlyRate` INT(11) UNSIGNED DEFAULT NULL,
            ADD `is_billable` TINYINT(1) UNSIGNED DEFAULT 1,
            ADD `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
            ADD `estimated_hours` INT(11) UNSIGNED DEFAULT NULL,
            ADD `external_url` TEXT DEFAULT NULL,
            ADD `deadline` DATE DEFAULT NULL,
            ADD KEY `wp_post_id` (`wp_post_id`)
        ";
        $wpdb->query($query);

        $query = "
            UPDATE {$wpdb->prefix}wph_projects projects
            SET projects.status = CASE 
                WHEN projects.status_id = 3 OR projects.status_id = 4 THEN 'archived'
                ELSE 'active'
            END
        ";
        $wpdb->query($query);

        $wpdb->query("ALTER TABLE {$wpdb->prefix}wph_projects DROP COLUMN status_id");

        $query = "
            UPDATE {$wpdb->prefix}wph_projects projects
            INNER JOIN {$wpdb->prefix}postmeta pm ON (pm.meta_value = projects.id AND pm.meta_key = 'backupId')
            SET projects.wp_post_id = pm.post_id
        ";
        $wpdb->query($query);

        $query = "
            UPDATE {$wpdb->prefix}wph_projects projects
            INNER JOIN {$wpdb->prefix}postmeta product ON (product.post_id = projects.wp_post_id AND product.meta_key = 'hourly_rate_product')
            INNER JOIN {$wpdb->prefix}postmeta pm ON (pm.post_id = product.post_id AND pm.meta_key = '_regular_price')
            SET projects.hourlyRate = pm.meta_value
        ";
        $wpdb->query($query);

        // time records
        $query = "
            ALTER TABLE {$wpdb->prefix}wph_time_records
            ADD `wp_post_id` BIGINT(20) UNSIGNED DEFAULT NULL,
            ADD `is_paid_out` TINYINT(1) UNSIGNED DEFAULT 0,
            ADD `tr_type` TINYTEXT,
            ADD KEY `wp_post_id` (`wp_post_id`)
        ";
        $wpdb->query($query);

        $query = "
            UPDATE {$wpdb->prefix}wph_time_records timeRecords
            INNER JOIN {$wpdb->prefix}postmeta pm ON (pm.meta_value = timeRecords.id AND pm.meta_key = 'backupId')
            SET timeRecords.wp_post_id = pm.post_id
        ";
        $wpdb->query($query);

        // assume that records without screenshot (expired) but 0.1 hours are added through the tracker
        $query = "
            UPDATE {$wpdb->prefix}wph_time_records tr
            SET tr_type = IF(tr.screenshot IS NOT NULL OR (tr.screenshot IS NULL AND tr.hours > 0.1), 'tracker', 'manual')
        ";
        $wpdb->query($query);

        $query = "
            UPDATE {$wpdb->prefix}wph_time_records tr
            INNER JOIN {$wpdb->prefix}postmeta po ON (po.post_id = tr.wp_post_id AND po.meta_key = 'is_paid_out')
            SET tr.is_paid_out = IF(po.meta_value, po.meta_value, 0)
        ";
        $wpdb->query($query);


        $hasUnpaidTable = !is_null($wpdb->get_var("show tables like '{$wpdb->prefix}wph_unpaid_time_records'"));

        if (!$hasUnpaidTable) {
            $query = "
                CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wph_unpaid_time_records (
                    id int(11) unsigned NOT NULL AUTO_INCREMENT,
                    recordId int(11) unsigned NOT NULL,
                    taskId int(11) unsigned DEFAULT NULL,
                    projectId int(11) unsigned DEFAULT NULL,
                    clientId int(11) unsigned NOT NULL,
                    hours decimal(3,1) NOT NULL,
                    productId int(11) DEFAULT NULL,
                    hourlyRate int(11) unsigned DEFAULT NULL,
                    isProcessing tinyint(1) DEFAULT 0,
                    createdAt datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
            ";
            $wpdb->query($query);

            $query = "
                INSERT INTO {$wpdb->prefix}wph_unpaid_time_records (
                    recordId,
                    taskId,
                    projectId,
                    clientId,
                    hours,
                    createdAt
                )
                SELECT
                    timeRecord.id,
                    wt.id,
                    wp.id,
                    client.id,
                    hours.meta_value hours,
                    IF (timeRecordDate.meta_value, FROM_UNIXTIME(timeRecordDate.meta_value), timeRecord.post_date)
                FROM {$wpdb->prefix}posts timeRecord
                INNER JOIN {$wpdb->prefix}postmeta unpaid ON (unpaid.post_id = timeRecord.id AND unpaid.meta_key = 'is_paid' and unpaid.meta_value = 0)
                INNER JOIN {$wpdb->prefix}postmeta hours ON (hours.post_id = timeRecord.id AND hours.meta_key = 'tr_hours' AND hours.meta_value > 0)
                INNER JOIN {$wpdb->prefix}postmeta timeRecordClient ON (timeRecordClient.post_id = timeRecord.id AND timeRecordClient.meta_key = 'time_record_client')
                INNER JOIN {$wpdb->prefix}users client ON (client.id = timeRecordClient.meta_value)
                LEFT JOIN {$wpdb->prefix}postmeta timeRecordTask ON (timeRecordTask.post_id = timeRecord.id AND timeRecordTask.meta_key = 'time_record_task')
                LEFT JOIN {$wpdb->prefix}posts task ON (task.id = timeRecordTask.meta_value)
                LEFT JOIN {$wpdb->prefix}wph_tasks wt ON (task.id = wt.wp_post_id)
                LEFT JOIN {$wpdb->prefix}postmeta taskProject ON (taskProject.post_id = task.id AND taskProject.meta_key = 'task_project')
                LEFT JOIN {$wpdb->prefix}posts project ON (project.id = taskProject.meta_value)
                LEFT JOIN {$wpdb->prefix}wph_projects wp ON (project.id = wp.wp_post_id)
                LEFT JOIN {$wpdb->prefix}postmeta timeRecordDate ON (timeRecordDate.post_id = timeRecord.id AND timeRecordDate.meta_key = 'tr_timestamp')
                WHERE
                    timeRecord.post_type = 'time_record'
            ";
            $wpdb->query($query);

            // update the product id
            $query = "
                UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
                INNER JOIN {$wpdb->prefix}usermeta clientProduct ON (utr.clientId = clientProduct.user_id AND clientProduct.meta_key = 'customer_woo_prod_id')
                LEFT JOIN {$wpdb->prefix}postmeta taskProduct ON (utr.taskId = taskProduct.post_id AND taskProduct.meta_key = 'hourly_rate_product')
                LEFT JOIN {$wpdb->prefix}postmeta projectProduct ON (utr.projectId = projectProduct.post_id AND projectProduct.meta_key = 'hourly_rate_product')
                LEFT JOIN {$wpdb->prefix}options options ON (options.option_name = 'wph_global_prod_id')
                SET
                    utr.productId = CASE
                        WHEN taskProduct.meta_id AND taskProduct.meta_value != '' THEN taskProduct.meta_value
                        WHEN projectProduct.meta_id AND projectProduct.meta_value != '' THEN projectProduct.meta_value
                        WHEN clientProduct.umeta_id AND clientProduct.meta_value != '' THEN clientProduct.meta_value
                        WHEN options.option_id AND options.option_value != '' THEN options.option_value
                        ELSE NULL
                    END
                WHERE utr.productId IS NULL
            ";
            $wpdb->query($query);

            $query = "
                UPDATE {$wpdb->prefix}wph_unpaid_time_records timeRecords
                INNER JOIN {$wpdb->prefix}postmeta product ON (product.post_id = timeRecords.productId AND product.meta_key = 'hourly_rate_product')
                INNER JOIN {$wpdb->prefix}postmeta pm ON (pm.post_id = product.post_id AND pm.meta_key = '_regular_price')
                SET timeRecords.hourlyRate = pm.meta_value
            ";
            $wpdb->query($query);

            $wpdb->query("ALTER TABLE {$wpdb->prefix}wph_unpaid_time_records DROP COLUMN productId");

            if (wphHasWooCommerce()) {
                // update processing status
                $incompleteOrders = get_posts(apply_filters('woocommerce_my_account_my_orders_query', array(
                    'numberposts' => -1,
                    'post_type' => wc_get_order_types('view-orders'),
                    'post_status' => array('wc-pending', 'wc-processing', 'wc-on-hold')
                )));

                $processingTimeRecords = [];
                foreach ($incompleteOrders as $incompleteOrder) {
                    if (!($orderRecordsJson = get_post_meta($incompleteOrder->ID, '_time_records_paid', true)) || !($orderRecords = json_decode($orderRecordsJson))) {
                        continue;
                    }

                    $processingTimeRecords += array_map(function ($timeRecord) {
                        return $timeRecord->recordId;
                    }, $orderRecords);
                }
                if (!empty($processingTimeRecords)) {
                    $trs = implode(',', $processingTimeRecords);
                    $query = "
                        UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
                        SET utr.isProcessing = 1
                        WHERE utr.recordId IN ({$trs})
                    ";
                    $wpdb->query($query);
                }
            }

            // switch to hourly rate
            $query = "
                INSERT INTO {$wpdb->prefix}postmeta (
                    post_id,
                    meta_key,
                    meta_value
                )
                SELECT
                    timeRecord.id,
                    'hourly_rate',
                    productPrice.meta_value
                FROM {$wpdb->prefix}posts timeRecord
                INNER JOIN {$wpdb->prefix}postmeta product ON (product.post_id = timeRecord.id AND product.meta_key = 'hourly_rate_product')
                INNER JOIN {$wpdb->prefix}postmeta productPrice ON (productPrice.post_id = product.meta_value AND productPrice.meta_key = '_regular_price') 
            ";
            $wpdb->query($query);

            // switch unpaid time records `recordId` value from `wp_post_id` to `id`
            $query = "
                UPDATE {$wpdb->prefix}wph_unpaid_time_records utr
                INNER JOIN {$wpdb->prefix}wph_time_records tr ON (tr.wp_post_id = utr.recordId)
                SET utr.recordId = tr.id
            ";
            $wpdb->query($query);

            // get rid of `wp_post_id` as it is now safe
            $query = "
                ALTER TABLE {$wpdb->prefix}wph_projects
                DROP INDEX `wp_post_id`
                DROP `wp_post_id`
            ";
            $wpdb->query($query);
            $query = "
                ALTER TABLE {$wpdb->prefix}wph_tasks
                DROP INDEX `wp_post_id`
                DROP `wp_post_id`
            ";
            $wpdb->query($query);
            $query = "
                ALTER TABLE {$wpdb->prefix}wph_time_records
                DROP INDEX `wp_post_id`
                DROP `wp_post_id`
            ";
            $wpdb->query($query);
        }
    }

    public function getVersionNumber()
    {
        return self::VERSION;
    }
}
