php artisan serve --host 192.168.2.174

sqlite3 extension for ubunut
  - sudo apt-get install php-sqlite3

function getDatesInRange(startDate, endDate) {
    const date = new Date(startDate.getTime());
  
    const dates = [];
  
    // ✅ Exclude end date
    while (date < endDate) {
            let today = new Date(date);
            let [y,m,d] = [today.getDate(),today.getMonth() + 1,today.getFullYear()]

      dates.push(`${y}-${m}-${d}`);
      date.setDate(date.getDate() + 1);
    }
  
    return dates;
  }
  
  const d1 = new Date('2022-01-18');
  const d2 = new Date('2022-01-24');
  
  console.log(getDatesInRange(d1, d2));


  Payslip references

  https://www.youtube.com/watch?v=AY3EEGGHV3Y

//SDK photo upload process
php artisan queue:restart
  php artisan queue:work

  nohup php artisan queue:work --daemon &   
  
  //background run   
  php artisan task:check_device_health

// node socket
  nohup node leaveNotifications --daemon &
   nohup node employeeLeaveNotifications --daemon &

   //view nohup services node
    pgrep -a node
    kill 155555



SDK  Live port : 9001
SDK  Live port : 9001
