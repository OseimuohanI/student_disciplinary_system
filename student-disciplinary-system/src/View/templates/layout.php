<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Disciplinary System</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <header>
        <h1>Student Disciplinary System</h1>
        <nav>
            <ul>
                <li><a href="/students">Students</a></li>
                <li><a href="/incidents">Incidents</a></li>
                <li><a href="/report">Report Incident</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php echo $content; ?>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Student Disciplinary System</p>
    </footer>
</body>
</html>