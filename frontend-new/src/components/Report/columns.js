import ProfilePicture from "@/components/ProfilePicture";
import { getBgColor, getTextColor, setStatusLabel } from "@/lib/utils";
import { Eye } from "lucide-react";

export default (shiftTypeId, { onViewLogs } = {}) => {
    // Base columns
    const columns = [

        {
            key: "name",
            header: "Name",
            render: ({ employee }) => (
                <div className="flex items-center space-x-3">

                    <ProfilePicture src={employee.profile_picture} />

                    <div>
                        <p className="font-medium text-sm text-slate-600 dark:text-slate-300 hidden xl:table-cell">{employee?.first_name}</p>
                        <p className="text-sm text-slate-600 dark:text-slate-300">
                            ID: {employee.employee_id}
                        </p>
                    </div>
                </div>
            ),
        },

        {
            key: "date", header: "Date",
            render: (log) => (<p className="text-sm text-slate-600 dark:text-slate-300">{log.day.toString().substring(0, 3)}, {log.date}</p>)
        },
        {
            key: "department", header: "Dept",
            render: ({ employee }) => (
                <p className="text-sm text-slate-600 dark:text-slate-300">{employee?.department?.name}</p>
            ),
        },
        {
            key: "shift", header: "Shift",
            render: (log) => (

                <p className="text-sm text-slate-600 dark:text-slate-300">{log.shift?.name}</p>
            ),
        },


    ];

    // Define in/out columns
    const inOutColumns = [];
    const totalShifts = 7; // For example, 7 in/out pairs
    for (let i = 1; i <= totalShifts; i++) {
        inOutColumns.push({
            key: `in${i}`,
            header: `In${i}`,
            render: (log) => (<p className="text-sm text-slate-600 dark:text-slate-300">{`${log[`in${i}`] || "—"}`}</p>)

        });
        inOutColumns.push({
            key: `out${i}`,
            header: `Out${i}`,
            render: (log) => (<p className="text-sm text-slate-600 dark:text-slate-300">{`${log[`out${i}`] || "—"}`}</p>)
        });
    }

    // Other columns
    const otherColumns = [
        { key: "ot", header: "OT", render: (log) => (<p className="text-sm text-slate-600 dark:text-slate-300">{log.ot}</p>) },
        { key: "total_hrs", header: "Total Hrs", render: (log) => (<p className="text-sm text-slate-600 dark:text-slate-300">{log.total_hrs}</p>) },
        {
            key: "status",
            header: "Status",
            render: (log) => (
                <span className={`text-sm ${getBgColor(log.status)}`}
                    style={{
                        padding: "2px 10px",
                        borderRadius: "50px",
                    }}
                >
                    {setStatusLabel(log?.status)}
                </span>
            ),
        },
        {
            accessorKey: "actions",
            header: "Actions",
            render: (item) => {
                return (
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => onViewLogs?.(item)}
                            className="inline-flex h-8 w-8 items-center justify-center rounded-md   text-primary  hover:bg-primary/5"
                            title="View Logs"
                        >
                            <Eye className="w-4 h-4" />
                        </button>
                    </div>
                );
            },
        },
    ];

    // If shiftTypeId == 2, use dynamic in/out columns
    return shiftTypeId == '2'
        ? [...columns, ...inOutColumns, ...otherColumns]
        : [
            ...columns,
            {
                key: "in",
                header: "In",
                render: (log) => (<p className={`text-sm text-slate-600 dark:text-slate-300`}>{`${log?.in}`}</p>),
            },
            {
                key: "out",
                header: "Out",
                render: (log) => (<p className={`text-sm text-slate-600 dark:text-slate-300`}>{`${log?.out}`}</p>),
            },
            {
                key: "late_coming",
                header: "Late In",
                render: (log) => (<p className={`text-sm text-slate-600 dark:text-slate-300`}>{`${log?.late_coming}`}</p>),
            },
            {
                key: "early_going",
                header: "Early Out",
                render: (log) => (<p className={`text-sm text-slate-600 dark:text-slate-300`}>{`${log?.early_going}`}</p>),
            },
            ...otherColumns,
        ];
};
