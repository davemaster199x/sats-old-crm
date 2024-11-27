<?php
include('inc/init_for_ajax.php');

$jc = new Job_Class();
$crm = new Sats_Crm_Class();

// data
$menu_id = $_POST['menu_id'];
$sc_id = $_SESSION['USER_DETAILS']['ClassID'];
$sa_id = $_SESSION['USER_DETAILS']['StaffID'];
$tester = array(2070, 2025);


// PAGES PER MENU
$pages_sql = $crm->getPagesPerMenu($menu_id, 1);
$pages = fetchAllArray($pages_sql);
$pagesById = [];

for ($x = 0; $x < count($pages); $x++) {
    $page = &$pages[$x];
    $page['can_view'] = false;

    $pagesById[$page['crm_page_id']] = &$page;
}

$pages_staff_can_view = $crm->pagesStaffCanView($sc_id, $sa_id);

foreach ($pages_staff_can_view as $p) {
    $pagesById[$p['crm_page_id']]['can_view'] = true;
}
// echo(implode('<br/>', array_map(function ($page) {
//     return "[{$page['crm_page_id']}] {$page['page_name']}";
// }, $pages)) . "<br/>");

// while ($page = mysql_fetch_array($pages_sql)) {
// foreach ($pages as $page) {
for ($x = 0; $x < count($pages); $x++) {
    $page = &$pages[$x];
    // visiblity
    // if ($crm->canViewPage($page['crm_page_id'], $sa_id, $sc_id) == true) {
    if ($page['can_view']) {


        // bubbles
        $jtot = 0;
        $bubble_class = 'hm-circle';


        // EDIT
        // REPORTS
        if ($menu_id == 4 && $page['crm_page_id'] == 62) {
            $next_month = strtoupper(date("M", strtotime("+1 month")));
            $page_name = "Service Due ({$next_month})";
        } else {
            $page_name = $page['page_name'];
        }

        // TECHNICIANS
        // Tech Stocktake
        if ($menu_id == 5 && $page['crm_page_id'] == 71) {
            $page_url = "update_tech_stock.php?id={$sa_id}";
        } else {

            $page_url = $page['page_url'];

            // PROPERTIES
            if ($page['page_url'] == 'add_property_static.php') {

                $crm_ci_page = 'properties/add';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'set_tech_run.php') {

                $crm_ci_page = '/tech_run/set';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // JOBS
            if ($page['page_url'] == 'outside_tech_hours.php') {

                $crm_ci_page = 'jobs/after_hours';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'booking_schedule.php') {

                $crm_ci_page = 'bookings/view_schedule';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'allocate.php') {

                $crm_ci_page = 'jobs/allocate';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'bne_to_call.php') {

                $crm_ci_page = 'jobs/bne_to_call';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'cancelled_jobs.php') {

                $crm_ci_page = 'jobs/cancelled';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'completed_jobs.php') {

                $crm_ci_page = 'jobs/completed';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'cot_jobs.php') {

                $crm_ci_page = 'jobs/cot';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'dha_jobs.php') {

                $crm_ci_page = 'jobs/dha';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'escalate.php') {

                $crm_ci_page = 'jobs/escalate';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'holiday_rentals.php') {

                $crm_ci_page = 'jobs/holiday_rentals';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'service_due_jobs.php') {

                $crm_ci_page = 'jobs/service_due';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'to_be_booked_jobs.php') {

                $crm_ci_page = 'jobs/to_be_booked';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'vacant_jobs.php') {

                $crm_ci_page = 'jobs/vacant';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'precompleted_jobs.php') {

                $crm_ci_page = 'jobs/pre_completion';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'merged_jobs.php') {

                $crm_ci_page = 'jobs/merged_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'send_letter_jobs.php') {

                $crm_ci_page = 'jobs/new_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'on_hold_jobs.php') {

                $crm_ci_page = 'jobs/on_hold';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'to_be_invoiced_jobs.php') {

                $crm_ci_page = 'jobs/to_be_invoiced';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // OPS DAILY ITEMS
            if ($page['page_url'] == 'ageing_jobs_30_to_60.php') {
                $crm_ci_page = '/jobs/ageing_jobs_30_to_60';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'ageing_jobs_60_to_90.php') {

                $crm_ci_page = '/jobs/ageing_jobs_60_to_90';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'ageing_jobs_90.php') {

                $crm_ci_page = '/jobs/ageing_jobs_90';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'duplicate_postcode.php') {

                $crm_ci_page = '/properties/duplicate_postcode';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'daily/duplicate_visit') {
                $crm_ci_page = '/daily/duplicate_visit';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'last_contact.php') {

                $crm_ci_page = '/daily/view_last_contact';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'missing_region.php') {

                $crm_ci_page = '/daily/missing_region';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'properties/next_service') {

                $crm_ci_page = '/properties/next_service';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'no_id_properties.php') {

                $crm_ci_page = '/daily/view_no_id_properties';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'str_less_jobs.php') {

                $crm_ci_page = '/daily/str_less_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'daily/incorrectly_upgraded_properties') {

                $crm_ci_page = '/daily/incorrectly_upgraded_properties';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'daily/view_nsw_act_job_with_tbb') {

                $crm_ci_page = '/daily/view_nsw_act_job_with_tbb';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'daily/overdue_nsw_jobs') {

                $crm_ci_page = '/daily/overdue_nsw_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'reports/no_retest_date_property') {

                $crm_ci_page = '/reports/no_retest_date_property';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == '/daily/overdue_other_jobs') {

                $crm_ci_page = '/daily/overdue_other_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'jobs/preferred_time') {

                $crm_ci_page = '/jobs/preferred_time';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'daily/overdue_jobs') {

                $crm_ci_page = '/daily/overdue_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // CS DAILY ITEMS
            if ($page['page_url'] == 'daily/active_unsold_services') {

                $crm_ci_page = '/daily/active_unsold_services';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'daily/no_job_status') {

                $crm_ci_page = '/daily/no_job_status';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'daily/no_job_types') {

                $crm_ci_page = '/daily/no_job_types';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'reports/dirty_address/') {

                $crm_ci_page = '/reports/dirty_address/';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'duplicate_properties.php') {

                $crm_ci_page = '/properties/duplicate_properties';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'property_me/properties_needs_verification') {

                $crm_ci_page = '/property_me/properties_needs_verification';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'no_active_job_properties.php') {

                $crm_ci_page = '/daily/view_no_active_job_properties/';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'multiple_jobs.php') {

                $crm_ci_page = '/daily/multiple_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'unserviced.php') {

                $crm_ci_page = '/daily/unserviced';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'action_required_jobs.php') {

                $crm_ci_page = '/daily/action_required_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'properties/inactive_properties_on_api') {

                $crm_ci_page = '/properties/inactive_properties_on_api';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'daily/active_properties_without_jobs') {

                $crm_ci_page = '/daily/active_properties_without_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'reports/properties_with_coordinates_errors') {

                $crm_ci_page = '/reports/properties_with_coordinates_errors';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'daily/missed_jobs') {
                $crm_ci_page = '/daily/missed_jobs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }


            // TECHNICIAN
            if ($page['page_url'] == 'add_purchase_order.php') {
                $crm_ci_page = '/reports/add_purchase_order';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'view_overall_schedule.php') {
                $crm_ci_page = '/tech/view_overall_schedule';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'resources/index') {
                $crm_ci_page = '/resources/index';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'resources/tech_doc_admin') {
                $crm_ci_page = '/resources/tech_doc_admin';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // VEHICLE/TOOLS
            if ($page['page_url'] == 'add_tools.php') {

                $crm_ci_page = '/vehicles/add_tools';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'add_vehicle.php') {

                $crm_ci_page = '/vehicles/add_vehicle';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // SALES
            if ($page['page_url'] == 'sales_snapshot.php') {

                $crm_ci_page = '/reports/sales_snapshot';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'view_target_agencies.php') {

                $crm_ci_page = '/agency/view_target_agencies';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'add_prospects.php') {

                $crm_ci_page = '/agency2/view_add_prospects';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'agency_audits.php') {

                $crm_ci_page = '/agency/agency_audits';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'view_all_agencies.php' || $page['page_url'] == 'agency/view_all_agencies') {

                $crm_ci_page = '/agency/view_all_agencies';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'sales_documents.php') {

                $crm_ci_page = '/sales/view_sales_documents';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            //  FORMS
            if ($page['page_url'] == 'expense.php') {

                $crm_ci_page = '/reports/view_add_expense/';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'incident_and_injury_report.php') {

                $crm_ci_page = '/users/incident_and_injury_report';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'leave_form.php') {

                $crm_ci_page = '/users/leave_form';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // ACCOUNTS
            if ($page['page_url'] == 'create_credit_request.php') {

                $crm_ci_page = '/credit/request';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'nlm_properties.php') {

                $crm_ci_page = '/properties/nlm_properties';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'statements.php') {

                $crm_ci_page = '/accounts/view_statements';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'accounts_logs.php') {

                $crm_ci_page = '/accounts/view_account_logs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'remittance.php') {

                $crm_ci_page = '/accounts/receipting';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'send_statements.php') {

                $crm_ci_page = '/accounts/send_statements';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'accounts/agency_payments') {

                $crm_ci_page = '/accounts/agency_payments';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'credit/credit_request_summary') {

                $crm_ci_page = '/credit/credit_request_summary';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'credit/refund_request_summary') {

                $crm_ci_page = '/credit/refund_request_summary';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'credit/credit_request') {

                $crm_ci_page = '/credit/credit_request';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'credit/refund_request') {

                $crm_ci_page = '/credit/refund_request';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // ADMIN
            if ($page['page_url'] == 'menu_manager.php') {

                $crm_ci_page = '/menu/manager';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'accomodation.php') {

                $crm_ci_page = '/admin/accommodation';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'alarm_pricing_page.php') {

                $crm_ci_page = '/admin/alarm_pricing_page';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'crm_tasks.php') {

                $crm_ci_page = '/reports/view_crm_tasks';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'agency_site_maintenance_mode.php') {

                $crm_ci_page = '/admin/agency_site_maintenance_mode';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'email_templates.php') {

                $crm_ci_page = '/email/view_email_templates';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'cronjobs/index') {

                $crm_ci_page = '/cronjobs/index';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'admin/emergency_action') {

                $crm_ci_page = '/admin/emergency_action';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'admin/page_totals') {

                $crm_ci_page = '/admin/page_totals';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'create_renewals.php') {

                $crm_ci_page = '/admin/renewals';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'admin_doc.php') {

                $crm_ci_page = '/admin/view_admin_docs';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'noticeboard.php') {

                $crm_ci_page = '/admin/noticeboard';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'resources.php') {

                $crm_ci_page = '/admin/resources';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'alarm_guide.php') {

                $crm_ci_page = '/admin/alarm_guide';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'add_alarm.php') {

                $crm_ci_page = '/admin/add_alarm';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'countries.php') {
                $crm_ci_page = '/admin/countries';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }
            else if ($page['page_url'] == 'view_regions.php') {
                $crm_ci_page = '/admin/view_regions';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }
            else if ($page['page_url'] == 'passwords.php') {
                $crm_ci_page = '/admin/passwords';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'gmapproperties') {
                $crm_ci_page = '/gmapproperties';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // SMS
            if ($page['page_url'] == 'sms/templates') {

                $crm_ci_page = '/sms/templates';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }
            if ($page['page_url'] == 'outgoing_sms.php') {

                $crm_ci_page = '/sms/view_outgoing_sms';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }
            if ($page['page_url'] == 'incoming_sms.php') {

                $crm_ci_page = '/sms/view_incoming_sms';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }
            if ($page['page_url'] == 'job_feedback.php') {

                $crm_ci_page = '/sms/view_job_feedback_sms';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // AGENCY
            if ($page['page_url'] == 'agency/services') {

                $crm_ci_page = '/agency/services';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'agency/multi_agency_users') {

                $crm_ci_page = 'agency/multi_agency_users';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'agency_booking_notes.php') {

                $crm_ci_page = '/agency2/view_agency_booking_notes';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'view_target_agencies.php') {

                $crm_ci_page = '/agency/view_target_agencies';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'add_agency_static.php') {
                $crm_ci_page = '/agency/add_agency';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'view_agencies.php') {
                $crm_ci_page = '/agency/view_agencies';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // CALENDAR
            if ($page['page_url'] == 'view_individual_staff_calendar.php') {

                $crm_ci_page = '/calendar/my_calendar_admin';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'add_calendar_entry_static.php') {

                $crm_ci_page = '/calendar/add_new_entry';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'view_tech_calendar.php') {

                $crm_ci_page = '/calendar/view_tech_calendar';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // USERS
            if ($page['page_url'] == 'view_sats_users.php') {
                $crm_ci_page = '/users';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'add_sats_user.php') {
                $crm_ci_page = '/users/add';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // MESSAGES
            if ($page['page_url'] == 'messages.php') {
                $crm_ci_page = '/messages';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            } else if ($page['page_url'] == 'create_message.php') {
                $crm_ci_page = '/messages/create';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            if ($page['page_url'] == 'benchmark/index') {
                $crm_ci_page = '/benchmark/index';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }

            // API
            if ($page['page_url'] == 'property_me/bulk_connect') { // PMe Bulk Match
                $crm_ci_page = '/property_me/bulk_connect';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'agency_api/linked_properties') { // Linked Properties
                $crm_ci_page = '/agency_api/linked_properties';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'property_me/supplier_pme') { // PMe Supplier
                $crm_ci_page = 'property_me/supplier_pme';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'property_me/agency_connections') { // Agency PropertyMe Connections
                $crm_ci_page = '/property_me/agency_connections';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'palace/index') { // Palace Bulk Match
                $crm_ci_page = '/palace/index';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'palace/supplier_palace') { // Palace Supplier
                $crm_ci_page = '/palace/supplier_palace';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'palace/agent_palace') { // Palace Agent
                $crm_ci_page = '/palace/agent_palace';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'palace/diary_palace') { // Palace Diary Code
                $crm_ci_page = '/palace/diary_palace';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'ourtradie/bulk_connect') { // Palace Diary Code
                $crm_ci_page = '/ourtradie/bulk_connect';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if (strpos($page['page_url'], "/") === 0) { // CI pages that aren't mentioned. put a / at the start of the url
                $page_url = $crm->crm_ci_redirect($page['page_url']);
            }else if ($page['page_url'] == 'console/bulk_connect') { // Console Bulk Connect
                $crm_ci_page = '/console/bulk_connect';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'console/unprocessed_webhooks') { // Console Property Info
                $crm_ci_page = '/console/unprocessed_webhooks';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'property_tree/bulk_connect') { // Property Tree Bulk Connect
                $crm_ci_page = '/property_tree/bulk_connect';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'property_tree/connect_agency') { // Property Tree Connect Agency
                $crm_ci_page = '/property_tree/connect_agency';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'console/tenants_info') { // Console Tenants Info
                $crm_ci_page = '/console/tenants_info';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'console/compliance_info') { // Console Compliance Info
                $crm_ci_page = '/console/compliance_info';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }else if ($page['page_url'] == 'property_me/updated_tenants') { // Pme updated tenants
                $crm_ci_page = '/property_me/updated_tenants';
                $page_url = $crm->crm_ci_redirect($crm_ci_page);
            }


        }
        ?>
        <li>
            <a href="<?php echo $page_url; ?>">
                <span class="crm_page_span" data-page_id="<?php echo $page['crm_page_id']; ?>">
                    <?php echo $page_name; ?>
                    <?php echo ($jtot > 0) ? '<span class="' . $bubble_class . '">' . $jtot . '</span>' : ''; ?>
                </span>
            </a>
        </li>
        <?php
    }
}
?>
