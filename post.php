<?php
	function http_post_json($url, $jsonStr)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json; charset=utf-8',
      'Content-Length: ' . strlen($jsonStr)
    )
  );
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  return $response;
}

$url = "http://120.55.112.185/getcode.php";
$jsonStr = json_encode(array("SessionId" => 123, "RoutineId" => 2, "VoiceFileName" => "test.wav"));
$returnContent = http_post_json($url, $jsonStr);
echo $returnContent;
?>
