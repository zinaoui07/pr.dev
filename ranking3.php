<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$tab = isset($_GET['tab']) ? (string)$_GET['tab'] : 'students';
$tab = in_array($tab, ['students', 'subjects', 'results'], true) ? $tab : 'students';

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$qLower = mb_strtolower($q);

function loadJson(string $file, array $seed): array
{
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $seed;
    }

    $raw = file_get_contents($file);
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function contains(string $haystack, string $needleLower): bool
{
    if ($needleLower === '') return true;
    return mb_strpos(mb_strtolower($haystack), $needleLower) !== false;
}

$studentsFile = __DIR__ . '/students_data.json';
$subjectsFile = __DIR__ . '/subjects_data.json';
$resultsFile  = __DIR__ . '/results_data.json';

$studentsSeed = [
    ['code' => 'ST-1001', 'first' => 'Amine', 'last' => 'Bensaid', 'major' => 'Computer Science'],
    ['code' => 'ST-1002', 'first' => 'Sara', 'last' => 'Khaldi', 'major' => 'Mathematics'],
    ['code' => 'ST-1003', 'first' => 'Youssef', 'last' => 'Haddad', 'major' => 'Physics'],
    ['code' => 'ST-1004', 'first' => 'Lina', 'last' => 'Saidi', 'major' => 'Biology'],
];

$subjectsSeed = [
    ['code' => 'SUB-210', 'name' => 'Algorithms', 'department' => 'CS'],
    ['code' => 'SUB-115', 'name' => 'Linear Algebra', 'department' => 'Math'],
    ['code' => 'SUB-140', 'name' => 'Mechanics', 'department' => 'Physics'],
    ['code' => 'SUB-330', 'name' => 'Genetics', 'department' => 'Biology'],
];

// This seed matches the shape used by your results page (student_code, student_name, subject_code, subject_name, grade)
$resultsSeed = [
    ['id' => '1', 'student_code' => 'E001', 'student_name' => 'Emma Smith', 'subject_code' => 'M001', 'subject_name' => 'Mathematics', 'grade' => 16.00],
    ['id' => '2', 'student_code' => 'E001', 'student_name' => 'Emma Smith', 'subject_code' => 'M002', 'subject_name' => 'Physics', 'grade' => 15.00],
    ['id' => '3', 'student_code' => 'E001', 'student_name' => 'Emma Smith', 'subject_code' => 'M003', 'subject_name' => 'English', 'grade' => 15.50],
    ['id' => '4', 'student_code' => 'E002', 'student_name' => 'Michael Johnson', 'subject_code' => 'M001', 'subject_name' => 'Mathematics', 'grade' => 14.00],
    ['id' => '5', 'student_code' => 'E002', 'student_name' => 'Michael Johnson', 'subject_code' => 'M002', 'subject_name' => 'Physics', 'grade' => 14.50],
];

$students = loadJson($studentsFile, $studentsSeed);
$subjects = loadJson($subjectsFile, $subjectsSeed);
$results  = loadJson($resultsFile, $resultsSeed);

$items = [];

if ($tab === 'students') {
    foreach ($students as $s) {
        $code = (string)($s['code'] ?? '');
        $first = (string)($s['first'] ?? '');
        $last = (string)($s['last'] ?? '');
        $major = (string)($s['major'] ?? '');

        if (
            contains($code, $qLower) ||
            contains($first, $qLower) ||
            contains($last, $qLower) ||
            contains($major, $qLower)
        ) {
            $items[] = [
                'title' => trim($first . ' ' . $last),
                'sub' => $code . ($major !== '' ? (' • ' . $major) : ''),
                'badge' => 'Student',
                'badgeType' => 'primary',
            ];
        }
    }
} elseif ($tab === 'subjects') {
    foreach ($subjects as $s) {
        $code = (string)($s['code'] ?? '');
        $name = (string)($s['name'] ?? '');
        $dept = (string)($s['department'] ?? '');

        if (contains($code, $qLower) || contains($name, $qLower) || contains($dept, $qLower)) {
            $items[] = [
                'title' => $name,
                'sub' => $code . ($dept !== '' ? (' • ' . $dept) : ''),
                'badge' => 'Subject',
                'badgeType' => 'primary',
            ];
        }
    }
} else {
    foreach ($results as $r) {
        $studentCode = (string)($r['student_code'] ?? '');
        $studentName = (string)($r['student_name'] ?? '');
        $subjectCode = (string)($r['subject_code'] ?? '');
        $subjectName = (string)($r['subject_name'] ?? '');
        $grade = isset($r['grade']) && is_numeric($r['grade']) ? (float)$r['grade'] : null;

        $hay = $studentCode . ' ' . $studentName . ' ' . $subjectCode . ' ' . $subjectName . ' ' . ($grade === null ? '' : (string)$grade);
        if (!contains($hay, $qLower)) continue;

        $badge = $grade === null ? '' : (number_format($grade, 2) . ' / 20');
        $items[] = [
            'title' => $subjectName !== '' ? $subjectName : $subjectCode,
            'sub' => trim($studentCode . ($studentName !== '' ? (' • ' . $studentName) : '')),
            'badge' => $badge,
            'badgeType' => $grade !== null && $grade >= 10 ? 'primary' : 'default',
        ];
    }
}

echo json_encode([
    'tab' => $tab,
    'q' => $q,
    'count' => count($items),
    'items' => $items,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

