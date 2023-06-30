<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;

        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }


        .table-container {
            display: table;
            width: 100%;
        }

        .table-cell {
            display: table-cell;
        }

        .gap {
            width: 2%;
        }
    </style>
</head>

<body>
    {{-- version 1 --}}
    <div class="table-container">
        <div class="table-cell" style="width:33%;">
            <img style="border-radius: 10%; " width="100" height="100"
                src="https://th.bing.com/th/id/R.b4e3fb857db675de7df59ab6f4cf30ab?rik=gbQLvTh9DaC6tQ&pid=ImgRaw&r=0"
                alt="">

            <p>AKIL SECURITY AND ALARM SYSTEM</p>
            <small>Bur Dubai, United Arab Emirates</small>
        </div>
        <div class="table-cell" style="width:34%; text-align:center;">
            <h2 class="payslip-title"><u>PAYSLIP </u></h2>(Version 1)
        </div>
        <div class="table-cell" style="width:33%; text-align:right;">
            <p>Payslip No: # 987654</p>
            <p>Date: June 30, 2023</p>
        </div>
    </div>
    <br>
    <div class="table-container">
        <div class="table-cell" style="width:49%; border:1px solid gray; border-radius:5px; padding:10px;">
            <div>
                <strong style="margin:12px">Employee Details</strong>
                <hr>
            </div>
            <table style="width:100%;">
                <tr>
                    <td>Employee Name</td>
                    <td style="text-align: right;">John Doe</td>
                </tr>
                <tr>
                    <td>Employee Id</td>
                    <td style="text-align: right;">1111</td>
                </tr>
                <tr>
                    <td>Department</td>
                    <td style="text-align: right;">IT</td>
                </tr>
                <tr>
                    <td style="">Designation</td>
                    <td style="text-align: right; ;">Developer</td>
                </tr>
            </table>
        </div>

        <div class="gap"></div>

        <div class="table-cell" style="width:49%; border:1px solid gray; border-radius:5px; padding:10px;">
            <div>
                <strong style="margin:12px">Other Details</strong>
                <hr>
            </div>
            <table style="width:100%;">


                <tr>
                    <td>Salary Month</td>
                    <td style="text-align: right;">June, 2023</td>
                </tr>
                <tr>
                    <td>Presents</td>
                    <td style="text-align: right;">25</td>
                </tr>
                <tr>
                    <td style="">Absent</td>
                    <td style="text-align: right; ">05
                    </td>
                </tr>
            </table>
        </div>

    </div>
    <br>
    <div class="table-container">
        <div class="table-cell" style="width:49%; border:1px solid gray; border-radius:5px; padding:10px;">
            <table style="width:100%;">
                <tr>
                    <th>Earnings</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
                <tr>
                    <td>Basic Salary</td>
                    <td style="text-align: right;">$5,000</td>
                </tr>
                <tr>
                    <td>Overtime</td>
                    <td style="text-align: right;">$500</td>
                </tr>
                <tr>
                    <td>Bonuses</td>
                    <td style="text-align: right;">$1,000</td>
                </tr>


                <tr>
                    <th style="border:none">Total Earning</th>
                    <th style="text-align: right; border:none;">$1,000</th>
                </tr>
            </table>
        </div>

        <div class="gap"></div>

        <div class="table-cell" style="width:49%; border:1px solid gray; border-radius:5px; padding:10px;">
            <table style="width:100%;">
                <tr>
                    <th>Deductions</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
                <tr>
                    <td>Absent</td>
                    <td style="text-align: right;">$5,000</td>
                </tr>

                <tr>
                    <td style="height:20px;"></td>
                    <td style="text-align: right;"></td>
                </tr>
                <tr>
                    <td style="height:20px;"></td>
                    <td style="text-align: right;"></td>
                </tr>

                <tr>
                    <th style="border:none">Total Deduction</th>
                    <th style="text-align: right; border:none;">$1,000</th>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <div class="table-container">
        <table style="width:100%;">
            <tr>
                <th style="border:none">Net Salary: $1,000</th>
                <th style="text-align: left; border:none;"></th>
            </tr>
        </table>


    </div>

    {{-- version 2 --}}

    <div style="display: none">
        <div class="table-container">
            <div class="table-cell" style="width:33%;">
                <img style="border-radius: 10%; " width="100" height="100"
                    src="https://th.bing.com/th/id/R.b4e3fb857db675de7df59ab6f4cf30ab?rik=gbQLvTh9DaC6tQ&pid=ImgRaw&r=0"
                    alt="">

                <p>AKIL SECURITY AND ALARM SYSTEM</p>
                <small>Bur Dubai, United Arab Emirates</small>
            </div>
            <div class="table-cell" style="width:34%; text-align:center;">
                <h2 class="payslip-title"><u>PAYSLIP </u></h2>( Version 2)
            </div>
            <div class="table-cell" style="width:33%; text-align:right;">
                <p>Payslip No: # 987654</p>
                <p>Date: June 30, 2023</p>
            </div>
        </div>
        <br>
        <div class="table-container">
            <div class="table-cell" style="width:100%; border:1px solid gray; border-radius:5px; padding:10px;">
                <div>
                    <strong style="margin:12px">Employee Details</strong>
                    <hr>
                </div>
                <table style="width:100%;">
                    <tr>
                        <td>Employee Name</td>
                        <td style="text-align: right;">John Doe</td>
                    </tr>
                    <tr>
                        <td>Employee Id</td>
                        <td style="text-align: right;">1111</td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td style="text-align: right;">IT</td>
                    </tr>
                    <tr>
                        <td style="border:none">Designation</td>
                        <td style="text-align: right; border:none;">Developer</td>
                    </tr>
                    <tr>
                        <td>Salary Month</td>
                        <td style="text-align: right;">June, 2023</td>
                    </tr>
                    <tr>
                        <td>Presents</td>
                        <td style="text-align: right;">25</td>
                    </tr>
                    <tr>
                        <td style="border:none">Absent</td>
                        <td style="text-align: right; border:none">05
                        </td>
                    </tr>
                </table>


            </div>

        </div>
        <br>
        <div class="table-container">
            <div class="table-cell" style="width:100%; border:1px solid gray; border-radius:5px; padding:10px;">
                <table style="width:100%;">
                    <tr>
                        <th>Earnings</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                    <tr>
                        <td>Basic Salary</td>
                        <td style="text-align: right;">$5,000</td>
                    </tr>
                    <tr>
                        <td>Overtime</td>
                        <td style="text-align: right;">$500</td>
                    </tr>
                    <tr>
                        <td style="border:none">Bonuses</td>
                        <td style="text-align: right; border:none;">$1,000</td>
                    </tr>


                    <tr>
                        <th style="border:none">Total Earning</th>
                        <th style="text-align: right; border:none;">$1,000</th>
                    </tr>
                </table>

            </div>
        </div>
        <br>
        <div class="table-container">
            <div class="table-cell" style="width:100%; border:1px solid gray; border-radius:5px; padding:10px;">


                <table style="width:100%;">
                    <tr>
                        <th>Deductions</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                    <tr>
                        <td>Absent</td>
                        <td style="text-align: right;">$5,000</td>
                    </tr>

                    <tr>
                        <td style="height:20px;"></td>
                        <td style="text-align: right;"></td>
                    </tr>
                    <tr>
                        <td style="height:20px;"></td>
                        <td style="text-align: right;"></td>
                    </tr>

                    <tr>
                        <th style="border:none">Total Deduction</th>
                        <th style="text-align: right; border:none;">$1,000</th>
                    </tr>
                </table>
            </div>
        </div>
        <br>
        <div class="table-container">
            <table style="width:100%;">
                <tr>
                    <th style="border:none">Net Salary: $1,000</th>
                    <th style="text-align: left; border:none;"></th>
                </tr>
            </table>


        </div>
    </div>


</body>

</html>
