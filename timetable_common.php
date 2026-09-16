<?php
/**
 * Shared bootstrap for the schedule-generator pages.
 * Include this first, before any output, in every supervisor-facing file.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';
require_once __DIR__ . '/schedule_lib.php';

// --- Auth guard -------------------------------------------------------
// Only a logged-in supervisor may build or assign schedules.
// NOTE: this assumes your login script (script1.php) sets
// $_SESSION['supervisor_id'] on successful supervisor login, matching the
// `supervisors.supervisor_id` column already used in admin.php. If your
// login uses a different session key, update the line below to match.
if (empty($_SESSION['supervisor_id'])) {
    header('Location: reg_form_sup.php');
    exit;
}
$supervisorID = (int) $_SESSION['supervisor_id'];

// --- Install guard ----------------------------------------------------
// Without the generator tables every page below dies with a SQL error,
// so say what to do instead.
if (!tt_tables_ready($connect)) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<title>Setup needed</title>'
       . '<link rel="stylesheet" href="style_timetable.css"></head><body>'
       . '<div class="header-row"><h1>Setup needed</h1>'
       . '<a href="main.html" class="enroll-link">&larr; Back to Main</a></div>'
       . '<div class="panel"><p class="error">The schedule tables have not been created yet.</p>'
       . '<p>Run this once against the <code>automated_system</code> database:</p>'
       . '<pre>mysql -u student1 -p automated_system &lt; sql/01_schedule_generator.sql</pre>'
       . '<p class="muted small">It only adds tables (subjects, timetables, timetable_slots, '
       . 'timetable_assignments) — nothing existing is changed.</p></div>'
       . '</body></html>';
    exit;
}

// Fixed list of school days used throughout the schedule generator.
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

// --- Tiny flash-message helpers ---------------------------------------
function flash(string $key, string $message): void
{
    $_SESSION[$key] = $message;
}

function takeFlash(string $key): string
{
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);
    return $message;
}
