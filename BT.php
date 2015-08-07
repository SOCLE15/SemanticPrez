<?php 
class BT extends RemoteObject{
	private $BTs;
	private $recipe;
	private $done;
	private $depth = 1;
	private $path;

	function __construct($dataArray,$path = 'reveal'){
		parent::__construct($dataArray);
		$this->path = $path;
		$this->BTs = array();
		$this->done =false;
	}
	public function test(){
		return $this->depth;
	}
	public function parse($results, $project){
		if(array_key_exists($sufix = $this->getTitle()."#".$project->getTitle(), $results)){
			//echo 'toto';
			$subobject = $results[$sufix]['printouts'];
			switch($subobject['Type'][0]){
				case 'RecetteInBT':
					$this->done = true;
					$this->extractRecipe($subobject['Recette liée'][0]);
					break;
				case 'BTInBT':
					$this->done = false;
					$this->extractTechReqs($results, $subobject['Besoin technique lié'], $project);
					break;
				default:
					break;
			}

		}
		return $this->depth;
	}
	public function extractRecipe($array){
		
		$this->recipe = new RemoteObject($array);
	}
	public function extractTechReqs($results, $array, $project){
		$depth = 0;
		foreach ($array as $techReqArray) {
			$bt = new BT($techReqArray);
			$depth = max($bt->parse($results, $project), $depth);
			$this->BTs[$bt->getTitle()] = $bt;
		}
		$this->depth = $depth+1;
	}
	public function htmlMe(){
		$string ='<section id="'.Zipper::pseudoHash($this->getTitle()).'">'."\n";
		$string .= '%H%<a href="'.$this->getUrl().'" target="_blank">'.$this->getTitle()."</a><br>%/H%";
		switch ($this->done) {
			case true:
				$string = str_replace('%H%', '', $string);
				$string = str_replace('%/H%', '', $string);
				$html = $this->htmlRecipe($string);
				break;
			default:
				$string = str_replace('%H%', '<h2>', $string);
				$string = str_replace('%/H%', '</h2>', $string);
				$html = $this->htmlBTs($string);
				break;
		}
		$html .= '</section>'."\n";
		return $html;
	}
	public function htmlRecipe($string){
        $recipe = new Recipe($this->recipe->getTitle(), $this->recipe->getUrl(),$this->path);
        $recipe->retrieveData();   
        $string .= $recipe->returnHTML(4);
        return $string;
	}
	public function htmlBTs($string){
		$string .= '<h3>Se décompose en :</h3>';
		$string .= '<ul>'."\n";
		foreach ($this->BTs as $BT) {
			$string.= '<li>'.'<a href="#/'.Zipper::pseudoHash($BT->getTitle()).'">'.$BT->getTitle()."</a>".'</li>'."\n";
		}
		$string .='</ul>'."\n";
		$string .= '</section>'."\n";
		foreach ($this->BTs as $BT) {
			$string .= $BT->htmlMe();
		}
		$string .= '<section>';
		return $string;
	}
}