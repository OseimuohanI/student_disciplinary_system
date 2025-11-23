<?php
// Minimal dummy-data generator for the student disciplinary system
// Edit DB settings below then run: php seed.php

$db = [
    'host' => '127.0.0.1:3305',
    'dbname' => 'student_disciplinary_system',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    echo "DB connect error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

function randDate($daysBack = 365) {
    $ts = time() - rand(0, $daysBack * 24 * 3600);
    return date('Y-m-d H:i:s', $ts);
}

$offenseTypes = [
    ['Code' => 'OT01', 'Description' => 'Cheating on exam', 'Severity' => 3],
    ['Code' => 'OT02', 'Description' => 'Classroom disruption', 'Severity' => 1],
    ['Code' => 'OT03', 'Description' => 'Vandalism', 'Severity' => 2],
    ['Code' => 'OT04', 'Description' => 'Bullying', 'Severity' => 3],
    ['Code' => 'OT05', 'Description' => 'Late submission', 'Severity' => 1],
];

$staffNames = [
    ['StaffNo' => 'S001', 'Name' => 'Alice Johnson', 'Role' => 'Teacher', 'Email' => 'alice@example.com'],
    ['StaffNo' => 'S002', 'Name' => 'Bob Smith', 'Role' => 'Dean', 'Email' => 'bob@example.com'],
    ['StaffNo' => 'S003', 'Name' => 'Cathy Lee', 'Role' => 'Counselor', 'Email' => 'cathy@example.com'],
    ['StaffNo' => 'S004', 'Name' => 'David Park', 'Role' => 'Teacher', 'Email' => 'david@example.com'],
    ['StaffNo' => 'S005', 'Name' => 'Eve Turner', 'Role' => 'Registrar', 'Email' => 'eve@example.com'],
];

$firstNames = ['Liam','Noah','Olivia','Emma','Ava','Sophia','Mason','Logan','Lucas','Zoe','Mia','Amelia','Ethan','James','Harper','Evelyn'];
$lastNames = ['Brown','Smith','Johnson','Williams','Jones','Garcia','Miller','Davis','Rodriguez','Martinez'];
$locations = ['Library','Cafeteria','Gym','Classroom 101','Dormitory','Playground','Main Hall'];

try {
    $pdo->beginTransaction();

    // offensetype
    $stmt = $pdo->prepare('INSERT INTO offensetype (OffenseTypeID, Code, Description, SeverityLevel, CreatedAt) VALUES (?, ?, ?, ?, ?)');
    $oid = 1;
    foreach ($offenseTypes as $ot) {
        $stmt->execute([$oid, $ot['Code'], $ot['Description'], $ot['Severity'], date('Y-m-d H:i:s')]);
        $oid++;
    }

    // staff
    $stmt = $pdo->prepare('INSERT INTO staff (StaffID, StaffNo, Name, Role, Email, CreatedAt) VALUES (?, ?, ?, ?, ?, ?)');
    $sid = 1;
    $staffIds = [];
    foreach ($staffNames as $s) {
        $stmt->execute([$sid, $s['StaffNo'], $s['Name'], $s['Role'], $s['Email'], date('Y-m-d H:i:s')]);
        $staffIds[] = $sid;
        $sid++;
    }

    // students
    $stmt = $pdo->prepare('INSERT INTO student (StudentID, EnrollmentNo, FirstName, LastName, DOB, Gender, Email, Phone, CreatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stid = 1;
    $studentIds = [];
    for ($i = 0; $i < 40; $i++) {
        $fn = $firstNames[array_rand($firstNames)];
        $ln = $lastNames[array_rand($lastNames)];
        $en = 'EN' . str_pad((string)($stid), 5, '0', STR_PAD_LEFT);
        $dob = date('Y-m-d', strtotime('-' . rand(17, 25) . ' years -' . rand(0, 365) . ' days'));
        $gender = ['Male','Female','Other'][rand(0,2)];
        $email = strtolower($fn) . '.' . strtolower($ln) . $stid . '@example.com';
        $phone = '07' . rand(10000000, 99999999);
        $stmt->execute([$stid, $en, $fn, $ln, $dob, $gender, $email, $phone, date('Y-m-d H:i:s')]);
        $studentIds[] = $stid;
        $stid++;
    }

    // incidents
    $stmt = $pdo->prepare('INSERT INTO incidentreport (IncidentID, ReportDate, Location, ReporterStaffID, StudentID, Description, Status, CreatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $incId = 1;
    $incidentIds = [];
    $statuses = ['Pending','In Review','Closed'];
    for ($i = 0; $i < 60; $i++) {
        $reportDate = randDate(400);
        $loc = $locations[array_rand($locations)];
        $reporter = $staffIds[array_rand($staffIds)];
        $student = $studentIds[array_rand($studentIds)];
        $desc = "Auto-generated incident #{$incId} — student misbehavior observed at {$loc}.";
        $status = $statuses[array_rand($statuses)];
        $stmt->execute([$incId, $reportDate, $loc, $reporter, $student, $desc, $status, date('Y-m-d H:i:s')]);
        $incidentIds[] = $incId;
        $incId++;
    }

    // attachments (some incidents)
    $stmt = $pdo->prepare('INSERT INTO attachment (AttachmentID, IncidentID, FileName, FilePath, UploadedBy, UploadedDateTime) VALUES (?, ?, ?, ?, ?, ?)');
    $attId = 1;
    foreach ($incidentIds as $inc) {
        if (rand(0, 3) === 0) { // ~25% incidents get an attachment
            $fn = "evidence_{$inc}.jpg";
            $fp = "/uploads/{$fn}";
            $uBy = $staffIds[array_rand($staffIds)];
            $udt = randDate(400);
            $stmt->execute([$attId, $inc, $fn, $fp, $uBy, $udt]);
            $attId++;
        }
    }

    // reportoffense (1-2 offenses per incident)
    $stmt = $pdo->prepare('INSERT INTO reportoffense (ReportOffenseID, IncidentID, OffenseTypeID, Notes) VALUES (?, ?, ?, ?)');
    $rofId = 1;
    $maxOffenseType = count($offenseTypes);
    foreach ($incidentIds as $inc) {
        $count = rand(1, 2);
        for ($k = 0; $k < $count; $k++) {
            $otid = rand(1, $maxOffenseType);
            $notes = "Auto note: offense {$otid} for incident {$inc}.";
            $stmt->execute([$rofId, $inc, $otid, $notes]);
            $rofId++;
        }
    }

    // hearing (some incidents)
    $stmt = $pdo->prepare('INSERT INTO hearing (HearingID, IncidentID, HearingDate, Outcome, HearingNotes) VALUES (?, ?, ?, ?, ?)');
    $hid = 1;
    foreach ($incidentIds as $inc) {
        if (rand(0, 4) === 0) { // ~20% get hearings
            $hdate = randDate(300);
            $out = ['Guilty','Not Guilty','No Action'][rand(0,2)];
            $notes = "Hearing held: outcome {$out}.";
            $stmt->execute([$hid, $inc, $hdate, $out, $notes]);
            $hid++;
        }
    }

    // disciplinaryaction (some incidents)
    $stmt = $pdo->prepare('INSERT INTO disciplinaryaction (ActionID, IncidentID, ActionType, ActionDate, DurationDays, DecisionMakerID, Notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $aid = 1;
    $actionTypes = ['Warning','Suspension','Detention','Expulsion','Counseling'];
    foreach ($incidentIds as $inc) {
        if (rand(0, 3) === 0) { // ~25% get actions
            $atype = $actionTypes[array_rand($actionTypes)];
            $adate = date('Y-m-d', strtotime(randDate(300)));
            $duration = $atype === 'Suspension' ? rand(1,14) : ($atype === 'Detention' ? rand(1,7) : 0);
            $dm = $staffIds[array_rand($staffIds)];
            $notes = "Action: {$atype}";
            $stmt->execute([$aid, $inc, $atype, $adate, $duration, $dm, $notes]);
            $aid++;
        }
    }

    // appeals (few)
    $stmt = $pdo->prepare('INSERT INTO appeal (AppealID, IncidentID, AppealDate, AppealStatus, Outcome) VALUES (?, ?, ?, ?, ?)');
    $apId = 1;
    $appealStatuses = ['Pending','Approved','Rejected'];
    foreach ($incidentIds as $inc) {
        if (rand(0, 9) === 0) { // ~10% get appeals
            $adt = randDate(200);
            $ast = $appealStatuses[array_rand($appealStatuses)];
            $outcome = $ast === 'Pending' ? null : ($ast === 'Approved' ? 'Penalty reduced' : 'Appeal dismissed');
            $stmt->execute([$apId, $inc, $adt, $ast, $outcome]);
            $apId++;
        }
    }

    $pdo->commit();

    echo "Seeding completed." . PHP_EOL;
    echo "Inserted: " . ($oid - 1) . " offense types, " . ($sid - 1) . " staff, " . ($stid - 1) . " students, " . ($incId - 1) . " incidents." . PHP_EOL;
    echo "Attachments: " . ($attId - 1) . ", ReportOffense: " . ($rofId - 1) . ", Hearings: " . ($hid - 1) . ", Actions: " . ($aid - 1) . ", Appeals: " . ($apId - 1) . PHP_EOL;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Seeding failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
?>