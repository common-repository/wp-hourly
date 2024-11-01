<?php

require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
require_once(ABSPATH . 'wp-admin/includes/screen.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class TaskListTable extends WP_List_Table
{
    /** ************************************************************************
     * HACK Custom Function - Needed for save / edit CPT
     ***************************************************************************/

    private $projectId;

    public function __construct($projectId)
    {
        $GLOBALS['hook_suffix'] = false;
        parent::__construct([
            'singular' => 'task',
            'plural' => 'tasks',
            'ajax' => false,
        ]);

        $this->projectId = $projectId;
    }

    function display_tablenav($which) {
        return '';
    }



    /**************************************************************************
     * REQUIRED. Query the tasks from the DB.
     ***************************************************************************/

    function get_tasks($projectId)
    {
        global $wpdb;
        $query_items = [];

        $tasks = $wpdb->get_results("
            SELECT *
            FROM {$wpdb->prefix}wph_tasks
            WHERE project_id = {$projectId}
        ");
        foreach ($tasks as $task) {
            $query_items[] = [
                'ID' => $task->id,
                'assignee' => get_avatar($task->assignee_id, 32),
                'title' => $task->title,
                'tracked_time' => wphGetTaskHours($task->id),
            ];
        }

        return $query_items;

    }

    function column_assignee($item)
    {
        return $item['assignee'];
    }

    function column_tracked_time($item)
    {
        return ($item['tracked_time'] ? $item['tracked_time'] : 0);
    }

    protected function get_primary_column_name()
    {
        return '';
    }

    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     *
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     *
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item){
        return sprintf('<a href="%s">%s</a> <span style="color:silver">(id:%s)</span>',
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]&task={$item['ID']}",
            $item['title'],
            $item['ID']
        );
    }

    /** ************************************************************************
     * REQUIRED. Get Views is required in order to sort posts by status
     ***************************************************************************/
    function get_views()
    {
        $views = array();
        $current = ( !empty($_REQUEST['task_status']) ? $_REQUEST['task_status'] : 'all');

        //All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg('task_status');
        $views['all'] = "<a href='{$all_url }' {$class} >All</a>";

        //Foo link
        $pending_review_url = add_query_arg('task_status','pending');
        $class = ($current == 'pending' ? ' class="current"' :'');
        $views['pending'] = "<a href='{$pending_review_url}' {$class} >Pending Review</a>";

        //Bar link
        $completed_url = add_query_arg('task_status','completed');
        $class = ($current == 'completed' ? ' class="current"' :'');
        $views['completed'] = "<a href='{$completed_url}' {$class} >Completed</a>";


        $link = '';
        foreach ($views as &$value) {
            $link .= $value.' | ';
        }
        return $link;

    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns()
    {
        $columns = array(
            'assignee'          => 'Assig.',
            'title'             => 'Title',
            'tracked_time'      => 'Hours',
        );

        $showAssigneeToClients = esc_attr(get_option('wph_tracker_show_assignee_to_client')) == 1;
        if (!$showAssigneeToClients) {
            unset($columns['assignee']);
        }

        return $columns;
    }



    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'title'         => array('title',false),
            'assignee'      => array('assignee',false),
            'tracked_time'  => array('tracked_time',false),
        );
        return [];
    }

    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 9999;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->get_tasks($this->projectId);


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );
    }


}
