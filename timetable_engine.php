<?php
/**
 * Automatic timetable generator.
 *
 * Given a set of subjects with weekly lesson counts, it fills a week grid
 * while respecting:
 *   - one class per period on this timetable
 *   - no teacher in two places at once (across every timetable, not just this one)
 *   - no room used twice at once (same)
 *   - a subject is not repeated in the same day unless it has to be
 *
 * Strategy: randomised greedy with restarts. Each attempt shuffles the order
 * in which lessons and periods are tried; the best attempt out of many is
 * kept. This is fast (milliseconds for a normal school week) and reliably
 * fills the grid when a valid arrangement exists.
 */

require_once __DIR__ . '/schedule_lib.php';   // tt_query() / tt_prepare()

/** Build the period rows: start/end times for each period of the day. */
function ttg_build_periods(string $startTime, int $lessonMinutes, int $breakMinutes, int $periodsPerDay): array
{
    $periods = [];
    $cursor  = strtotime('1970-01-01 ' . $startTime . ':00');
    if ($cursor === false) {
        $cursor = strtotime('1970-01-01 08:30:00');
    }

    for ($i = 0; $i < $periodsPerDay; $i++) {
        $end = $cursor + $lessonMinutes * 60;
        $periods[] = [
            'start' => date('H:i:s', $cursor),
            'end'   => date('H:i:s', $end),
        ];
        $cursor = $end + $breakMinutes * 60;
    }
    return $periods;
}

/**
 * Everything already booked elsewhere, so the generator does not
 * double-book a teacher or a room that another class is using.
 * Key format: "Monday|08:30:00".
 */
function ttg_load_external_bookings($connect, int $excludeTimetableID): array
{
    $teacherBusy = [];
    $roomBusy    = [];

    $res = tt_query($connect, "
        SELECT TeacherID, ClassroomID, DayOfWeek, TimeStart
        FROM timetable_slots
        WHERE TimetableID <> " . (int) $excludeTimetableID . "
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $key = $row['DayOfWeek'] . '|' . $row['TimeStart'];
            $teacherBusy[(int) $row['TeacherID']][$key] = true;
            $roomBusy[(int) $row['ClassroomID']][$key]  = true;
        }
    }

    return ['teachers' => $teacherBusy, 'rooms' => $roomBusy];
}

/**
 * Expand the per-subject weekly counts into one entry per lesson to place.
 * $demand: [subjectID => ['count' => int, 'teachers' => int[], 'name' => string]]
 */
function ttg_build_lesson_list(array $demand): array
{
    $lessons = [];
    foreach ($demand as $subjectID => $info) {
        for ($i = 0; $i < $info['count']; $i++) {
            $lessons[] = [
                'subject_id' => (int) $subjectID,
                'teachers'   => $info['teachers'],
            ];
        }
    }

    // Hardest first: subjects with the fewest eligible teachers are the most
    // likely to fail late, so they get first pick of the grid.
    usort($lessons, function ($a, $b) {
        return count($a['teachers']) <=> count($b['teachers']);
    });

    return $lessons;
}

/**
 * One placement attempt. Returns the slots it managed to place plus the
 * lessons it could not fit.
 */
function ttg_attempt(array $lessons, array $days, array $periods, array $rooms, array $busy, bool $preferOneRoom, bool $spreadSubjects): array
{
    // Real school timetables fill each day front-to-back — period 1, then
    // period 2, and so on — rather than scattering classes at random times
    // with free periods left in between. Shuffling the DAY order still
    // gives every attempt a different layout, but periods within a day are
    // always tried in chronological order, so a lesson only ever lands on
    // a later period once every earlier one that day is taken (or
    // unusable). Any gaps that still happen — because a teacher or room
    // was only free later — get squeezed out afterwards by ttg_compact().
    $cells = [];
    $shuffledDays = $days;
    shuffle($shuffledDays);
    foreach ($shuffledDays as $day) {
        foreach ($periods as $period) {
            $cells[] = ['day' => $day, 'start' => $period['start'], 'end' => $period['end']];
        }
    }

    $teacherBusy = $busy['teachers'];
    $roomBusy    = $busy['rooms'];

    $cellTaken    = [];   // periods already used on THIS timetable
    $subjectOnDay = [];   // [subjectID][day] => count, for spreading out
    $placed       = [];
    $unplaced     = [];

    // A "home room" makes the class stay put where possible.
    $homeRoom = ($preferOneRoom && $rooms) ? $rooms[array_rand($rooms)] : null;

    foreach ($lessons as $lesson) {
        $done = false;

        foreach ($cells as $cell) {
            $key = $cell['day'] . '|' . $cell['start'];

            if (isset($cellTaken[$key])) {
                continue;
            }
            if ($spreadSubjects && !empty($subjectOnDay[$lesson['subject_id']][$cell['day']])) {
                continue;   // already has this subject today
            }

            // A teacher who is free at this moment.
            $teachers = $lesson['teachers'];
            shuffle($teachers);
            $teacherID = null;
            foreach ($teachers as $candidate) {
                if (empty($teacherBusy[$candidate][$key])) {
                    $teacherID = $candidate;
                    break;
                }
            }
            if ($teacherID === null) {
                continue;
            }

            // A room that is free at this moment, home room first.
            $roomOrder = $rooms;
            shuffle($roomOrder);
            if ($homeRoom !== null) {
                array_unshift($roomOrder, $homeRoom);
            }
            $roomID = null;
            foreach ($roomOrder as $candidate) {
                if (empty($roomBusy[$candidate][$key])) {
                    $roomID = $candidate;
                    break;
                }
            }
            if ($roomID === null) {
                continue;
            }

            // Book it.
            $cellTaken[$key] = true;
            $teacherBusy[$teacherID][$key] = true;
            $roomBusy[$roomID][$key]       = true;
            $subjectOnDay[$lesson['subject_id']][$cell['day']] =
                ($subjectOnDay[$lesson['subject_id']][$cell['day']] ?? 0) + 1;

            $placed[] = [
                'subject_id'   => $lesson['subject_id'],
                'teacher_id'   => $teacherID,
                'classroom_id' => $roomID,
                'day'          => $cell['day'],
                'time_start'   => $cell['start'],
                'time_end'     => $cell['end'],
            ];
            $done = true;
            break;
        }

        if (!$done) {
            $unplaced[] = $lesson;
        }
    }

    return ['placed' => $placed, 'unplaced' => $unplaced];
}

/**
 * Push classes earlier within each day to close up empty periods sitting
 * between two classes — the same "no free period sandwiched in the middle
 * of the day" rule real school timetables follow. A class only moves into
 * an earlier gap when the teacher and room are actually free at that
 * moment (checked against every OTHER timetable's bookings); a gap caused
 * by a genuine conflict is left alone rather than forced.
 */
function ttg_compact(array $placed, array $days, array $periods, array $busy): array
{
    if (!$placed || !$periods) {
        return $placed;
    }

    $periodIndex = [];
    foreach ($periods as $i => $period) {
        $periodIndex[$period['start']] = $i;
    }

    $teacherBusy = $busy['teachers'];
    $roomBusy    = $busy['rooms'];

    // This timetable's own placements, per day, keyed by period index.
    $byDay = [];
    foreach ($placed as $idx => $slot) {
        $pIdx = $periodIndex[$slot['time_start']] ?? 0;
        $byDay[$slot['day']][$pIdx] = $idx;
    }

    $periodCount = count($periods);

    foreach ($days as $day) {
        if (empty($byDay[$day])) {
            continue;
        }

        for ($gap = 0; $gap < $periodCount; $gap++) {
            if (isset($byDay[$day][$gap])) {
                continue;   // already occupied — not a gap
            }

            // The nearest occupied period after this gap, on this day.
            $next = null;
            foreach ($byDay[$day] as $pIdx => $slotIdx) {
                if ($pIdx > $gap && ($next === null || $pIdx < $next)) {
                    $next = $pIdx;
                }
            }
            if ($next === null) {
                break;   // nothing later today — the rest of the day is legitimately free
            }

            $slotIdx   = $byDay[$day][$next];
            $slot      = $placed[$slotIdx];
            $gapPeriod = $periods[$gap];
            $newKey    = $day . '|' . $gapPeriod['start'];

            $teacherFree = empty($teacherBusy[$slot['teacher_id']][$newKey]);
            $roomFree    = empty($roomBusy[$slot['classroom_id']][$newKey]);

            if ($teacherFree && $roomFree) {
                $oldKey = $day . '|' . $slot['time_start'];
                unset($teacherBusy[$slot['teacher_id']][$oldKey]);
                unset($roomBusy[$slot['classroom_id']][$oldKey]);

                $placed[$slotIdx]['time_start'] = $gapPeriod['start'];
                $placed[$slotIdx]['time_end']   = $gapPeriod['end'];

                $teacherBusy[$slot['teacher_id']][$newKey] = true;
                $roomBusy[$slot['classroom_id']][$newKey]  = true;

                unset($byDay[$day][$next]);
                $byDay[$day][$gap] = $slotIdx;
            }
            // Otherwise the teacher or room is genuinely busy elsewhere at
            // that moment — leave the gap rather than double-book them.
        }
    }

    return $placed;
}

/**
 * Run several attempts and keep the best one.
 * Later attempts drop the "spread subjects across days" rule so a tight
 * grid still gets filled rather than left half-empty.
 */
function ttg_generate(array $demand, array $days, array $periods, array $rooms, array $busy, bool $preferOneRoom, int $attempts = 60): array
{
    $lessons = ttg_build_lesson_list($demand);
    if (!$lessons || !$rooms || !$days || !$periods) {
        return ['placed' => [], 'unplaced' => count($lessons), 'relaxed' => false];
    }

    $best        = null;
    $bestRelaxed = false;

    for ($i = 0; $i < $attempts; $i++) {
        // First two thirds of the attempts keep subjects spread out.
        $spread = $i < (int) ($attempts * 0.66);

        shuffle($lessons);
        usort($lessons, function ($a, $b) {
            return count($a['teachers']) <=> count($b['teachers']);
        });

        $result = ttg_attempt($lessons, $days, $periods, $rooms, $busy, $preferOneRoom, $spread);

        if ($best === null || count($result['unplaced']) < count($best['unplaced'])) {
            $best        = $result;
            $bestRelaxed = !$spread;
        }
        if (!$result['unplaced']) {
            return [
                'placed'   => ttg_compact($result['placed'], $days, $periods, $busy),
                'unplaced' => 0,
                'relaxed'  => !$spread,
            ];
        }
    }

    return [
        'placed'   => ttg_compact($best['placed'], $days, $periods, $busy),
        'unplaced' => count($best['unplaced']),
        'relaxed'  => $bestRelaxed,
    ];
}

/** Write the generated slots, replacing whatever was on the timetable. */
function ttg_save($connect, int $timetableID, array $slots, bool $replaceExisting): int
{
    if ($replaceExisting) {
        tt_query($connect, "DELETE FROM timetable_slots WHERE TimetableID = " . $timetableID);
    }

    $stmt = tt_prepare($connect, "
        INSERT IGNORE INTO timetable_slots
            (TimetableID, SubjectID, TeacherID, ClassroomID, DayOfWeek, TimeStart, TimeEnd)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        return 0;
    }

    $saved = 0;
    foreach ($slots as $slot) {
        mysqli_stmt_bind_param(
            $stmt,
            'iiiisss',
            $timetableID,
            $slot['subject_id'],
            $slot['teacher_id'],
            $slot['classroom_id'],
            $slot['day'],
            $slot['time_start'],
            $slot['time_end']
        );
        try {
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                $saved++;
            }
        } catch (Throwable $e) {
            // A slot that clashes with the unique key is simply skipped.
        }
    }

    tt_query($connect, "UPDATE timetables SET UpdatedAt = NOW() WHERE TimetableID = " . $timetableID);

    return $saved;
}