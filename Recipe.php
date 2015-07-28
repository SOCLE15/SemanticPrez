<?php
require_once('RecipeParser.php');

/*$socle = new Recipe("Test");
$socle->retrieveData();
$socle->createGraph();
*/
//TODO : create an internalisation class w. strings
//TODO : link members and recipes

/*
*Recipe class has ingredients, definitions, technicals  requirements and members
*In this class you will find the project model and few functions to render a project's graph
*You should also give a look at the ProjectParser class 
*/
class Recipe{
	private $objectsToQuery = "[[%RECETTE%]] OR [[-Has subobject::%RECETTE%]] OR [[-Découle du besoin technique::%RECETTE%]] ";
	private $parametersToQuery = "|?Texte|?PlantUML|?PDF|?Numero|?Image|?Service|?Video|?Contenu|?BesoinTechnique|?Code|?Langage|?Description
		|?A membre|?Découle du besoin technique|?Besoin technique lié|?A thème|?Ingrédient lié|?Définition liée|?Projet lié|?Titre";
	private $title;
	private $description;
	private $numberSteps;
	private $steps;
	private $theme;
	private $father;
	private $definitions;
	private $ingredients;
	private $members;
	private $techReqs;
	private $projects;
	private $url;
	private $path;
	function __construct($recipeName = '',$url = '',$path = 'reveal'){
		$this->path = $path;
		$this->title = $recipeName;
		$this->url = $url;
		$this->definitions = array();
		$this->members = array();
		$this->ingredients = array();
		$this->techReqs = array();
		$this->projects = array();
		$this->steps = array();
	}
	public function getPath(){
		return $this->path;
	}
	public function getSteps(){
		return $this->steps;
	}	
	public function getFather(){
		return $this->father;
	}
	public function getMembers(){
		return $this->members;
	}	
	public function getIngredients(){
		return $this->ingredients;
	}	
	public function getDefinitions(){
		return $this->definitions;
	}
	public function addStep($step, $number){
		$this->steps[$number] = $step;
	}
	public function setNumberSteps($number){
		$this->numberSteps = $number;
	}
	public function getNumberSteps(){
		return $this->numberSteps;
	}
	public function setTheme($theme){
		$this->theme = $theme;
	}
	public function setFather($father){
		$this->father = $father;
	}
	public function addIngredient($ingredient){
		array_push($this->ingredients,$ingredient);
	}
	public function addDefinition($definition){
		array_push($this->definitions,$definition);
	}
	public function setDescription($description){
		$this->description = $description;
	}
	public function addMember($member){
		array_push($this->members,$member);
	}
	public function addTechReq($techReq){
		array_push($this->techReqs,$techReq);
	}
	public function addProject($project){
		array_push($this->projects,$project);
	}
	public function getQuery(){
		$query = str_replace("%RECETTE%", $this->title, $this->objectsToQuery);
		$query .= $this->parametersToQuery;
		return $query;
	}
	public function getTitle(){
		return $this->title;
	}
	/*
	*Creates a recipe type parser and ask for data from the mediawiki api
	*The query used exists is in this class but you should NOT change it as everything is tight linked
	*@args 
	*@return 
	*/
	public function getDescription(){
		return $this->description;
	}
	public function retrieveData(){
		$mParser = new RecipeParser;
		$mParser->retrieveInfoForObject($this);
	}
	public function returnHtml($decalage){
		$i = 0;
		$n = "\n";
		$t = "\t";
		$dec = "";
		$ret ="";
		for($j=0;$j<$decalage;$j++){
			$dec .= $t;
		}
		//$ret = $dec.'<section>'.$n;
		$ret .= $dec.$t.' - '.'<a href="'.$this->url.'" target="_blank">'.$this->title."</a>".$n;
		$ret .=  $dec.$t.'<section>'. $n.$dec.$t.$t.'<i>'.$this->description.'</i><br><br>'.$n;
		$ret .=  $dec.$t.'</section>'.$n;
		foreach ($this->steps as $key => $value) {
			$i++;
			$ret .= $dec.$t.'<section>'.$n;
			$ret .= $value->getHTML($decalage+2);
			$ret .= $dec.$t.'</section>'.$n;
		}
		//$ret .= $dec.'</section>'.$n;
		return $ret;
	}

	/*	
	public function setDefinitions($resultsArray){
		$this->definitions = $resultsArray["Définition liée"];
	}
	public function setIngredients($resultsArray){
		$this->ingredients = $resultsArray["Ingrédient lié"];
	}
	public function setFuncReqs($funcReqs){
		$this->funcReqs = $funcReqs;
	}
	public function setMembers($members){
		$this->members = $members;
	}*/
}
?>