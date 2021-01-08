<?PHP
/*
**  _ _                      _                 _
** (_) |_ ___ _ __ ___   ___| | ___  _   _  __| |
** | | __/ _ \ '_ ` _ \ / __| |/ _ \| | | |/ _` |
** | | ||  __/ | | | | | (__| | (_) | |_| | (_| |
** |_|\__\___|_| |_| |_|\___|_|\___/ \__,_|\__,_|
**          ITEMCLOUD (LEMON) Version 1.1
**
** Copyright (c) 2019-2020, ITEMCLOUD http://www.itemcloud.org/
** All rights reserved.
** developers@itemcloud.org
**
** Free Software License
** -------------------
** Lemon is licensed under the terms of the MIT license.
**
** @category   ITEMCLOUD (Lemon)
** @package    Build Version 1.1
** @copyright  Copyright (c) 2019-2020 ITEMCLOUD (http://www.itemcloud.org)
** @license    https://spdx.org/licenses/MIT.html MIT License
*/

/* -------------------------------------------------------- **
** -------------------- DOCUMENT CLASS -------------------- **
** -------------------------------------------------------- */

class Document {
	
	function displayDocumentHeader($meta) {
		foreach($meta['scripts'] as $script => $src) { 
		 	$scripts = (isset($scripts)) ? $scripts . '<script src="' . $src . '"></script>' : '<script src="' . $src . '"></script>';
		}
		
		foreach($meta['styles'] as $style => $src) { 
		 	$styles = (isset($styles)) ? $styles . '<link rel="stylesheet" type="text/css" href="' . $src . '">' : '<link rel="stylesheet" type="text/css" href="' . $src . '">';
		}		

		$title = $meta['title'];
		if (isset($this->meta['title'])) { $title = $this->meta['title'] ? $meta['title'] . " | " . $this->meta['title'] : $title; }
		
		$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
			 . '<html>'
		 	 . '<head>'
		 	 . '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />'
		 	 . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
		 	 . '<title>' . $title . '</title>'
		 	 . '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">';
		 	 
		$header .= '<meta property="og:title" content="' . $title . '">';
		if (isset($this->meta['active_img'])) { 
			$header .= '<meta property="og:image" content="' . $this->meta['active_img'] . '">';
		}	 
		 	 
		$header .= $styles;
		$header .= $scripts;
		$header .= '</head>';
		$header .= '<body>';
		echo $header;
	}

	function displayDocumentFooter($links) {
		$left = '<div style="float: left; font-size: .5em;">'
		       . $links['copyleft']
		       . '</div>';
			
		$right = '<div style="float: right; font-size: 1em">'
			. '<div class="clear" style="font-size: .5em;">'
			. $links['copyright']
			. '</div>'
			. '</div>';
			
		$footer = $left . $right . '<div class="clear"></div>';
		$footerDisplay = $this->displayWrapper('div', 'footer', 'footer_inner', $footer);
		
		echo $footerDisplay;
		echo "</body></html>";
	}

	function displayPageBanner ($user, $auth) {
		$banner = new documentBanner($user, $auth);
		
		global $addOns;
		if($addOns) {
			foreach($addOns as $addOn) {
				if(isset($addOn['banner-display'])){
					$addonClass = new $addOn['banner-display']($user, $auth, $this);
					$addonClass->updateOutputHTML($banner);
				}
			}
		}
		
		$banner_output = $banner->outputHTML();
		echo $this->displayWrapper('div', 'header', 'header_inner', $banner_output);
	}

	function displayWrapper ($tag, $class, $class_inner, $items) {
		$wrap = "<$tag class='$class'><$tag class='$class_inner'>$items</$tag><div class='clear'></div></$tag>";
		return $wrap;
	}
		
	function joinForm () {
		 $phpJoinForm = "<div id=\"joinFormBox\"></div>"		 
		 	      . "<script>joinForm('joinFormBox');</script>";
			   
		 return $phpJoinForm;
	}
}

class documentBanner {
	function __construct ($user, $auth) {
		$this->user = $user;
		$this->auth = $auth;
		
		$this->logo = $this->pageBannerLogo();
		$this->links = $this->pageBannerLinks();
		$this->user_links = $this->pageBannerUser();
	}

	function outputHTML () {
		return $this->logo . $this->links . $this->user_links;	
	}
	
	function pageBannerLogo () {
		return "<div class=\"logo\" onClick=\"window.location='./'\">lemon</small></div>";	
	}
	
	function pageBannerUser() {
		$user_links = '<div class="user_links">';
		if($this->auth) {
			  $user_links .= '+ <a href="index.php?add=new">New</a>' . ' &nbsp;'
			  	      . '<a onclick="logout()"><u>Sign Out</u></a><form id="logoutForm" action="./?connect=1&logout=1" method="post"><input name="logout" type="hidden"/></form>';
		}
		else { $user_links .=  '<a href="./?connect=1">Sign In</a>'; }
		$user_links .= '</div>';		
		return $user_links;
	}

	function pageBannerLinks() {
		$user = $this->user;
		$links = "";		
		return $links;
	}
}

/* -------------------------------------------------------- **
** ----------------------- PAGE CLASS --------------------- **
** -------------------------------------------------------- */

class pageManager extends Document {

	function __construct($itemData, $ROOTweb) {
		$this->meta = $itemData->meta;
		$this->items = $itemData->items;
		$this->classes = $itemData->classes;
		$this->ROOTweb = $ROOTweb;
		$this->addOns = NULL;
		$this->displayClass = (empty($_GET) || isset($_GET['browse'])) ? " splash-page" : " page";
		$this->uri_prefix = "?";
		
		//Binds page section to item request
		$this->top_section = "<div class='left-col'></div>";
		$this->item_section = "";
		$this->bottom_section = "<div class='right-col'></div>";
		$this->pageOutput = "";
	}
	
	function enableAddOns () {
		global $addOns;
		if(isset($addOns)) {
			$this->addOns = $addOns;
		}
	}
	
	function enableRSS () {			
		if(isset($_GET['RSS'])) {
			echo $this->handleXML($this->items);
			exit();
		}
	}
	
	function displayPageItems () {
		$this->item_section = $this->handlePageItems();
		
		$itemsPage = $this->top_section;
		$itemsPage .= $this->item_section;
		$itemsPage .= $this->bottom_section;
		$pageDisplay = $this->displayWrapper('div', 'section', 'section_inner' . $this->displayClass, $itemsPage);
		$this->pageOutput .= $pageDisplay;
		
		echo $this->pageOutput;
	}
	
	function displayPageTop () {
		echo $this->top_section;
	}
	
	function displayPageBottom () {
		echo $this->bottom_section;
	}
		
	function displayPageOmniBox () {
		$omniBox = $this->displayOmniBox($this->classes);
		$omniBox = "<div style=\"padding: 80px\"><h1>Add to Profile</h1>" . $omniBox . "</div>";
		$this->pageOutput = $this->displayWrapper('div', 'section', 'section_inner', $omniBox);
		echo $this->pageOutput;
	}
		
	function handlePageItems() {
		if($this->addOns) {
			foreach($this->addOns as $addOn) {
				if(isset($addOn['page-banner-display'])){
					$addonClass = new $addOn['page-banner-display']($this);
					$addonClass->updateOutputHTML($this);
				}
			}
		}

		if($this->addOns) {
			foreach($this->addOns as $addOn) {
				if(isset($addOn['page-display'])) { 
					$addonClass = new $addOn['page-display']($this);
					$returnPage = $addonClass->output;
					if($returnPage) { return $returnPage; }
				}
			}
		}
		
		$page = "";
		if (isset($_POST['itc_class_id'])) {
				$omniBox = $this->displayOmniBox();
				$omniBox = "<div style=\"margin: 80px auto;\"><h1>Add to Profile</h1>" . $omniBox . "</div>";
				$page = $this->displayWrapper('div', 'section', 'section_inner', $omniBox);
		} else if(isset($_POST['delete'])) {
				$page = "<div class=\"item-section\"><page>"
		       	    . $this->displayItemBlog()
					. "</page></div>";	
				if($this->meta['owner'] == true) {
					$omniBox = $this->displayOmniBox();
					$page = $omniBox . $page;
				}		
		} else if(isset($_POST['edit'])) {
				$page = "<div class=\"item-section\"><page>"
		       	    . $this->displayOmniEditBox($_GET['id'])
					. "</page></div>";	
		} else if(isset($_GET['id'])) {	      	     
				$page = "<div class=\"item-section\"><page>"
					. $this->displayItem()
					. "</page></div>";
		} else if (isset($_GET['user'])) {
				$page = "<div class=\"clear\"></div>";
				$page .= "<div class=\"item-section\"><page>"
		       	    . $this->displayItemBlog()
					. "</page>";
				$page .= $this->handleItemBrowser('limit');	
				$page .= "</div>";
				if($this->meta['owner'] == true) {
					$omniBox = $this->displayOmniBox();
					$page = $omniBox . $page;
				}			
		} else if ($this->items) {
				$page = "<div class=\"item-section\"><page>"
					. $this->displayItemBlog()
					. "</page></div>";
				$page .= "<div id=\"more-items\" class=\"item-section\"></div>";
		}
		return $page;
	}

	function displayItem() {
		$box_class = "item-page";
		if(!isset($this->items)){ return "<div class=\"clear\">This item could not be found.</div>"; }	
		$item_html = $this->handleItemType(reset($this->items), $box_class, null, 0);
		return $item_html;
	}
	
	function displayItemXML() {
		//DEBUG: $item_JSON = json_encode($this->items);
		$item_XML = $this->handleXML($this->items);
		
		$feed_preview = "<div class=\"clear\"></div>";
		if(isset($item_JSON)) { $feed_preview .= "<div style=\"margin: 40px 20%; height: 400px;\"><textarea style=\"color: #FFF; background-color: #222; width: 100%; height: 100%; font-size: 16px\">" . $item_JSON. "</textarea></div>"; }
		$feed_preview .= "<div style=\"margin: 40px 20%; height: 400px;\"><textarea style=\"color: #FFF; background-color: #222; width: 100%; height: 100%; font-size: 16px\">" . $item_XML . "</textarea></div>"; 
		
		return $feed_preview;
	}

	function displayItems($type, $limit) {
		$box_class = "item-" . $type;
		$info_limit = $limit;
		$item_html = "";

		if(!isset($this->items)){ return "<div class=\"clear\"></div>"; }

		$count = 0;
		foreach($this->items as $item) {
			$item_html .= $this->handleItemType($item, $box_class, $info_limit, $count);
			$count++;
		}

		return $item_html;
	}
		
	function displayItemGrid($items, $maxcount) {
		$box_class = "item-box";
		$info_limit = 240;
		
		$start = 1;
		$col_max = $maxcount;
		$col_holder= array();
		$num = $start;

		if(!isset($this->items)){ return "<div class=\"clear\">No items were found.</div>"; }
		
		$count = 0;
		foreach($items as $i) {			
			if($num > $col_max) { $num = $start; }
			$item_html = $this->handleItemType($i, $box_class, $info_limit, $count);
			$col_holder[$num][] = $item_html;
			$num++;
			$count++;
		}

		$grid = NULL;
		foreach($col_holder as $col_group) {
			foreach($col_group as $column) {
				$grid.= $column;
			}
		} return "<div class='photos'>" . $grid . "</div>";
		
	}

	function displayItemBlog() {
		$box_class = "item-page";
		$info_limit = 2800;
		$item_html = "";

		if(!isset($this->items)){ return "<div class=\"clear\"></div>"; }

		$count = 0;
		foreach($this->items as $item) {
			$item_html .= $this->handleItemType($item, $box_class, $info_limit, $count);
			$count++;
		}

		return $item_html;
	}

	function handleItemBrowser() {	
		$post_extra = "";
		$separator = "";
		foreach($_GET as $key => $value) {
			if($key != 'start') {
				$post_extra .= $separator . "$key=" . $value;
				$separator = "&";
			}
		}
		
		global $CONFIG;	
		if($CONFIG['limit_items'] == true) {	
			$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
			$count = $CONFIG['item_count'];
			$total  = isset($this->items[0]['total']) ? $this->items[0]['total'] : count($this->items);
			$browser = $this->pageItemBrowser($start, $count, $total, $post_extra);
			
			return $this->displayWrapper('div', 'clear', '', $browser);
		}
	}

	function pageItemBrowser($start, $count, $total, $post_extra) {
		$item_html = "";
		if($start >= 0) {
			$new_start = $start - $count;
			$new_start = ($new_start < $count) ? 0 : $new_start;
			
			$back_link = ($new_start == 0) ? "" : "start=$new_start";
			if($post_extra && $back_link) { 
				$back_link = "?" . $post_extra . "&" . $back_link;
			} else if ($post_extra) {
				$back_link = "?" . $post_extra;
			} else if (!$post_extra && $back_link) {
				$back_link = "?" . $back_link;
			} 
			
			if($start > 0) { $item_html .= "<a href=\"./$back_link\"><div class=\"item-tools_dark float-left\">BACK</div></a>"; }
		}

		if($start + $count < $total) {
			$new_start = $start + $count;
			$next_link = "start=$new_start";
			$next_link = ($post_extra)? "?" . $post_extra . "&" . $next_link : "?" . $next_link;
			
			$next_count_txt = (($total - ($start + $count)) < $count) ? ($total - ($start + $count)) : $count;
			$item_html .= "<a href=\"./$next_link\"><div class=\"item-tools_dark float-right\">NEXT</div></a>";
		}
		
		return $item_html;
	}

	function displayOmniBox() {
		if(!$this->classes) { return; }
		
		$classes = $this->classes;
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController = new OmniBox(" . $class_js_array . ", 'itemOmniBox');\n OmniController.toggle('" . $class_id . "');\n</script>";
		$message = (isset($this->meta['message'])) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $this->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div class=\"item-section\"><div class=\"item-page\" style=\"margin: 20px; width: auto;\" id=\"itemOmniBox\">" . "</div></div>";
		$createForm .= $javascript_omni_box;
		return $message . $createForm . $javascript_omni_box;
	}	

	function displayOmniEditBox($item_id) {
		if(!$this->classes) { return; }
		
		$classes = $this->classes;
		$class_js_array = json_encode($classes);
		$item_js_array = json_encode($this->items[0]);
		$class_id = $this->items[0]['class_id'];
		
		$javascript_omni_box = "<script>var OmniControllerEdit = new OmniEditBox(" . $class_js_array . ", 'itemOmniEditBox');\n OmniControllerEdit.set_active_str('Edit'); OmniControllerEdit.set_active_item(" . $item_js_array . "); OmniControllerEdit.toggle('" . $class_id . "');\n</script>\n";
		$message = (isset($this->meta['message'])) ? "<center><div id=\"alertboxEdit\" class=\"alertbox-show\">" . $this->meta['message'] . "</div></center>" : "<center><div id=\"alertboxEdit\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div class=\"item-section\"><div class=\"item-page\" id=\"itemOmniEditBox\">" . "</div></div>";
		$createForm .= $javascript_omni_box;
		return $message . $createForm . $javascript_omni_box;
	}	
		
	function handleXML() {		
		global $_ROOTweb;
		
		$nl = "\r\n";
		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$feed_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$item_html = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>$nl"
			. "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">$nl"
			. "<channel>$nl"
			. " <title>" . htmlspecialchars ($this->meta['title']) . "</title>$nl"
			. " <link>" . htmlspecialchars ($feed_url) . "</link>$nl"
			. " <description></description>$nl"
			. " <atom:link href=\"" . htmlspecialchars ($feed_url) . "\" rel=\"self\" type=\"application/rss+xml\" />";
		if($this->items){
			foreach($this->items as $i) {			
			$item_html .= "  <item>$nl"
			   . "    <title>" . htmlspecialchars ($i['title']) . "</title>$nl"
			   . "    <link>" . htmlspecialchars ($_ROOTweb . "?id=" . $i['item_id']) . "</link>$nl"
			   . "    <description>" . htmlspecialchars ($i['description']) . "</description>$nl"

			   . "    <pubDate>" . date('r', strtotime($i['date']))  . "</pubDate>$nl"
			   . "    <source url=\"http://itemcloud.org\">ItemCloud</source>$nl"
			   . "    <guid>" . htmlspecialchars ($_ROOTweb . "?id=" . $i['item_id']) . "</guid>$nl"  
			   . "  </item>$nl";
			}
		}
		$item_html .= "</channel>$nl"
			. "</rss>$nl";
		return $item_html;
	}
			
	function handleItemType ($item, $box_class, $info_limit, $count) {
		global $client;
		$user_id = $client->user_serial;
			
		switch ($item['class_id']) {
			case 2: // item_type: link
				$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $user_id, $info_limit, $this->uri_prefix);
				$itemDisplay->fileOutput = $itemDisplay->linkOverride();
				break;
			case 3: // item_type: download
				$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $user_id, $info_limit, $this->uri_prefix);
				$itemDisplay->fileOutput = $itemDisplay->downloadOverride();
				break;
			case 4: // item_type: photo
				$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $user_id, $info_limit, $this->uri_prefix);
				$itemDisplay->fileOutput = $itemDisplay->photoOverride();
				break;
			case 5: // item_type: audio
				$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $user_id, $info_limit, $this->uri_prefix);
				$itemDisplay->fileOutput = $itemDisplay->audioOverride();
				break;
			case 6: // item_type: video
				$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $user_id, $info_limit, $this->uri_prefix);
				$itemDisplay->fileOutput = $itemDisplay->videoOverride();
				break;
			default:
				$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $user_id, $info_limit, $this->uri_prefix);
				break;
		}
		$itemDisplay->output = $itemDisplay->displayHTML();
								
		if($this->addOns) {
			foreach($this->addOns as $addOn) {
				if(isset($addOn['item-display'])) {
					$addonClass = new $addOn['item-display']();
					$itemDisplay->updateAddOns($addonClass);
					$itemDisplay->output = $itemDisplay->displayHTML();
				}
			}
		}
		
		return $itemDisplay->output;
	}

	function displayJoinForm ($auth) {
		global $message;
		if(!$auth && $message) { $this->meta['message'] = $message; }	
		else if ($auth) { $message = "You are currently signed in."; }
		
		$message = ($message) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $message . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		$messageBlock =  "<div class=\"item-section\">"
			. $message
			. "</div>";
			
		if(!$auth) {
			$messageBlock .= $this->joinForm();
		} else {
			$messageBlock .= "<div class=\"item-section\"><h1>Add to Profile</h1></div>" . $this->displayOmniBox($this->classes);
		}
		
		$this->pageOutput = $this->displayWrapper('div', 'section', 'section_inner page', $messageBlock);
		echo $this->pageOutput;
	}
}
	
/* -------------------------------------------------------- **
** -------------------- ITEM DISPLAY ---------------------- **
** -------------------------------------------------------- */

class ItemDisplay {
	function __construct ($item, $webroot, $box_class, $user_id, $info_limit, $prefix) {		
		$this->item = $item;
		
		$this->item_id = $item['item_id'];
		$this->class_id = $item['class_id'];
		$this->item_user_id = $item['user_id'];
		$this->box_class = $box_class;
		
		$this->user_id = $user_id;
		$this->owner = ($this->user_id && $this->item_user_id == $this->user_id) ? $this->user_id : false;
		
		$this->webroot = $webroot;		
		$this->item_user_img = (isset($item['profile']['user_img'])) ? $item['profile']['user_img'] : "";
		$this->dateService = new DateService($item['date']);

		$this->title = $item['title'];
		$this->info = $item['description'];
		$this->file = $item['link'];
		
		$this->info_limit = $info_limit;
		$this->itemLink = ($prefix) ? $prefix . "id=" . $this->item_id : "";
		$this->onclick = $this->onclickHTML(); 
		
		$this->titleOutput = $this->titleDisplayHTML();
		$this->infoOutput = $this->infoDisplayHTML();
		$this->fileOutput = $this->fileDisplayHTML();
		$this->metaOutput = $this->itemMetaLinks();
		$this->userTools = $this->itemUserTools();
		
		//set $this->nodeOutput
		$this->nodeOutputHTML();
		$this->output = $this->displayHTML($info_limit);
	}
	
	function updateAddOns ($addons) {
		$addons->updateOutputHTML($this);
	}
	
	function onclickHTML () {
		return ($this->itemLink) ? " onclick=\"window.location='" . $this->webroot . $this->itemLink . "'\"" : "";
	}
	
	function titleDisplayHTML () {
		$onclick = $this->onclick;
		$title_html = "<div class=\"item-title\"$onclick>" . $this->title . "</div>";
		return $title_html;
	}
	
	function infoDisplayHTML () {
		$limit = $this->info_limit;
		$onclick = $this->onclick;
		$extra = "<div class=\"item-tools_grey\"title=\"Show more\"$onclick>...</div>";
		$info_string = ($limit) ? chopString($this->info, $limit,  $extra) : $this->info;
		$info_html = '<div class="item-info"><span>' . nl2br($info_string) . '</span></div>';
		return $info_html;
	}
	
	function fileDisplayHTML () {
		$file_name_text = chopString($this->file, 34, '...');
		$file_display = '<div class="item-link"><center>'
			  . '<div class="file_text">' . $file_name_text . '</div>'
			  . '<a href="' . $this->file . '" title="' . $this->file . '" target="_blank">'
			  . '<div class="file_button">Go to File</div></a>'
			  . '</center></div>';
		return $file_display;
	}
	
	function itemMetaLinks() {
		$onclick = $this->onclick;
		$item_link_html = "<div class=\"item-user-link\"><a$onclick>" . $this->webroot . "?item="  . $this->item_id . "</a></div>";
		$date_html = '<div class="item-date">' . $this->dateService->date_time . '</div>';
		
		return "<div class='meta-links float-left'>" . $item_link_html . $date_html . "</div>";
	}

	function itemUserTools() {
		if($this->owner) { 
			$edit_button = "<form id=\"itemEditForm" . $this->box_class . $this->item_id . "\" action=\"./?id=" . $this->item_id . "\" method=\"post\">"
			. "<input type=\"hidden\" name=\"edit\" value=\"" . $this->item_id ."\"/>"
			. "<div class=\"item-tools_grey float-right\" onclick=\"domId('itemEditForm" . $this->box_class . $this->item_id . "').submit()\">edit </div>"
			. "</form>";
			
			$edit_form = "<form id=\"itemForm" . $this->box_class . $this->item_id . "\" action=\"./?user=" . $this->item_user_id . "\" method=\"post\">"
			. "<input type=\"hidden\" name=\"delete\" value=\"" . $this->item_id ."\"/>"
			. "<div class=\"item-tools_grey float-right\" onclick=\"domId('itemForm" . $this->box_class . $this->item_id . "').submit()\">delete</div>"
			. "</form>" . $edit_button; 
			
			return "<div onmouseover=\"domId('userTools" . $this->box_class . $this->item_id . "').style.display='inline-block';\" onmouseout=\"domId('userTools" . $this->box_class . $this->item_id . "').style.display='none';\">"
			. "<div class='item-settings item-tools_grey float-left' style='position: relative; padding: 8px 9px; margin: 0px'>&#8942;<div id='userTools" . $this->box_class . $this->item_id . "' style='position: absolute; width: 100px; display: none'>"
			. $edit_form 
			. "</div></div></div>";
		}
	}		
	
	function nodeOutputHTML () {
		$item_html = "";
		if($this->title) { $item_html .= $this->titleOutput; }

		$item_html .= "<div class='item-meta'>";
		$item_html .= $this->metaOutput;
		$item_html .= "<div class=\"clear\"></div>";	
		$item_html .= "</div>";		
		
		if($this->file) { $item_html .= $this->fileOutput; }
		if($this->info) { $item_html .= $this->infoOutput; }
		$this->nodeOutput = $item_html;
	}
	
	function displayHTML() {
		$this->nodeOutputHTML();
		
		$item_html = "<div class=\"" . $this->box_class . " class_" . $this->item['class_id'] . "\">";

		$item_html .= "<div class='item-nodes'>";
		$item_html .= $this->nodeOutput;
		$item_html .= "<div class='clear'></div>";
		$item_html .= "</div>";

		$item_html .= "<div class='item-toolbar'>";		
		$item_html .= $this->userTools;
		$item_html .= "</div>";

			
		$item_html .= "<div class='clear'></div>";
		$item_html .= "</div>";
		return $item_html;
	}
	
	function linkOverride () {
		$file_name_text = chopString($this->file, 54, '...');

		$file_display = '<div class="item-link"><center>'
			  . '<div class="file_text">' . $file_name_text . '</div>'
			  . '<a href="' . $this->file . '" title="' . $this->file . '" target="_blank">'
			  . '<div class="file_button">Go to Page</div></a>'
			  . '</center></div>';
		return $file_display;
	}
	
	function downloadOverride () {
		$fn = $this->file;
		$fn = substr($fn, strrpos($fn, '/')+1, strlen($fn));
		$file_name_text = chopString($fn, 54, '...');
		
		$file_display = '<div class="item-link file-link"><center>'
				  . '<div class="file_text">' . $file_name_text . '</div>'
				  . '<a href="' . $this->file . '">'
				  . '<div class="file_button">Download File</div></a>'
				  . '</center></div>';
		return $file_display;
	}
	
	function photoOverride () {
		$onclick = $this->onclick;
		$file_display = "<div $onclick class=\"item-link\"><div class=\"image-cell\"><img src=\"" . $this->webroot . $this->file . "\" width=\"100%\"></div></div>";
		return $file_display;
	}
	
	function audioOverride () {
		$file_display = '<div class="item-link"><audio controls><source src="' 
			. $this->webroot .  $this->file . '" type="audio/mpeg">Download to play audio.</audio></div>';
		return $file_display;
	}
	
	function videoOverride () {
		$file_display =  '<div class="item-link"><video width="100%" controls><source src="' 
			. $this->webroot . $this->file . '" type="audio/mpeg">Download to play video.</video></div>';
		return $file_display;
	} 				
}
?>
