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
echo "This script is written to show the basic way to brute force a wordpress login authentication. Use of this script for illegal activities is not allowed. Using this script for illegal activities is up to your own risk.\n";
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

function main(){
	$url = readline("Please insert URL:");
	echo "\n";
	
	$url = rtrim($url, "/") . "/";
	
	echo "[+] Checking URL: " . $url;
	if(checkURL($url) == "200"){
		echo " - SUCCESS\n";
		
		$canEnum = false;
		$wpjsonURL = rtrim($url, "/") . "/wp-json";
		echo "[+] Checking URL: " . $wpjsonURL;
		if(checkURL($wpjsonURL) == "200"){
			$canEnum = true;
			echo " - SUCCESS\n";
		}else{
			echo " - FAILED\n";
		}
		
		$canBruteforce = false;
		$xmlrpcURL = rtrim($url, "/") . "/xmlrpc.php";
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
