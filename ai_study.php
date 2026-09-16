<?php

require 'openai_config.php'; // defines OPENAI_API_KEY — must NOT be committed to git

$data = json_decode(file_get_contents("php://input"), true);
$topic = $data['topic'];



$prompt = "
Create a short educational mini-lesson in CLEAN HTML.

IMPORTANT:
- Do NOT use <html>, <body>
- Do NOT use markdown or ` blocks
- Return ONLY inner HTML
- Do NOT use ellipsis (...)
- Keep it visually clean and structured

STYLE:
Make it modern and easy to read like a learning app.

STRUCTURE:
1. Title
2. Short explanation (3–4 sentences, simple language)
3. Visual block (choose ONE depending on topic):
   - a simple table
   - or a step-by-step flow using divs
   - or a comparison block
4. Key idea (1 sentence)

FORMAT:
Use these classes:
- ai-card
- ai-title
- ai-section
- ai-visual

For visuals:
- Use <table> for comparisons
- Use <div> blocks for processes (like steps or flow)
- Avoid ASCII diagrams

Use small relevant emojis in titles or sections (but not too many)
Topic: $topic
";

$ch = curl_init("https://api.openai.com/v1/chat/completions");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . OPENAI_API_KEY
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ]
]));

$response = curl_exec($ch);

if ($response === false) {
    echo "Error: " . curl_error($ch);
    exit;
}

$result = json_decode($response, true);

echo $result['choices'][0]['message']['content'];
?>
