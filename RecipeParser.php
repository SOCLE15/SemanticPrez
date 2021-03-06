<?php
require_once('RemoteObject.php');
require_once('RecipeStep.php');
require_once('Login.php');

class RecipeParser{
		private $apiURL = "/api.php?";
		private $actionASK = "ask";
		private $queryPrefix = "&query=";
		private $actionPrefix = "&action=";
		private $formatPrefix = "&format=";
		private $formatJSON = "json";
		private $mn;
		function __construct(){
			$this->mn = new Login();
		}
		protected function jsonToObject($jsonString, $recipe){
			//echo '<br><br>'.$jsonString.'<br><br>';
			$results = json_decode($jsonString, true);
			if (count($results) > 0) {
				$results = $results["query"]["results"];
				$jsonRecipe = $results[$recipe->getTitle()]["printouts"];
				$this->extractMembers($recipe, $jsonRecipe);
				$this->extractIngredients($recipe, $jsonRecipe);
				$this->extractDefinitions($recipe, $jsonRecipe);
				$this->extractProjects($recipe, $jsonRecipe);
				$this->extractNumberOfSteps($recipe, $results);
				$this->extractSteps($recipe, $results);
				$this->extractTheme($recipe, $jsonRecipe);
			}
			return $recipe;
		}
		public function extractSteps($recipe, $results){
			$number = $recipe->getNumberSteps();
			if($number>0){
				for($i = 1; $i <= $number; $i++){
					$step = new RecipeStep($results[$recipe->getTitle().'#Etape'.$i]['printouts'],$recipe->getPath());
					$recipe->addStep($step, $i);
				}
			}
		}
		public function extractNumberOfSteps($recipe, $results){
			if(array_key_exists($recipe->getTitle().'#NombreEtapes', $results) && array_key_exists('Numero', $results[$recipe->getTitle().'#NombreEtapes']['printouts'])){
				$recipe->setNumberSteps($results[$recipe->getTitle().'#NombreEtapes']['printouts']['Numero'][0]);

			}else{
				$recipe->setNumberSteps(0);
			}
		}
		public function extractTheme($recipe, $jsonRecipe){
			$theme = $jsonRecipe["A thème"][0];
			$recipe->setTheme($theme);
		}

		public function extractMembers($recipe, $jsonRecipe){
			foreach ($jsonRecipe["A membre"] as $el) {
					$member = new RemoteObject($el);
					$recipe->addMember($member);
			}
		}
		public function extractIngredients($recipe, $jsonRecipe){
			foreach ($jsonRecipe["Ingrédient lié"] as $el) {
				$ingredient = new RemoteObject($el);
				$recipe->addIngredient($ingredient);
			}
		}
		public function extractDefinitions($recipe, $jsonRecipe){
			foreach ($jsonRecipe["Définition liée"] as $el) {
				$definition = new RemoteObject($el);
				$recipe->addDefinition($definition);
			}
		}
		public function extractProjects($recipe, $jsonRecipe){
			foreach($jsonRecipe["Projet lié"] as $el){
				$project = new RemoteObject($el);
				$recipe->addProject($project);
			}
		}
		public function retrieveInfoForObject($object){
			$jsonString = $this->getObjectAsJson($object);
			$this->jsonToObject($jsonString, $object);
		}
		private function getObjectAsJson($object){
			$mQuery=urlencode($object->getQuery());
			$url=$this->apiURL.$this->actionPrefix.$this->actionASK.$this->queryPrefix.$mQuery.$this->formatPrefix.$this->formatJSON;
			return $this->mn->callApi($url);
		}
		public function getData(){
			return json_encode($this);
		}
	}
?>