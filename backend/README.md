pm2 start "node log-listener.js" --name "staging worker"


// run this command to seed the data => php artisan db:seed --class=StatusSeeder

php artisan serve --host 192.168.2.192

sqlite3 extension for ubunut

-   sudo apt-get install php-sqlite3

let employee_ids = [1, 2, 3];
function getDatesInRange(startDate, endDate) {
    const date = new Date(startDate.getTime());

    const dates = [];

    // ✅ Exclude end date
    while (date <= endDate) {
        let today = new Date(date);
        let y = today.getFullYear();
        let m = (today.getMonth() + 1).toString().padStart(2, '0');
        let d = today.getDate().toString().padStart(2, '0');

        employee_ids.forEach(e => {
            dates.push(`${y}-${m}-${d},${e}`);

        })

        date.setDate(date.getDate() + 1);
    }

    return dates;
}

const d1 = new Date('2022-01-01');
const d2 = new Date('2022-01-05');

const datesInRange = getDatesInRange(d1, d2);
console.log(datesInRange);


Payslip references

https://www.youtube.com/watch?v=AY3EEGGHV3Y

//SDK photo upload process
php artisan queue:restart
php artisan queue:work

nohup php artisan queue:work

//background run  
 php artisan task:check_device_health

// node socket
nohup node leaveNotifications  
 nohup node employeeLeaveNotifications

//view nohup services node
pgrep -a node
kill 155555

/etc/nginx/sites-available to allow iframes edit configuration
sudo systemctl restart nginx

$ sudo systemctl restart nginx

SDK Live port : 9001
SDK Live port : 9001




public function getCurrentMonthDates()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        // Get the number of days in the current month
        $numDays = date('t', strtotime("$currentYear-$currentMonth-01"));
        $dates = [];
        // Loop through the days of the current month and display them
        for ($day = 1; $day <= $numDays; $day++) {
            $date = sprintf("%04d-%02d-%02d", $currentYear, $currentMonth, $day);
            $dates[] = ["date" => $date, "day" => date("D", strtotime($date))];
            // $dates[] = $date;
        }
        return $dates;
    }