<?php
// otp.php
session_start();
header("Content-Type: application/json");

// === CONFIG ===
$username = "Nuvoraa";
$apikey   = "BJvDBuadWZta";
$senderid = "MOBTIN";
$route    = "OTP";
$TID      = "1707176156392272496"; // Template ID (must match DLT)
$PEID     = "1701159099727478056"; // Principal Entity ID

// === INPUT ===
$mobile = $_POST['mobile'] ?? '';
if (empty($mobile)) {
  echo json_encode(["status" => "error", "message" => "Mobile number is required"]);
  exit;
}

// ✅ Ensure mobile number has '91' prefix (MDS requires full Indian format)
if (strlen($mobile) === 10) {
  $mobile = "91" . $mobile;
}

// === GENERATE OTP ===
$otp = rand(100000, 999999);

// === MESSAGE ===
// Must match DLT template *exactly*, including spaces and punctuation
$message = "Dear Customer, $otp is your OTP for Login and registration. OTPs are SECRET, Do not disclose it to anyone MOBTIN";

// === BUILD API URL ===
$url = "https://mdssend.in/api.php?username=" . urlencode($username)
     . "&apikey=" . urlencode($apikey)
     . "&senderid=" . urlencode($senderid)
     . "&route=" . urlencode($route)
     . "&mobile=" . urlencode($mobile)
     . "&text=" . urlencode($message)
     . "&TID=" . urlencode($TID)
     . "&PEID=" . urlencode($PEID);

// === SEND REQUEST VIA CURL ===
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// === DEBUG / RESPONSE VALIDATION ===
if ($error) {
  echo json_encode(["status" => "error", "message" => "Failed to connect to MDS API: $error"]);
  exit;
}

// Check if MDS returned failure
if (stripos($response, 'error') !== false || empty($response)) {
  echo json_encode(["status" => "error", "message" => "SMS gateway rejected the request", "debug" => $response]);
  exit;
}

// === SAVE OTP TO SESSION ===
$_SESSION['otp'] = $otp;
$_SESSION['mobile'] = $mobile;

// === RETURN SUCCESS ===
echo json_encode([
  "status" => "success",
  "message" => "OTP sent successfully to $mobile",
  "debug" => $response
]);
?>
