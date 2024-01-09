#!/usr/bin/php -d open_basedir=/usr/syno/bin/ddns
<?php

if ($argc !== 5) {
  echo 'badparam';
  exit();
}

$account = (string)$argv[1];
$pwd = (string)$argv[2];
$hostname = (string)$argv[3];
$ip = (string)$argv[4];

// check the hostname contains '.'
if (strpos($hostname, '.') === false) {
  echo 'badparam';
  exit();
}

// only for IPv4 format
if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
  echo "badparam";
  exit();
}
// zone.ee API params
$zone_api = 'https://api.zone.eu/v2';
$service_type = '/dns';
$service_name = processDomain($hostname);
$resource_name = '/a';
$resource_identificator = get_resource_identificator($zone_api,$service_type,$service_name,$resource_name,$account,$pwd,$hostname);
setArecord($zone_api,$service_type,$service_name,$resource_name,$resource_identificator,$account,$pwd,$hostname,$ip);


function handle_response_code($code){
  switch ($code) {
    case '200':
      //Return to main code
      return true;
    case '201':
      //Return to main code
      return true;
    case '202':
      //Return to main code
      return true;
  
    case '401':
      //Bad Auth
        echo 'badauth';
        exit();

    case '404':
      //No named service or resource
      echo 'nohost';
      exit();

    case '409':
      //Request limit exceeded
      echo 'abuse';
      exit();
    case '400':
      //Request limit exceeded
      echo 'badagent';
      exit();
    case '422':
      //Request limit exceeded
      echo 'badagent';
      exit();
    
    default:
      // Code to be executed if none of the cases match $variable
      echo "badparam";
      exit();
  }
}

function processDomain($inputDomain) {
  // Split the domain into parts
  $domainParts = explode('.', $inputDomain);

  // Check if there are more than 1 part and not more than 4 parts
  if (count($domainParts) > 1 && count($domainParts) <= 4) {
      // If it is domain.tld then return it
      if (count($domainParts) == 2){
        return "/" . $inputDomain;
        
      }
      // Get the remaining parts (excluding the first one)
      $remainingParts = implode('.', array_slice($domainParts, 1));

      // Return the result
      return "/" . $remainingParts;
  } else {
      // In case of errors return badparam
      echo "badparam";
      exit();
  }
}



function get_resource_identificator($api,$service_type,$service_name,$resource_name,$account,$pwd,$hostname){
  // Function gets zone.ee resource id needed for updating it
  global $ip;
  // Combine the the info for request URL and get data
  $serviceurl = $api . $service_type . $service_name . $resource_name;
  $req = curl_init();
  curl_setopt($req, CURLOPT_URL, $serviceurl);
  curl_setopt($req, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($req, CURLOPT_USERPWD, "$account:$pwd");
  curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
  $res = curl_exec($req);
  $httpCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
  curl_close($req);

  //Handles any error codes
  handle_response_code($httpCode);
  $data = json_decode($res, true);

  //Loops thorugh every json entry for host and returs empty if not found or a match.
  //Should the ip be same return "No change" result.
  foreach ($data as $item) {
    if (isset($item['name']) && $item['name'] === $hostname) {
      if ($ip == $item['destination']){
        echo 'nochg';
        exit();
      }
      else{
        return '/' . $item['id'];
  }
}
}
}

function setArecord($api,$service_type,$service_name,$resource_name,$resource_identificator,$account,$pwd,$hostname,$ip){
  // Combine the the info for request URL
  $serviceurl = $api . $service_type . $service_name . $resource_name . $resource_identificator;
  //Build the json
  $data = array(
    'name' => $hostname,
    'destination' => $ip  
  );
  $jsonData = json_encode($data);
  //Send PUT to update record
  $req = curl_init();
  curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($req, CURLOPT_POSTFIELDS, $jsonData);
  curl_setopt($req, CURLOPT_URL, $serviceurl);
  curl_setopt($req, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($req, CURLOPT_USERPWD, "$account:$pwd");
  curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($req, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
  ));
  $res = curl_exec($req);
  $httpCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
  curl_close($req);
  if (handle_response_code($httpCode)){
    echo 'good';
  };
  
}
?>