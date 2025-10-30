<?php
// verify_otp.php
session_start();
header("Content-Type: application/json");

$userOtp = $_POST['otp'] ?? '';

if (empty($userOtp)) {
  echo json_encode(["status" => "error", "message" => "OTP is required"]);
  exit;
}

if (!isset($_SESSION['otp'])) {
  echo json_encode(["status" => "error", "message" => "No OTP found. Please request a new one."]);
  exit;
}

if ($userOtp == $_SESSION['otp']) {
  $_SESSION['verified'] = true;
  echo json_encode(["status" => "success", "message" => "OTP verified successfully"]);
} else {
  echo json_encode(["status" => "error", "message" => "Invalid OTP"]);
}
?>
