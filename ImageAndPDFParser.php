<?php 
require_once('RemoteObject.php');
require_once('RemoteRecipe.php');

class ImageAndPDFParser {
		private static $apiURL = "/api.php?";
		private static  $query = "action=query&prop=imageinfo&titles=Image:%TITRE%&iiprop=url&format=json";
		private $mn;
		public function __construct(){
			$this->mn = new Login;
		}
		public function getJson($object){
			$mQuery = urlencode($object->getQuery());
			$url= ImageAndPDFParser::$apiURL.ImageAndPDFParser::$query;
			return $this->mn->callApi($url);
		}
		public function getURL($name){
			$jsonString = $this->getObjectAsJson($name);
			return $this->retrieveUrlFromObject(json_decode($jsonString, true));
		}
		private function retrieveUrlFromObject($object){
			$ret = 'http://www.404notfound.fr/assets/images/pages/img/kaspcreationsdotnl.jpg';
			$result =  $object["query"]["pages"];
			if(!array_key_exists("-1", $result)){
				$dump = array_pop($result);
				$ret = $dump['imageinfo'][0]['url'];
			}
			return $ret;
		}
		private function getObjectAsJson($name){
			$url=ImageAndPDFParser::$apiURL.ImageAndPDFParser::$query;	
			$url = str_replace('%TITRE%', $name, $url);		
			return $this->mn->callApi($url);
		}
	}
?>