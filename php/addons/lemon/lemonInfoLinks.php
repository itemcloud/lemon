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
		
		$class_name = "item-info";
		if($raw_input) { $item->infoOutput = "<div class=\"$class_name\">" . nl2br($this->replaceUrls($item->info)) . "</div>"; }
	}

	function replaceUrls($inputText) {
		// make the urls hyper links (rough development version) yikes!
		$reg_exUrl = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
		
		$inputText =  preg_replace($reg_exUrl, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $inputText);
		return $inputText;
    }
}
?>
