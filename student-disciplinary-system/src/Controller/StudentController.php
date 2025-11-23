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
            } catch (\Throwable $e) {
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

          <!-- dark mode variable overrides and toggle styles -->
          <style>
            [data-theme="dark"] {
              --bg: #0b1020;
              --card: #0f1724;
              --primary: #60a5fa;
              --muted: #94a3b8;
              --text: #ffffff;
              color-scheme: dark;
            }

            /* ensure dark-mode background doesn't repeat and uses a fixed gradient */
            [data-theme="dark"] body {
              background-image: linear-gradient(135deg, #071226 0%, var(--bg) 100%) !important;
              background-repeat: no-repeat !important;
              background-attachment: fixed !important;
              background-size: cover !important;
            }

            /* force main text to white in dark mode and adjust form colors */
            [data-theme="dark"] body,
            [data-theme="dark"] .card,
            [data-theme="dark"] h1,
            [data-theme="dark"] h2,
            [data-theme="dark"] h3,
            [data-theme="dark"] p,
            [data-theme="dark"] .lead,
            [data-theme="dark"] label,
            [data-theme="dark"] th,
            [data-theme="dark"] td,
            [data-theme="dark"] input,
            [data-theme="dark"] select,
            [data-theme="dark"] textarea {
              color: var(--text) !important;
            }

            [data-theme="dark"] th {
              background: rgba(255,255,255,0.04) !important;
              color: var(--text) !important;
              box-shadow: inset 0 -1px 0 rgba(255,255,255,0.03);
              backdrop-filter: blur(6px);
            }
            [data-theme="dark"] .table-wrap { border-color: rgba(255,255,255,0.06) !important; }
            [data-theme="dark"] input,
            [data-theme="dark"] select,
            [data-theme="dark"] textarea {
              background: rgba(255,255,255,0.03);
              border-color: rgba(255,255,255,0.08);
            }
            [data-theme="dark"] .muted { color: var(--muted) !important; }

            /* toggle button */
            .theme-toggle {
              position:fixed;
              right:16px;
              top:16px;
              z-index:11000;
              width:44px;
              height:44px;
              border-radius:10px;
              border:1px solid rgba(0,0,0,0.08);
              background:var(--card);
              display:inline-flex;
              align-items:center;
              justify-content:center;
              font-size:18px;
              cursor:pointer;
              transition:transform .12s ease, box-shadow .12s ease;
              box-shadow:0 6px 18px rgba(2,6,23,0.06);
            }
            .theme-toggle:active{ transform:scale(.98) }
          </style>
        </head>
        <body>
          <!-- theme toggle button (site-wide) -->
          <button id="themeToggle" class="theme-toggle" aria-pressed="false" aria-label="Toggle dark mode" title="Toggle dark mode">🌙</button>

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
                      <th>Actions</th> <!-- added -->
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
                      <td>
                        <a class="button" href="<?php echo htmlspecialchars($base . '/students/edit?id=' . $s['StudentID'], ENT_QUOTES); ?>">Edit</a>

                        <!-- delete form: button now opens modal, form has an id -->
                        <form id="delete-student-<?php echo $s['StudentID']; ?>" method="post" action="<?php echo htmlspecialchars($base . '/students/delete?id=' . $s['StudentID'], ENT_QUOTES); ?>" style="display:inline;margin-left:8px">
                          <button type="button" class="button secondary btn-delete" data-form-id="delete-student-<?php echo $s['StudentID']; ?>" style="background:#e3342f;color:#fff;border:0;padding:8px 10px;border-radius:6px;cursor:pointer">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- confirmation modal (auth) -->
              <div id="authModal" aria-hidden="true" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(2,6,23,0.5);z-index:9999">
                <div role="dialog" aria-modal="true" style="width:100%;max-width:420px;background:#fff;border-radius:10px;padding:18px;box-shadow:0 12px 40px rgba(2,6,23,0.25);">
                  <h3 style="margin:0 0 6px;font-size:1.1rem">Confirm delete</h3>
                  <p style="margin:0 0 12px;color:#6b7280">Enter admin credentials to delete the record.</p>

                  <div style="margin-bottom:8px">
                    <label style="display:block;font-weight:600;margin-bottom:6px">Username</label>
                    <input id="authUsername" type="text" autocomplete="username" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:6px">
                  </div>

                  <div style="margin-bottom:8px">
                    <label style="display:block;font-weight:600;margin-bottom:6px">Password</label>
                    <input id="authPassword" type="password" autocomplete="current-password" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:6px">
                  </div>

                  <div id="authError" style="color:#7a1b1b;margin-bottom:8px;min-height:18px"></div>

                  <div style="display:flex;gap:8px;justify-content:flex-end">
                    <button id="authCancel" type="button" style="background:#6c757d;color:#fff;padding:8px 12px;border-radius:8px;border:0">Cancel</button>
                    <button id="authConfirm" type="button" style="background:#e3342f;color:#fff;padding:8px 12px;border-radius:8px;border:0">Delete</button>
                  </div>
                </div>
              </div>

              <script>
                (function(){
                  var modal = document.getElementById('authModal');
                  var username = document.getElementById('authUsername');
                  var password = document.getElementById('authPassword');
                  var err = document.getElementById('authError');
                  var currentForm = null;

                  document.querySelectorAll('.btn-delete').forEach(function(btn){
                    btn.addEventListener('click', function(){
                      var fid = this.getAttribute('data-form-id');
                      currentForm = document.getElementById(fid);
                      err.textContent = '';
                      username.value = '';
                      password.value = '';
                      modal.style.display = 'flex';
                      modal.setAttribute('aria-hidden','false');
                      username.focus();
                    });
                  });

                  document.getElementById('authCancel').addEventListener('click', function(){
                    modal.style.display = 'none';
                    modal.setAttribute('aria-hidden','true');
                  });

                  document.getElementById('authConfirm').addEventListener('click', function(){
                    // hardcoded credentials
                    if (username.value === 'admin' && password.value === 'admin') {
                      if (currentForm) currentForm.submit();
                    } else {
                      err.textContent = 'Invalid credentials';
                    }
                  });

                  // close modal on Esc or click outside dialog
                  window.addEventListener('keydown', function(e){ if (e.key === 'Escape'){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); }});
                  modal.addEventListener('click', function(e){ if (e.target === modal){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); }});
                })();
              </script>

            <?php endif; ?>

          </div>

          <!-- theme toggle script (persist in localStorage) -->
          <script>
            (function(){
              var key = 'site-theme';
              var btn = document.getElementById('themeToggle');
              function applyTheme(t){
                if (t === 'dark') {
                  document.documentElement.setAttribute('data-theme','dark');
                  btn.textContent = '☀️';
                  btn.setAttribute('aria-pressed','true');
                } else {
                  document.documentElement.removeAttribute('data-theme');
                  btn.textContent = '🌙';
                  btn.setAttribute('aria-pressed','false');
                }
                try { localStorage.setItem(key, t); } catch(e){}
              }
              // init
              var saved = null;
              try { saved = localStorage.getItem(key); } catch(e){}
              if (!saved) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) saved = 'dark';
                else saved = 'light';
              }
              applyTheme(saved);

              // toggle
              btn.addEventListener('click', function(){
                var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                applyTheme(cur === 'dark' ? 'light' : 'dark');
              });

              // keyboard: Enter/Space
              btn.addEventListener('keydown', function(e){
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
              });
            })();
          </script>
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

          <!-- dark mode variable overrides and toggle styles (inserted for create/edit/index) -->
          <style>
            [data-theme="dark"] {
              --bg: #0b1020;
              --card: #0f1724;
              --primary: #60a5fa;
              --muted: #94a3b8;
              --text: #ffffff;
              color-scheme: dark;
            }
            [data-theme="dark"] body {
              background-image: linear-gradient(135deg, #071226 0%, var(--bg) 100%) !important;
              background-repeat: no-repeat !important;
              background-attachment: fixed !important;
              background-size: cover !important;
            }
            [data-theme="dark"] body,
            [data-theme="dark"] .card,
            [data-theme="dark"] h1,
            [data-theme="dark"] h2,
            [data-theme="dark"] h3,
            [data-theme="dark"] p,
            [data-theme="dark"] .lead,
            [data-theme="dark"] label,
            [data-theme="dark"] th,
            [data-theme="dark"] td,
            [data-theme="dark"] input,
            [data-theme="dark"] select,
            [data-theme="dark"] textarea {
              color: var(--text) !important;
            }
            [data-theme="dark"] th {
              background: rgba(255,255,255,0.04) !important;
              color: var(--text) !important;
              box-shadow: inset 0 -1px 0 rgba(255,255,255,0.03);
              backdrop-filter: blur(6px);
            }
            [data-theme="dark"] .table-wrap { border-color: rgba(255,255,255,0.06) !important; }
            [data-theme="dark"] input,
            [data-theme="dark"] select,
            [data-theme="dark"] textarea {
              background: rgba(255,255,255,0.03);
              border-color: rgba(255,255,255,0.08);
            }
            [data-theme="dark"] .muted { color: var(--muted) !important; }

            .theme-toggle {
              position:fixed;
              right:16px;
              top:16px;
              z-index:11000;
              width:44px;
              height:44px;
              border-radius:10px;
              border:1px solid rgba(0,0,0,0.08);
              background:var(--card);
              display:inline-flex;
              align-items:center;
              justify-content:center;
              font-size:18px;
              cursor:pointer;
              transition:transform .12s ease, box-shadow .12s ease;
              box-shadow:0 6px 18px rgba(2,6,23,0.06);
            }
            .theme-toggle:active{ transform:scale(.98) }
          </style>
        </head>
        <body>
          <!-- theme toggle button (site-wide) -->
          <button id="themeToggle" class="theme-toggle" aria-pressed="false" aria-label="Toggle dark mode" title="Toggle dark mode">🌙</button>

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

          <!-- theme toggle script (persist in localStorage) -->
          <script>
            (function(){
              var key = 'site-theme';
              var btn = document.getElementById('themeToggle');
              function applyTheme(t){
                if (t === 'dark') {
                  document.documentElement.setAttribute('data-theme','dark');
                  btn.textContent = '☀️';
                  btn.setAttribute('aria-pressed','true');
                } else {
                  document.documentElement.removeAttribute('data-theme');
                  btn.textContent = '🌙';
                  btn.setAttribute('aria-pressed','false');
                }
                try { localStorage.setItem(key, t); } catch(e){}
              }
              // init
              var saved = null;
              try { saved = localStorage.getItem(key); } catch(e){}
              if (!saved) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) saved = 'dark';
                else saved = 'light';
              }
              applyTheme(saved);

              // toggle
              btn.addEventListener('click', function(){
                var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                applyTheme(cur === 'dark' ? 'light' : 'dark');
              });

              // keyboard: Enter/Space
              btn.addEventListener('keydown', function(e){
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
              });
            })();
          </script>
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

    // new: edit student
    public function edit()
    {
        header('Content-Type: text/html; charset=utf-8');
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $base = $base === '/' ? '' : $base;
        $studentsUrl = $base . '/students';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . $studentsUrl);
            exit;
        }

        $errors = [];
        $row = null;
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare('SELECT StudentID, EnrollmentNo, FirstName, LastName, Email, Phone FROM student WHERE StudentID = ? LIMIT 1');
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if (!$row) {
                    $errors[] = 'Student not found.';
                }
            } catch (\Throwable $e) {
                $errors[] = 'Error loading student: ' . $e->getMessage();
            }
        } else {
            $errors[] = 'Database not configured.';
        }

        $old = [
            'name' => $row ? trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? '')) : '',
            'email' => $row['Email'] ?? '',
            'phone' => $row['Phone'] ?? '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['name'] = trim($_POST['name'] ?? '');
            $old['email'] = trim($_POST['email'] ?? '');
            $old['phone'] = trim($_POST['phone'] ?? '');

            if ($old['name'] === '') { $errors[] = 'Name is required.'; }
            if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid email is required.'; }
            if ($old['phone'] === '') { $errors[] = 'Phone is required.'; }

            if (empty($errors) && $this->pdo) {
                try {
                    $parts = preg_split('/\s+/', $old['name'], 2);
                    $first = $parts[0] ?? '';
                    $last = $parts[1] ?? '';
                    $upd = $this->pdo->prepare('UPDATE student SET FirstName=?, LastName=?, Email=?, Phone=? WHERE StudentID=?');
                    $upd->execute([$first, $last, $old['email'], $old['phone'], $id]);
                    header('Location: ' . $studentsUrl);
                    exit;
                } catch (\Throwable $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }
        }

        // render simple edit form (reuse styles)
        ?>
        <!doctype html>
        <html lang="en">
        <head><meta charset="utf-8"><title>Edit Student</title><meta name="viewport" content="width=device-width,initial-scale=1"><style>
        :root{--bg:#f6f8fa;--card:#fff;--primary:#007bff;--muted:#6c757d}
        body{margin:0;font-family:Inter,Roboto,Arial;background:linear-gradient(135deg,#eef2f7 0%,#f6f8fa 100%);padding:64px 32px 32px}
        .card{max-width:640px;background:var(--card);padding:24px;border-radius:12px;box-shadow:0 6px 24px rgba(16,24,40,0.08);border:1px solid #eceef3}
        label{display:block;margin:8px 0 6px;font-weight:600}
        input{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px}
        .actions{display:flex;gap:12px;justify-content:flex-end;margin-top:18px}
        .errors{background:#fff0f0;border:1px solid #ffd1d1;padding:10px;border-radius:8px;color:#7a1b1b}
        .btn{padding:10px 14px;border-radius:8px;border:0;color:#fff;background:var(--primary)}
        a.cancel{background:#6c757d;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none}
        </style></head>
        <body>
        <div class="card">
          <h2>Edit Student</h2>
          <?php if (!empty($errors)): ?><div class="errors"><ul style="margin:0;padding-left:18px"><?php foreach ($errors as $e){ echo '<li>'.htmlspecialchars($e,ENT_QUOTES).'</li>'; } ?></ul></div><?php endif; ?>
          <form method="post" novalidate>
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($old['name'], ENT_QUOTES); ?>">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($old['email'], ENT_QUOTES); ?>">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="text" value="<?php echo htmlspecialchars($old['phone'], ENT_QUOTES); ?>">
            <div class="actions">
              <a class="cancel" href="<?php echo htmlspecialchars($studentsUrl, ENT_QUOTES); ?>">Cancel</a>
              <button class="btn" type="submit">Save</button>
            </div>
          </form>
        </div>
        </body>
        </html>
        <?php
    }

    // new: delete student (POST)
    public function delete()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $studentsUrl = ($base === '/' ? '' : $base) . '/students';
        if ($id <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $studentsUrl);
            exit;
        }

        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare('DELETE FROM student WHERE StudentID = ?');
                $stmt->execute([$id]);
            } catch (\Throwable $e) {
                // ignore and redirect
            }
        }
        header('Location: ' . $studentsUrl);
        exit;
    }

    // ...existing other methods...
}
?>