<?php

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



    SUPER WP HEROES

    FILE PURPOSE: generate a weekly timesheet for employee



   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

   /*
   //    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   //    ***
   //    ***        SUPER WP HEROES
   //    ***        DESCRIPTION: spits put a html table of worked time grouped by task, project and week for an employee
   //    ***        CALLED ON:
   //    ***
   //    ***        TO DO: -
   //    ***
   //    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   */

    add_action('wp_ajax_get_time_sheet_print','wph_generate_timesheet');
    function wph_generate_timesheet($uid='', $start_date='', $end_date='', $projectId = '')
    {
        $timesheet_tr = array();

        if(!$uid && $requestedUID = filter_input(INPUT_GET, 'uid', FILTER_SANITIZE_NUMBER_INT)) {
            $uid = $requestedUID;
        }

        if(!$start_date && $requestedStartDate = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING)) {
            $start_date = $requestedStartDate;
        }

        if(!$end_date && $requestedEndDate = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING)) {
            $end_date = $requestedEndDate;
        }

        if ($uid == '') {
            die('No user id specified');
        }

        $start = strtotime(date("Y-m-d 00:00:00", strtotime($start_date)));;
        $end = strtotime(date("Y-m-d 23:59:59", strtotime($end_date)));
        global $wpdb;
        $projectCondition = is_numeric($projectId) ? $projectCondition = "AND p.id = {$projectId}" : '';
        $query = "
            SELECT
                tr.id trId,
                tr.timestamp time,
                p.id project,
                p.title projectTitle,
                tr.task_id task,
                t.title taskTitle,
                tr.hours hours
            FROM {$wpdb->prefix}wph_time_records tr
            LEFT JOIN {$wpdb->prefix}wph_tasks t ON (t.id = tr.task_id)
            LEFT JOIN {$wpdb->prefix}wph_projects p ON (t.project_id = p.id)
            WHERE
                (
                    tr.assignee_id = {$uid} OR
                    tr.client_id = {$uid}
                ) AND
                tr.`timestamp` >= '{$start}' AND tr.`timestamp` <= '{$end}'
                {$projectCondition}
        ";
        $results = $wpdb->get_results($query);
        foreach($results as $result) {
            $timesheet_tr[date('Y-m-d D', $result->time)][] = array(
                'tr_id' => $result->trId,
                'tr_project' => $result->project,
                'project_title' => $result->projectTitle,
                'tr_task' => $result->task,
                'task_title' => $result->taskTitle,
                'tr_hours' => $result->hours,
            );
        }

        $total_worked_in_interval = array();
        $table = '';

        $sDate = $start_date;
        while ($sDate <= $end_date) {
                $time = strtotime($sDate);
                $day_totals_worked = array();

                $currentDay = '<hr /><h6>'.date('Y-m-d D', $time).'</h6>';

                $currentDay .= '
                    <table class="wp-list-table widefat fixed striped pages">
                        <thead>
                            <tr>
                                <th>' . __('Project', 'wphourly') . '</th>
                                <th>' . __('Task', 'wphourly') . '</th>
                                <th style="text-align:center">' . __('Worked', 'wphourly') . '</th>
                                <th style="display:none">' . __('Unpaid In', 'wphourly') . '</th>
                            </tr>
                        </thead>
                        <tbody>';

                        $day = date('Y-m-d D', $time);
                        $day_records = isset($timesheet_tr[$day]) ? $timesheet_tr[date('Y-m-d D', $time)] : false;

                        // group records by date
                         $new_task_array = array();
                         if ($day_records) {
                            foreach ($day_records as $v) {

                                $task_key = $v['tr_task'];
                                if (!isset($new_task_array[$task_key])) {
                                    $new_task_array[$task_key] = array_merge($v, array('tr_hours' => 0));
                                }
                                $time = is_numeric($v['tr_hours']) ? $v['tr_hours'] : 0;
                                $new_task_array[$task_key]['tr_hours'] += $time;
                            }
                        }
                        $new_task_array = array_values($new_task_array); // remove unix time keys

                        foreach($new_task_array as $entry) {
                            $projectName = '-';
                            if ($entry['tr_project']) {
                                $projectName = $entry['project_title'];
                            }
                            $taskName = '-';
                            if ($entry['tr_task']) {
                                $taskName = $entry['task_title'];
                            }
                            $currentDay .= '
                            <tr>
                                <td>' . $projectName . '</td>
                                <td>' . $taskName . '</td>
                                <td style="text-align:center">'.$entry['tr_hours'].' h ' . wphHoursInTimeFormat($entry['tr_hours']) . '</td>
                            </tr>';

                            $day_totals_worked[] = $entry['tr_hours'];
                        }

                $totalHours = array_sum($day_totals_worked);
                $currentDay .= '
                    </tbody >
                    <tfoot>
                        <tr>
                            <th><b>' . __('TOTALS', 'wphourly') . '</b></th>
                            <th></th>
                            <th style="text-align:center"><b>'.$totalHours.' h ' . wphHoursInTimeFormat($totalHours) . '</b></th>
                        </tr>
                    </tfoot>
                </table>';

            if ($totalHours > 0 || get_option('wph_hide_empty_lines_in_reports') != 1) {
                $total_worked_in_interval[] = array_sum($day_totals_worked);
                $table .= $currentDay;
            }

            $sDate = date('Y-m-d', strtotime($sDate . ' +1 day'));
        }

        $projects = array();
        $total = 0;
        foreach ($timesheet_tr as $records) {
            if(is_array($records)) {
                foreach ($records as $record) {
                    $time = is_numeric($record['tr_hours']) ? $record['tr_hours'] : 0;
                    if(!isset($projects[$record['tr_project']])) {
                        $projects[$record['tr_project']] = ['hours' => 0, 'title' => $record['project_title']];
                    }
                    $projects[$record['tr_project']]['hours'] += $time;
                    $total += $time;
                }
            }
        }

        $isCustomer = user_can($uid, 'customer');
        $userLabel = $isCustomer ? __('CLIENT', 'wphourly') : __('ASSIGNEE', 'wphourly');
            $hoursInInterval = array_sum($total_worked_in_interval);
            $theads = '<br /><br />
            <table class="wp-list-table widefat fixed striped pages">
                <thead>
                    <tr>
                        <th style="width: 200px">' . $userLabel . '</th>
                        <th style="text-align:center">' . __('START DATE', 'wphourly') . '</th>
                        <th style="text-align:center">' . __('END DATE', 'wphourly') . '</th>
                        <th style="text-align:center">' . __('TOTAL WORKED', 'wphourly') . '</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><div style=" float: left; margin-right: 15px; height: 40px; overflow: hidden;">'.get_avatar($uid, 40).'</div><span style="display: block;line-height: 40px;">'.get_userdata($uid)->display_name.'</span></td>
                        <td style="text-align:center"><span style="display: block;line-height: 40px;">'.$start_date.'</span></td>
                        <td style="text-align:center"><span style="display: block;line-height: 40px;">'.$end_date.'</span></td>
                        <td style="text-align:center"><span style="display: block;line-height: 40px;">'.$hoursInInterval.' h ' . wphHoursInTimeFormat($hoursInInterval) .'</span></td>
                    </tr>
                </tbody>
            </table>
            <table class="wp-list-table widefat fixed striped pages">
                <tbody>';
                foreach($projects as $project) {
                    $theads .= '<tr>
                        <td>' . __('PROJECT', 'wphourly') . '</td>
                        <td style="text-align:center">'.$project['title'].'</td>
                        <td>' . __('TOTAL HOURS', 'wphourly') . '</td>
                        <td style="text-align:center">'.$project['hours'].' h ' . wphHoursInTimeFormat($project['hours']) . '</td>
                    </tr>';
                }


                $theads .='</tbody>

            </table>';

        wphPrintDocument('printme');

        echo '<div id="printme" class="printme"><div id="doc-body-print">';

        $reportFor = __('EMPLOYEE WORK REPORT', 'wphourly');
        if ($isCustomer) {
            $reportFor = __('CUSTOMER WORK REPORT', 'wphourly');
        }
        echo '<div class="header">
                <h5>'. __('DOCUMENT TYPE', 'wphourly') . ': <strong>' . $reportFor . '</strong></h5>
                <span class="clearfix clear"></span>
            </div>';

        do_action('wph_before_report', $uid, $start, $end, $results);
        echo $theads;
        echo '<br />';
        echo '<h5>' . __('Daily hours breakdown', 'wphourly') . '</h5>';
        echo $table;
        do_action('wph_after_report', $uid, $start, $end, $results);
        echo '</div></div>';
   }
