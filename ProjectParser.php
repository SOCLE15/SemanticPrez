<?php 
require_once('RemoteObject.php');
require_once('RemoteRecipe.php');
require_once 'BT.php';

class ProjectParser {
		private $apiURL = "http://smw.learning-socle.org/api.php?";
		private $actionASK = "ask";
		private $queryPrefix = "&query=";
		private $actionPrefix = "&action=";
		private $formatPrefix = "&format=";
		private $formatJSON = "json";
		private $jsonService;

		function __construct(){
		}
		protected function jsonToObject($jsonString, $project){
			$results = json_decode($jsonString, true);
			if (count($results) > 0) {
				$results = $results["query"]["results"];
				if(array_key_exists($project->getTitle(), $results)){
					$project->setFound(true);
					$jsonProject = $results[$project->getTitle()]["printouts"];
					$this->extractNumberOfSteps($project, $results);
					$this->extractSteps($project, $results);
					$this->extractSujet($project, $jsonProject);
					$this->extractResume($project, $jsonProject);
					$this->extractDescription($project, $jsonProject);
					$this->extractMembers($project, $jsonProject);
					$this->extractIngredients($project, $jsonProject);
					$this->extractDefinitions($project, $jsonProject);
					$this->extractNonFunReqs($project, $jsonProject);
					$this->extractFuncReqs($project, $jsonProject);
					foreach ($project->getFuncReqs() as $el) {
						$title = $el->getTitle();
						$this->extractTechReq($project, $results, $title);
					}
				}else{
					$project->setFound(false);
				}
				
			}
			return $project;
		}
		public function extractResume($project, $jsonProject){
			if(array_key_exists(0, $jsonProject['A résumé']) ){
				$project->setResume($jsonProject['A résumé'][0]);
			}
		}
		public function extractDescription($project, $jsonProject){
			if(array_key_exists(0, $jsonProject['A description']) ){
				$project->setDescription($jsonProject['A description'][0]);
			}
		}
		public function extractSujet($project, $jsonProject){
			if(array_key_exists(0, $jsonProject['A sujet'])&&array_key_exists("fulltext", $jsonProject['A sujet'][0]) ){
				$project->setSujet($jsonProject['A sujet'][0]["fulltext"]);
			}
		}		
		public function extractNumberOfSteps($project, $results){
			if(array_key_exists($project->getTitle().'#NombreEtapes', $results) && array_key_exists('Numero', $results[$project->getTitle().'#NombreEtapes']['printouts'])){
				$project->setNumberOfSteps($results[$project->getTitle().'#NombreEtapes']['printouts']['Numero'][0]);
			}else{
				$project->setNumberOfSteps(0);
			}
		}
		public function extractSteps($project, $results){
			$number = $project->getNumberOfSteps();
			if($number>0){
				for($i = 1; $i <= $number; $i++){
					$step = $results[$project->getTitle().'#Etape'.$i]['printouts']['Texte'][0];
					$project->addStep($step, $i);
				}
			}
		}
		public function extractTechReq($project, $results, $funcReqName){
			foreach($results[$project->getTitle()."#".$funcReqName]["printouts"]["Contenu"] as $techReqArray){
				$techReq = new BT($techReqArray,$project->getPath());
				$techReq->parse($results, $project);
				$project->addTechToFunc($techReq, $funcReqName);
			}
		}

		public function extractMembers($project, $jsonProject){
			foreach ($jsonProject["A membre"] as $el) {
				$member = new RemoteObject($el);
				$project->addMember($member);
			}
		}
		public function extractNonFunReqs($project, $jsonProject){
			foreach ($jsonProject["Besoin non fonctionnel lié"] as $el) {
				$nFuncReq = new RemoteObject($el);
				//var_dump($el);
				$project->addNonFuncReq($nFuncReq);
			}
		}
		public function extractIngredients($project, $jsonProject){
			foreach ($jsonProject["Ingrédient lié"] as $el) {
				$ingredient = new RemoteObject($el);
				$project->addIngredient($ingredient);
			}
		}
		public function extractDefinitions($project, $jsonProject){
			foreach ($jsonProject["Définition liée"] as $el) {
				$definition = new RemoteObject($el);
				$project->addDefinition($definition);
			}
		}
		public function extractFuncReqs($project, $jsonProject){
			foreach($jsonProject["Besoin fonctionnel lié"] as $el){
				$project->addFuncReq($el);
			}
		}
		public function retrieveInfoForObject($object){
			$jsonString = $this->getObjectAsJson($object);
			$this->jsonToObject($jsonString, $object);
		}
		private function getObjectAsJson($object){
			$mQuery=urlencode($object->getQuery());
			$url=$this->apiURL.$this->actionPrefix.$this->actionASK.$this->queryPrefix.$mQuery.$this->formatPrefix.$this->formatJSON;
			return file_get_contents($url);
		}
		public function getJson($object){
			$mQuery=urlencode($object->getQuery());
			$url=$this->apiURL.$this->actionPrefix.$this->actionASK.$this->queryPrefix.$mQuery.$this->formatPrefix.$this->formatJSON;
			return file_get_contents($url);
		}
	}
?>