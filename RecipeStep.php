<?php
require_once 'ImageAndPDFParser.php';
require_once 'Utils.php';
class RecipeStep{
	private $html;
	private $type;

	public function __construct($stepArray){
		$this->html = "%t".'<h4>'.$stepArray['Titre'][0].'</h4>'."%n";
		$this->html .= Utils::resumeHtmlCreator($stepArray['Description'][0]);
		switch ($stepArray['Contenu'][0]['fulltext']) {
			case 'Document PDF':
				$this->pdfConstructor($stepArray);
				$this->setType('pdf');
				break;
			case 'Texte':
				$this->textCreator($stepArray);
				$this->setType('texte');
				break;
			case 'Video':
				$this->videoConstructor($stepArray);
				$this->setType('video');
				break;
			case 'Image':
				$this->imageConstructor($stepArray);
				$this->setType('image');
				break;			
			case 'Code':
				$this->codeConstructor($stepArray);
				$this->setType('code');
				break;			
			case 'Besoin(s) technique(s)':
				$this->bTConstructor($stepArray);
				$this->setType('bt');
				break;
			case 'PlantUML':
				$this->plantUMLConstructor($stepArray);
				$this->setType('plantuml');
			default:
				break;
		}
	}
	public function textCreator($stepArray){
		if(array_key_exists(0, $stepArray['Texte'])){
			$this->html .= Utils::textHtmlCreator($stepArray['Texte'][0]);
		}
	}
	public function setType($type){
		$this->type = $type;
	}
	public function getType(){
		return $this->type;
	}
	public function getHtml($decalage){
		$n = "\n";
		$t = "\t";
		$dec = "";
		for($j=0;$j<$decalage;$j++){
			$dec .= $t;
		}
		$html = $this->html;
		$html = str_replace("%t", $dec, $html);
		$html = str_replace("%n", $n, $html);
		return $html;
	}
	public function videoConstructor($stepArray){
		switch ($stepArray['Service'][0]) {
			case 'youtube':
				$this->youtubeConstructor($stepArray);
				break;
			case 'dailymotion':
				$this->dailymotionConstructor($stepArray);
				break;
			case 'ted':
				$this->tedConstructor($stepArray);
				break;
			case 'teachertube':
				$this->teacherTubeConstructor($stepArray);
				break;
			case 'asciinema':
				$this->asciinemaConstructor($stepArray);
				break;
			case 'vimeo':
				$this->vimeoConstructor($stepArray);
				break;
			case 'slideshare':
				$this->slideshareConstructor($stepArray);
				break;
			default:
				break;
		}
	}
	public function plantUMLConstructor($stepArray){
		if(array_key_exists(0, $stepArray['PlantUML'])){
			$umlText = $stepArray['PlantUML'][0];
			$imageID = Utils::encodep($umlText);
			$this->html .= "%t".'<a class="image-popup" href="http://www.plantuml.com/plantuml/img/'.$imageID.'"><img class="image-recipe" data-src="http://www.plantuml.com/plantuml/img/'.$imageID.'"></a>'."%n";
		}
	}
	public function imageConstructor($stepArray){
		if(array_key_exists(0, $stepArray['Image'])){
			$imageSrc = $this->retrieveFileWithName($stepArray['Image'][0]);
			$this->html .= "%t".'<a class="image-popup" href="'.$imageSrc.'"><img class="image-recipe" data-src="'.$imageSrc.'"></a>'."%n";
		}
	}
	public function pdfConstructor($stepArray){
		if(array_key_exists(0, $stepArray['PDF'])){
			$pdfLink = $this->retrieveFileWithName($stepArray['PDF'][0]);
			//if(Utils::createPreviewOfPDF($pdfLink)){
			Utils::createPreviewOfPDF($pdfLink);
				$this->html .= "%t".'<a class="iframe-popup" href="'.$pdfLink.'"><img class="image-recipe" alt="OPEN PDF" src="images/'.md5($pdfLink).'.png"/></a>'."%n";
			//}
		}
	}
	public function codeConstructor($stepArray){
		$code = $stepArray['Code'][0];
		$this->html .= Utils::codeHtmlCreator($code);
	}
	public function bTConstructor($stepArray){
		$this->html .= "%t".'<p>Nécessite les besoins techniques : '."%n";
		foreach ($stepArray["BesoinTechnique"] as $BTArray) {
			$BTObject = new RemoteObject($BTArray);
			$this->html .= "%t"."\t".'<a href="'.$BTObject->getUrl().'">'.$BTObject->getTitle().'</a>'."%n";
		}
		$this->html =  rtrim($this->html, ", ");
		$this->html .= "%t".'</p>'."%n";
		
	}
	//TODO : vimeo teachertube ted slideshare
	public function retrieveFileWithName($name){
		return ImageAndPDFParser::getURL($name);
	}
	public function youtubeConstructor($stepArray){
		$this->html .= "%t".'<a class="video" href="http://www.youtube.com/watch?v='.$stepArray['Video'][0].'"><img src="http://img.youtube.com/vi/'.$stepArray['Video'][0].'/0.jpg" alt="" class="thumbnail"/></a>';
	}
	public function dailymotionConstructor($stepArray){
		$this->html .= "%t".'<a class="dailymotion-video" href="http://www.dailymotion.com/video/'.$stepArray['Video'][0].'"><img src="http://www.dailymotion.com/thumbnail/video/'.$stepArray['Video'][0].'" class="thumbnail"/></a>';
	}
 	public function vimeoConstructor($stepArray){
		$this->html .= 	"%t".'<iframe src="https://player.vimeo.com/video/'.$stepArray['Video'][0].'" width="500" height="315" frameborder="0" allowfullscreen="true"></iframe>'."%n";
	}
	public function teacherTubeConstructor($stepArray){
		$this->html .= 	"%t".'<iframe src="http://www.teachertube.com/embed/video/'.$stepArray['Video'][0].'" width="500" height="315" frameborder="0" allowfullscreen="true"></iframe>'."%n";
	}
	public function tedConstructor($stepArray){
		$this->html .= 	"%t".'<iframe src="//embed-ssl.ted.com/talks/'.$stepArray['Video'][0].'.html" width="500" height="315" frameborder="0" allowfullscreen="true"></iframe>'."%n";
	}	
	public function slideshareConstructor($stepArray){
		$this->html .= 	"%t".'<iframe src="//fr.slideshare.net/slideshow/embed_code/key/'.$stepArray['Video'][0].'" width="500" height="315" frameborder="0" allowfullscreen="true"></iframe>'."%n";
	}
	public function asciinemaConstructor($stepArray){
		$this->html .= "%t".'<a class="asciinema-video" href="https://asciinema.org/a/'.$stepArray['Video'][0].'"><img class="thumbnail" src="https://asciinema.org/a/'.$stepArray['Video'][0].'.png"/></a>';

	}
}