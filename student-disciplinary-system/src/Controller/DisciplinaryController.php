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
                $stmt = $this->pdo->query('SELECT IncidentID, ReportDate, Location, Status, StudentID FROM incidentreport ORDER BY ReportDate DESC LIMIT 100');
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
        </head>
        <body>
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
        </head>
        <body>
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
}