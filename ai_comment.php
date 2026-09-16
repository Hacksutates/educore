<?php
/**
 * Generates a formal supervisor conclusion straight from the attendance
 * numbers, no external LLM call required.
 *
 * Expects JSON like:
 * { "12": {"present": 8, "total": 10}, "13": {"present": 3, "total": 10}, ... }
 * (same shape attendancemanagement.php already sends via $attendanceStats)
 */

header('Content-Type: text/plain; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data) || count($data) === 0) {
    echo "No attendance data is available for this period, so no conclusion can be generated.";
    exit;
}

$totalStudents = count($data);
$totalPresent   = 0;
$totalSessions  = 0;

$low      = 0; // below 50%
$moderate = 0; // 50-79%
$high     = 0; // 80% and above

foreach ($data as $st) {
    $total   = isset($st['total']) ? (int)$st['total'] : 0;
    $present = isset($st['present']) ? (int)$st['present'] : 0;
    $percent = $total > 0 ? ($present / $total) * 100 : 0;

    $totalPresent  += $present;
    $totalSessions += $total;

    if ($percent < 50) {
        $low++;
    } elseif ($percent < 80) {
        $moderate++;
    } else {
        $high++;
    }
}

$classAverage = $totalSessions > 0 ? round(($totalPresent / $totalSessions) * 100) : 0;

/**
 * Picks a phrasing deterministically from the data itself, so the same
 * attendance numbers always produce the same wording (no randomness),
 * but different classes/periods don't all read identically.
 */
function pickPhrase($seed, $options) {
    $index = intval(substr(md5($seed), 0, 8), 16) % count($options);
    return $options[$index];
}

$seed = "$totalStudents-$totalPresent-$totalSessions-$low-$moderate-$high";

// --- Opening sentence: overall class performance ---
if ($classAverage >= 90) {
    $openings = [
        "Overall attendance for this period is excellent, with a class average of {$classAverage}%.",
        "Attendance across the group has been very strong, averaging {$classAverage}% for this period.",
    ];
} elseif ($classAverage >= 75) {
    $openings = [
        "Overall attendance for this period is solid, with a class average of {$classAverage}%.",
        "Attendance across the group remains at a good level, averaging {$classAverage}% for this period.",
    ];
} elseif ($classAverage >= 50) {
    $openings = [
        "Overall attendance for this period is moderate, with a class average of {$classAverage}%.",
        "Attendance across the group is mixed this period, averaging {$classAverage}%.",
    ];
} else {
    $openings = [
        "Overall attendance for this period is concerning, with a class average of only {$classAverage}%.",
        "Attendance across the group has been weak this period, averaging just {$classAverage}%.",
    ];
}

$comment = pickPhrase($seed, $openings);

// --- Breakdown sentence ---
$parts = [];
if ($high > 0) {
    $parts[] = "{$high} student" . ($high > 1 ? "s" : "") . " maintained strong attendance at 80% or above";
}
if ($moderate > 0) {
    $parts[] = "{$moderate} student" . ($moderate > 1 ? "s" : "") . " showed moderate attendance between 50% and 79%";
}
if ($low > 0) {
    $parts[] = "{$low} student" . ($low > 1 ? "s" : "") . " fell below 50% and " . ($low > 1 ? "require" : "requires") . " closer attention";
}

if (!empty($parts)) {
    $lastPart = array_pop($parts);
    $breakdown = empty($parts)
        ? "Of {$totalStudents} students, {$lastPart}."
        : "Of {$totalStudents} students, " . implode(", ", $parts) . ", and {$lastPart}.";
    $comment .= " " . $breakdown;
}

// --- Optional closing recommendation when some students are struggling ---
if ($low > 0) {
    $closings = [
        "Follow-up with these students is recommended before the next reporting period.",
        "It is advisable to reach out to these students to identify the cause of their absences.",
    ];
    $comment .= " " . pickPhrase($seed . "-closing", $closings);
}

echo $comment;