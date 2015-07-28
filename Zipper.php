<?php
require_once('Project.php');
require_once('Recipe.php');
if(isset($_GET['action'])  && isset($_GET['project']) && strcmp($_GET['project'], "index") != 0){
    $name = $_GET['project'];

    if(strcmp($_GET['action'], 'show') == 0){
        $project = new Project($name);
        $zip = new Zipper($project);
        if($project->isFound()){
             if($zip->createPrezInReveal($name)==false){
                echo 'BUG<br>';
            }
            $zip->create('reveal/',$name);
            $zip->showPrez($name);
        }
    }elseif(strcmp($_GET['action'], 'zip')==0){
        $project = new Project($name, Zipper::dirName($name));
        $zip = new Zipper($project);
        if($project->isFound()){
            $zip->initNewFolder($name);
            $zip->create(Zipper::dirName($name).'/', 'index');
            $zip->Zip(Zipper::dirName($name),$name.'.zip', true);
            $zip->sendZip($name.'.zip');
        }
    }
}
elseif(isset($_GET['project'])){
    echo 'action?';
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
    echo 'Params get \'?project\' requis';
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
    private $found;
    function __construct($project = null){
        if($project != null){
            $project->retrieveData();
            if($project->isFound()){
                $this->found =true;
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

        }
    public function showPrez($title){
        header('Location: reveal/'.$title.'.html');
    }
    public function createPrezInReveal($filename){
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
    public function create($filepath, $filename){
        $file = $filepath.$filename.'.html';
        $content = file_get_contents($file);
        $content = $this->firstPage($content);
        $content = $this->conception($content);
        $content = $this->funcReqs($content);
        file_put_contents($file, $content);
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
        header('Location: reveal/test.html');
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
    public function initNewFolder($title){
        $source = "reveal";
        $dest= $this->dirName($title);
        if(!is_dir($dest)){
            mkdir($dest, 0755);
            foreach (
               $iterator = new \RecursiveIteratorIterator(
                  new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                  \RecursiveIteratorIterator::SELF_FIRST) as $item
               ) {
                if ($item->isDir()) {
                    mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                } else {
                    copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                }

            }
        }
    }
    public function sendZip($file){
        if (headers_sent()) {
            echo 'HTTP header already sent';
        } else {
            if (!is_file($file)) {
                header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
                echo 'File not found';
            } else if (!is_readable($file)) {
                header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
                echo 'File not readable';
            } else {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: ".filesize($file));
                header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
                readfile($file);
                exit;
            }
        }
    }
    
    public function Zip($source, $destination, $include_dir = false)
    {

        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        if (file_exists($destination)) {
            unlink ($destination);
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true)
        {

            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            if ($include_dir) {

                $arr = explode("/",$source);
                $maindir = $arr[count($arr)- 1];

                $source = "";
                for ($i=0; $i < count($arr) - 1; $i++) { 
                    $source .= '/' . $arr[$i];
                }

                $source = substr($source, 1);

                $zip->addEmptyDir($maindir);

            }

            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true)
                {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                }
                else if (is_file($file) === true)
                {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        }
        else if (is_file($source) === true)
        {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
    public static function dirName($name){
        return str_replace(" ", "_", $name);
    }

}