{{-- @php
    phpinfo();
    die();
    @endphp --}}
<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #eeeeee;
            text-align: left;
        }

        /* tr:nth-child(even) {
                    background-color: #eeeeee;
                } */

        th {
            font-size: 9px;

        }

        td {
            font-size: 7px;
        }

        footer {
            width: 100%;
            position: fixed;
            bottom: 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <td style="text-align: center; border :none;">
                <div>
                    <h2>Weekly Summary</h2>
                </div>
            </td>
        </tr>
    </table>


    <table style="margin-top: -20px !important;">
        <tr>
            <td style="text-align: left; border :none; padding:15px;">
                <div style="display: flex">
                    {{-- 1665500012 --}}
                    {{-- <img src="{{ $company->logo }}" height="70px" width="70"> --}}
                    <img src="{{ getcwd() . '/upload/1665500012.jpeg' }}" height="70px" width="70">

                    <table style="text-align: left; border :none; margin-top:10px">
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: left; border :none;"><strong>{{ $company->name ?? 'V Perfume' }}
                                </strong></td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: left; border :none;"><strong>{{ $company->company_code ?? 'AE0001' }}
                                </strong>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: left; border :none;">
                                <strong>{{ 'V Perfume LLC BR.' ?? 'Waleed Road Burdubai' }}
                                </strong>
                            </td>
                        </tr>
                    </table>

                </div>
            </td>
            <td style="text-align: right; border :none; width:120px;">
                <table style="text-align: left; border :none; margin-top:0px;">

                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none; color: green;"><b>Present: </b></td>
                        <td style="text-align: left; border :none; color: green;">
                            {{ $info->total_present ?? 14 }}</td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none; color: red;"><b>Absent: </b></td>
                        <td style="text-align: left; border :none; color: red;">{{ $info->total_absent ?? 98 }}</td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none; color: #f34100ed;"><b>Late: </b></td>
                        <td style="text-align: left; border :none; color: #f34100ed;">{{ $info->total_missing ?? 45 }}
                        </td>
                    </tr>

                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none;"><strong>Date: </strong></td>
                        <td style="text-align: left; border :none;">{{ $info->daily_date ?? '2022-10-21' }}</td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none;"><b>Department: </b></td>
                        <td style="text-align: left; border :none;">{{ $info->department ?? 'IT' }}</td>
                    </tr>

                </table>
            </td>

            </td>
        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 7; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach
        </tr>
        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 7; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Arvind</td>
            <td style="text-align: left;"><b>EID</b>: 000333</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>




    <footer>
        <table>
            <tr>
                <td style="text-align: left;"><b>Device</b>: Main Entrance = MED, Back Entrance = BED</td>
                <td style="text-align: left;"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                <td style="text-align: left;"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2</td>
                <td style="text-align: right;">
                    Date : {{ date('d/M/Y H:i:s') }}
                </td>
            </tr>
        </table>
    </footer>
</body>

</html>
