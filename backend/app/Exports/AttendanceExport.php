<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AttendanceExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize
{
    protected $query;

    public function __construct(Builder|\Illuminate\Database\Eloquent\Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        // Use `select()` to reduce fetched columns if possible
        return $this->query;
    }

    public function headings(): array
    {
        return [
            "Date",
            "E.ID",
            "Full Name",
            "Department",
            "Position",
            "In1", "Out1",
            "In2", "Out2",
            "In3", "Out3",
            "In4", "Out4",
            "In5", "Out5",
            "In6", "Out6",
            "In7", "Out7",
            "Total Hrs",
            "OT",
            "Status",
        ];
    }

    public function map($row): array
    {
        // Append default log values if logs are missing
        $logArray = [];

        $logs = $row->logs ?? [];
        $count = count($logs);

        if ($count < 7) {
            for ($i = $count; $i < 7; $i++) {
                $logs[] = null;
            }
        }

        foreach (range(0, 6) as $index) {
            $logArray[] = $logs[$index]["in"] ?? "---";
            $logArray[] = $logs[$index]["out"] ?? "---";
        }

        return array_merge([
            $row->date,
            (string) ($row->employee->employee_id ?? "---"),
            $row->employee->full_name ?? $row->employee->first_name . ' ' . $row->employee->last_name,
            $row->employee->department->name ?? "---",
            $row->employee->designation->name ?? "---",
        ], $logArray, [
            $row->total_hrs ?? "---",
            $row->ot ?? "---",
            $row->status ?? "---",
        ]);
    }

    public function styles($sheet)
    {
        return [
            // Apply text format to the 'Email' column
            'C' => ['numberFormat' => NumberFormat::FORMAT_TEXT],
        ];
    }
}
