<?php
declare(strict_types=1);


$students = [
    ['code' => 'E003', 'first_name' => 'Sophia',  'last_name' => 'Williams', 'year' => '3rd Year', 'major' => 'Literature', 'average' => 16.80],
    ['code' => 'E001', 'first_name' => 'Emma',    'last_name' => 'Smith',    'year' => '2nd Year', 'major' => 'Science',    'average' => 15.50],
    ['code' => 'E002', 'first_name' => 'Michael', 'last_name' => 'Johnson',  'year' => '2nd Year', 'major' => 'Science',    'average' => 14.20],
];

function honorsLabel(float $avg): ?string
{
    if ($avg >= 16.0) return 'Summa Cum Laude';
    if ($avg >= 14.0) return 'Magna Cum Laude';
    if ($avg >= 12.0) return 'Cum Laude';
    return null;
}

function honorsClass(float $avg): string
{
    if ($avg >= 16.0) return 'pill pill--summa';
    if ($avg >= 14.0) return 'pill pill--magna';
    if ($avg >= 12.0) return 'pill pill--cum';
    return 'pill pill--none';
}

function medal(int $rank): string
{
    return match ($rank) {
        1 => '🥇',
        2 => '🥈',
        3 => '🥉',
        default => '🏅',
    };
}

$majors = array_values(array_unique(array_map(static fn($s) => (string)$s['major'], $students)));
sort($majors, SORT_NATURAL | SORT_FLAG_CASE);
$years = array_values(array_unique(array_map(static fn($s) => (string)$s['year'], $students)));
sort($years, SORT_NATURAL | SORT_FLAG_CASE);

$selectedMajor = isset($_GET['major']) ? trim((string)$_GET['major']) : 'All';
$selectedYear = isset($_GET['year']) ? trim((string)$_GET['year']) : 'All';

if ($selectedMajor !== 'All' && !in_array($selectedMajor, $majors, true)) $selectedMajor = 'All';
if ($selectedYear !== 'All' && !in_array($selectedYear, $years, true)) $selectedYear = 'All';

$filtered = array_values(array_filter($students, static function (array $s) use ($selectedMajor, $selectedYear): bool {
    if ($selectedMajor !== 'All' && $s['major'] !== $selectedMajor) return false;
    if ($selectedYear !== 'All' && $s['year'] !== $selectedYear) return false;
    return true;
}));

usort($filtered, static fn($a, $b) => $b['average'] <=> $a['average']);

$totalStudents = count($filtered);
$overallAverage = $totalStudents > 0
    ? array_sum(array_map(static fn($s) => (float)$s['average'], $filtered)) / $totalStudents
    : 0.0;
$passed = count(array_filter($filtered, static fn($s) => (float)$s['average'] >= 10.0));
$failed = $totalStudents - $passed;

$top3 = array_slice($filtered, 0, 3);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Rankings</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="./ranking.css" />
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
      <div class="brand" aria-label="School app">
        <span class="brand__dot" aria-hidden="true"></span>
        <span class="brand__text">School</span>
      </div>
      <nav class="nav" aria-label="Primary">
        <a class="nav__item" href="./index.php">Home</a>
        <a class="nav__item" href="./students.php">Students</a>
        <a class="nav__item" href="./subjects.php">Subjects</a>
        <a class="nav__item" href="./results.php">Results</a>
        <a class="nav__item" href="./search.php">Search</a>
        <a class="nav__item nav__item--active" href="./ranking.php" aria-current="page">Rankings</a>
      </nav>
    </div>
  </header>

  <main class="container page">
    <div class="pageHead">
      <div>
        <h1 class="pageTitle">Student Rankings</h1>
        <p class="pageSubtitle">Rankings by overall average</p>
      </div>
    </div>

    <section class="kpiGrid" aria-label="Summary">
      <article class="kpi kpi--students">
        <div class="kpi__label">Total Students</div>
        <div class="kpi__value"><?= (int)$totalStudents ?></div>
      </article>
      <article class="kpi kpi--avg">
        <div class="kpi__label">Overall Average</div>
        <div class="kpi__value"><?= number_format((float)$overallAverage, 2) ?></div>
      </article>
      <article class="kpi kpi--pass">
        <div class="kpi__label">Passed (≥ 10)</div>
        <div class="kpi__value"><?= (int)$passed ?></div>
      </article>
      <article class="kpi kpi--fail">
        <div class="kpi__label">Failed (&lt; 10)</div>
        <div class="kpi__value"><?= (int)$failed ?></div>
      </article>
    </section>

    <section class="card">
      <div class="cardHead">
        <div>
          <h2 class="cardHead__title">Filters</h2>
          <p class="cardHead__sub">Filter rankings by year and major</p>
        </div>
      </div>

      <form class="filterBar" method="get" action="./ranking.php">
        <label class="field">
          <span class="field__label">Major</span>
          <select class="input input--select" name="major">
            <option value="All" <?= $selectedMajor === 'All' ? 'selected' : '' ?>>All</option>
            <?php foreach ($majors as $m): ?>
              <option value="<?= htmlspecialchars($m) ?>" <?= $selectedMajor === $m ? 'selected' : '' ?>>
                <?= htmlspecialchars($m) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field">
          <span class="field__label">Year</span>
          <select class="input input--select" name="year">
            <option value="All" <?= $selectedYear === 'All' ? 'selected' : '' ?>>All</option>
            <?php foreach ($years as $y): ?>
              <option value="<?= htmlspecialchars($y) ?>" <?= $selectedYear === $y ? 'selected' : '' ?>>
                <?= htmlspecialchars($y) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <div class="filterBar__actions">
          <button class="btn btn--accent" type="submit">Apply Filters</button>
          <a class="btn btn--ghost" href="./ranking.php">Reset</a>
        </div>
      </form>
    </section>

    <section class="card">
      <div class="cardHead">
        <div>
          <h2 class="cardHead__title">Rankings list</h2>
          <p class="cardHead__sub">Showing <?= (int)$totalStudents ?> student(s) — sorted by average (desc)</p>
        </div>
      </div>

      <div class="tableWrap">
        <table class="table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Code</th>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Year</th>
              <th>Major</th>
              <th class="table__num">Average</th>
              <th>Honors</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$filtered): ?>
              <tr>
                <td colspan="8" class="table__empty">No students match the current filters.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($filtered as $i => $s): ?>
                <?php
                  $rank = $i + 1;
                  $hon = honorsLabel((float)$s['average']);
                ?>
                <tr>
                  <td class="table__rank">
                    <span class="rank">
                      <span class="rank__medal" aria-hidden="true"><?= htmlspecialchars(medal($rank)) ?></span>
                      <span><?= (int)$rank ?></span>
                    </span>
                  </td>
                  <td><span class="mono"><?= htmlspecialchars((string)$s['code']) ?></span></td>
                  <td><?= htmlspecialchars((string)$s['last_name']) ?></td>
                  <td><?= htmlspecialchars((string)$s['first_name']) ?></td>
                  <td><span class="badge badge--year"><?= htmlspecialchars((string)$s['year']) ?></span></td>
                  <td><span class="badge badge--major"><?= htmlspecialchars((string)$s['major']) ?></span></td>
                  <td class="table__num">
                    <span class="grade"><?= number_format((float)$s['average'], 2) ?></span>
                    <span class="grade__muted"> / 20</span>
                  </td>
                  <td>
                    <?php if ($hon): ?>
                      <span class="<?= htmlspecialchars(honorsClass((float)$s['average'])) ?>"><?= htmlspecialchars($hon) ?></span>
                    <?php else: ?>
                      <span class="pill pill--none">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($top3): ?>
        <div class="podiumWrap">
          <div class="podiumGrid" aria-label="Top 3">
            <?php
              $slots = [
                1 => $top3[0] ?? null,
                2 => $top3[1] ?? null,
                3 => $top3[2] ?? null,
              ];
            ?>
            <?php foreach ($slots as $place => $st): ?>
              <?php if (!$st) continue; ?>
              <article class="podiumCard podiumCard--<?= (int)$place ?>">
                <div class="podiumCard__icon" aria-hidden="true"><?= htmlspecialchars(medal($place)) ?></div>
                <div class="podiumCard__place"><?= (int)$place ?><?= $place === 1 ? 'st' : ($place === 2 ? 'nd' : 'rd') ?> Place</div>
                <div class="podiumCard__name"><?= htmlspecialchars((string)$st['first_name'] . ' ' . (string)$st['last_name']) ?></div>
                <div class="podiumCard__avg"><?= number_format((float)$st['average'], 2) ?></div>
                <div class="podiumCard__meta"><?= htmlspecialchars((string)$st['year'] . ' • ' . (string)$st['major']) ?></div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
