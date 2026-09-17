<?php
session_start();
require 'sso_config.php'; // defines SSO_SHARED_SECRET — must NOT be committed to git

if (!isset($_SESSION['StudentID']) && !isset($_SESSION['TeacherID'])) {
    header("Location: registration.php");
    exit;
}

$payload = json_encode([
    "email" => $_SESSION['name'],
    "display_name" => $_SESSION['name'],
]);

// Release the session file lock before making the slow outbound request
// below. PHP's default (file-based) session handler lets only one script
// per session ID hold the lock at a time, and every other page here calls
// session_start() too — so as long as this script keeps the session open,
// any other tab/page hits a wall in session_start() and just sits there
// until this request finishes (or the 30s curl timeout hits), even if the
// chat window that triggered it was already closed. We're done reading
// $_SESSION at this point, so it's safe to close it early.
session_write_close();

$ch = curl_init("https://weteach.onrender.com/sso/exchange"); // <-- put your real Render backend URL here
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-SSO-Secret: " . SSO_SHARED_SECRET,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$data = json_decode($response, true);
$tokenHash = $data['token_hash'] ?? null;

if (!$tokenHash) {
    echo "<pre>";
    echo "HTTP code: " . $httpCode . "\n";
    echo "curl error: " . $curlError . "\n";
    echo "raw response: " . htmlspecialchars($response) . "\n";
    echo "</pre>";
    die();
}
?>
<iframe
  src="https://weteach-lovat.vercel.app/embed/chat?token_hash=<?= urlencode($tokenHash); ?>&primary=4a2fa5&font=Quicksand"
  style="width:100%; height:80vh; border:none; border-radius:12px;"
></iframe>