CREATE TABLE incidents (
    IncidentID INT AUTO_INCREMENT PRIMARY KEY,
    ReportDate DATETIME NOT NULL,
    Location VARCHAR(255) NOT NULL,
    ReporterStaffID INT NOT NULL,
    StudentID INT NOT NULL,
    Description TEXT NOT NULL,
    Status ENUM('Pending', 'Resolved', 'Dismissed') DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ReporterStaffID) REFERENCES staff(StaffID),
    FOREIGN KEY (StudentID) REFERENCES students(StudentID)
);