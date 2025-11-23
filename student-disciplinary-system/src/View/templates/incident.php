<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Reports</title>
    <link rel="stylesheet" href="/path/to/your/styles.css">
</head>
<body>
    <header>
        <h1>Incident Reports</h1>
    </header>
    <main>
        <h2>Reported Incidents</h2>
        <table>
            <thead>
                <tr>
                    <th>Incident ID</th>
                    <th>Report Date</th>
                    <th>Location</th>
                    <th>Reporter Staff ID</th>
                    <th>Student ID</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $incident): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($incident->IncidentID); ?></td>
                        <td><?php echo htmlspecialchars($incident->ReportDate); ?></td>
                        <td><?php echo htmlspecialchars($incident->Location); ?></td>
                        <td><?php echo htmlspecialchars($incident->ReporterStaffID); ?></td>
                        <td><?php echo htmlspecialchars($incident->StudentID); ?></td>
                        <td><?php echo htmlspecialchars($incident->Description); ?></td>
                        <td><?php echo htmlspecialchars($incident->Status); ?></td>
                        <td><?php echo htmlspecialchars($incident->CreatedAt); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Student Disciplinary System</p>
    </footer>
</body>
</html>