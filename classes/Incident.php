<?php
require_once __DIR__ . '/../config/database.php';

class Incident {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createIncident($reportDate, $location, $reporterStaffID, $studentID, $description, $offenseTypeID) {
        // Start a transaction (good practice for multi-table inserts)
        mysqli_begin_transaction($this->conn);

        try {
            // 1. Insert into incidentreport
            $stmt = $this->conn->prepare("INSERT INTO incidentreport (ReportDate, Location, ReporterStaffID, StudentID, Description, Status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("ssiis", $reportDate, $location, $reporterStaffID, $studentID, $description);
            $stmt->execute();
            $incidentID = $this->conn->insert_id; // Get the newly created IncidentID
            $stmt->close();

            // 2. Insert into reportoffense
            $stmt_offense = $this->conn->prepare("INSERT INTO reportoffense (IncidentID, OffenseTypeID) VALUES (?, ?)");
            $stmt_offense->bind_param("ii", $incidentID, $offenseTypeID);
            $stmt_offense->execute();
            $stmt_offense->close();

            // Commit the transaction
            mysqli_commit($this->conn);
            return true;

        } catch (Exception $e) {
            // Rollback the transaction on error
            mysqli_rollback($this->conn);
            error_log($e->getMessage());
            return false;
        }
    }

    // You would add other methods here (e.g., getIncidents(), updateStatus(), etc.)
}
?>