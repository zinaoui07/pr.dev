<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$tab = isset($_GET['tab']) ? (string)$_GET['tab'] : 'students';
$tab = in_array($tab, ['students', 'subjects', 'results'], true) ? $tab : 'students';

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$qLower = mb_strtolower($q);

$students = [
  ['code' => 'ST-1001', 'first' => 'Amine', 'last' => 'Bensaid', 'major' => 'Computer Science'],
  ['code' => 'ST-1002', 'first' => 'Sara', 'last' => 'Khaldi', 'major' => 'Mathematics'],
  ['code' => 'ST-1003', 'first' => 'Youssef', 'last' => 'Haddad', 'major' => 'Physics'],
  ['code' => 'ST-1004', 'first' => 'Lina', 'last' => 'Saidi', 'major' => 'Biology'],
];

$subjects = [
  ['code' => 'SUB-210', 'name' => 'Algorithms', 'department' => 'CS'],
  ['code' => 'SUB-115', 'name' => 'Linear Algebra', 'department' => 'Math'],
  ['code' => 'SUB-140', 'name' => 'Mechanics', 'department' => 'Physics'],
  ['code' => 'SUB-330', 'name' => 'Genetics', 'department' => 'Biology'],
];

$results = [
  ['student' => 'ST-1001', 'subject' => 'Algorithms', 'score' => 16.5],
  ['student' => 'ST-1002', 'subject' => 'Linear Algebra', 'score' => 14.0],
  ['student' => 'ST-1003', 'subject' => 'Mechanics', 'score' => 12.75],
  ['student' => 'ST-1004', 'subject' => 'Genetics', 'score' => 17.25],
];

function contains(string $haystack, string $needleLower): bool {
  if ($needleLower === '') return true;
  return mb_strpos(mb_strtolower($haystack), $needleLower) !== false;
}

$items = [];

if ($tab === 'students') {
  foreach ($students as $s) {
    if (
      contains($s['code'], $qLower) ||
      contains($s['first'], $qLower) ||
      contains($s['last'], $qLower) ||
      contains($s['major'], $qLower)
    ) {
      $items[] = [
        'title' => $s['first'] . ' ' . $s['last'],
        'sub'   => $s['code'] . ' • ' . $s['major'],
        'badge' => 'Student',
      ];
    }
  }
} elseif ($tab === 'subjects') {
  foreach ($subjects as $s) {
    if (contains($s['code'], $qLower) || contains($s['name'], $qLower) || contains($s['department'], $qLower)) {
      $items[] = [
        'title' => $s['name'],
        'sub'   => $s['code'] . ' • ' . $s['department'],
        'badge' => 'Subject',
      ];
    }
  }
} else {
  foreach ($results as $r) {
    if (contains($r['student'], $qLower) || contains($r['subject'], $qLower) || contains((string)$r['score'], $qLower)) {
      $items[] = [
        'title' => $r['subject'],
        'sub'   => $r['student'],
        'badge' => (string)$r['score'],
      ];
    }
  }
}

echo json_encode([
  'tab' => $tab,
  'q' => $q,
  'count' => count($items),
  'items' => $items,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
