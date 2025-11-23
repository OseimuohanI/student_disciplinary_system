<?php

namespace App\Model;

class Incident
{
    private $incidentID;
    private $reportDate;
    private $location;
    private $reporterStaffID;
    private $studentID;
    private $description;
    private $status;
    private $createdAt;

    public function __construct($incidentID, $reportDate, $location, $reporterStaffID, $studentID, $description, $status, $createdAt)
    {
        $this->incidentID = $incidentID;
        $this->reportDate = $reportDate;
        $this->location = $location;
        $this->reporterStaffID = $reporterStaffID;
        $this->studentID = $studentID;
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }

    public function getIncidentID()
    {
        return $this->incidentID;
    }

    public function getReportDate()
    {
        return $this->reportDate;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getReporterStaffID()
    {
        return $this->reporterStaffID;
    }

    public function getStudentID()
    {
        return $this->studentID;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}