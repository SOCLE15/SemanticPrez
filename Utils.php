<?php
class Utils{
	private static function isTooLong($text, $type = ''){
		switch ($type) {
			case 'withContent':
				$maxLen = 150;
				break;			
			case 'resume':
				$maxLen = 120;
				break;			
			case 'code':
				$maxLen = 200;
				break;			
			default:
				$maxLen = 221;
				break;
		}
		$ret = false;
		if(strlen($text)>$maxLen){
			$ret = true;
		}
		return $ret;
	}
	private static function returnShortText($text, $type = ''){
		switch ($type) {
			case 'withContent':
				$maxLen = 150;
				break;			
			case 'resume':
				$maxLen = 120;
				break;			
			case 'code':
				$maxLen = 200;
				break;			
			default:
				$maxLen = 221;
				break;
		}
		$ret = Utils::poperlyTruncate($text, $maxLen);
		return $ret;
	}
	public static function returnHtml(){

	}

	public static function resumeHtmlCreator($text){
		$html = '';
		if(Utils::isTooLong($text,'resume')){
			$id = md5($text);
			$html .= "%t"."\t".'<a href="#'.$id.'" class="text-popup"><p>'.Utils::returnShortText($text,'resume').'...</p></a>'."\n";
			$html .= "%t"."\t".'<div id="'.$id.'" class="text-popup-style mfp-hide">'."\n"."%t"."\t\t".'<p>'.$text.'</p>'."\n".'</div>'."\n";
		}else{
			$html .= "%t"."\t".'<p>'.$text.'</p>'."\n";
		}
        return $html;
	}	
	public static function textHtmlCreator($text){
		$html = '';
		if(Utils::isTooLong($text)){
			$id = md5($text);
			$html .= "%t"."\t".'<a href="#'.$id.'" class="text-popup"><p>'.Utils::returnShortText($text).'...</p></a>'."\n";
			$html .= "%t"."\t".'<div id="'.$id.'" class="text-popup-style mfp-hide">'."\n"."%t"."\t\t".'<p>'.$text.'</p>'."\n".'</div>'."\n";
		}else{
			$html .= "%t"."\t".'<p>'.$text.'</p>'."\n";
		}
        return $html;
	}
	public static function conceptionHtmlCreator($text, $decalage){
		$html = '';
		if(Utils::isTooLong($text)){
			$id = md5($text);
			$html .= $decalage."\t".'<section>'."\n".$decalage."\t\t".'<a href="#'.$id.'" class="text-popup"><p>'.Utils::returnShortText($text).'...</p></a>'."\n";
			$html .= $decalage."\t\t".'<div id="'.$id.'" class="text-popup-style mfp-hide">'."\n".$decalage."\t\t\t".'<p>'.$text.'</p>'."\n".'</div>'."\n";
			$html .= $decalage."\t".'</section>'."\n";
		}else{
			$html .= $decalage."\t".'<section>'."\n".$decalage."\t\t".'<p>'.$text.'</p>'."\n".$decalage.'</section>'."\n";
		}
        return $html;
	}

	public static function codeHtmlCreator($code){
		$html = '';
		$code = str_replace("\n\n", "\n", $code);
		if(Utils::isTooLong($code, 'code')){
			$id = md5($code);
			$html .= "%t".'<a href="#'.$id.'" class="text-popup code-preview"><pre><code data-trim contenteditable>'."%n";
			$html .= "%t"."\t".Utils::returnShortText($code, 'code')."\nCLIQUEZ POUR VOIR LA SUITE..."."%n";
			$html .= "%t".'</code></pre></a>'."%n";
			$html .= "%t".'<pre id="'.$id.'" class="mfp-hide"><code data-trim contenteditable>'."%n";
			$html .= "%t"."\t".$code."%n";
			$html .= "%t".'</code></pre>'."%n";
		}else{
			$html .= "%t".'<pre><code data-trim contenteditable>'."%n";
			$html .= "%t"."\t".$code."%n";
			$html .= "%t".'</code></pre>'."%n";
		}
		return $html;
	}
	//stackoverflow 1193500 truncate text containing html ignoring tags
	public static function poperlyTruncate($html, $maxLength, $isUtf8 = true){

		$ret ='';
		$printedLength = 0;
		$position = 0;
		$tags = array();

    // For UTF-8, we need to count multibyte sequences as one character.
		$re = $isUtf8
		? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}'
		: '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';

		while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position))
		{
			list($tag, $tagPosition) = $match[0];

        // Print text leading up to the tag.
			$str = substr($html, $position, $tagPosition - $position);
			if ($printedLength + strlen($str) > $maxLength)
			{
				$ret .= substr($str, 0, $maxLength - $printedLength);
				$printedLength = $maxLength;
				break;
			}

			$ret .= $str;
			$printedLength += strlen($str);
			if ($printedLength >= $maxLength) break;

			if ($tag[0] == '&' || ord($tag) >= 0x80)
			{
            // Pass the entity or UTF-8 multibyte sequence through unchanged.
				$ret .= $tag;
				$printedLength++;
			}
			else
			{
            // Handle the tag.
				$tagName = $match[1][0];
				if ($tag[1] == '/')
				{
                // This is a closing tag.

					$openingTag = array_pop($tags);
                assert($openingTag == $tagName); // check that tags are properly nested.

                $ret .= $tag;
           	 	}
            	else if ($tag[strlen($tag) - 2] == '/')
            	{
                // Self-closing tag.
            	$ret .= $tag;
            	}
            	else
            	{
                // Opening tag.
            		$ret .= $tag;
            		$tags[] = $tagName;
           	 	}
        	}

        	// Continue after the tag.
        	$position = $tagPosition + strlen($tag);
    	}

	    // Print any remaining text.
	    if ($printedLength < $maxLength && $position < strlen($html))
	    	$ret .= substr($html, $position, $maxLength - $printedLength);

	    // Close any open tags.
	    while (!empty($tags)){
	    	$tag = array_pop($tags);
	    	$ret .= $tag;
	    }
	    return $ret;
	}

	public static function encodep($text) {
		$data = utf8_encode($text);
		$compressed = gzdeflate($data, 9);
		return Utils::encode64($compressed);
	}

	private static function encode6bit($b) {
		if ($b < 10) {
			return chr(48 + $b);
		}
		$b -= 10;
		if ($b < 26) {
			return chr(65 + $b);
		}
		$b -= 26;
		if ($b < 26) {
			return chr(97 + $b);
		}
		$b -= 26;
		if ($b == 0) {
			return '-';
		}
		if ($b == 1) {
			return '_';
		}
		return '?';
	}

	private static function append3bytes($b1, $b2, $b3) {
		$c1 = $b1 >> 2;
		$c2 = (($b1 & 0x3) << 4) | ($b2 >> 4);
		$c3 = (($b2 & 0xF) << 2) | ($b3 >> 6);
		$c4 = $b3 & 0x3F;
		$r = "";
		$r .= Utils::encode6bit($c1 & 0x3F);
		$r .= Utils::encode6bit($c2 & 0x3F);
		$r .= Utils::encode6bit($c3 & 0x3F);
		$r .= Utils::encode6bit($c4 & 0x3F);
		return $r;
	}

	private static function encode64($c) {
		$str = "";
		$len = strlen($c);
		for ($i = 0; $i < $len; $i+=3) {
			if ($i+2==$len) {
				$str .= Utils::append3bytes(ord(substr($c, $i, 1)), ord(substr($c, $i+1, 1)), 0);
			} else if ($i+1==$len) {
				$str .= Utils::append3bytes(ord(substr($c, $i, 1)), 0, 0);
			} else {
				$str .= Utils::append3bytes(ord(substr($c, $i, 1)), ord(substr($c, $i+1, 1)), ord(substr($c, $i+2, 1)));
			}
		}
		return $str;
	} 
	public static function createPreviewOfPDF($pdfLink, $path ='reveal'){
		$im = new Imagick($pdfLink);
		$im->setIteratorIndex(0);
		$im->setCompression(Imagick::COMPRESSION_LZW);
		$im->setCompressionQuality(90);
		$im->writeImage($path.'/images/'.md5($pdfLink).'.png');
	}
}