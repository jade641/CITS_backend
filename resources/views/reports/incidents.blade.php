<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incident Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { margin-bottom: 0; }
        .meta { margin-bottom: 16px; color: #4b5563; }
        .grid { width: 100%; margin-bottom: 16px; }
        .grid td { padding: 6px 8px; border: 1px solid #d1d5db; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Cyber Incident Ticketing System Report</h1>
    <div class="meta">Generated at {{ $generatedAt->format('Y-m-d H:i:s') }}</div>

    <table class="grid">
        <tr>
            <td><strong>Total Incidents</strong></td>
            <td>{{ $summary['totalIncidents'] }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Title</th>
                <th>Severity</th>
                <th>Category</th>
                <th>Status</th>
                <th>Reporter</th>
                <th>Assignee</th>
                <th>Reported At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($incidents as $incident)
                <tr>
                    <td>{{ $incident->ticket_number }}</td>
                    <td>{{ $incident->title }}</td>
                    <td>{{ ucfirst($incident->severity) }}</td>
                    <td>{{ $incident->category?->name }}</td>
                    <td>{{ $incident->status?->name }}</td>
                    <td>{{ $incident->reporter?->email }}</td>
                    <td>{{ $incident->currentAssignee?->email }}</td>
                    <td>{{ optional($incident->reported_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
