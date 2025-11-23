<?php
namespace App\Controller;

class StudentController
{
    private $pdo;

    public function __construct(\PDO $pdo = null)
    {
        if ($pdo instanceof \PDO) {
            $this->pdo = $pdo;
        } elseif (!empty($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof \PDO) {
            $this->pdo = $GLOBALS['pdo'];
        } else {
            $this->pdo = null;
        }
    }

    public function index()
    {
        header('Content-Type: text/html; charset=utf-8');

        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $base = $base === '/' ? '' : $base;
        $backUrl = $base ?: '/';
        $addUrl = $base . '/students/create';

        $students = [];
        $error = null;
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query('SELECT StudentID, EnrollmentNo, FirstName, LastName, Email, Phone FROM student ORDER BY StudentID DESC LIMIT 100');
                $students = $stmt->fetchAll();
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        ?>
        <!doctype html>
        <html lang="en">
        <head>
          <meta charset="utf-8">
          <title>Students — Student Disciplinary System</title>
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <style>
            :root{--bg:#f6f8fa;--card:#fff;--primary:#007bff;--muted:#6c757d}
            html,body{height:100%}
            body{
              margin:0;
              font-family: Inter, Roboto, "Helvetica Neue", Arial, sans-serif;
              background: linear-gradient(135deg,#eef2f7 0%,var(--bg) 100%);
              display:flex;
              align-items:center;
              justify-content:center;
              padding:64px 32px 32px;
              color:#222;
            }
            .card{
              width:100%;
              max-width:900px;
              background:var(--card);
              box-shadow:0 6px 24px rgba(16,24,40,0.08);
              border-radius:12px;
              padding:24px;
              border:1px solid #eceef3;
            }
            .header{display:flex;align-items:center;justify-content:space-between;gap:12px}
            h1{margin:0 0 2px;font-size:1.25rem;}
            p.lead{color:var(--muted);margin:0 0 12px;font-size:0.95rem}
            .actions{display:flex;gap:12px;align-items:center}
            a.button{
              display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none;color:#fff;background:var(--primary);
              box-shadow:0 4px 12px rgba(2,6,23,0.08);font-weight:600;font-size:0.95rem;
            }
            a.button.secondary{background:#6c757d}
            .table-wrap{max-height:420px;overflow:auto;margin-top:16px;border-radius:8px;border:1px solid #f1f3f5}
            table{width:100%;border-collapse:collapse}
            th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #eef0f3;background:transparent}
            th{
              background:#fbfcfd;font-weight:700;color:#374151;font-size:0.9rem;
              position:sticky;top:0;z-index:2;
              box-shadow: inset 0 -1px 0 rgba(0,0,0,0.03);
            }
            .muted{color:var(--muted);font-size:0.9rem}
            .empty{padding:18px;background:#fff6; border-radius:8px;margin-top:12px;color:var(--muted)}
            @media (max-width:720px){ .card{padding:18px} table{font-size:0.9rem} }
          </style>
        </head>
        <body>
          <div class="card" role="main">
            <div class="header">
              <div>
                <h1>Students</h1>
                <p class="lead">List of students in the system</p>
              </div>
              <div class="actions">
                <a class="button secondary" href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES); ?>">Back</a>
                <a class="button" href="<?php echo htmlspecialchars($addUrl, ENT_QUOTES); ?>">Add Student</a>
              </div>
            </div>

            <?php if (!empty($error)): ?>
              <div class="empty">Error loading students: <?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
            <?php elseif (empty($students)): ?>
              <div class="empty">No students found.</div>
            <?php else: ?>
              <div class="table-wrap" role="region" aria-label="Students table">
                <table>
                  <caption style="caption-side:top;text-align:left;padding:8px 12px;font-weight:700;color:#374151">Students</caption>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Enrollment</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Phone</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($students as $s): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($s['StudentID'], ENT_QUOTES); ?></td>
                      <td><?php echo htmlspecialchars($s['EnrollmentNo'] ?? '', ENT_QUOTES); ?></td>
                      <td><?php echo htmlspecialchars(trim(($s['FirstName'] ?? '') . ' ' . ($s['LastName'] ?? '')), ENT_QUOTES); ?></td>
                      <td class="muted"><?php echo htmlspecialchars($s['Email'] ?? '', ENT_QUOTES); ?></td>
                      <td class="muted"><?php echo htmlspecialchars($s['Phone'] ?? '', ENT_QUOTES); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>

          </div>
        </body>
        </html>
        <?php
    }

    public function create()
    {
        header('Content-Type: text/html; charset=utf-8');

        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $base = $base === '/' ? '' : $base;
        $backUrl = $base ?: '/';
        $studentsUrl = $base . '/students';

        $errors = [];
        $old = ['name'=>'', 'email'=>'', 'phone'=>''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['name'] = trim($_POST['name'] ?? '');
            $old['email'] = trim($_POST['email'] ?? '');
            $old['phone'] = trim($_POST['phone'] ?? '');

            if ($old['name'] === '') {
                $errors[] = 'Name is required.';
            }
            if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'A valid email is required.';
            }
            if ($old['phone'] === '') {
                $errors[] = 'Phone is required.';
            }

            if (empty($errors)) {
                if ($this->pdo) {
                    try {
                        $this->pdo->beginTransaction();
                        $stmt = $this->pdo->query('SELECT COALESCE(MAX(StudentID),0) + 1 AS nextId FROM student');
                        $nextId = (int)$stmt->fetchColumn();
                        if ($nextId <= 0) { $nextId = 1; }

                        $enrollment = 'EN' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

                        $parts = preg_split('/\s+/', $old['name'], 2);
                        $first = $parts[0] ?? '';
                        $last = $parts[1] ?? '';

                        $ins = $this->pdo->prepare('INSERT INTO student (StudentID, EnrollmentNo, FirstName, LastName, DOB, Gender, Email, Phone, CreatedAt) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?)');
                        $ins->execute([
                            $nextId,
                            $enrollment,
                            $first,
                            $last,
                            'Other',
                            $old['email'],
                            $old['phone'],
                            date('Y-m-d H:i:s'),
                        ]);

                        $this->pdo->commit();

                        header('Location: ' . $studentsUrl);
                        exit;
                    } catch (\Exception $e) {
                        if ($this->pdo && $this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        $errors[] = 'Database error: ' . $e->getMessage();
                    }
                } else {
                    $errors[] = 'Database not configured.';
                }
            }
        }

        ?>
        <!doctype html>
        <html lang="en">
        <head>
          <meta charset="utf-8">
          <title>Add Student — Student Disciplinary System</title>
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <style>
            :root{--bg:#f6f8fa;--card:#fff;--primary:#007bff;--muted:#6c757d}
            html,body{height:100%}
            body{
              margin:0;
              font-family: Inter, Roboto, "Helvetica Neue", Arial, sans-serif;
              background: linear-gradient(135deg,#eef2f7 0%,var(--bg) 100%);
              display:flex;
              align-items:center;
              justify-content:center;
              padding:64px 32px 32px;
              color:#222;
            }
            .card{width:100%;max-width:640px;background:var(--card);box-shadow:0 6px 24px rgba(16,24,40,0.08);border-radius:12px;padding:24px;border:1px solid #eceef3}
            h1{margin:0 0 8px;font-size:1.25rem}
            p.lead{color:var(--muted);margin:0 0 12px}
            form{margin-top:14px}
            label{display:block;margin:8px 0 6px;font-weight:600;color:#374151}
            input[type="text"], input[type="email"]{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font-size:1rem}
            .actions{display:flex;gap:12px;justify-content:flex-end;margin-top:18px}
            button.btn{padding:10px 14px;border-radius:8px;border:0;color:#fff;background:var(--primary);font-weight:700}
            a.btn.secondary{background:#6c757d;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none}
            .errors{background:#fff0f0;border:1px solid #ffd1d1;padding:10px;border-radius:8px;color:#7a1b1b;margin-top:12px}
            .hint{font-size:0.9rem;color:var(--muted);margin-top:8px}
          </style>
        </head>
        <body>
          <div class="card" role="main">
            <h1>Add Student</h1>
            <p class="lead">Enter student details. ID and Enrollment are generated automatically.</p>

            <?php if (!empty($errors)): ?>
              <div class="errors">
                <ul style="margin:0 0 0 18px;padding:0">
                  <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e, ENT_QUOTES); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post" novalidate>
              <label for="name">Name</label>
              <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($old['name'], ENT_QUOTES); ?>" required>

              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($old['email'], ENT_QUOTES); ?>" required>

              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="text" value="<?php echo htmlspecialchars($old['phone'], ENT_QUOTES); ?>" required>

              <div class="hint">EnrollmentNo will be like EN00001 and StudentID increments automatically.</div>

              <div class="actions">
                <a class="btn secondary" href="<?php echo htmlspecialchars($studentsUrl, ENT_QUOTES); ?>">Cancel</a>
                <button class="btn" type="submit">Save student</button>
              </div>
            </form>
          </div>
        </body>
        </html>
        <?php
    }

    // JSON search endpoint for AJAX suggestions
    public function searchJson(string $q = '')
    {
        header('Content-Type: application/json; charset=utf-8');
        $results = [];

        $q = trim($q ?? '');
        if ($q === '') {
            echo json_encode($results, JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($this->pdo) {
            try {
                $like = '%' . str_replace(['%','_'], ['\\%','\\_'], $q) . '%';
                $sql = "SELECT StudentID, EnrollmentNo, FirstName, LastName
                        FROM student
                        WHERE EnrollmentNo LIKE :q OR CONCAT(FirstName,' ',LastName) LIKE :q
                        ORDER BY FirstName, LastName
                        LIMIT 20";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':q' => $like]);
                $rows = $stmt->fetchAll();
                foreach ($rows as $r) {
                    $results[] = [
                        'id' => (int)$r['StudentID'],
                        'enrollment' => $r['EnrollmentNo'],
                        'name' => trim(($r['FirstName'] ?? '') . ' ' . ($r['LastName'] ?? '')),
                    ];
                }
            } catch (\Exception $e) {
                echo json_encode([], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        echo json_encode($results, JSON_UNESCAPED_UNICODE);
    }
}