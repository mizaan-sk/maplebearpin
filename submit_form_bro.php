<?php
// submit_form_bro.php
session_start();
header("Content-Type: application/json");

// ✅ Ensure OTP verification
if (empty($_SESSION['verified'])) {
  echo json_encode(["status" => "error", "message" => "Please verify your phone number before submitting."]);
  exit;
}

// ✅ Read incoming JSON payload
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
  echo json_encode(["status" => "error", "message" => "No data received"]);
  exit;
}

// ✅ Capture user IP
function getUserIP() {
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
  return $_SERVER['REMOTE_ADDR'];
}

$payload = [
  "fullName" => $data['fullName'] ?? '',
  "phone" => $data['phone'] ?? '',
  "email" => $data['email'] ?? '',
  "state" => $data['state'] ?? '',
  "city" => $data['city'] ?? '',
  "investment" => $data['investment'] ?? '',
  "timeline" => $data['timeline'] ?? '',
  "form_source" => "Brochure Form",
  "userIP" => getUserIP(),
  "utm_source" => $data['utm_source'] ?? '',
  "utm_ad" => $data['utm_ad'] ?? '',
  "utm_campaign" => $data['utm_campaign'] ?? '',
  "utm_placement" => $data['utm_placement'] ?? '',
  "utm_keyword" => $data['utm_keyword'] ?? '',
  "gclid" => $data['gclid'] ?? '',
  "fbclid" => $data['fbclid'] ?? ''
];

$googleScriptURL = "https://script.google.com/macros/s/AKfycbz9-lGQt9BOQKmkpGf1HsCtV0haZxgwWBvp34sqk24l6xTEC5TCi1HAZUQHjQBQdG10zg/exec";

$options = [
  "http" => [
    "header"  => "Content-Type: application/json\r\n",
    "method"  => "POST",
    "content" => json_encode($payload),
  ],
];

$context  = stream_context_create($options);
$response = @file_get_contents($googleScriptURL, false, $context);

session_destroy();

if ($response === FALSE) {
  echo json_encode(["status" => "error", "message" => "Failed to send data to Google Sheet"]);
} else {
  echo json_encode(["status" => "success", "message" => "Form submitted successfully", "debug" => $response]);
}
?>
