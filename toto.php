<?php
$zip = new ZipArchive();
$filename = 'Toto.zip';
$path = '/var/www/html/SemanticProjectGraph/SemanticPresentation/reveal/';
$filepath = '/var/www/html/SemanticProjectGraph/SemanticPresentation/';
	

if(file_exists($filename)){
	/*$size = filesize($filename);
	header("Content-Type: application/force-download");
	header("Content-Transfer-Encoding: binary");
	header("Content-length: ".$size);
	header('Content-Disposition: attachment; filename="'.$filename.'";');
	header('Expires: 0');
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache"); 
	readfile($filename);*/
	sendZip($filepath.$filename);
}

function sendZip($file){
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
	
function Zip($source, $destination, $include_dir = false)
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