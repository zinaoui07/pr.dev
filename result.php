<?php
declare(strict_types=1);

session_start();

$activeNav = 'results';
$pageTitle = 'Results';

$dataFile = __DIR__ . '/results_data.json';

function loadResults(string $file): array
{
    if (!file_exists($file)) {
        $seed = [
            ['id' => '1', 'student_code' => 'E001', 'student_name' => 'Emma Smith', 'subject_code' => 'M001', 'subject_name' => 'Mathematics', 'grade' => 16.00],
            ['id' => '2', 'student_code' => 'E001', 'student_name' => 'Emma Smith', 'subject_code' => 'M002', 'subject_name' => 'Physics', 'grade' => 15.00],
            ['id' => '3', 'student_code' => 'E001', 'student_name' => 'Emma Smith', 'subject_code' => 'M003', 'subject_name' => 'English', 'grade' => 15.50],
            ['id' => '4', 'student_code' => 'E002', 'student_name' => 'Michael Johnson', 'subject_code' => 'M001', 'subject_name' => 'Mathematics', 'grade' => 14.00],
            ['id' => '5', 'student_code' => 'E002', 'student_name' => 'Michael Johnson', 'subject_code' => 'M002', 'subject_name' => 'Physics', 'grade' => 14.50],
        ];
        file_put_contents($file, json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $seed;
    }

    $raw = file_get_contents($file);
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function saveResults(string $file, array $rows): void
{
    file_put_contents($file, json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf']) || $_SESSION['csrf'] === '') {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function requireValidCsrf(): void
{
    $token = isset($_POST['csrf']) ? (string)$_POST['csrf'] : '';
    if ($token === '' || !isset($_SESSION['csrf']) || !hash_equals((string)$_SESSION['csrf'], $token)) {
        http_response_code(400);
        exit('Bad request (CSRF).');
    }
}

$errors = [];
$flash = null;

$results = loadResults($dataFile);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string)$_POST['action'] : '';

    if ($action === 'delete') {
        requireValidCsrf();
        $id = isset($_POST['id']) ? (string)$_POST['id'] : '';
        $results = array_values(array_filter($results, fn($r) => (string)($r['id'] ?? '') !== $id));
        saveResults($dataFile, $results);
        header('Location: ./results.php');
        exit;
    }

    if ($action === 'create') {
        requireValidCsrf();

        $studentCode = trim((string)($_POST['student_code'] ?? ''));
        $studentName = trim((string)($_POST['student_name'] ?? ''));
        $subjectCode = trim((string)($_POST['subject_code'] ?? ''));
        $subjectName = trim((string)($_POST['subject_name'] ?? ''));
        $gradeRaw = trim((string)($_POST['grade'] ?? ''));

        if ($studentCode === '') $errors[] = 'Student code is required.';
        if ($studentName === '') $errors[] = 'Student name is required.';
        if ($subjectCode === '') $errors[] = 'Subject code is required.';
        if ($subjectName === '') $errors[] = 'Subject name is required.';

        if ($gradeRaw === '' || !is_numeric($gradeRaw)) {
            $errors[] = 'Grade must be a number.';
            $grade = 0.0;
        } else {
            $grade = (float)$gradeRaw;
            if ($grade < 0 || $grade > 20) $errors[] = 'Grade must be between 0 and 20.';
        }

        if (!$errors) {
            $results[] = [
                'id' => bin2hex(random_bytes(8)),
                'student_code' => $studentCode,
                'student_name' => $studentName,
                'subject_code' => $subjectCode,
                'subject_name' => $subjectName,
                'grade' => round($grade, 2),
            ];
            saveResults($dataFile, $results);
            header('Location: ./results.php');
            exit;
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<div class="pageHead">
  <div>
    <h1 class="pageTitle">Results Management</h1>
    <p class="pageSubtitle">Enter and manage student grades</p>
  </div>

  <a class="btn btn--accent" href="./results.php?new=1">
    <span class="btn__plus" aria-hidden="true">+</span>
    New Result
  </a>
</div>

<section class="card">
  <div class="cardHead">
    <div>
      <h2 class="cardHead__title">Results List</h2>
      <p class="cardHead__sub"><?= count($results) ?> result(s) recorded</p>
    </div>
  </div>

  <div class="tableWrap">
    <table class="table">
      <thead>
        <tr>
          <th>Student Code</th>
          <th>Student Name</th>
          <th>Subject Code</th>
          <th>Subject Name</th>
          <th>Grade</th>
          <th class="table__actionsCol">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$results): ?>
          <tr>
            <td colspan="6" style="color:#64748b;padding:16px;">No results yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($results as $r): ?>
            <tr>
              <td><span class="mono"><?= htmlspecialchars((string)$r['student_code']) ?></span></td>
              <td><?= htmlspecialchars((string)$r['student_name']) ?></td>
              <td><span class="mono"><?= htmlspecialchars((string)$r['subject_code']) ?></span></td>
              <td><?= htmlspecialchars((string)$r['subject_name']) ?></td>
              <td>
                <span class="grade">
                  <?= number_format((float)$r['grade'], 2) ?>
                  <span class="grade__muted">/ 20</span>
                </span>
              </td>
              <td class="table__actions">
                <form method="post" action="./results.php" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>" />
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?= htmlspecialchars((string)$r['id']) ?>" />
                  <button class="iconBtn iconBtn--danger" type="submit" aria-label="Delete">🗑</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php if (isset($_GET['new'])): ?>
  <div class="modal" style="display:grid;place-items:center;">
    <div class="modal__dialog" role="dialog" aria-modal="true" aria-label="Add new result">
      <div class="modal__head">
        <h3 class="modal__title">New Result</h3>
        <a class="iconBtn" href="./results.php" aria-label="Close">✕</a>
      </div>

      <form class="formGrid" method="post" action="./results.php" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>" />
        <input type="hidden" name="action" value="create" />

        <?php if ($errors): ?>
          <div class="field field--span2" style="color:#b91c1c;font-weight:700;">
            <?= htmlspecialchars(implode(' ', $errors)) ?>
          </div>
        <?php endif; ?>

        <label class="field">
          <span class="field__label">Student Code</span>
          <input class="input" name="student_code" placeholder="E001" required />
        </label>

        <label class="field">
          <span class="field__label">Student Name</span>
          <input class="input" name="student_name" placeholder="Emma Smith" required />
        </label>

        <label class="field">
          <span class="field__label">Subject Code</span>
          <input class="input" name="subject_code" placeholder="M001" required />
        </label>

        <label class="field">
          <span class="field__label">Subject Name</span>
          <input class="input" name="subject_name" placeholder="Mathematics" required />
        </label>

        <label class="field field--span2">
          <span class="field__label">Grade (0 - 20)</span>
          <input class="input" name="grade" type="number" min="0" max="20" step="0.01" placeholder="16.00" required />
        </label>

        <div class="formActions field--span2">
          <a class="btn btn--ghost" href="./results.php">Cancel</a>
          <button class="btn btn--accent" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
