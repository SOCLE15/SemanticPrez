<?php 
require_once('RemoteObject.php');
require_once('RemoteRecipe.php');

class ImageAndPDFParser {
		private static $apiURL = "http://smw.learning-socle.org/api.php?";
		private static $query = "action=query&prop=imageinfo&titles=Image:%TITRE%&iiprop=url&format=json";

		public static function getJson($object){
			$mQuery = urlencode($object->getQuery());
			$url= ImageAndPDFParser::$apiURL.ImageAndPDFParser::$query;
			return file_get_contents($url);
		}
		public static function getURL($name){
			$jsonString = ImageAndPDFParser::getObjectAsJson($name);
			return ImageAndPDFParser::retrieveUrlFromObject(json_decode($jsonString, true));
		}
		private static function retrieveUrlFromObject($object){
			$ret = 'http://www.404notfound.fr/assets/images/pages/img/kaspcreationsdotnl.jpg';
			$result =  $object["query"]["pages"];
			if(!array_key_exists("-1", $result)){
				$dump = array_pop($result);
				$ret = $dump['imageinfo'][0]['url'];
			}
			return $ret;
		}
		private static function getObjectAsJson($name){
			$url=ImageAndPDFParser::$apiURL.ImageAndPDFParser::$query;	
			$url = str_replace('%TITRE%', $name, $url);		
			return file_get_contents($url);
		}
	}
?>