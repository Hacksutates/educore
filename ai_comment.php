<?php
$data = json_decode(file_get_contents("php://input"), true);

$totalStudents = count($data);
$low = 0;
$high = 0;

foreach ($data as $st) {
    $percent = ($st['total'] > 0) ? ($st['present'] / $st['total']) * 100 : 0;

    if ($percent < 50) $low++;
    if ($percent > 80) $high++;
}

$summary = "Total students: $totalStudents. ";
$summary .= "$high students have high attendance. ";
$summary .= "$low students have low attendance.";

$apiKey = "k-proj-4abjBTbkkZzKsBBzXnqjITbATZrX8fG3GszyvzWMz02Ib2ve1PZfCvxttl2t6IXixbiw7n0OkjT3BlbkFJG5d_NYwZsCtz7zW_GJBTFVHTqzeMrdQDSK0rYcRkBkMPZn3SBYKCSQhWESSs_pCRCgc_h1rO4A";

$prompt = "Write a short supervisor comment (1-2 sentences) in a formal academic tone. No lists, no titles. Based on: $summary";

$ch = curl_init("https://api.openai.com/v1/chat/completions");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ]
]));

$response = curl_exec($ch);

if ($response === false) {
    echo "CURL ERROR: " . curl_error($ch);
    exit;
}

$result = json_decode($response, true);

// 👉 ВЫВОДИМ ТОЛЬКО ТЕКСТ
echo $result['choices'][0]['message']['content'];
?>
