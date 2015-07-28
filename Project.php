<?php
require_once('ProjectParser.php');
require_once('FunctionalRequirement.php');

/*$socle = new Project("SOCLE LAAS15");
$socle->retrieveData();
$socle->createGraph();*/

//TODO : create an internalisation class w. strings
//TODO : link members and recipes

/*
*Project class has ingredients, definitions, functional requirements and members
*In this class you will find the project model and few functions to render a project's graph
*You should also give a look at the ProjectParser class 
*/
class Project{
	private $objectsToQuery = "[[%PROJET%]] OR [[-Has subobject::%PROJET%]] OR [[Type::RecetteInBT]] [[Projet lié::%PROJET%]] OR [[Type::BTInBT]] [[Projet lié::%PROJET%]] ";
	private $parametersToQuery = "|?Conception|?A membre|?Contenu|?Besoin fonctionnel lié|?Type|?Ingrédient lié|?Définition liée|?Besoin technique lié|?Recette liée|?Besoin non fonctionnel lié|?A sujet|?A description|?A résumé|?Numero|?Text";
	private $title;
	private $definitions;
	private $ingredients;
	private $funcReqs;
	private $members;
	private $nonFuncReqs;
	private $description;
	private $sujet;
	private $resume;
	private $stepsConception;
	private $path;
	private $found;


	function __construct($projectName = '',$path = 'reveal'){
		$this->title = $projectName;
		$this->path = $path;
		$this->definitions = array();
		$this->members = array();
		$this->ingredients = array();
		$this->funcReqs = array();
		$this->nonFuncReqs = array();
		$this->stepsConception;

	}
	public function isFound(){
		return $this->found;
	}
	public function setFound($found){
		$this->found = $found;
	}
	public function getPath(){
		return $this->path;
	}
	public function setDescription($description){
		$this->description;
	}	
	public function setResume($resume){
		$this->resume = $resume;
	}
	public function setSujet($sujet){
		$this->sujet = $sujet;
	}
	public function getConception(){
		return $this->stepsConception;
	}
	public function setConception($conception){
		return $this->stepsConception = $conception;
	}
	public function getResume(){
		return $this->resume;
	}
	public function getSujet(){
		return $this->sujet;
	}
	public function getNumberOfSteps(){
		return $this->numberOfSteps;
	}
	public function getDescription(){
		return $this->description;
	}
	public function getDefinitions(){
		return $this->definitions;
	}
	public function getIngredients(){
		return $this->ingredients;
	}
	public function getMembers(){
		return $this->members;
	}

	public function addIngredient($ingredient){
		array_push($this->ingredients,$ingredient);
	}
	public function addDefinition($definition){
		array_push($this->definitions,$definition);
	}
	public function addMember($member){
		array_push($this->members,$member);
	}
	public function addNonFuncReq($nonFuncReq){
		array_push($this->nonFuncReqs,$nonFuncReq);
	}
	public function addFuncReq($funcReqTitle){
		$funcReq = new FunctionalRequirement($funcReqTitle);
		$this->funcReqs[$funcReqTitle] = $funcReq;
	}
	public function addTechToFunc($techReq, $funcReqTitle){
		$this->funcReqs[$funcReqTitle]->addTechReq($techReq);
	}

	public function getFuncReqs(){
		return $this->funcReqs;
	}
	public function getQuery(){
		$query = str_replace("%PROJET%", $this->title, $this->objectsToQuery);
		$query .= $this->parametersToQuery;
		return $query;
	}
	public function getFuncReqByTitle($title){
		$ret = null;
		foreach($this->funcReqs as $funcReq){
			if(strcmp($funcReq->getTitle(), $title)){
				$ret = $funcReq;
				break;
			}
		}
		return $ret;
	}
	public function getTitle(){
		return $this->title;
	}
	/*
	*Creates a project type parser and ask for data from the mediawiki api
	*The query used exists is in this class but you should NOT change it as everything is tight linked
	*@args 
	*@return 
	*/
	public function retrieveData(){
		$mParser = new ProjectParser;
		$mParser->retrieveInfoForObject($this);
	}
	/*
	*Add and link recipe with the corresponding technical requirement in a functional requirement (the one where exists the func. requirement)
	*The functional requirement MUST already exists in the functional requirement 
	*You only need to specify the title of the technical requirement
	*@args $recipe to add (RemoteObject type) $title of the Technical requirement to link with
	*@return 
	*/
	public function addRecipeToBF($recipe, $techReqTitle){
		foreach ($this->funcReqs as $funcReq) { 
			if($funcReq->hasTechReqByTitle($techReqTitle)){
				$funcReq->linkRecipeWithTechReqTitle($recipe, $techReqTitle);
				break;
			}
		}
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