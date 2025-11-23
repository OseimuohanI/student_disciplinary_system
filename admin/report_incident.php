<?php
require_once '../config/database.php';
require_once '../classes/Incident.php';

$incidentHandler = new Incident($link);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportDate = $_POST['report_date'];
    $location = $_POST['location'];
    $reporterStaffID = $_POST['reporter_staff_id']; // Should come from session data in a real app
    $studentID = $_POST['student_id'];
    $description = $_POST['description'];
    $offenseTypeID = $_POST['offense_type_id'];

    if ($incidentHandler->createIncident($reportDate, $location, $reporterStaffID, $studentID, $description, $offenseTypeID)) {
        $message = "Incident reported successfully!";
    } else {
        $message = "Error reporting incident.";
    }
}

// Fetch students and offense types for the dropdowns (simplified)
$students = mysqli_query($link, "SELECT StudentID, FirstName, LastName FROM student");
$offenseTypes = mysqli_query($link, "SELECT OffenseTypeID, Description FROM offensetype");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Report Incident</title>
</head>
<body>
    <h1>Report a New Incident</h1>

    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- Form fields go here -->
        <div>
            <label for="report_date">Report Date:</label>
            <input type="datetime-local" id="report_date" name="report_date" required>
        </div>
        
        <div>
            <label for="student_id">Student:</label>
            <select id="student_id" name="student_id" required>
                <?php while ($row = mysqli_fetch_assoc($students)): ?>
                    <option value="<?php echo $row['StudentID']; ?>"><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="offense_type_id">Offense Type:</label>
            <select id="offense_type_id" name="offense_type_id" required>
                <?php while ($row = mysqli_fetch_assoc($offenseTypes)): ?>
                    <option value="<?php echo $row['OffenseTypeID']; ?>"><?php echo htmlspecialchars($row['Description']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>
        </div>
        
        <div>
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>

        <!-- In a real system, ReporterStaffID would be pulled securely from the active user session -->
        <input type="hidden" name="reporter_staff_id" value="1"> 

        <button type="submit">Submit Report</button>
    </form>
</body>
</html>