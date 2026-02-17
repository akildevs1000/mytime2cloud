# Attendance Report React Components

A complete React conversion of the Vue-based Attendance Report component system with child components.

## Components

### 1. **Attendance.jsx** (Main Component)
The main Attendance Report component that manages the entire attendance report workflow.

#### Features:
- Multiple report templates (Monthly Format A/B/C, Daily)
- Employee, Department, and Branch filtering
- Status-based filtering
- Tab-based report generation (Single, Double, Multi shift types)
- Regenerate report functionality
- Missing logs viewing
- Export to Print/PDF/Excel
- Permission-based access control

#### Props:
```javascript
<Attendance 
  authUser={currentUser}        // Current authenticated user object
  backendUrl="http://api.example.com"  // Backend API base URL
  pagePermission={permissionService}   // Permission service instance
  axios={axiosInstance}         // Axios instance for API calls
/>
```

#### Required Methods from Props:
- `authUser.id`, `authUser.company_id`, `authUser.user_type`, `authUser.branch_id`, `authUser.company.name`
- `pagePermission.can(permission_string)` - Check user permissions
- `axios.get(endpoint, options)` - Make HTTP requests

### 2. **AttendanceReport.jsx** (Child Component)
Displays tabular attendance data for different shift types.

#### Props:
```javascript
<AttendanceReport
  title="General Reports"
  headers={generalHeadersArray}
  report_template="Template1"   // Template ID
  payload1={payloadObject}      // Data payload
  render_endpoint="render_general_report"  // API endpoint
  shift_type_id={0}             // Shift type identifier
  onReturnedPayload={callback}  // Callback for payload updates
/>
```

#### Features:
- Dynamic table rendering
- Loading state management
- Responsive design
- Empty state handling

### 3. **MissingRecords.jsx** (Child Component)
Displays missing device logs in a tabular format.

#### Features:
- Automatic data fetching
- Status badge indicators
- Loading state management
- Empty state handling

## Installation & Setup

### 1. Dependencies
Ensure you have the following installed:
```bash
npm install axios react react-dom
```

### 2. Integration with Existing Project

#### In your main App or Router:
```javascript
import Attendance from './components/Reports/Attendance';

function App() {
  return (
    <Attendance 
      authUser={authUser}
      backendUrl={process.env.REACT_APP_BACKEND_URL}
      pagePermission={permissionService}
      axios={axiosInstance}
    />
  );
}
```

#### With Context/Redux (Alternative):
```javascript
// If using context or Redux, you can access props from those instead
import { useAuth } from './contexts/AuthContext';
import { useAxios } from './hooks/useAxios';

function App() {
  const authUser = useAuth();
  const axios = useAxios();
  
  return <Attendance authUser={authUser} axios={axios} />;
}
```

## API Endpoints Expected

The component expects the following API endpoints:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `attendance-statuses` | GET | Get available attendance statuses |
| `get_attendance_tabs` | GET | Get enabled shift types |
| `branch` | GET | Get all branches |
| `department-list` | GET | Get departments |
| `/scheduled_employees_with_type` | GET | Get filtered employees |
| `start-report-generation` | GET | Start report generation |
| `render_logs` | GET | Regenerate logs |
| `{multi_in_out_}monthly` | GET | Export monthly report |
| `{multi_in_out_}monthly_download_pdf` | GET | Export as PDF |
| `{multi_in_out_}monthly_download_csv` | GET | Export as Excel |

## API Request Parameters

### Get Scheduled Employees
```javascript
{
  params: {
    per_page: 1000,
    branch_ids: [1, 2],
    company_id: 5,
    department_ids: [10, 20],
    shift_type_id: 0
  }
}
```

### Render Report
```javascript
{
  params: {
    dates: ['2025-01-01', '2025-01-31'],
    company_ids: [5],
    user_id: 123,
    updated_by: 123,
    reason: '',
    employee_ids: [225, 226],
    shift_type_id: 0,
    company_id: 5,
    showTabs: JSON.stringify({single: true, double: true, multi: true})
  }
}
```

## State Structure

### Main Payload
```javascript
{
  from_date: '2025-01-01',
  to_date: '2025-01-31',
  employee_id: [225, 226],
  department_ids: [10],
  statuses: ['present', 'absent'],
  branch_id: 1,
  daily_date: '2025-01-15',
  report_template: 'Template1'
}
```

## Permissions

The component checks for the following permissions:
- `attendance_report_access` - Allow component access
- `attendance_report_view` - Display and view reports

## Customization

### Changing Headers
Modify the header arrays in the Attendance component:

```javascript
const generalHeaders = [
  { text: 'Date', value: 'date', display: 'Date' },
  { text: 'Employee ID', value: 'employee_id', display: 'Employee ID' },
  // Add more columns as needed
];
```

### Styling
CSS classes are available for customization:
- `.attendance-container` - Main container
- `.toolbar` - Filter toolbar
- `.tabs-container` - Tab container
- `.dialog-overlay` - Dialog background
- `.dialog-content` - Dialog box
- `.table-container` - Table wrapper

Override in your CSS:
```css
.attendance-container {
  background-color: #fff;
  padding: 30px;
}
```

### Report Templates
Modify available templates in the Attendance component:

```javascript
const templates = [
  { id: 'Template1', name: 'Monthly Report Format A' },
  { id: 'Template2', name: 'Monthly Report Format B' },
  // Add new templates
];
```

## Handling Responses

### Attendance Report Response Format
```javascript
[
  {
    date: '2025-01-15',
    employee_id: 225,
    in_time: '09:00',
    out_time: '17:30',
  },
  // More rows...
]
```

### Missing Records Response Format
```javascript
[
  {
    employee_id: 225,
    device_name: 'Device A',
    date: '2025-01-15',
    time: '09:00',
    status: 'Missing'
  },
  // More rows...
]
```

## Migration from Vue to React

Key differences from Vue:
- Use `useState` instead of `data()`
- Use `useCallback` for memoized functions
- Use `useRef` for imperative operations
- Use `useMemo` for computed properties
- Event handlers are prefixed with `on` (e.g., `onReturnedPayload`)
- Class binding with conditional strings
- Template literals for dynamic values

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Performance Tips

1. Memoize callbacks with `useCallback` to prevent unnecessary re-renders
2. Use `useMemo` for derived values
3. Implement pagination for large datasets
4. Debounce filter changes
5. Lazy load dialogs content

## Troubleshooting

### Component not rendering
- Check permissions: `pagePermission.can('attendance_report_access')`
- Verify `authUser` object has required properties

### No data displayed
- Verify API endpoints are correct
- Check network requests in browser DevTools
- Ensure parameters match API expectations

### Styling issues
- Verify CSS files are imported
- Check for CSS conflicts with global styles
- Use browser DevTools to debug CSS

## License

Same as parent project

## Additional Notes

- All timestamps are expected in ISO format (YYYY-MM-DD)
- Dialog overlays use fixed positioning - ensure proper z-index management
- File exports are handled via document.createElement('a')
- The component manages its own state independently
