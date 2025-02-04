<?php

/* === LIST TASKS TABLE === */


/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin,
 * rename it, and work inside the copy. If you modify this plugin directly and
 * an update is released, your changes will be lost!
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */



// if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/template.php' );//added
    require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );//added
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );//added
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
// }
// die(ABSPATH);



/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */
class WPH_Tasks_List_Table extends WP_List_Table {

    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query()
     *
     * In a real-world scenario, you would make your own custom query inside
     * this class' prepare_items() method.
     *
     * @var array
     **************************************************************************/
    /*var $example_data = array(
            array(
                'ID'        => 1,
                'title'     => '300',
                'rating'    => 'R',
                'director'  => 'Zach Snyder'
            )
        );*/

    /** ************************************************************************
     * HACK Custom Function - Needed for save / edit CPT
     ***************************************************************************/

    public $location;
    private $project_id;
    private $is_task_paid;

    function display_tablenav($which) {
        return '';
    }

    /** ************************************************************************
     * REQUIRED. Check if the task is fully paid or not.
     ***************************************************************************/
    function is_task_paid($task_id) {
        $time_record_args = array(
            'post_type'=>'time_record',
            'post_per_page' => -1,
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key'     => 'time_record_task',
                    'value'   => $task_id,
                    'compare' => '=',
                ),
            ),
        );
        $time_records = get_posts( $time_record_args );
        $isTaskPaid = 'task_paid_in';
        foreach ( $time_records as $time_record ) {
            setup_postdata( $time_record );
            if (get_post_meta($time_record->ID, "is_paid", true) == 0) {
                $isTaskPaid = 'task_not_paid_in';
            }
        }
        /* Restore original Post Data */
        wp_reset_postdata();

        return array($task_id => $isTaskPaid);
    }

    /** ************************************************************************
     * REQUIRED. Query the time records, per task from the  DB.
     ***************************************************************************/
    function get_task_time($task_id) {
        $result = array();

        $args = array(
            'post_type'=>'time_record',
            'post_per_page' => -1,
                'nopaging' => true,
              'meta_query' => array(
                  array(
                      'key'     => 'time_record_task',
                      'value'   => $task_id,
                      'compare' => '=',
                  ),
              ),
        );

        $the_query = new WP_Query( $args );
        // The Loop
        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $the_time = get_post_meta(get_the_ID(), 'tr_hours', true);
                $result[] = $the_time;
            }
            wp_reset_postdata();
        }

        return array_sum($result);
    }



    /**************************************************************************
     * REQUIRED. Query the tasks from the DB.
     ***************************************************************************/

    function get_tasks($project_id, $is_task_paid) {
        if($is_task_paid == 'no') {
            $check_against = 'task_not_paid_in';
        }
        if($is_task_paid == 'yes') {
            $check_against = 'task_paid_in';
        }

        $query_items = array();

        $tasks_args = array(
            'post_type'     =>'task',
            'post_per_page' => -1,
            'nopaging' => true,
            'meta_query' => [
                [
                    'key' => 'task_project',
                    'value' => $project_id,
                ]
            ]
        );

        // Custom query.
        $tasks = get_posts( $tasks_args );
        foreach ( $tasks as $task ) {
            setup_postdata($task);

            $task_payment_status = $this->is_task_paid($task->ID);
            if ($is_task_paid != '') {
                if (in_array($check_against, $task_payment_status)) {
                    $query_items[] = array(
                        'ID' => $task->ID,
                        'assignee' => get_avatar(get_post_meta($task->ID, 'assignee', true), 32),
                        'title' => get_the_title($task->ID),
                        'tracked_time' => $this->get_task_time($task->ID),
                        'status' => $this->get_task_status($task->ID)
                    );
                }
            } else {
                $query_items[] = array(
                    'ID' => $task->ID,
                    'assignee' => get_avatar(get_post_meta($task->ID, 'assignee', true), 32),
                    'title' => get_the_title($task->ID),
                    'tracked_time' => $this->get_task_time($task->ID),
                    //'status'            => $this->get_task_status($task->ID)
                );
            }
        }

       // Restore original post data.
       wp_reset_postdata();

        return $query_items;

    }

    /** ************************************************************************
     * REQUIRED. Query and calculate the task status.
     ***************************************************************************/

    function get_task_status($task_id) {
        $status = '';
        $status .= '<span class="dashicons dashicons-warning"></span>';
        $status .= '<span class="dashicons dashicons-calendar-alt"></span>';
        $status .= '<span class="dashicons dashicons-clock"></span>';

        $task_payment_status = $this->is_task_paid($task_id);
        if($task_payment_status[$task_id] != 'task_not_paid_in') {
            $status .= '<span class="dashicons dashicons-cart task-status" style="color:red;" data-taskid="'.$task_id.'"></span>';
        } else {
            $status .= '<span class="dashicons dashicons-cart task-status" style="color:green;" data-taskid="'.$task_id.'"></span>';
        }

        //$status .= print_r($task_payment_status, true);

        return $status;
    }




    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct($project_id, $is_task_paid = '', $loc = ''){
        parent::__construct( array(
            'singular'  => 'task',     //singular name of the listed records
            'plural'    => 'tasks',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );


        $this->project_id = $project_id;
        $this->is_task_paid = $is_task_paid;
        $this->location = $loc;
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'assignee':
            //case 'status':
            case 'tracked_time':

                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
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
        if($this->location == 'front-end') {
            //Build row actions
            $actions = array(
                'edit'      => sprintf('<a href="?task_item=%s&action=%s">View Task</a>', $item['ID'],'edit'),
                //'delete'    => sprintf('<a href="?post=%s&action=%s&">Delete</a>',$item['ID'],'delete'),

            );
        } else {
            //Build row actions
            $actions = array(
                'edit'      => sprintf('<a href="%s?post=%s&action=%s">View Task</a>', admin_url('post.php'), $item['ID'],'edit'),
                //'delete'    => sprintf('<a href="?post=%s&action=%s&">Delete</a>',$item['ID'],'delete'),

            );
        }
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

     /** ************************************************************************
     * REQUIRED. Get Views is required in order to sort posts by status
     ***************************************************************************/
    function get_views(){
        //   $status_links = array(
        //    "all"       => __("<a href='#'>All</a>",'my-plugin-slug'),
        ////    "published" => __("<a href='#'>Published</a>",'my-plugin-slug'),
        //    "trashed"   => __("<a href='#'>Trashed</a>",'my-plugin-slug')
        //);
        //echo $status_links .'aaaa';

           $views = array();
           $queriedStatus = filter_input(INPUT_REQUEST, 'task_status', FILTER_SANITIZE_STRING);
           $current = $queriedStatus ? $queriedStatus : 'all';

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
    function get_columns(){
        $columns = array(
            'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
            'assignee'          => 'Assig.',
            'title'             => 'Title',
            'tracked_time'      => 'Hours',
            //'status'            => 'Status'
        );
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
            'title'         => array('title',false),     //true means it's already sorted
            'assignee'      => array('assignee',false),
            'tracked_time'  => array('tracked_time',false),
            //'status'        => array('status',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }

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
        $pid = $this->project_id;

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
        //$data = $this->example_data;
        $data = $this->get_tasks($pid, $this->is_task_paid);


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
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}
