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

		$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
			 . '<html>'
		 	 . '<head>'
		 	 . '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />'
		 	 . '<title>' . $meta['title'] . '</title>'
		 	 . '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">';
		 	 
		$header .= $styles;
		$header .= $scripts;
		$header .= '</head>';
		$header .= '<body>';
		echo $header;
	}

	function displayDocumentFooter($links) {
		$left = '<div style="float: left; font-size: 14px; padding: 2px;">'
		       . $links['copyleft']
		       . '</div>';
			
		$right = '<div style="float: right; font-size: 1em">'
			. '<div class="clear" style="font-size: .8em;">'
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
		return "<div class=\"logo\" onClick=\"window.location='./'\">lemon</div>";	
	}
	
	function pageBannerUser() {
		$user_links = '<div class="user_links">';
		if($this->auth) {
			  $user_links .= '+ <a href="add.php">New</a>' . ' &nbsp;'
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
		$this->pageOutput = "";
		$this->pageExtra = "";
		$this->extraCSS = (empty($_GET) || isset($_GET['browse'])) ? " splash-page" : " page";
		$this->uri_prefix = "?";
	}
	
	function enableAddOns () {
		global $addOns;
		if(isset($addOns)) {
			$this->addOns = $addOns;
		}
	}
	
	function displayPageItems () {
		$itemsPage = $this->handlePageItems();
		$itemsPage .= $this->pageExtra;
		$pageDisplay = $this->displayWrapper('div', 'section', 'section_inner' . $this->extraCSS, $itemsPage);
		$this->pageOutput .= $pageDisplay;
		echo $this->pageOutput;
	}
	
	function displayPageExtra () {
		echo $this->pageExtra;
	}
	
	function displayPageOmniBox () {
		$omniBox = $this->displayOmniBox($this->classes);
		$this->pageOutput .= $this->displayWrapper('div', 'section', 'section_inner page', $omniBox);
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
					$addonClass = new $addOn['page-display']();
					$returnPage = $addonClass->addonPageItems($this);
					if($returnPage) { return $returnPage; }
				}
			}
		}
		
		$page = "";
		if(isset($_GET['connect'])) {
				$page = "<div class=\"item-section\">"
					. "<div id=\"alertbox\" class=\"alertbox-show\">You are currently signed in.</div>"
			    	. "</div>";
		} else if(isset($_POST['delete'])) {
				$page = "<div class=\"item-section\">"
		       	    . $this->displayItemBlog()
					. "</div>";	
				if($this->meta['owner'] == true) {
					$omniBox = $this->displayOmniBox();
					$page = $omniBox . $page;
				}		
		}  else if(isset($_POST['edit'])) {
				$page = "<div class=\"item-section\">"
		       	    . $this->displayOmniEditBox($_GET['id'])
					. "</div>";	
		} else if(isset($_GET['id'])) {	      	     
				$page = "<div class=\"item-section\">"
					. $this->displayItem()
					. "</div>";
		} else if (isset($_GET['user'])) {
				$page = "<div class=\"item-section\">"
		       	    . $this->displayItemBlog()
					. "</div>";
				if($this->meta['owner'] == true) {
					$omniBox = $this->displayOmniBox();
					$page = $omniBox . $page;
				}					
		} else if ($this->items) {
				$page = "<div class=\"item-section\">"
					. $this->displayItemBlog()
					. "</div>";
		}
		return $page;
	}

	function displayItem() {
		$box_class = "item-page";
		if(!isset($this->items)){ return "<div>No items found.</div>"; }	
		$item_html = $this->handleItemType(reset($this->items), $box_class, null, 0);
		return $item_html;
	}
	
	function displayItemXML() {
		$item_JSON = json_encode($this->items);
		$item_XML = $this->handleXML($this->items);
		echo "<div class=\"clear\"></div></div><hr />JSON:<br/><textarea style=\"width:100%; height: 400px; resize: none;font-size: 16px\">" . $item_JSON . "</textarea>" . "XML<br /><textarea style=\"width:100%; height: 400px; resize: none;font-size: 16px\">" . $item_XML . "</textarea>"; 
	}
		
	function displayItemGrid($maxcount) {
		$box_class = "item-box";
		$info_limit = 240;
		
		$start = 1;
		$col_max = $maxcount;
		$col_holder= array();
		$num = $start;

		if(!isset($this->items)){ return "<div>No items found.</div>"; }
		
		$count = 0;
		foreach($this->items as $i) {			
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
		} return "<div id='photos'>" . $grid . "</div>";
		
	}	

	function displayItemBlog() {
		$box_class = "item-page";
		$info_limit = 2800;
		$item_html = "";

		if(!isset($this->items)){ return "<div>No items found.</div>"; }

		$count = 0;
		foreach($this->items as $item) {
			$item_html .= $this->handleItemType($item, $box_class, $info_limit, $count);
			$count++;
		}
		
		$post_extra = "";
		$separator = "";
		foreach($_GET as $key => $value) {
			if($key != 'start') {
				$post_extra .= $separator . "$key=" . $value;
				$separator = "&";
			}
		}
		
		global $CONFIG;		
		$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
		$count = $CONFIG['item_count'];
		$total  = isset($this->items[0]['total']) ? $this->items[0]['total'] : count($this->items);
		$item_html .= $this->pageItemBrowser($start, $count, $total, $post_extra);

		return $item_html;
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
		
		$createForm  = "<div class=\"item-section\"><div class=\"item-page\" id=\"itemOmniBox\">" . "</div></div>";
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
		$item_html = "<xml><items>";
		foreach($this->items as $i) {			
			$item_html .= "<item type=\"" . $i['class_id'] . "\" date=\"" . $i['date'] . "\">"
			   . "<title>" . $i['title'] . "</title>"
			   . "<info>" . $i['info'] . "</info>"
			   . "<file>" . $i['file'] . "</file>"
			   . "</item>";
		}
		$item_html .= "</items></xml>";
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

	function displayJoinForm () {
		$joinForm = $this->joinForm();
		
		global $message;
		if($message) { $this->meta['message'] = $message; }	
		
		$message = ($this->meta['message']) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $this->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		$messageBlock =  "<div class=\"item-section\">"
			. $message
			. "</div>";
		
		$this->pageOutput = $this->displayWrapper('div', 'section', 'section_inner page', $messageBlock . $joinForm);
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
		$this->info = $item['info'];
		$this->file = $item['file'];
		
		$this->info_limit = $info_limit;
		$this->itemLink = ($prefix) ? $prefix . "id=" . $this->item_id : "";
		$this->onclick = $this->onclickHTML(); 
		
		$this->titleOutput = $this->titleDisplayHTML();
		$this->infoOutput = $this->infoDisplayHTML();
		$this->fileOutput = $this->fileDisplayHTML();
		$this->metaOutput = $this->itemMetaLinks();
		$this->userTools = $this->itemUserTools();
		
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
		
		return "<div style='float: left;'>" . $item_link_html . $date_html . "</div>";
	}

	function itemUserTools() {
		if($this->owner) { 
			$edit_button = "<form id=\"itemEditForm" . $this->item_id . "\" action=\"./?id=" . $this->item_id . "\" method=\"post\">"
			. "<input type=\"hidden\" name=\"edit\" value=\"" . $this->item_id ."\"/>"
			. "<div class=\"item-tools_grey float-right\" onclick=\"domId('itemEditForm" . $this->item_id . "').submit()\">edit </div>"
			. "</form>";
			
			return "<form id=\"itemForm" . $this->item_id . "\" action=\"./?user=" . $this->item_user_id . "\" method=\"post\">"
			. "<input type=\"hidden\" name=\"delete\" value=\"" . $this->item_id ."\"/>"
			. "<div class=\"item-tools_grey float-right\" onclick=\"domId('itemForm" . $this->item_id . "').submit()\">delete</div>"
			. "</form>" . $edit_button; 
		}
	}	
	
	function displayHTML() {
		$item_html = "<div onmouseover=\"domId('userTools" . $this->item_id . "').style.display='inline-block';\" onmouseout=\"domId('userTools" . $this->item_id . "').style.display='none';\" class=\"" . $this->box_class . "\">";
		$item_html .= "<div class='item-settings' style='position: relative'><div id='userTools" . $this->item_id . "' style='position: absolute; right: 0px; width: 120px; display: none'>" . $this->userTools . "</div></div>";
		
		$item_html .= "<div class='item-nodes'>";
		if($this->title) { $item_html .= $this->titleOutput; }
		if($this->file) { $item_html .= $this->fileOutput; }
		if($this->info) { $item_html .= $this->infoOutput; }
		$item_html .= "</div>";
		
		$item_html .= "<div class='item-meta'>";
		$item_html .= $this->metaOutput;
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
		$onlick = "onclick=\"window.location='" . $this->webroot . $this->itemLink . "'\"";
		$file_display = "<div $onlick class=\"item-link\"><div class=\"image-cell\"><img src=\"" . $this->webroot . $this->file . "\" width=\"100%\"></div></div>";
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
