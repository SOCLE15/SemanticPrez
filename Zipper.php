<?php
require_once('Project.php');
require_once('Recipe.php');
if(isset($_GET['project']) && isset($_GET['file'])){
    $project = new Project($_GET['project']);
    $zip = new Zipper($project);
    if($zip->createPrez($_GET['file'])==false){
        echo 'BUG<br>';
    }
    $zip->modify($_GET['file']);
} 
elseif(isset($_GET['test'])){
    $project = new Project('SOCLE LAAS15');
    $zip = new Zipper($project);
    $zip->test();
}
elseif(isset($_GET['clean'])){
    $zip = new Zipper();
    $zip->clean();
} 
else{
    echo 'Params get \'?project\' et \'?file\' requis';
}

//$zip->modify('test');
//readfile('reveal/test.html');
class Zipper{
    private $title;
    private $definitions;
    private $ingredients;
    private $funcReqs;
    private $members;
    private $resume;
    private $sujet;
    private $description;
    private $steps;
    private $decalage = "";
    function __construct($project = null){
        if($project != null){
            $project->retrieveData();
            $this->title = $this->escapePercent($project->getTitle());
            $this->definitions = $this->escapePercent($project->getDefinitions());
            $this->ingredients = $this->escapePercent($project->getIngredients());
            $this->funcReqs = $this->escapePercent($project->getFuncReqs());
            $this->members = $this->escapePercent($project->getMembers());
            $this->description = $this->escapePercent($project->getDescription());
            $this->sujet = $this->escapePercent($project->getSujet());
            $this->resume = $this->escapePercent($project->getResume());
            $this->steps = $this->escapePercentInArray($project->getConception());
            for($i=0;$i<4;$i++){
                $this->decalage .= "\t";
            }
        }

        }
    public function createPrez($filename){
        return copy ( 'reveal/index.html' , 'reveal/'.$filename.'.html');
    }
    public function firstPage($content){
        $content = str_replace('%TITRE%', $this->title, $content);
        $content = str_replace('%SUJET%', $this->sujet, $content);
        $content = str_replace('%RESUME%', $this->cleanWikiCode($this->resume), $content);
        $content = $this->eachMember($content);
        return $content;
    }
    public function conception($content){
        $string = '';
        foreach ($this->steps as $value) {
            $string .= Utils::conceptionHtmlCreator($this->cleanWikiCode($value), $this->decalage);
        }

        return str_replace('%CONCEPTIONSECTION%', $string, $content);
    }
    public function eachMember($content){
        $string = 'Created by ';
        foreach ($this->members as $member) {
            $name = str_replace('Utilisateur:', '', $member->getTitle());
            $string .= $name.', ';
        }
        $string = rtrim($string, ", ");
        return str_replace('%MEMBERS%', $string, $content);
    }
    public function modify($filename){
        $file = 'reveal/'.$filename.'.html';
        $content = file_get_contents($file);
        $content = $this->firstPage($content);
        $content = $this->conception($content);
        $content = $this->funcReqs($content);
        file_put_contents($file, $content);
        echo 'DONE';
    }
    public function clean(){
        shell_exec('ls reveal| grep  html |grep -v index | sed \'s/.*/reveal\/&/\' |xargs rm');
    }
    public function cleanWikiCode($notClean){
        $cleaner =  $this->wcToBalise($notClean,  '\'\'\'','b');
        return $this->wcToBalise($cleaner,  '\'\'[^\']','i');
    }
    public function wcToBalise($text, $wc, $balise){
        $oldText = $text;
        while(strcmp($oldText, $text = preg_replace('/'.$wc.'/', '<'.$balise.'>', $text, 1)) != 0){
            $text = preg_replace('/'.$wc.'/', '</'.$balise.'>', $text, 1);
            $oldText = $text;
        }
        return $text;
    }
    public function escapePercent($content){
        return str_replace('%', '.%.', $content);
    }
    public function unEscapePercent($content){
        return str_replace('.%.', '%', $content);
    }
    public function escapePercentInArray($array){
        foreach ($array as $key => $value) {
            $array[$key] = $this->escapePercent($value);
        }
        return $array;
    }
    public function test(){
        echo $this->title;
        echo '<br><br>';
        print_r($this->definitions);
    }
    public function funcReqs($content){
        $string = '';
        foreach($this->funcReqs as $funcReq){
            $techReqs = $funcReq->getTechReqs();
            $string .= $this->decalage.'<section>'."\n";
            $string .= $this->decalage."\t".'<h2>Besoin fonctionnel : <br>'.$funcReq->getTitle().'</h2>'."\n";
            $string .= $this->decalage."\t".'Se décompose en : <br>'."\n";
            $string .= $this->decalage."\t".'<ul>'."\n";
            foreach ($techReqs as $key => $value) {
                $string .= $this->decalage."\t"."\t".'<li>'.'<a href="'.$value->getUrl().'" target="_blank">'.$key."</a>".'</li>'."\n";
            }
            $string .= $this->decalage."\t".'</ul>'."\n";
            $string .=  $this->decalage.'</section>'."\n";
            foreach($techReqs as $techReq){
                $string .= $techReq->htmlMe();
            }
        }
        $content = str_replace('%BESFUNCSECTION%', $string, $content);
        return $content;
    }
}