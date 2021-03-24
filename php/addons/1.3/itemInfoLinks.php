<?PHP
/*
**  _ _                      _                 _
** (_) |_ ___ _ __ ___   ___| | ___  _   _  __| |
** | | __/ _ \ '_ ` _ \ / __| |/ _ \| | | |/ _` |
** | | ||  __/ | | | | | (__| | (_) | |_| | (_| |
** |_|\__\___|_| |_| |_|\___|_|\___/ \__,_|\__,_|
**          ITEMCLOUD (LEMON) Version 1.3
**
** Copyright (c) 2019-2021, ITEMCLOUD http://www.itemcloud.org/
** All rights reserved.
** developers@itemcloud.org
**
** @category   ITEMCLOUD (Lemon)
** @package    Build Version 1.3
** @copyright  Copyright (c) 2019-2021 ITEMCLOUD (http://www.itemcloud.org)
** @license    https://spdx.org/licenses/MIT.html MIT License
*/

/* -------------------------------------------------------- **
** --------- Add-On for Inline links and images ----------- **
** -------------------------------------------------------- */

// Tested for Lemon 1.2+
class inlineInfoLinks {

	function __construct () {
		$info_links_addon['addon-name'] = 'Inline Info Links';
		$info_links_addon['addon-version'] = '1.0';
		$info_links_addon['item-display'] = 'infoLinks';
	}
	
	function setActions() {
		global $actions;
		$actions['item-display'][] = 'infoLinks';
	}
}

//Add to global $addOns variable
$addOns[] = 'inlineInfoLinks';

class infoLinks {
	function update ($item) {
		//only update raw info to be safe
		//include new infoLinks().updateOutputHTML($item) to use with another add-on
		$raw_input = ($item->infoOutput == $item->infoDisplayHTML()) ? $item->info : NULL;
		
		$limit = $item->info_limit;
		$extra = "<div class=\"tools\" onclick=\"window.location='" . $item->webroot . $item->itemLink . "'\" title=\"Show more\">...</div>";

		$info_string = $item->info;
		$info_string = ($limit && strlen($this->replaceUrls($info_string)) > $limit) ? chopString($info_string, $limit,  $extra) : $this->replaceUrls($info_string);
		
		$class_name = "info";
		if($raw_input) { $item->infoOutput = "<div class=\"$class_name\"><span>" . nl2br($info_string) . "</span></div>"; }
	
		$raw_input = ($item->fileOutput == $item->linkOverride()) ? $item->file : NULL;
		
		if($raw_input && checkImages($item->file)) {
			$onlick = "onclick=\"window.location='" . $item->webroot . $item->itemLink . "'\"";
			$file_display = "<div $onlick class=\"link\"><div class=\"image-cell\"><img src=\"" . $item->file . "\" width=\"100%\"></div></div>";		
				
			$item->fileOutput = $file_display;
		}
	}

	function replaceUrls($inputText) {
		// make the urls hyper links (rough development version) yikes!
		$reg_exUrl = '|(https?://([\d\w\.-]+\.[\w\.]{2,6})[^\s\]\[\<\>]*/?)|i'; //'@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
		
		$inputText =  preg_replace_callback($reg_exUrl, "updateUrls", $inputText);
		return $inputText;
    }
 
}

function updateUrls($match) {
		
	$src_file_name = $match[1];
	$link = $src_file_name;
	if (checkImages($match[1])) {
		$link = '<a href="' . $match[1] . '" target="_blank" title="' . $match[2] . '"><img class="inline-image" src="' . $match[1] . '"/></a>';
	} else if(fnmatch("*twitter.com/*/status*", $match[0])) {
		//Support for platform.twitter.com status embeds (widgets.js 7/1/2020)
		$link = '<blockquote class="twitter-tweet"><a href="' . $match[0] . '">' . $match[0] . '</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
	} else {
		$link = '<a href="' . $match[1] . '">' . $match[1] . '</a>';
	}
	return $link;
}

function checkImages($link) {
	$supported_image = array(
		'gif',
		'jpg',
		'jpeg',
		'png'
	);

	$src_file_name = $link;
	$ext = strtolower(pathinfo($src_file_name, PATHINFO_EXTENSION)); // Using strtolower to overcome case sensitive
	
	return (in_array($ext, $supported_image)) ? true : false;
}
?>
