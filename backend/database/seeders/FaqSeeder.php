<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faqs = [
            [
                "question" => "How do I download an attendance report?",
                "answer" => "To download an attendance report, go to the 'Reports' section, select the desired date range, and click the 'Download' button."
            ],
            [
                "question" => "How do I create a new employee profile?",
                "answer" => "To create a new employee profile, navigate to the 'Employees' section, click 'Add Employee', and fill in the required details such as name, employee ID, and department."
            ],
            [
                "question" => "How do I create a shift?",
                "answer" => "To create a shift, go to the 'Shifts' section, click 'Add Shift', and define the shift timings, break times, and other relevant details."
            ],
            [
                "question" => "How do I create a schedule?",
                "answer" => "To create a schedule, go to the 'Schedules' section, click 'Create Schedule', and assign shifts to employees for specific dates."
            ],
            [
                "question" => "How do I schedule employees?",
                "answer" => "To schedule employees, go to the 'Schedules' section, select the desired week or month, and assign shifts to employees by dragging and dropping or using the 'Assign Shift' option."
            ],
            [
                "question" => "How do I edit an employee's details?",
                "answer" => "To edit an employee's details, go to the 'Employees' section, find the employee, click 'Edit', and update the necessary information."
            ],
            [
                "question" => "How do I delete an employee profile?",
                "answer" => "To delete an employee profile, go to the 'Employees' section, find the employee, click 'Delete', and confirm the action."
            ],
            [
                "question" => "How do I view attendance records?",
                "answer" => "To view attendance records, go to the 'Attendance' section, select the desired date range, and view the records in the table or calendar view."
            ],
            [
                "question" => "How do I mark attendance manually?",
                "answer" => "To mark attendance manually, go to the 'Attendance' section, select the date, and use the 'Mark Attendance' button to update the status for each employee."
            ],
            [
                "question" => "How do I set up overtime rules?",
                "answer" => "To set up overtime rules, go to the 'Settings' section, click 'Overtime Rules', and define the conditions and rates for overtime."
            ],
            [
                "question" => "How do I export attendance data?",
                "answer" => "To export attendance data, go to the 'Reports' section, select the desired date range, and click the 'Export' button to download the data in CSV or Excel format."
            ],
            [
                "question" => "How do I add a holiday to the calendar?",
                "answer" => "To add a holiday, go to the 'Calendar' section, click 'Add Holiday', and enter the holiday name and date."
            ],
            [
                "question" => "How do I assign a shift to multiple employees?",
                "answer" => "To assign a shift to multiple employees, go to the 'Schedules' section, select the shift, and use the 'Assign to Multiple' option to choose the employees."
            ],
            [
                "question" => "How do I check for attendance discrepancies?",
                "answer" => "To check for discrepancies, go to the 'Attendance' section, use the 'Discrepancies' filter, and review the flagged records."
            ],
            [
                "question" => "How do I set up biometric integration?",
                "answer" => "To set up biometric integration, go to the 'Settings' section, click 'Biometric Devices', and follow the instructions to connect your device."
            ],
            [
                "question" => "How do I generate a payroll report?",
                "answer" => "To generate a payroll report, go to the 'Reports' section, select 'Payroll', choose the date range, and click 'Generate Report'."
            ],
            [
                "question" => "How do I customize attendance policies?",
                "answer" => "To customize attendance policies, go to the 'Settings' section, click 'Attendance Policies', and configure the rules as per your requirements."
            ],
            [
                "question" => "How do I notify employees about their schedules?",
                "answer" => "To notify employees, go to the 'Schedules' section, select the schedule, and use the 'Notify Employees' option to send notifications via email or SMS."
            ],
            [
                "question" => "How do I handle shift swaps?",
                "answer" => "To handle shift swaps, go to the 'Schedules' section, select the shift, and use the 'Swap Shift' option to approve or manage swap requests."
            ]
            // Add the rest of the FAQs here
        ];

        Faq::insert($faqs);

        ld(Faq::get());
    }
}
