<?php
/**
 * Shared bootstrap for the schedule-generator pages.
 * Include this first, before any output, in every supervisor-facing file.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

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
