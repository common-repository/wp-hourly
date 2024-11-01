<?php

/*
Plugin Name: WP Hourly
Plugin URI: https://wphourly.com
Description: WP HOURLY allows you to track billable time and have your customers pay you for it through your WooCommerce website. It is ideal for Freelancers and Agencies alike, giving you to ability to create tasks and assign them to your team members (Employees). An Employee can then submit time records which will show up in the My Account section of your WooCommerce website. The plugin also features extensive reports as well.
Author: SUPER WP HEROES
Version: 3.2.0
Author URI: https://wphourly.com
Requires at least: 4.5
WC requires at least: 3.0.0
WC tested up to: 5.6.0
Text Domain: wphourly
*/


define('WP_HOURLY_PATH', plugin_dir_path(__FILE__));
define('WP_HOURLY_URI', plugins_url('/', __FILE__));

require_once(plugin_dir_path( __FILE__ ) . 'core/public.php');
require_once(plugin_dir_path( __FILE__ ) . 'core/insertion.php');
require_once(plugin_dir_path( __FILE__ ) . 'core/projects.php');
require_once(plugin_dir_path( __FILE__ ) . 'core/tasks.php');
require_once(plugin_dir_path( __FILE__ ) . 'core/functions.php');
require_once(plugin_dir_path( __FILE__ ) . 'core/shortcodes.php');
require_once(plugin_dir_path( __FILE__ ) . 'core/reports/tasks-table-unpaid-out.php');
require_once(plugin_dir_path( __FILE__ ) . 'core/reports.php');

require_once(plugin_dir_path( __FILE__ ) . 'migrations/migration-runner.php');

define('WP_HOURLY', true);
define('WPH_VERSION', '2.0.8');


define('WPH_TRACKER_URL', 'https://wphourly.com/add-ons/wp-hourly-tracker/');
define('WPH_WOOCOMMERCE_URL', 'https://wphourly.com/add-ons/wp-hourly-wc/');
define('WPH_WIDGETS_URL', 'https://wphourly.com/add-ons/wp-hourly-widgets/');
define('WPH_TIMESHEETS_URL', 'https://wphourly.com/add-ons/wp-hourly-timesheets/');



/**
 * load plugin assets
 *
 * @return void
 * @author swph
 **/
function wph_styles()
{
    $screen = get_current_screen();
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);

    wp_register_style('wph-bootstrap', esc_url( plugins_url( 'assets/css/bootstrap.min.css',  __FILE__ ) ));

    wp_enqueue_style('wph-bootstrap');
    if(in_array($screen->id, ['wp-hourly_page_wph-dashboard', 'dashboard', 'wp-hourly_page_wph-my-workspace']) || in_array($page, ['wph-dashboard', 'wph-my-workspace'])) {
        wp_enqueue_style( 'wph-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css', array(), '1.1', 'all');
        wp_enqueue_style( 'wph-dashboard', esc_url( plugins_url( 'assets/css/wph-dashboard.css',  __FILE__ ) ), array(), '1.1', 'all');
    }

    wp_enqueue_style( 'wph-styles', esc_url( plugins_url( 'assets/css/wph-styles.css',  __FILE__ ) ), array(), '1.1', 'all');
}
add_action( 'admin_print_styles', 'wph_styles', 10, 1 );

/**
 * load plugin backend scripts
 *
 * @return void
 * @author swph
 **/

function wph_scripts()
{
    $screen = get_current_screen();
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);

    wp_register_script('select2', esc_url(plugins_url('assets/js/select2.min.js', __FILE__)), ['jquery']);
    wp_register_style('select2', esc_url(plugins_url('assets/css/select2.min.css', __FILE__)));

    require_once(plugin_dir_path( __FILE__ ) . '/templates/wph-project-modal-task-tpl.php');
    require_once(plugin_dir_path( __FILE__ ) . '/templates/wph-project-modal-tpl.php');
    // require_once(plugin_dir_path( __FILE__ ) . '/templates/wph-project-modal-add-time.php');

    wp_enqueue_script( 'jquery-ui','https://code.jquery.com/ui/1.12.1/jquery-ui.min.js',array('jquery') );

    wp_enqueue_script( 'wph-popper', esc_url( plugins_url( 'assets/js/popper.min.js', __FILE__ ) ), array( 'jquery' ));
    wp_enqueue_script( 'wph-bootstrap', esc_url( plugins_url( 'assets/js/bootstrap.min.js', __FILE__ ) ), array( 'jquery' ));
    wp_enqueue_script( 'wph-sweetalert', esc_url( plugins_url( 'assets/js/sweetalert2.all.min.js', __FILE__ ) ), array( 'jquery' ));
    wp_enqueue_script( 'wph-dashboard', esc_url( plugins_url( 'assets/js/wph-dashboard.js', __FILE__ ) ), array( 'jquery' ));
    // if (in_array($screen->id, ['wp-hourly_page_wph-dashboard', 'dashboard']) || in_array($page, ['wph-dashboard'])) {
    // }

    if (in_array($screen->id, ['user-edit', 'dashboard', 'wp-hourly_page_wph-dashboard', 'wp-hourly_page_wph-my-workspace', 'wp-hourly_page_wph-screenshots', 'profile']) || in_array($page, ['wph-reports', 'wph-dashboard', 'wph', 'wph-my-workspace'])) {
        if ($page == 'wph-reports') {
            wp_enqueue_style('select2');
            wp_enqueue_script('select2');
        }

        wp_enqueue_script( 'wph-scripts', esc_url( plugins_url( 'assets/js/wph-scripts.js', __FILE__ ) ), array( 'jquery' ));
    }

    wp_enqueue_script( 'wph-modernizer', esc_url( plugins_url( 'assets/js/modernizr-custom.js', __FILE__ ) ), array( 'jquery' ));

    wp_localize_script('wph-scripts', 'pw_script_vars', array(
            'project_err01' => __('Could not delete project; please check console.log for errors', 'wphourly'),
            'project_err02' => __('Cannot delete, someone is working on a task', 'wphourly'),
            'project_err03' => __('Are you sure you want to delete this project?', 'wphourly'),
            'project_err04' => __('Could not update project; please check console.log for errors', 'wphourly'),

            'confirm_1' => __('Are you sure', 'wphourly'),
            'confirm_2' => __('Save', 'wphourly'),
            'confirm_3' => __("Don't save", 'wphourly'),
            'confirm_4' => __("Remove", 'wphourly'),
            'confirm_5' => __("Success!", 'wphourly'),
            'confirm_6' => __("Error!", 'wphourly'),

            
            'reccord_msg_1' => __("The record has been updated successfully!", 'wphourly'),
            'reccord_msg_2' => __("Something went wrong! Check the console.", 'wphourly'),

            'screenshot_msg_1' => __("There was an error, see the console", 'wphourly'),
            'screenshot_msg_2' => __("Select project", 'wphourly'),
            'screenshot_msg_3' => __("Select task", 'wphourly'),
            'screenshot_msg_4' => __("Select user", 'wphourly'),
            'screenshot_msg_5' => __("Select project", 'wphourly'),
            'screenshot_msg_6' => __("Select project", 'wphourly'),
            'screenshot_msg_7' => __("There was an error while uploading the screenshot.", 'wphourly'),

            'task_msg_1' => __("There was an error while starting the task!", 'wphourly'),            
            'task_msg_2' => __("There is a task in progress. Are you sure?", 'wphourly'),            
            'task_msg_3' => __("Something went wrong, try refreshing the page or contact admin", 'wphourly'),            

            'manual_time_msg_1' => __("Manual time added succesfuly!", 'wphourly'),            
            'manual_time_msg_2' => __("Cannot add time record. please check console.log!", 'wphourly'),  


            'certif_msg_1' => __("Certificates successfully regenerated!", 'wphourly'),            
            'certif_msg_2' => __("OUPS! There was an error while regenerating the certificates!", 'wphourly'),            
            'certif_msg_3' => __("Sorry, there was an error", 'wphourly'),            


            'widget_msg_1' => __("Loading data ...", 'wphourly'),            
            'widget_msg_2' => __("Loading user tasks. Please wait..this can take a while ...", 'wphourly'),            
            'widget_msg_3' => __("SOMETHING WENT WRONG!", 'wphourly'),            
            'widget_msg_4' => __("There was an error. Try reloading the page.", 'wphourly'),            
            'widget_msg_5' => __("Someone is working on the task", 'wphourly'),            
            'widget_msg_6' => __("ARE YOU SURE YOU WANT TO MARK ALL OF THESE HOURS AS PAID?", 'wphourly'),            
            'widget_msg_7' => __("Email sent ok!", 'wphourly'),            
            'widget_msg_8' => __("Error occurred!", 'wphourly'),       
            'widget_msg_9' => __("TIME SUCCESSFULLY MARKED AS PAID!", 'wphourly'),       


            'wc_msg_1' => __("Loading tasks table ...", 'wphourly'),       
            'wc_msg_2' => __("Loading", 'wphourly'),       
            'wc_msg_3' => __("Redirecting", 'wphourly')       


            
        )
    );
}
add_action( 'admin_enqueue_scripts', 'wph_scripts');




/**
 * add the front end styles
 *
 * @return void
 * @author swph
 **/
function wph_front_end_styles() {
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'wph-front-end-styles', esc_url(plugins_url('assets/css/wph-front-end-styles.css', __FILE__)), array(), '1.1', 'all');
}
add_action( 'template_redirect', 'wph_front_end_styles', 10, 1 );

/**
 * add the front end boostrap if dosen't have
 *
 * @return void
 * @author swph
 **/
function wph_front_end_script() {
    wp_enqueue_script( 'wph-front-end-script', esc_url( plugins_url( 'assets/js/wph-front-end-script.js', __FILE__ ) ), array( 'jquery' ));
}
add_action( 'wp_footer', 'wph_front_end_script' );




/**
 * create employee user role
 *
 * @return void
 * @author
 **/

function wphAddRolesAndCapabilitiesOnPluginActivation() {
    // roles
    $employeeRole = get_role('employee');
    if (!$employeeRole) {
        $employeeRole = add_role('employee', 'Employee', array('read' => true, 'level_0' => true));
    }

    $customerRole = get_role('customer');
    if (!$customerRole) {
        $customerRole = add_role('customer', 'Customer', ['read' => true, 'level_0' =>true]);
    }

    $administratorRole = get_role('administrator');

    // capabilities
    $administratorRole->add_cap('wphourly');

    $employeeRole->add_cap('wphourly');

    $employeeRole->add_cap('read_task', 1);
    $employeeRole->add_cap('read_tasks', 1 );
    $employeeRole->add_cap('edit_task', 1);
    $employeeRole->add_cap('edit_tasks', 1);
    $employeeRole->add_cap('edit_others_tasks', 1);
    $employeeRole->add_cap('edit_published_tasks', 1);
    $employeeRole->add_cap('publish_tasks');
    $employeeRole->add_cap('delete_others_tasks');
    $employeeRole->add_cap('delete_private_tasks');

    $employeeRole->add_cap('read_time_record', 1);
    $employeeRole->add_cap('read_time_records', 1 );
    $employeeRole->add_cap('edit_post', 1);
    $employeeRole->add_cap('edit_posts', 1);
    $employeeRole->add_cap('edit_time_record', 1);
    $employeeRole->add_cap('edit_time_records', 1);
    $employeeRole->add_cap('edit_others_time_records', 1);
    $employeeRole->add_cap('edit_published_time_records', 1);
    $employeeRole->add_cap('publish_time_records');
    $employeeRole->add_cap('delete_others_time_records');
    $employeeRole->add_cap('delete_private_time_records');

    $customerRole->add_cap('wphourly');
}
register_activation_hook( __FILE__, 'wphAddRolesAndCapabilitiesOnPluginActivation' );
add_action('init', 'wphAddRolesAndCapabilitiesOnPluginActivation');

register_deactivation_hook(
    __FILE__,
    function () {
        if (!wphHasWooCommerce()) {
            remove_role('customer');
        }
    }
);

add_action('admin_bar_menu', 'wph_project_items', 20);
function wph_project_items($admin_bar) {
    if (current_user_can('administrator')) {
        $admin_bar->add_menu(array(
            'id' => 'add-project',
            'parent' => 'top-secondary',
            'title' => 'Add Project',
            'href' => '#',
            'meta' => array(
                'title' => __('Add New Project', 'wphourly'),
                
            )
        ));

        $admin_bar->add_menu(array(
            'id' => 'add-task',
            'parent' => 'top-secondary',
            'title' => 'Add New Task',
            'href' => '#',
            'meta' => array(
                'title' => __('Add New Task', 'wphourly'),
                'class' => 'add-new-task'
            )
        ));
    }

    // $admin_bar->add_menu(array(
    //     'id' => 'add-time-records',
    //     'parent' => 'top-secondary',
    //     'title' => 'Add Time Records',
    //     'href' => '#',
    //     'meta' => array(
    //         'title' => __('Add Time Records', 'wphourly'),
    //     )
    // ));
}

function wphHasWooCommerce()
{
    return class_exists( 'WooCommerce' );
}

function wphHasTrackerAddonInstalled()
{
    return class_exists('WPHTracker');
}

function wphHasWooCommerceAddonInstalled()
{
    return class_exists('WPHWooCommerce');
}

function wphHasWidgetsAddonInstalled()
{
    return class_exists('WpHourlyWidgets');
}

function wphHasTimeSheetsAddonInstalled()
{
    return class_exists('WpHourlyTimeSheets');
}

register_activation_hook( __FILE__, 'wphMigrateIfNeeded' );


/**
 * creates a settings page for the plugin & save
 *
 * @return void
 * @author swph
 **/

add_action('admin_menu', function() {
    add_menu_page(
        'WP Hourly',
        'WP Hourly',
        'administrator',
        'wph',
        'wph_plugin_page',
        'data:image/png;base64,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',
        20
    );
}, 9);

// default priority so that the add-ons page is the last one
add_action('admin_menu', function() {
    add_submenu_page('wph', 'Projects', 'Projects', 'wphourly', 'wph-dashboard', 'wph_dashboard');
    // add_submenu_page('wph', 'My Workspace', 'My Workspace', 'wphourly', 'wph-my-workspace', 'wph_my_workspace');
    if(!current_user_can('customer')){
        add_submenu_page('wph', 'My Workspace', 'My Workspace', 'wphourly', 'wph-my-workspace', 'wph_my_workspace');    
    }
    
    add_submenu_page('wph', 'Reports', 'Reports', 'wphourly', 'wph-reports', 'wph_reports');
});

add_action( 'admin_init', function() {
    register_setting('wph-general-options', 'wph_hide_empty_lines_in_reports');
    register_setting('wph-general-options', 'wph_show_hours_in_time_format');
    register_setting('wph-general-options', 'wph_unpaid_hours_menu_item_active');
    register_setting('wph-general-options', 'wph_reports_menu_item_active');
    register_setting('wph-general-options', 'wph_flush_rewrite_rules');
    register_setting('wph-general-options', 'wph_hourly_rate');
    register_setting('wph-general-options', 'wph_treat_admins_as_employees');
    register_setting('wph-general-options', 'wph_allow_employees_to_view_all_projects');
    register_setting('wph-general-options', 'wph_allow_employees_to_view_all_tasks');
    register_setting('wph-general-options', 'wph_allow_employees_to_edit_project');
    register_setting('wph-general-options', 'wph_allow_employees_to_edit_task');
    register_setting('wph-general-options', 'wph_allow_employees_to_add_manual_time');
});

add_action('admin_head', function() {
    global $submenu;

    if (!isset($submenu['wph']) || !current_user_can('administrator')) {
        return;
    }
    $settingsMenu = array_shift($submenu['wph']);
    $settingsMenu[0] = __('Settings', 'wphourly');
    $settingsMenu[3] = __('Settings', 'wphourly');
    array_push($submenu['wph'], $settingsMenu);

    usort($submenu['wph'], function ($menuA, $menuB) {
        return ($menuA[0] == 'Add-ons') ? 1 : 0;
    });
});


function wph_plugin_page()
{
    if (!current_user_can('manage_options')) {
        echo '<div class="wrap"><h1>' . __('WP Hourly', 'wphourly') . '</h1></div>';

        if (current_user_can('employee')) {
            echo '<p>' . __('You need to be an admin to view and edit the settings', 'wphourly') . '</p>';
        }

        return;
    }
    
    
?>
    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>


         <h1><?php echo __('WP Hourly settings', 'wphourly'); ?></h1>
         <?php settings_errors(); ?>

        <h2 class="nav-tab-wrapper">
            <?php
                $defaultTabs = [
                    'general_options' => __('General Options', 'wphourly'),
                ];

                $tabs = apply_filters('wph-config-tabs', $defaultTabs);
                $activeTab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING);
                if (!in_array($activeTab, array_keys($tabs))) {
                    $activeTab = 'general_options';
                }
                foreach ($tabs as $tabKey => $tabName) {
                    echo sprintf(
                '<a href="?page=wph&tab=%s" class="nav-tab %s">%s</a>',
                        $tabKey,
                        $tabKey == $activeTab ? 'nav-tab-active' : '',
                        $tabName
                    );
                }
            ?>
        </h2>

         <form action="options.php" method="post">

        <?php do_action("wph-render-{$activeTab}-config-tab"); ?>

        <div>
            <?php submit_button(); ?>
        </div>
      </form>
    </div>
  <?php
}

add_action('wph-render-general_options-config-tab', 'wphGeneralOptions');
function wphGeneralOptions()
{
    
    settings_fields( 'wph-general-options' );
    do_settings_sections( 'wph-general-options' );

    $hourlyRate = get_option('wph_hourly_rate', ''); ?>
    <br />
    <h5><?php _e('Global Hourly Rate', 'wphourly') ?></h5>
    <p><?php _e('This is the global rate WP Hourly will use to calculate the costs for your customers. This rate can be overwritten at a Customer, Project and This is the global rate WP ask level', 'wphourly') ?></p>
    <div>
        <label>
            <input type="text" name="wph_hourly_rate" value="<?php echo $hourlyRate; ?>" placeholder="ex.: 100" />
        </label>
    </div>
    <br />
    <h5><?php _e('User permissions', 'wphourly'); ?></h5>
    <p><?php _e('These global user permissions can be overwrittin at a <a href="'.get_admin_url().'/users.php">user</a> level as well.', 'wphourly'); ?></p>
    <?php $adminsAsEmployees = esc_attr(get_option('wph_treat_admins_as_employees')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_treat_admins_as_employees" value="1" <?php echo $adminsAsEmployees ? 'checked' : ''; ?> /> <?php _e('<b>Treat Admins as Employees.</b> This allows you to assign Tasks to Admins so that Admins can also work and track time. This is useful for solo businesses, such as Freelancers.', 'wphourly'); ?>
        </label>
    </div>
    <?php $canEmployeeViewAllProjects = esc_attr(get_option('wph_allow_employees_to_view_all_projects')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_allow_employees_to_view_all_projects" value="1" <?php echo $canEmployeeViewAllProjects ? 'checked' : ''; ?> /> <?php _e('<b>Allow Employees to view all Projects.</b> Alternatively, they will just see Projects in which they have at least 1 Task assigned to them ', 'wphourly'); ?>
        </label>
    </div>
    <?php $canEmployeeViewAllTasks = esc_attr(get_option('wph_allow_employees_to_view_all_tasks')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_allow_employees_to_view_all_tasks" value="1" <?php echo $canEmployeeViewAllTasks ? 'checked' : ''; ?> /> <?php _e('<b>Allow Employees to view all Tasks.</b> Alternatively, they will just see the Tasks that are assigned to them, in any given Project.', 'wphourly'); ?>
        </label>
    </div>
    <?php $canEmployeeEditProject = esc_attr(get_option('wph_allow_employees_to_edit_project')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_allow_employees_to_edit_project" value="1" <?php echo $canEmployeeEditProject ? 'checked' : ''; ?> /> <?php _e('<b>Allow Employees to edit Projects.</b>', 'wphourly'); ?>
        </label>
    </div>
    <?php $canEmployeeEditTask = esc_attr(get_option('wph_allow_employees_to_edit_task')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_allow_employees_to_edit_task" value="1" <?php echo $canEmployeeEditTask ? 'checked' : ''; ?> /> <?php _e('<b>Allow Employees to edit Tasks.</b>', 'wphourly'); ?>
        </label>
    </div>
    <?php $canEmployeeAddManualTime = esc_attr(get_option('wph_allow_employees_to_add_manual_time')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_allow_employees_to_add_manual_time" value="1" <?php echo $canEmployeeAddManualTime ? 'checked' : ''; ?> /> <?php _e('<b>Allow Employees to add Manual Time Records.</b> Alternatively, only Admins can add Time Recoords. Use the <a href="https://wphourly.com/checkout/?add-to-cart=2341" target="_blank"><b>WP Hourly PRO</b><a/> version for the web based Time Tracker that allows your team to track time with screenshots, as proof of work.','wphourly'); ?>
        </label>
    </div>
    <br />
    <h5><?php _e('Display rules', 'wphourly'); ?></h5>

    <?php $hideEmpty = esc_attr(get_option('wph_hide_empty_lines_in_reports')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_hide_empty_lines_in_reports" value="1" <?php echo $hideEmpty ? 'checked' : ''; ?> /> <?php _e('Hide empty lines (days with no tracked time on them) in Time Reports', 'wphourly'); ?>
        </label>
    </div>

    <?php $showTimeFormat = esc_attr(get_option('wph_show_hours_in_time_format')) == 1; ?>
    <div>
        <label>
            <input type="checkbox" name="wph_show_hours_in_time_format" value="1" <?php echo $showTimeFormat ? 'checked' : ''; ?> /> <?php _e('Show hours in time format. Ex: 2.5 h (2:30) in Time Reports', 'wphourly'); ?>
        </label>
    </div>
    <br />
    <h5><?php _e('Available shortcodes', 'wphourly'); ?></h5>
    <p><?php _e('Allow your customers to see their Time Reports on a page on the front end of your website using the <b>[wph-client-reports]</b> shortcode. This should be a page protected by a login, for ex. a My Account page.', 'wphourly'); ?></p>
    <p><?php _e('Allow your customers to see their Projects on a page on the front end of your website using the  <b>[wph-client-projects]</b> shortcode. This should be a page protected by a login, for ex. a My Account page.', 'wphourly'); ?></p>

    <input type="hidden" name="wph_flush_rewrite_rules" value="1" />

    <?php
}

function wph_reports()
{
    
    echo '<div class="wrap">';
    echo '<h2>Reports</h2>';

    echo '<div class="report-form">';
    echo '<form method="post" name="run-report-form">';

    // SELECT CLIENT AND PROJECT TYPE
    // prepare arguments
    $user_args  = array(
        'role__in' => array('customer','employee'),
    );
    if (esc_attr(get_option('wph_treat_admins_as_employees')) == 1) {
        $user_args['role__in'][] = 'administrator';
    }
    // Create the WP_User_Query object
    $wp_user_query = new WP_User_Query($user_args);
    // Get the results
    $authors = $wp_user_query->get_results();
    // Check for results
    if (empty($authors)) {
        echo 'Please make sure you added at least one Employee with the status \'Active\' or a Customer first.';

        return;
    }

    $clients = '';
    $employees = '';
    // loop trough each author
    foreach ($authors as $author) {
        $author_info = get_userdata($author->ID);
        $option = sprintf(
            '<option value="%d">%s%s</option>',
            $author_info->ID,
            $author_info->display_name,
            user_can($author->ID, 'administrator') ? ' (admin)' : ''
        );
        if (wphIsClientUser($author->ID)) {
            $clients .= $option;
        } else {
            $employees .= $option;
        }
    }

    if (empty($employees) && empty($clients)) {
        echo 'Please make sure you added at least one Employee with the status \'Active\' or a Customer first.';

        return;
    }

    if ((current_user_can('employee') || current_user_can('customer')) && !current_user_can('administrator')) {
        echo '<input type="hidden" name="report_select_user" value="' . get_current_user_id() . '" />';
        echo '<input type="hidden" name="return_view" value="tasks-table" />';
    } else {
        echo '<div class="input-wrap" id="select_client" style="width: auto; float: left; margin-right: 20px;">';
        // Name is your custom field key
        echo "<select name='report_select_user' id='report_select_user'>";
        echo '<option value="">Select User</option>';
            echo '<optgroup label="Select Client">' . $clients . '</optgroup>';
            echo '<optgroup label="Select Employee">' . $employees . '</optgroup>';
        echo '</select>';
        echo '<div class="err_empty_tooltip">Please Select User</div>';
        echo '</div>';

        if (is_admin() && current_user_can('manage_options')) {
            echo '<div style="float:left; margin-right: 20px;">
                <label>Filter time records</label>
                <select name="return_view">
                    <option value="tasks-table" selected>All</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>';
        } else {
            echo '<input type="hidden" name="return_view" value="tasks-table" />';
        }
    }

    echo '<div class="input-wrap" id="select_project" style="width: auto; float: left; margin-right: 20px;">' . wphGetLoader('select_project_loader') . '</div>';

    // start date end date for time records unpaid out
    echo '<div class="input-wrap" id="filter_unpaid_out" style=" width: auto; float: left; margin-right: 20px;">';
    echo '<label for="unpaid_out_start">'. __('Start Date', 'wphourly') .'</label><input type="date" id="unpaid_out_start" name="unpaid_out_start" style="width: auto; margin-right: 20px;" value="'.date('Y-m-d', strtotime('-1 month +1 day')).'"/>';
    echo '<label for="unpaid_out_end">'. __('End Date', 'wphourly') .'</label><input type="date" id="unpaid_out_end" name="unpaid_out_end" value="'.date('Y-m-d').'"/>';
    echo '</div>';

    echo '<div class="input-wrap" id="run-report-wrap" style="width: auto; float: left; margin-right: 20px;"><input name="save" type="submit" class="button button-primary button-large" id="run-report" name="run-report" value="Run Report"></div>';
    echo '<div style="clear:both;"></div>';
    echo '</form>';
    echo '</div> <!-- report-form -->';


    $userId = filter_input(INPUT_POST, 'report_select_user', FILTER_VALIDATE_INT);
    $projectId = filter_input(INPUT_POST, 'select_project_list', FILTER_VALIDATE_INT);
    $returnView = filter_input(INPUT_POST, 'return_view', FILTER_SANITIZE_STRING);
    $startDate = filter_input(INPUT_POST, 'unpaid_out_start', FILTER_SANITIZE_STRING);
    $endDate = filter_input(INPUT_POST, 'unpaid_out_end', FILTER_SANITIZE_STRING);

    if ($userId) {
        echo '<div style="clear:both;"></div>';
        echo '<hr />';
        // echo '<h5>REPORT FOR: '.get_userdata($userId)->first_name.' '.get_userdata($userId)->last_name.' - Project: '.get_the_title($projectId).'</h5>';
        // echo '<hr />';
        echo wph_run_report($userId, $projectId, $returnView, $startDate, $endDate);

    }

    echo '</div> <!-- wrap -->';
}


/*
//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//    ***
//    ***        SUPER WP HEROES
//    ***        FUNCTION NAME: wph_my_workspace()
//    ***        DESCRIPTION: display the workspace for an employee or admin
//    ***        CALLED ON: admin dashboard
//    ***
//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
*/


/*
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    *******
//    *******
//    *******
//    *******
//    *******        SUPER WP HEROES
//    *******        SECTION NAME: DE MUTAT IN PRO
//    *******        DESCRIPTION: 
//    *******
//    *******
//    *******
//    *******
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
*/





/*
//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//    ***
//    ***        SUPER WP HEROES
//    ***        FUNCTION NAME: wph_dashboard()
//    ***        DESCRIPTION: create a nice custom dashboard for WPH
//    ***        CALLED ON: admin dashboard
//    ***
//    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
*/

function wph_dashboard()
{
    
    echo '<div class="wrap">';



    if( isset($_GET['project']) ) {

        require_once(plugin_dir_path( __FILE__ ) . 'templates/wph-project-tpl.php');

    } else {
        require_once(plugin_dir_path( __FILE__ ) . 'templates/wph-projects-tpl.php');
    }

    echo '</div> <!-- end wrapper -->';
}

add_action('rest_api_init', function () {
    register_rest_field('user', 'active', [
        'get_callback' => function($user) {
            return get_user_meta($user['id'], 'wph-user-status', true) === 'active';
        },
        'schema' => [
            'description' => __('Is current user an active employee', 'wphourly'),
            'type'        => 'boolean'
        ],
    ]);
});

function wph_store_main_admin_post()
{
    if (!is_admin()) {
        return;
    }

    global $wphMainAdminPost, $post;

    $wphMainAdminPost = $post;
}
add_action('admin_head', 'wph_store_main_admin_post');

function wph_reset_admin_postdata()
{
    global $wphMainAdminPost, $post;

    if (!is_admin() || !$wphMainAdminPost) {
        return;
    }

    $post = $wphMainAdminPost;
}


// ADD SUPPORT LINK
function wphAddCustomPluginLinks($links)
{
    $support_link = '<a target="_blank" href="https://wphourly.com/docs/">' . __('Documetation', 'wphourly') . '</a>';
    array_unshift($links, $support_link);

    $support_link = '<a target="_blank" href="mailto:support@wphourly.com?subject=I need help customizing WP Hourly on '.get_bloginfo("url").'">' . __('Custom Integration Help', 'wphourly') . '</a>';
    array_unshift($links, $support_link);

    $support_link = '<a target="_blank" href="https://wphourly.com/support/">' . __('SUPPORT', 'wphourly') . '</a>';
    array_unshift($links, $support_link);

    $settings_link = '<a href="'. admin_url('admin.php?page=wph') . '">' . __('Settings', 'wphourly') . '</a>';
    array_unshift($links, $settings_link);

    return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_{$plugin}", 'wphAddCustomPluginLinks' );


add_action('plugins_loaded', 'wphMigrateIfNeeded');

// adds the option to display reports in woocommerce menu
function wphReportsMenuItem($items)
{
    $items[] = [
        'pageId' => 'my-projects',
        'pageName' => __('My projects', 'wphourly'),
        'cb' => 'wph_projects_endpoint_content',
    ];
    $items[] = [
        'pageId' => 'reports',
        'pageName' => __('Reports', 'wphourly'),
        'cb' => 'wph_reports_endpoint_content',
    ];

    return $items;
}
add_filter('wph_woocommerce_custom_pages', 'wphReportsMenuItem');

function wph_reports_endpoint_content()
{
    echo do_shortcode('[wph-client-reports]');
}

function wph_projects_endpoint_content()
{
    echo do_shortcode('[wph-client-projects]');
}

/**
 * This function allows you to track usage of your plugin
 * Place in your main plugin file
 * Refer to https://wisdomplugin.com/support for help
 */
if (!class_exists('Plugin_Usage_Tracker')) {
    require_once dirname( __FILE__ ) . '/tracking/class-plugin-usage-tracker.php';
}
if (!function_exists('wp_hourly_start_plugin_tracking')) {
    function wp_hourly_start_plugin_tracking() {
        $wisdom = new Plugin_Usage_Tracker(
            __FILE__,
            'https://wphourly.com',
            array(),
            true,
            true,
            2
        );
    }
    wp_hourly_start_plugin_tracking();
}

//wisdomplugin deactivation form settings.
if (!function_exists('wph_wisdom_plugin_filter_deactivation_form')) {
    function wph_wisdom_plugin_filter_deactivation_form($form)
    {
        $form['heading']    = __('Sorry to see you go', 'wphourly' );
        $form['body']       = __('Before you deactivate the plugin, would you quickly give us your reason for doing so? ( If you need help with WP HOURLY, just drop a line at support@wphourly.com )', 'wphourly' );
        $form['details']    = __('If you would be so kind to let us know what features you are missing so that we can work on them for the future, that would be very much appreciated ', 'wphourly' );

        return $form;
    }
}
add_filter('wisdom_form_text_' . basename( __FILE__, '.php' ), 'wph_wisdom_plugin_filter_deactivation_form' , 1);

function wphFilterOutFromWCUpdates($matches)
{
    unset($matches[plugin_basename(__FILE__)]);

    return $matches;
}
add_filter('woocommerce_get_plugins_for_woocommerce', 'wphFilterOutFromWCUpdates');


add_action( 'wph_my_workspace_sidebar', 'wph_my_workspace_sidebar_notifications', 99 );

function wph_my_workspace_sidebar_notifications() {

    if(function_exists('wph_msg_dahboard_notif')) {
        wph_msg_dahboard_notif();
    }else{
        echo __('Notification available only in Pro version , upgrade to Pro Version <a target="_blank" href="https://wphourly.com/checkout/?add-to-cart=2341">WP Hourly PRO</a>');

    }
}



function wph_my_workspace()
{

    echo '<div class="wrap">';
    
    require_once(plugin_dir_path( __FILE__ ) . 'templates/wph-my-workspace-tpl.php');

    echo '</div> <!-- end wrapper -->';
}