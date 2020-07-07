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
** --------- Add-On for YouTube links as videos ----------- **
** -------------------------------------------------------- */

// Tested for Lemon 1.0+
$info_links_addon['addon-name'] = 'Info Links';
$info_links_addon['addon-version'] = '1.0';
$info_links_addon['item-display'] = 'infoLinks';

//Add to global $addOns variable
$addOns[] = $info_links_addon;

class infoLinks {
	function updateOutputHTML ($item) {
		//only update raw info to be safe
		//include new infoLinks().updateOutputHTML($item) to use with another add-on
		$raw_input = ($item->infoOutput == $item->infoDisplayHTML()) ? $item->info : NULL;
		
		$limit = $item->info_limit;
		$extra = "<div class=\"item-tools_grey\" onclick=\"window.location='" . $item->webroot . $item->itemLink . "'\" title=\"Show more\">...</div>";

		$info_string = $this->replaceUrls($item->info);
		$info_string = ($limit && !isset($_GET['id'])) ? chopString($info_string, $limit,  $extra) : $info_string;
		
		$class_name = "item-info";
		if($raw_input) { $item->infoOutput = "<div class=\"$class_name\"><span>" . nl2br($info_string) . "</span></div>"; }
	}

	function replaceUrls($inputText) {
		// make the urls hyper links (rough development version) yikes!
		$reg_exUrl = '|(https?://([\d\w\.-]+\.[\w\.]{2,6})[^\s\]\[\<\>]*/?)|i'; //'@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
		
		$inputText =  preg_replace_callback($reg_exUrl, "checkUrls", $inputText);
		return $inputText;
    }
 
}
 
function checkUrls($match) {
	if(fnmatch("*twitter.com/*/status*", $match[0])) {
		//Support for platform.twitter.com status embeds (widgets.js 7/1/2020)
		$link = '<blockquote class="twitter-tweet"><a href="' . $match[0] . '">' . $match[0] . '</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
	} else {
		$link = '<a href="' . $match[1] . '" target="_blank" title="' . $match[2] . '">' . $match[1] . '</a>';
	}
	return $link;
}
?>
