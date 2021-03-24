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
** --------- Add-On for YouTube links as video ----------- **
** -------------------------------------------------------- */
class ytVideoLinks {
	function __construct() {
		$addon_name = 'YouTube Links as Video';
		$addon_version = '1.0';
	}
	
	function setActions() {
		global $actions;
		$actions['item-display'][] = 'youtubeVideoLinks';
	}
}

//Add to global $addOns variable
$addOns[] = 'ytVideoLinks';

class youtubeVideoLinks {
	function update ($item) {		
		//only update raw info to be safe
		//include new youtubeVideoLinks().updateOutputHTML($item) to use with another add-on

		$raw_input = ($item->fileOutput == $item->linkOverride()) ? $item->file : NULL;

		$active_index = isset($_GET['id']) && isset($item->active) ? $item->active :  NULL;
		

		//check for youtube links (rough development version) yikes!
		if ($raw_input && strpos($item->file, 'youtube.com') && $this->getYoutubeIdFromUrl($item->file) && !$active_index) { 			
			$youtube_ID = $this->getYoutubeIdFromUrl($item->file); 
			$youtube_file = 'http://i3.ytimg.com/vi/' . $youtube_ID . '/hqdefault.jpg';

			$onlick = "onclick=\"window.location='" . $item->webroot . $item->itemLink . "'\"";
			$file_display = "<div $onlick class=\"file\"><div class=\"image-cell\"><img src=\"" . $youtube_file . "\" width=\"100%\"></div></div>";		
			
			$item->fileOutput = $file_display;
			$item->nodeOutput = $item->nodeOutputHTML();
		} else if($raw_input && strpos($item->file, 'youtube.com') && $this->getYoutubeIdFromUrl($item->file) && $item->box_class != "card" && $item->box_class != "box" && $item->box_class != "slide") { 

			$youtube_ID = $this->getYoutubeIdFromUrl($item->file); 
			$ytFrame = '<iframe width="100%" height="446" src="https://www.youtube.com/embed/' . $youtube_ID . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
			$item->fileOutput = $ytFrame;
			$item->nodeOutput = $item->nodeOutputHTML();
		}
	}
	
	function returnImageURL ($file) {	
		//check for youtube links (rough development version) yikes!
		if (strpos($file, 'youtube.com') && $this->getYoutubeIdFromUrl($file)) { 			
			$youtube_ID = $this->getYoutubeIdFromUrl($file); 
			$youtube_file = 'http://i3.ytimg.com/vi/' . $youtube_ID . '/hqdefault.jpg';

			return $youtube_file;
		} return $raw_input;
	}
	
	function getYoutubeIdFromUrl($url) {
		$parts = parse_url($url);
		if(isset($parts['query'])){
			parse_str($parts['query'], $qs);
			if(isset($qs['v'])){
				return $qs['v'];
			}else if(isset($qs['vi'])){
				return $qs['vi'];
			}
		}
		if(isset($parts['path'])){
			$path = explode('/', trim($parts['path'], '/'));
			return $path[count($path)-1];
		}
		return false;
	}
}
?>
