<?php
namespace App\Controller;

class DisciplinaryController
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
        $createUrl = $base . '/incidents/create';

        $incidents = [];
        $error = null;
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query('SELECT IncidentID, ReportDate, Location, Status, StudentID FROM incidentreport ORDER BY COALESCE(ReportDate, CreatedAt) DESC LIMIT 100');
                $incidents = $stmt->fetchAll();
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        ?>
        <!doctype html>
        <html lang="en">
        <head>
          <meta charset="utf-8">
            <?php require_once __DIR__ . '/../views/_theme_head.php'; ?>
          <title>Incidents — Student Disciplinary System</title>
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
              /* extra space at the top */
              padding:64px 32px 32px;
              color:#222;
            }
            .card{
              width:100%;
              max-width:1000px;
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
            /* table wrapper to allow scroll with sticky header */
            .table-wrap{max-height:420px;overflow:auto;margin-top:16px;border-radius:8px;border:1px solid #f1f3f5}
            table{width:100%;border-collapse:collapse}
            th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #eef0f3;background:transparent}
            th{
              background:#fbfcfd;font-weight:700;color:#374151;font-size:0.9rem;
              position:sticky;top:0;z-index:2;
              box-shadow: inset 0 -1px 0 rgba(0,0,0,0.03);
            }
            .muted{color:var(--muted);font-size:0.9rem}
            .status{padding:6px 8px;border-radius:8px;font-weight:700;color:#fff;display:inline-block}
            .status.Pending{background:#f59e0b}
            .status.InReview{background:#06b6d4}
            .status.Closed{background:#10b981}
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
                        <?php require_once __DIR__ . '/../views/_theme_body.php'; ?>
          <!-- theme toggle button (site-wide) -->
          <button id="themeToggle" class="theme-toggle" aria-pressed="false" aria-label="Toggle dark mode" title="Toggle dark mode">🌙</button>

          <div class="card" role="main">
            <div class="header">
              <div>
                <h1>Incidents</h1>
                <p class="lead">Reported incidents</p>
              </div>
              <div class="actions">
                <a class="button secondary" href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES); ?>">Back</a>
                <a class="button" href="<?php echo htmlspecialchars($createUrl, ENT_QUOTES); ?>">New Incident</a>
              </div>
            </div>

            <?php if (!empty($error)): ?>
              <div class="empty">Error loading incidents: <?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
            <?php elseif (empty($incidents)): ?>
              <div class="empty">No incidents found.</div>
            <?php else: ?>
              <div class="table-wrap" role="region" aria-label="Incidents table">
                <table>
                  <caption style="caption-side:top;text-align:left;padding:8px 12px;font-weight:700;color:#374151">Incidents</caption>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Date</th>
                      <th>Location</th>
                      <th>Student</th>
                      <th>Status</th>
                      <th>Actions</th> <!-- added -->
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($incidents as $r): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($r['IncidentID'], ENT_QUOTES); ?></td>
                      <td><?php echo htmlspecialchars($r['ReportDate'], ENT_QUOTES); ?></td>
                      <td><?php echo htmlspecialchars($r['Location'] ?? '', ENT_QUOTES); ?></td>
                      <td class="muted"><?php echo htmlspecialchars($r['StudentID'] ?? '', ENT_QUOTES); ?></td>
                      <td>
                        <?php
                          $st = $r['Status'] ?? '';
                          $cls = preg_replace('/\s+/', '', $st);
                        ?>
                        <span class="status <?php echo htmlspecialchars($cls, ENT_QUOTES); ?>"><?php echo htmlspecialchars($st, ENT_QUOTES); ?></span>
                      </td>
                      <td>
                        <a class="button" href="<?php echo htmlspecialchars($base . '/incidents/edit?id=' . $r['IncidentID'], ENT_QUOTES); ?>">Edit</a>

                        <!-- delete form: button opens auth modal -->
                        <form id="delete-incident-<?php echo $r['IncidentID']; ?>" method="post" action="<?php echo htmlspecialchars($base . '/incidents/delete?id=' . $r['IncidentID'], ENT_QUOTES); ?>" style="display:inline;margin-left:8px">
                          <button type="button" class="button secondary btn-delete" data-form-id="delete-incident-<?php echo $r['IncidentID']; ?>" style="background:#e3342f;color:#fff;border:0;padding:8px 10px;border-radius:6px;cursor:pointer">Delete</button>
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
                    if (username.value === 'admin' && password.value === 'admin') {
                      if (currentForm) currentForm.submit();
                    } else {
                      err.textContent = 'Invalid credentials';
                    }
                  });

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
              var saved = null;
              try { saved = localStorage.getItem(key); } catch(e){}
              if (!saved) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) saved = 'dark';
                else saved = 'light';
              }
              applyTheme(saved);
              btn.addEventListener('click', function(){
                var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                applyTheme(cur === 'dark' ? 'light' : 'dark');
              });
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
        $incidentsUrl = $base . '/incidents';

        $errors = [];
        $old = [
            'reportDate' => date('Y-m-d\TH:i'),
            'location' => '',
            'reporter' => '',
            'student' => '',
            'description' => '',
        ];

        $staffList = [];
        $studentList = [];
        if ($this->pdo) {
            try {
                $staffList = $this->pdo->query('SELECT StaffID, Name FROM staff ORDER BY Name')->fetchAll();
                $studentList = $this->pdo->query('SELECT StudentID, FirstName, LastName FROM student ORDER BY FirstName')->fetchAll();
            } catch (\Throwable $e) {
                $errors[] = 'Error loading lookups: ' . $e->getMessage();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['reportDate'] = trim($_POST['reportDate'] ?? $old['reportDate']);
            $old['location'] = trim($_POST['location'] ?? '');
            $old['reporter'] = trim($_POST['reporter'] ?? '');
            $old['student'] = trim($_POST['student'] ?? '');
            $old['description'] = trim($_POST['description'] ?? '');

            if ($old['location'] === '') { $errors[] = 'Location is required.'; }
            if ($old['reporter'] === '') { $errors[] = 'Reporter is required.'; }
            if ($old['student'] === '') { $errors[] = 'Student is required.'; }
            if ($old['description'] === '') { $errors[] = 'Description is required.'; }

            if (empty($errors)) {
                if ($this->pdo) {
                    try {
                        $this->pdo->beginTransaction();
                        $stmt = $this->pdo->query('SELECT COALESCE(MAX(IncidentID),0) + 1 AS nextId FROM incidentreport');
                        $nextId = (int)$stmt->fetchColumn();
                        if ($nextId <= 0) { $nextId = 1; }

                        // convert HTML datetime-local to MySQL DATETIME if needed
                        $reportDate = str_replace('T', ' ', $old['reportDate']);
                        $createdAt = date('Y-m-d H:i:s');

                        $ins = $this->pdo->prepare('INSERT INTO incidentreport (IncidentID, ReportDate, Location, ReporterStaffID, StudentID, Description, Status, CreatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                        $ins->execute([
                            $nextId,
                            $reportDate,
                            $old['location'],
                            (int)$old['reporter'],
                            (int)$old['student'],
                            $old['description'],
                            'Pending',
                            $createdAt,
                        ]);

                        $this->pdo->commit();

                        header('Location: ' . $incidentsUrl);
                        exit;
                    } catch (\Throwable $e) {
                        if ($this->pdo && $this->pdo->inTransaction()) { $this->pdo->rollBack(); }
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
          <title>New Incident — Student Disciplinary System</title>
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
            .card{width:100%;max-width:760px;background:var(--card);box-shadow:0 6px 24px rgba(16,24,40,0.08);border-radius:12px;padding:24px;border:1px solid #eceef3}
            .header{display:flex;align-items:center;justify-content:space-between}
            h1{margin:0 0 8px;font-size:1.25rem}
            p.lead{color:var(--muted);margin:0 0 12px}
            form{margin-top:14px}
            .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
            label{display:block;margin:8px 0 6px;font-weight:600;color:#374151}
            input[type="text"], input[type="datetime-local"], select, textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font-size:1rem}
            textarea{min-height:120px;resize:vertical}
            .actions{display:flex;gap:12px;justify-content:flex-end;margin-top:18px}
            button.btn{padding:10px 14px;border-radius:8px;border:0;color:#fff;background:var(--primary);font-weight:700}
            a.btn.secondary{background:#6c757d;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none}
            .errors{background:#fff0f0;border:1px solid #ffd1d1;padding:10px;border-radius:8px;color:#7a1b1b;margin-top:12px}
            .hint{font-size:0.9rem;color:var(--muted);margin-top:8px}
            @media (max-width:720px){ .grid{grid-template-columns:1fr} }
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
            <div class="header">
              <div>
                <h1>New Incident</h1>
                <p class="lead">Create a new incident report</p>
              </div>
              <div class="actions">
                <a class="btn secondary" href="<?php echo htmlspecialchars($incidentsUrl, ENT_QUOTES); ?>">Cancel</a>
              </div>
            </div>

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
              <div class="grid">
                <div>
                  <label for="reportDate">Report date/time</label>
                  <input id="reportDate" name="reportDate" type="datetime-local" value="<?php echo htmlspecialchars($old['reportDate'], ENT_QUOTES); ?>">
                </div>
                <div>
                  <label for="location">Location</label>
                  <input id="location" name="location" type="text" value="<?php echo htmlspecialchars($old['location'], ENT_QUOTES); ?>">
                </div>

                <div>
                  <label for="reporter">Reporter (staff)</label>
                  <select id="reporter" name="reporter">
                    <option value="">Select reporter</option>
                    <?php foreach ($staffList as $s): ?>
                      <option value="<?php echo (int)$s['StaffID']; ?>" <?php echo ((string)$s['StaffID'] === (string)$old['reporter']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['Name'], ENT_QUOTES); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div>
                  <label for="student_search">Student (type name or enrollment)</label>
                  <input id="student_search" name="student_search" type="text" autocomplete="off" placeholder="Type name or EN00001" value="<?php echo htmlspecialchars(($old['student_display'] ?? ''), ENT_QUOTES); ?>">
                  <input id="student" name="student" type="hidden" value="<?php echo htmlspecialchars($old['student'] ?? '', ENT_QUOTES); ?>">
                  <ul id="student_suggestions" style="list-style:none;margin:6px 0 0;padding:0;max-height:180px;overflow:auto;border:1px solid #eef0f3;border-radius:6px;display:none;background:#fff"></ul>
                </div>
              </div>

              <label for="description">Description</label>
              <textarea id="description" name="description"><?php echo htmlspecialchars($old['description'], ENT_QUOTES); ?></textarea>

              <div class="hint">Status will be set to "Pending".</div>

              <div class="actions">
                <a class="btn secondary" href="<?php echo htmlspecialchars($incidentsUrl, ENT_QUOTES); ?>">Cancel</a>
                <button class="btn" type="submit">Save incident</button>
              </div>
            </form>
          </div>

          <script>
           (function(){
             const base = '<?php echo addslashes($base === '' ? '' : $base); ?>';
             const searchInput = document.getElementById('student_search');
             const hiddenInput = document.getElementById('student');
             const suggestions = document.getElementById('student_suggestions');
             let timer = null;

             function renderList(items){
               suggestions.innerHTML = '';
               if (!items.length) { suggestions.style.display = 'none'; return; }
               items.forEach(it=>{
                 const li = document.createElement('li');
                 li.style.padding = '8px 12px';
                 li.style.cursor = 'pointer';
                 li.textContent = it.name + (it.enrollment ? ' — ' + it.enrollment : '');
                 li.dataset.id = it.id;
                 li.addEventListener('click', function(){
                   hiddenInput.value = this.dataset.id;
                   searchInput.value = this.textContent;
                   suggestions.style.display = 'none';
                 });
                 suggestions.appendChild(li);
               });
               suggestions.style.display = 'block';
             }

             function doSearch(q){
               if (!q || q.length < 1) { renderList([]); return; }
               fetch(base + '/api/students?q=' + encodeURIComponent(q), {credentials:'same-origin'})
                 .then(r => r.json())
                 .then(data => renderList(data))
                 .catch(()=> renderList([]));
             }

             searchInput.addEventListener('input', function(){
               hiddenInput.value = ''; // clear selected id when typing
               clearTimeout(timer);
               const q = this.value.trim();
               timer = setTimeout(()=> doSearch(q), 250);
             });

             // clicking outside closes suggestions
             document.addEventListener('click', function(e){
               if (!suggestions.contains(e.target) && e.target !== searchInput) {
                 suggestions.style.display = 'none';
               }
             });

             // if user submits without selecting, allow server-side fallback to search by typed enrollment/name
           })();
           </script>
         </body>
        </html>
        <?php
    }

    // new: edit incident (GET show form, POST update)
    public function edit()
    {
        header('Content-Type: text/html; charset=utf-8');
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $base = $base === '/' ? '' : $base;
        $incidentsUrl = $base . '/incidents';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { header('Location: ' . $incidentsUrl); exit; }

        $errors = [];
        $old = [
            'reportDate' => date('Y-m-d\TH:i'),
            'location' => '',
            'reporter' => '',
            'student' => '',
            'description' => '',
            'status' => 'Pending',
        ];

        $staffList = [];
        $studentList = [];
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare('SELECT IncidentID, ReportDate, Location, ReporterStaffID, StudentID, Description, Status FROM incidentreport WHERE IncidentID = ? LIMIT 1');
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if (!$row) {
                    $errors[] = 'Incident not found.';
                } else {
                    // prepare datetime-local value (keep minutes)
                    if (!empty($row['ReportDate'])) {
                        $dt = substr($row['ReportDate'], 0, 16); // "YYYY-MM-DD HH:MM"
                        $old['reportDate'] = str_replace(' ', 'T', $dt);
                    }
                    $old['location'] = $row['Location'] ?? '';
                    $old['reporter'] = $row['ReporterStaffID'] ?? '';
                    $old['student'] = $row['StudentID'] ?? '';
                    $old['description'] = $row['Description'] ?? '';
                    $old['status'] = $row['Status'] ?? 'Pending';
                }

                $staffList = $this->pdo->query('SELECT StaffID, Name FROM staff ORDER BY Name')->fetchAll();
                $studentList = $this->pdo->query('SELECT StudentID, FirstName, LastName FROM student ORDER BY FirstName, LastName')->fetchAll();
            } catch (\Throwable $e) {
                $errors[] = 'Error loading incident: ' . $e->getMessage();
            }
        } else {
            $errors[] = 'Database not configured.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['reportDate'] = trim($_POST['reportDate'] ?? $old['reportDate']);
            $old['location'] = trim($_POST['location'] ?? '');
            $old['reporter'] = trim($_POST['reporter'] ?? '');
            $old['student'] = trim($_POST['student'] ?? '');
            $old['description'] = trim($_POST['description'] ?? '');
            $old['status'] = trim($_POST['status'] ?? $old['status']);

            if ($old['location'] === '') { $errors[] = 'Location is required.'; }
            if ($old['reporter'] === '') { $errors[] = 'Reporter is required.'; }
            if ($old['student'] === '') { $errors[] = 'Student is required.'; }
            if ($old['description'] === '') { $errors[] = 'Description is required.'; }

            if (empty($errors) && $this->pdo) {
                try {
                    $reportDate = str_replace('T', ' ', $old['reportDate']);
                    $upd = $this->pdo->prepare('UPDATE incidentreport SET ReportDate = ?, Location = ?, ReporterStaffID = ?, StudentID = ?, Description = ?, Status = ? WHERE IncidentID = ?');
                    $upd->execute([$reportDate, $old['location'], (int)$old['reporter'], (int)$old['student'], $old['description'], $old['status'], $id]);
                    header('Location: ' . $incidentsUrl);
                    exit;
                } catch (\Throwable $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }
        }

        // render full edit form (prefilled)
        ?>
        <!doctype html>
        <html lang="en">
        <head>
          <meta charset="utf-8">
          <title>Edit Incident — Student Disciplinary System</title>
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <style>
            :root{--bg:#f6f8fa;--card:#fff;--primary:#007bff;--muted:#6c757d}
            html,body{height:100%}
            body{
              margin:0;
              font-family:Inter,Roboto,Arial,sans-serif;
              background:linear-gradient(135deg,#eef2f7 0%,var(--bg) 100%);
              display:flex;align-items:center;justify-content:center;padding:64px 32px 32px;color:#222;
            }
            .card{width:100%;max-width:760px;background:var(--card);box-shadow:0 6px 24px rgba(16,24,40,0.08);border-radius:12px;padding:24px;border:1px solid #eceef3}
            .header{display:flex;align-items:center;justify-content:space-between}
            h1{margin:0 0 8px;font-size:1.25rem}
            p.lead{color:var(--muted);margin:0 0 12px}
            form{margin-top:14px}
            .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
            label{display:block;margin:8px 0 6px;font-weight:600;color:#374151}
            input[type="text"], input[type="datetime-local"], select, textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font-size:1rem}
            textarea{min-height:120px;resize:vertical}
            .actions{display:flex;gap:12px;justify-content:flex-end;margin-top:18px}
            button.btn{padding:10px 14px;border-radius:8px;border:0;color:#fff;background:var(--primary);font-weight:700}
            a.btn.secondary{background:#6c757d;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none}
            .errors{background:#fff0f0;border:1px solid #ffd1d1;padding:10px;border-radius:8px;color:#7a1b1b;margin-top:12px}
            .hint{font-size:0.9rem;color:var(--muted);margin-top:8px}
            @media (max-width:720px){ .grid{grid-template-columns:1fr} }
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
            <div class="header">
              <div>
                <h1>Edit Incident #<?php echo htmlspecialchars($id, ENT_QUOTES); ?></h1>
                <p class="lead">Modify the incident details below.</p>
              </div>
              <div class="actions" style="position:static">
                <a class="btn secondary" href="<?php echo htmlspecialchars($incidentsUrl, ENT_QUOTES); ?>">Back</a>
              </div>
            </div>

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
              <div class="grid">
                <div>
                  <label for="reportDate">Report date/time</label>
                  <input id="reportDate" name="reportDate" type="datetime-local" value="<?php echo htmlspecialchars($old['reportDate'], ENT_QUOTES); ?>">
                </div>
                <div>
                  <label for="location">Location</label>
                  <input id="location" name="location" type="text" value="<?php echo htmlspecialchars($old['location'], ENT_QUOTES); ?>">
                </div>

                <div>
                  <label for="reporter">Reporter (staff)</label>
                  <select id="reporter" name="reporter">
                    <option value="">Select reporter</option>
                    <?php foreach ($staffList as $s): ?>
                      <option value="<?php echo (int)$s['StaffID']; ?>" <?php echo ((string)$s['StaffID'] === (string)$old['reporter']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['Name'], ENT_QUOTES); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div>
                  <label for="student">Student</label>
                  <select id="student" name="student">
                    <option value="">Select student</option>
                    <?php foreach ($studentList as $st): $nm = trim(($st['FirstName'] ?? '') . ' ' . ($st['LastName'] ?? '')); ?>
                      <option value="<?php echo (int)$st['StudentID']; ?>" <?php echo ((string)$st['StudentID'] === (string)$old['student']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($nm ?: 'Student ' . $st['StudentID'], ENT_QUOTES); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <label for="description">Description</label>
              <textarea id="description" name="description"><?php echo htmlspecialchars($old['description'], ENT_QUOTES); ?></textarea>

              <label for="status" style="margin-top:12px">Status</label>
              <select id="status" name="status">
                <?php
                  $statuses = ['Pending', 'In Review', 'Closed'];
                  foreach ($statuses as $stt) {
                      $sel = ($old['status'] === $stt) ? 'selected' : '';
                      echo '<option value="'.htmlspecialchars($stt,ENT_QUOTES).'" '.$sel.'>'.htmlspecialchars($stt,ENT_QUOTES).'</option>';
                  }
                ?>
              </select>

              <div class="hint">Save to update the incident. Status can be changed here.</div>

              <div class="actions">
                <a class="btn secondary" href="<?php echo htmlspecialchars($incidentsUrl, ENT_QUOTES); ?>">Cancel</a>
                <button class="btn" type="submit">Save incident</button>
              </div>
            </form>
          </div>
        </body>
        </html>
        <?php
    }

    // new: delete incident (POST)
    public function delete()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $incidentsUrl = ($base === '/' ? '' : $base) . '/incidents';
        if ($id <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $incidentsUrl);
            exit;
        }

        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare('DELETE FROM incidentreport WHERE IncidentID = ?');
                $stmt->execute([$id]);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        header('Location: ' . $incidentsUrl);
        exit;
    }

    // ...existing other methods ...
}
?>