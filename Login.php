<?php
	// if (empty($argv[1])) {
	//  print("Specify the part of the URL after 'https://my.private.wiki/wiki/index.php/' as argument.\n");
	//  exit;
	// }
include "Snoopy.class.php";
class Login{
	private $snoopy;
	private $login_vars;
	function __construct(){
		$this->snoopy = new Snoopy();
		$this->snoopy->curl_path="/usr/bin/curl";
		$wikiroot = "http://smw.learning-socle.org";
		$api_url = $wikiroot . "/api.php";

		# Login via api.php
		$this->login_vars['action'] = "login";
		$this->login_vars['lgname'] = "ApiUser";
		$this->login_vars['lgpassword'] = "pls15";
		$this->login_vars['format'] = "php";
		## First part
		$this->snoopy->submit($api_url,$this->login_vars);
		$response = unserialize(trim($this->snoopy->results));
		$this->login_vars['lgtoken'] = $response["login"]["token"];
		$this->snoopy->cookies = $this->getCookieHeaders($this->snoopy->headers);		## Second part, now that we have the token
		$this->snoopy->submit($api_url,$this->login_vars);		
	}
	function callApi($url){
		//$this->login_vars['action'] = "ask";
		$this->snoopy->submit("http://smw.learning-socle.org".$url);
		return $this->snoopy->results;
		//print($this->snoopy->results);
	}
	function writeStuf($page, $property, $value, $summary){
		$this->snoopy->submit("http://smw.learning-socle.org/api.php?&action=query&meta=tokens&format=json");
		$arrayToken = json_decode($this->snoopy->results, true);
		$token = urlencode($arrayToken["query"]["tokens"]["csrftoken"]);
		//$args = array("action" => "smwwrite", "token" => $token, "title" => $page, "add" =>"[[".$property."::".$value."]]", "summary" =>$summary, "format"=>"json");
		//$this->snoopy->_httpmethod = "POST";
		$args = array("action" => "smwwritable", "title" => $page);

		$this->snoopy->submit("http://smw.learning-socle.org/api.php", $args);
		print($this->snoopy->results);
	}
	function getCookieHeaders($headers){
        $cookies = array();
        foreach($headers as $header)
                if(preg_match("/Set-Cookie: ([^=]*)=([^;]*)/", $header, $matches))
                        $cookies[$matches[1]] = $matches[2];
        return $cookies;
	} 
}