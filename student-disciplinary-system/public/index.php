<?php
require_once __DIR__ . '/../src/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');

// compute base path so links work when app is in a subfolder
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$base = $scriptDir === '/' ? '' : $scriptDir;

// normalize route
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($base !== '' && strpos($requestUri, $base) === 0) {
    $route = substr($requestUri, strlen($base));
} else {
    $route = $requestUri;
}
$route = '/' . trim($route, '/');

$studentsUrl = $base . '/students';
$incidentsUrl = $base . '/incidents';

if ($route === '/' || $route === '/index.php') {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>Student Disciplinary System</title>
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
          padding:32px;
          color:#222;
        }
        .card{
          width:100%;
          max-width:640px;
          background:var(--card);
          box-shadow:0 6px 24px rgba(16,24,40,0.08);
          border-radius:12px;
          padding:28px;
          text-align:center;
          border:1px solid #eceef3;
        }
        h1{margin:0 0 8px;font-size:1.6rem;}
        p.lead{color:var(--muted);margin:0 0 20px;}
        .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:18px}
        a.button{
          display:inline-block;
          padding:12px 18px;
          border-radius:8px;
          text-decoration:none;
          color:#fff;
          background:var(--primary);
          box-shadow:0 4px 12px rgba(2,6,23,0.08);
          font-weight:600;
        }
        a.button.secondary{background:#6c757d}
        .small{font-size:0.85rem;color:#6b7280;margin-top:14px}
        @media (max-width:420px){.card{padding:20px}}
      </style>
    </head>
    <body>
      <div class="card" role="main">
        <h1>Student Disciplinary System</h1>
        <p class="lead">Choose where you want to go</p>
        <div class="actions">
          <a class="button" href="<?php echo htmlspecialchars($studentsUrl, ENT_QUOTES); ?>">Students</a>
          <a class="button secondary" href="<?php echo htmlspecialchars($incidentsUrl, ENT_QUOTES); ?>">Incidents</a>
        </div>
        <p class="small">If you still see the XAMPP dashboard, ensure .htaccess/rewrite and base path are configured properly.</p>
      </div>
    </body>
    </html>
    <?php
    exit;
}

if ($route === '/students' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../src/Controller/StudentController.php';
    $c = new \App\Controller\StudentController($GLOBALS['pdo'] ?? null);
    $c->index();
    exit;
}

// added: students create GET/POST
if ($route === '/students/create' && in_array($_SERVER['REQUEST_METHOD'], ['GET','POST'])) {
    require_once __DIR__ . '/../src/Controller/StudentController.php';
    $c = new \App\Controller\StudentController($GLOBALS['pdo'] ?? null);
    $c->create();
    exit;
}

// added: incidents create GET/POST
if ($route === '/incidents/create' && in_array($_SERVER['REQUEST_METHOD'], ['GET','POST'])) {
    require_once __DIR__ . '/../src/Controller/DisciplinaryController.php';
    $c = new \App\Controller\DisciplinaryController($GLOBALS['pdo'] ?? null);
    $c->create();
    exit;
}

// changed code below: handle /incidents (GET)
if ($route === '/incidents' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../src/Controller/DisciplinaryController.php';
    $c = new \App\Controller\DisciplinaryController($GLOBALS['pdo'] ?? null);
    $c->index();
    exit;
}

// fallback 404
http_response_code(404);
echo '<h1>404 Not Found</h1>';
exit;
?>