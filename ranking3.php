<?php

$students = [
    [
        'code' => 'E003',
        'first_name' => 'Sophia',
        'last_name' => 'Williams',
        'year' => '3rd Year',
        'major' => 'Literature',
        'average' => 16.80
    ],

    [
        'code' => 'E001',
        'first_name' => 'Emma',
        'last_name' => 'Smith',
        'year' => '2nd Year',
        'major' => 'Science',
        'average' => 15.50
    ],

    [
        'code' => 'E002',
        'first_name' => 'Michael',
        'last_name' => 'Johnson',
        'year' => '2nd Year',
        'major' => 'Science',
        'average' => 14.20
    ]
];



function honorsLabel($avg)
{
    if ($avg >= 16.0) {
        return 'Summa Cum Laude';
    }

    if ($avg >= 14.0) {
        return 'Magna Cum Laude';
    }

    if ($avg >= 12.0) {
        return 'Cum Laude';
    }

    return null;
}



function honorsClass($avg)
{
    if ($avg >= 16.0) {
        return 'pill pill--summa';
    }

    if ($avg >= 14.0) {
        return 'pill pill--magna';
    }

    if ($avg >= 12.0) {
        return 'pill pill--cum';
    }

    return 'pill pill--none';
}



function medal($rank)
{
    switch ($rank) {

        case 1:
            return '🥇';

        case 2:
            return '🥈';

        case 3:
            return '🥉';

        default:
            return '🏅';
    }
}



/* majors */

$tempMajors = array();

foreach ($students as $s) {
    $tempMajors[] = $s['major'];
}

$majors = array_unique($tempMajors);
sort($majors);



/* years */

$tempYears = array();

foreach ($students as $s) {
    $tempYears[] = $s['year'];
}

$years = array_unique($tempYears);
sort($years);



/* filters */

$selectedMajor = 'All';
$selectedYear = 'All';

if (isset($_GET['major'])) {
    $selectedMajor = trim($_GET['major']);
}

if (isset($_GET['year'])) {
    $selectedYear = trim($_GET['year']);
}



/* filtering */

$filtered = array();

foreach ($students as $s) {

    if ($selectedMajor != 'All' && $s['major'] != $selectedMajor) {
        continue;
    }

    if ($selectedYear != 'All' && $s['year'] != $selectedYear) {
        continue;
    }

    $filtered[] = $s;
}



/* sorting */

usort($filtered, function ($a, $b) {

    if ($a['average'] == $b['average']) {
        return 0;
    }

    return ($a['average'] < $b['average']) ? 1 : -1;
});



/* statistics */

$totalStudents = count($filtered);

$overallAverage = 0;

if ($totalStudents > 0) {

    $sum = 0;

    foreach ($filtered as $s) {
        $sum += $s['average'];
    }

    $overallAverage = $sum / $totalStudents;
}



$passed = 0;

foreach ($filtered as $s) {

    if ($s['average'] >= 10) {
        $passed++;
    }
}

$failed = $totalStudents - $passed;



$top3 = array_slice($filtered, 0, 3);

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Student Rankings</title>

<link rel="stylesheet" href="ranking.css">

</head>

<body>

<h1>Student Rankings</h1>



<h2>Statistics</h2>

<ul>
    <li>Total Students: <?php echo $totalStudents; ?></li>

    <li>
        Overall Average:
        <?php echo number_format($overallAverage, 2); ?>
    </li>

    <li>Passed: <?php echo $passed; ?></li>

    <li>Failed: <?php echo $failed; ?></li>
</ul>



<h2>Filters</h2>

<form method="get">

    <label>Major</label>

    <select name="major">

        <option value="All">All</option>

        <?php foreach ($majors as $m) { ?>

            <option value="<?php echo $m; ?>">

                <?php echo $m; ?>

            </option>

        <?php } ?>

    </select>



    <label>Year</label>

    <select name="year">

        <option value="All">All</option>

        <?php foreach ($years as $y) { ?>

            <option value="<?php echo $y; ?>">

                <?php echo $y; ?>

            </option>

        <?php } ?>

    </select>



    <button type="submit">Apply Filters</button>

</form>



<h2>Ranking List</h2>

<table border="1" cellpadding="10">
    <tr>
    <th>Rank</th>
    <th>Code</th>
    <th>Last Name</th>
    <th>First Name</th>
    <th>Year</th>
    <th>Major</th>
    <th>Average</th>
    <th>Honors</th>
</tr>

<?php if (count($filtered) == 0) { ?>

<tr>
    <td colspan="8">No students found</td>
</tr>

<?php } else { ?>

<?php foreach ($filtered as $i => $s) { ?>

<?php
$rank = $i + 1;
$hon = honorsLabel($s['average']);
?>

<tr>

<td>
    <?php echo medal($rank); ?>
    <?php echo $rank; ?>
</td>

<td><?php echo $s['code']; ?></td>

<td><?php echo $s['last_name']; ?></td>

<td><?php echo $s['first_name']; ?></td>

<td><?php echo $s['year']; ?></td>

<td><?php echo $s['major']; ?></td>

<td>
    <?php echo number_format($s['average'], 2); ?>
</td>

<td>

<?php

if ($hon) {
    echo $hon;
} else {
    echo '-';
}

?>

</td>

</tr>

<?php } ?>

<?php } ?>

</table>



<h2>Top 3 Students</h2>

<?php foreach ($top3 as $i => $st) { ?>

<div style="margin-bottom:15px; padding:10px; border:1px solid #ccc;">

    <h3>
        <?php echo medal($i + 1); ?>

        Place <?php echo $i + 1; ?>
    </h3>

    <p>

        <?php echo $st['first_name']; ?>
        <?php echo $st['last_name']; ?>

    </p>

    <p>
        Average:
        <?php echo number_format($st['average'], 2); ?>
    </p>

</div>

<?php } ?>

</body>
</html>
