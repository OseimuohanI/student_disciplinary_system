<?php

namespace App\Repository;

use App\Model\Incident;
use PDO;

class IncidentRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(Incident $incident): bool
    {
        $stmt = $this->db->prepare("INSERT INTO incidents (ReportDate, Location, ReporterStaffID, StudentID, Description, Status, CreatedAt) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $incident->getReportDate(),
            $incident->getLocation(),
            $incident->getReporterStaffID(),
            $incident->getStudentID(),
            $incident->getDescription(),
            $incident->getStatus(),
            $incident->getCreatedAt()
        ]);
    }

    public function update(Incident $incident): bool
    {
        $stmt = $this->db->prepare("UPDATE incidents SET ReportDate = ?, Location = ?, ReporterStaffID = ?, StudentID = ?, Description = ?, Status = ? WHERE IncidentID = ?");
        return $stmt->execute([
            $incident->getReportDate(),
            $incident->getLocation(),
            $incident->getReporterStaffID(),
            $incident->getStudentID(),
            $incident->getDescription(),
            $incident->getStatus(),
            $incident->getIncidentID()
        ]);
    }

    public function findById(int $id): ?Incident
    {
        $stmt = $this->db->prepare("SELECT * FROM incidents WHERE IncidentID = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Incident($data);
        }

        return null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM incidents");
        $incidents = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $incidents[] = new Incident($data);
        }

        return $incidents;
    }
}