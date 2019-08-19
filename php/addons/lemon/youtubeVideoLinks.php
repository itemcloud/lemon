<?PHP
/*
**  _ _                      _                 _
** (_) |_ ___ _ __ ___   ___| | ___  _   _  __| |
** | | __/ _ \ '_ ` _ \ / __| |/ _ \| | | |/ _` |
** | | ||  __/ | | | | | (__| | (_) | |_| | (_| |
** |_|\__\___|_| |_| |_|\___|_|\___/ \__,_|\__,_|
**          ITEMCLOUD (LEMON) Version 1.0
**
** Copyright (c) 2019, ITEMCLOUD http://www.itemcloud.org/
** All rights reserved.
** developers@itemcloud.org
**
** Free Software License
** -------------------
** Lemon is licensed under the terms of the MIT license.
**
** @category   ITEMCLOUD (Lemon)
** @package    Build Version 1.0
** @copyright  Copyright (c) 2019 ITEMCLOUD (http://www.itemcloud.org)
** @license    https://spdx.org/licenses/MIT.html MIT License
*/

/* -------------------------------------------------------- **
** --------- Add-On for YouTube links as video ----------- **
** -------------------------------------------------------- */

// Tested for Lemon 1.0+
$yt_video_links_addon['addon-name'] = 'YouTube Links as Video';
$yt_video_links_addon['addon-version'] = '1.0';
$yt_video_links_addon['item-display'] = 'youtubeVideoLinks';

//Add to global $addOns variable
$addOns[] = $yt_video_links_addon;

class youtubeVideoLinks {
	function updateOutputHTML ($item) {		
		//only update raw info to be safe
		//include new youtubeVideoLinks().updateOutputHTML($item) to use with another add-on

		$raw_input = ($item->fileOutput == $item->linkOverride()) ? $item->file : NULL;
		
		//check for youtube links (rough development version) yikes!
		if($raw_input && strpos($item->file, 'youtube.com') && $this->getYoutubeIdFromUrl($item->file)) { 

			$youtube_ID = $this->getYoutubeIdFromUrl($item->file); 
			$ytFrame = '<iframe width="100%" height="446" src="https://www.youtube.com/embed/' . $youtube_ID . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
			$item->fileOutput = $ytFrame;
		}
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
