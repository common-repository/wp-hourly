<?php

require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
require_once(ABSPATH . 'wp-admin/includes/screen.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

final class ProjectListTable extends WP_List_Table
{
    /**
     * @var int
     */
    private $clientId;

    public function __construct(int $clientId)
    {
        $GLOBALS['hook_suffix'] = false;
        parent::__construct([
            'singular' => 'project',
            'plural' => 'projects',
            'ajax' => false,
        ]);

        $this->clientId = $clientId;
    }

    public function display_topnav($which)
    {
        return '';
    }

    public function get_columns()
    {
        return [
            'title' => __('Project Title', 'wphourly'),
            'hours' => __('Hours', 'wphourly'),
        ];
    }

    public function column_title($project)
    {
        return sprintf('<a href="?project=%s">%s</a> <span style="color: silver">(id:%s)</span>',
            $project->ID,
            $project->title,
            $project->ID
        );
    }

    public function column_hours($project)
    {
        return ($project->hours ? $project->hours : 0);
    }

    public function prepare_items()
    {
        global $wpdb;

        $query = "
            SELECT
                p.title title,
                p.id ID,
                SUM(CAST(tr.hours AS DECIMAL(4, 1))) hours
            FROM {$wpdb->prefix}wph_projects p
            INNER JOIN {$wpdb->prefix}wph_tasks t ON (p.id = t.project_id)
            LEFT JOIN {$wpdb->prefix}wph_time_records tr ON (t.id = tr.task_id)
            WHERE p.client_id = {$this->clientId}
            GROUP BY p.id
        ";

        $this->items = $wpdb->get_results($query);
    }

    protected function handle_row_actions($item, $columnName, $primary)
    {
        return '';
    }
}
