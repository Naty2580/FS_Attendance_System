<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e3a8a; font-size: 20px; }
        .header p { margin: 5px 0 0 0; color: #666; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #1e3a8a; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #888; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sunday School Attendance Report</h1>
        <p>
            <strong>Date Range:</strong> {{ $filters['from_date'] }} to {{ $filters['to_date'] }} <br>
            <strong>Class:</strong> {{ $filters['class_name'] ?? 'All Classes' }} | 
            <strong>Session Type:</strong> {{ $filters['session_type_name'] ?? 'All Types' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th class="text-center">Present</th>
                <th class="text-center">Late</th>
                <th class="text-center">Permission</th>
                <th class="text-center">Absent</th>
                <th class="text-center">Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $student['student_number'] }}</td>
                <td>{{ $student['full_name'] }}</td>
                <td class="text-center">{{ $student['present'] }}</td>
                <td class="text-center">{{ $student['late'] }}</td>
                <td class="text-center">{{ $student['permission'] }}</td>
                <td class="text-center">{{ $student['absent'] }}</td>
                <td class="text-center bold">{{ $student['score'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>