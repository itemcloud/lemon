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
** Free Software License
** -------------------
** Lemon is licensed under the terms of the MIT license.
** Free to use and share with this copyright included.
** Thanks for your support!
**
** @category   ITEMCLOUD (Lemon)
** @package    Build Version 1.3
** @copyright  Copyright (c) 2019-2021 ITEMCLOUD (http://www.itemcloud.org)
** @license    https://spdx.org/licenses/MIT.html MIT License
*/

/* -------------------------------------------------------- **
** -------------------- DOCUMENT CLASS -------------------- **
** -------------------------------------------------------- */

class Document {
	
	function enableActions ($actions) {
		if(isset($actions)) {
			$this->actions = $actions;
		}
	}
		
	function enableRSS () {			
		if(isset($_GET['RSS'])) {
			echo $this->handleXML($this->items);
			exit();
		}
	}
	
	function displayDocumentHeader($meta) {
		$this->title = isset($meta['title']) ? $meta['title']: "";
		$this->className = isset($meta['className']) ? $meta['className'] : "";
		
		foreach($meta['scripts'] as $script => $src) { 
		 	$scripts = (isset($scripts)) ? $scripts . '<script src="' . $src . '"></script>' : '<script src="' . $src . '"></script>';
		}
		
		foreach($meta['styles'] as $style => $src) { 
		 	$styles = (isset($styles)) ? $styles . '<link rel="stylesheet" type="text/css" href="' . $src . '">' : '<link rel="stylesheet" type="text/css" href="' . $src . '">';
		}		

		$title = $this->title;
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
			$header .= '<meta property="og:image" content="' . $this->ROOTweb . $this->meta['active_img'] . '">';
		}	 
		 	 
		$header .= $styles;
		$header .= $scripts;
		$header .= '</head>';
		
		$body_class = $this->className;
		$header .= '<body class="' . $body_class. '">';
		echo $header;
	}

	function displayPageContent ($client) {
		$contentDisplay = $this->displayWrapper('div', 'frame_wrap', 'frame', "");
		echo $contentDisplay;
	}
		
	function displayDocumentFooter($links) {
		$left = '<div style="float: left;">'
		       . $links['copyleft']
		       . '</div>';
			
		$right = '<div style="float: right; font-size: 1em">'
			. '<div class="clear">'
			. $links['copyright']
			. '</div>'
			. '</div>';
			
		$footer = "<div class=\"center\">" . $left . $right . "</div>";
		$footerDisplay = $this->displayWrapper('div', 'frame_wrap', 'frame footer bar', $footer);
		
		echo $footerDisplay;
		echo "</body></html>";
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
	function __construct ($user, $title) {
		$this->user = $user;
		$this->auth = $user->auth;		
		$this->title = $title;
		
		$this->logo = $this->pageBannerLogo();
		$this->links = $this->pageBannerLinks();
		$this->user_links = $this->pageBannerUser();
	}

	function outputHTML () {
		return $this->logo . $this->links . $this->user_links;
	}
	
	function pageBannerLogo () {
		return "<div class=\"left\"><div class=\"menu\"><div class=\"logo\" onClick=\"window.location='./'\">". $this->title . "</div></div></div>";	
	}
	
	function pageBannerUser() {	
		$user_links = '<div class="right">';
		$user_links .= '<div class="menu">';
		if($this->auth) {
			  $user_links .= '<div class="link">+ <a href="index.php?add=new"><span class="name">New</span></a></div>'
			  	      . '<div class="link"><a onclick="logout()"><span class="name"><u>Sign Out</u></span></a><form id="logoutForm" action="./?connect=1&logout=1" method="post"><input name="logout" type="hidden"/></form></div>';
		}
		else { $user_links .=  '<div class="link"><a href="./?connect=1"><span class="name">Sign In</span></a></div>'; }
		$user_links .= '</div>';
		$user_links .= '</div>';		
		return $user_links;
	}

	function pageBannerLinks() {
		$user = $this->user;
		$links = "";		
		return $links;
	}
}

function runAddons($actions, $object, $addon_name) {
	if(isset($actions[$addon_name])) { 
		foreach($actions[$addon_name] as $update) {
			$display = new $update;
			$display->update($object);
		}
	}
}


/* -------------------------------------------------------- **
** ----------------------- PAGE CLASS --------------------- **
** -------------------------------------------------------- */

class pageManager extends Document {

	function __construct($itemData, $ROOTweb) {
		$this->meta = $itemData->meta;
		$this->items = $itemData->items;
		$this->index = $itemData->item_index;
		$this->classes = $itemData->classes;
		$this->ROOTweb = $ROOTweb;
		$this->addOns = NULL;
		$this->displayClass = (empty($_GET) || isset($_GET['browse'] ) || isset($this->meta['profile'])) ? " fixed" : " fixed-left";
		$this->uri_prefix = "?";
		
		$this->user_id = $itemData->client->user_serial; //OWNER?
		$this->item_count = $itemData->meta['item_count'];
		$this->limit_items = true;

		$this->frames = [];
		$this->pageOutput = "";
	}
	
	function displayPageContent ($client) {

		$this->displayPageBanner($client);

		if (isset($_GET['connect'])) { 
			$this->displayJoinform($client->auth);
		} else if ($client->auth && isset($_GET['add'])) { 
			$this->displayPageOmniBox();
		} else { $this->displayPageItems(); }
		
		$contentDisplay = $this->displayWrapper('div', 'frame_wrap', 'frame', "");
		echo $contentDisplay;
	}
		
	function displayPageBanner ($user) {
		$title = $this->title;
		$banner = new documentBanner($user, $title);
		if(isset($this->actions)) { runAddons($this->actions, $banner, 'banner-display'); }
		$banner_output = $banner->outputHTML();
		
		echo $this->displayWrapper('div', 'frame_wrap', 'frame header inline bar', $banner_output);
	}
	
	function handlePageItemRequest () {
		$this->section['items']['output'] = "";
		if(!$this->pageOutput) { $this->handlePageItems(); }
		return $this->section['items']['output'];
	}
	
	function displayPageItems () {
				
		//Binds page section to item request
		$this->section['top']['output'] = "";
		$this->section['top']['displayClass'] = "";
		$this->section['left']['output'] = "<div class=\"left\"></div>";
		$this->section['items']['output'] = "";
		$this->section['items']['displayClass'] = $this->displayClass;
		$this->section['right']['output'] = "<div class=\"right\"></div>";
		$this->section['bottom']['output'] = "";
		$this->section['bottom']['displayClass'] = "";
		
		if(isset($this->actions)) { runAddons($this->actions, $this, 'page-banner-display'); }
		if(isset($this->actions) && !isset($_POST['edit'])) { runAddons($this->actions, $this, 'page-display'); }
		
		if(!$this->section['items']['output']) { 
		$this->handlePageItems(); }
		
		$this->frames[] = $this->displayWrapper('div', 'frame_wrap', 'frame inline' . $this->section['top']['displayClass'], $this->section['top']['output']);
		$this->frames[] = $this->displayWrapper('div', 'frame_wrap', 'frame content ' . $this->section['items']['displayClass'], $this->section['items']['output'] . $this->section['left']['output'] . $this->section['right']['output']);
		$this->frames[] = $this->displayWrapper('div', 'frame_wrap', 'frame ' . $this->section['bottom']['displayClass'], $this->section['bottom']['output']);
		foreach($this->frames as $frame) {
			$this->pageOutput .= $frame;
		}
		echo $this->pageOutput;
	}
	
	function displayPageTop () {
		echo $this->section['top']['output'];
	}
	
	function displayPageBottom () {
		echo $this->section['bottom']['output'];
	}
		
	function displayPageOmniBox () {
		$omniBox = $this->displayOmniBox($this->classes);
		$omniBox = "<div style=\"padding: 80px\"><h1>Add to Profile</h1>" . $omniBox . "</div>";
		$this->pageOutput = $this->displayWrapper('div', 'frame_wrap', 'frame', $omniBox);
		echo $this->pageOutput;
	}
		
	function handlePageItems() {
		
		$page = "";
		if (isset($_POST['itc_class_id'])) {
				$omniBox = $this->displayOmniBox();
				$omniBox = "<div class=\"center\"><div style=\"margin: 80px auto;\"><h1>Add to Profile</h1>" . $omniBox . "</div></div>";
				$page = $this->displayWrapper('div', 'frame_wrap', 'frame fixed', $omniBox);
		} else if (isset($_GET['api'])) {
				$page .= $this->displayItems($_GET['more_class'], 240);
		} else if(isset($_POST['delete'])) {
		       	$page = "<div class=\"center\">"; 
				if($this->meta['owner'] == true) {
					$omniBox = $this->displayOmniBox();
					$page .= $omniBox;
				}		
				$page .= $this->displayItems('page', 240);
				$page .= "</div>";
		} else if(isset($_POST['edit'])) {
		       	$page  = "<div class=\"center\">"; 
		       	$page .= $this->displayOmniEditBox($_GET['id']);
				$page .= "</div>";
		} else if(isset($_GET['id'])) {	  
		       	$page  = "<div class=\"center\">";    	     
				$page .= $this->displayItem();
				$page .= "</div>";
		} else if (isset($_GET['user'])) {
				$page = "<div class=\"clear\"></div>";
		       	$page .= "<div class=\"center\">";
				if(isset($this->meta['owner']) && $this->meta['owner'] == true) {
					$omniBox = $this->displayOmniBox();
					$page .= $omniBox;
				}
				$page .= $this->handleItemBrowser('limit');	
				$page .= $this->displayItems('page', 240);
				$page .= $this->handleItemBrowser('limit');	
				$page .= "</div>";
		} else if ($this->items && isset($_GET['grid'])) {
				$page = "<div class=\"center col-3\">" . $this->displayItemsGrid('page', 240, 3);
				$this->section['items']['displayClass'] = ""; 
		} else if ($this->items && empty($_GET)) {
				$page = "<div class=\"center\">" . $this->displayItems('page', 240);
				$page .= "<script>more_class = '" . 'page'  . "';</script>";
				$page .= "</div>"; $page .= "<div class=\"center\" id=\"more-items\"></div>";
				$this->section['items']['displayClass'] = "fixed"; 
		}
		$this->section['items']['output'] = $page;
	}

	function displayItem() {
		$box_class = "item";
		if(!isset($this->items)){ return "<div class=\"clear\">This item could not be found.</div>"; }	
		
		$itemDisplay = new ItemDisplay(reset($this->items), $this->ROOTweb, $box_class, $this->user_id, null, $this->uri_prefix);				
		if(isset($this->actions)) { runAddons($this->actions, $itemDisplay, 'item-display'); }	
		$itemDisplay->nodeOutput = $itemDisplay->nodeOutputHTML();
		$item_html = $itemDisplay->displayHTML();
			
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
		$box_class = $type;
		$info_limit = $limit;
		$item_html = "";

		if(!isset($this->items)){ return "<div class=\"clear\"></div>"; }
		
		$count = 0;
		foreach($this->items as $item) {
			$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $this->user_id, $info_limit, $this->uri_prefix);				
			if(isset($this->actions)) { runAddons($this->actions, $itemDisplay, 'item-display'); }	
			$itemDisplay->nodeOutput = $itemDisplay->nodeOutputHTML();
			$item_html .= $itemDisplay->displayHTML();
			$count++;
		}

		return $item_html;
	}

	function displayItemsGrid($type, $limit, $col) {
		$box_class = $type;
		$info_limit = $limit;
		$item_html = "<div class=\"col-$col\">";
		$item_html = "";


		if(!isset($this->items)){ return "<div class=\"clear\"></div>"; }
		
		$count = 0;
		$boxes = count($this->items) > 1 ? array_chunk($this->items,(int)count($this->items)/$col) : array_chunk($this->items, 1);
		$column = 1;
		foreach($boxes as $box) {
			$item_html .= "<div class='col--x$column'>";
			$tmp_columns;
			foreach($box as $item) {
				$itemDisplay = new ItemDisplay($item, $this->ROOTweb, $box_class, $this->user_id, $info_limit, $this->uri_prefix);				
				if(isset($this->actions)) { runAddons($this->actions, $itemDisplay, 'item-display'); }
				$itemDisplay->nodeOutput = $itemDisplay->nodeOutputHTML();
				$item_html .= $itemDisplay->displayHTML();
				$count++;
				

			}
			if($column % $col == 0) {
				$column = 0;
			}	$column++;
			$item_html .= "</div>";
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

		if($this->limit_items == true) {	
			$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
			$count = $this->item_count;
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
			
			if($start > 0) { $item_html .= "<a href=\"./$back_link\"><div class=\"tools float-left\">BACK</div></a>"; }
		}

		if($start + $count < $total) {
			$new_start = $start + $count;
			$next_link = "start=$new_start";
			$next_link = ($post_extra)? "?" . $post_extra . "&" . $next_link : "?" . $next_link;
			
			$next_count_txt = (($total - ($start + $count)) < $count) ? ($total - ($start + $count)) : $count;
			$item_html .= "<a href=\"./$next_link\"><div class=\"tools float-right\">NEXT</div></a>";
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
		
		$createForm  = "<div class=\"page\"><div class=\"item\" style=\"padding: 20px; width: auto;\" id=\"itemOmniBox\">" . "</div></div>";
		$createForm .= $javascript_omni_box;
		return $message . $createForm;
	}	

	function displayOmniEditBox($item_id) {
		if(!$this->classes) { return; }
		
		$classes = $this->classes;
		$class_js_array = json_encode($classes);
		$item_js_array = json_encode($this->items[0]);
		$class_id = $this->items[0]['class_id'];
		
		$javascript_omni_box = "<script>var OmniControllerEdit = new OmniEditBox(" . $class_js_array . ", 'itemOmniEditBox');\n OmniControllerEdit.set_active_str('Edit'); OmniControllerEdit.set_active_item(" . $item_js_array . "); OmniControllerEdit.toggle('" . $class_id . "');\n</script>\n";
		$message = (isset($this->meta['message'])) ? "<center><div id=\"alertboxEdit\" class=\"alertbox-show\">" . $this->meta['message'] . "</div></center>" : "<center><div id=\"alertboxEdit\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div class=\"page\"><div class=\"item\" style=\"padding: 20px; width: auto;\"  id=\"itemOmniEditBox\">" . "</div></div>";
		$createForm .= $javascript_omni_box;
		return $message . $createForm;
	}	
		
	function displayOmniFeedBox($pageManager, $feed, $item_id) {
		if(!$pageManager->classes) { return; }
		
		$feed_id = $feed['feed_id'];
		$classes = isset($feed['feed_item_class']) ? $feed['feed_item_class']: $pageManager->classes;
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController = new OmniFeedBox(" . $class_js_array . ", 'itemOmniBox$feed_id');\n OmniController.set_active_feed('" . $feed_id . "');\n OmniController.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div>" : "<div id=\"alertbox\" class=\"alertbox-hide\"></div>";


		$createForm = "<div onclick=\"domId('itemOmniBox$feed_id').style.display='block'; this.style.display='none'\" style=\"padding: 20px; text-align: center; cursor: pointer\"><div class=\"tools\">+ <u>Add an Item</u></div></div>";		
		$createForm .= "<div class=\"page\"><div class=\"item\" style=\"display: none; padding: 20px;\" id=\"itemOmniBox$feed_id\">" . "</div></div>";
		$createForm .= $javascript_omni_box;
		return $message . $createForm;
	}
		
	function handleXML() {		
		$_ROOTweb = $this->ROOTweb;
		
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

	function displayJoinForm ($auth) {
		$message = $this->meta['message'];
		if ($auth) { $message = "You are currently signed in."; }
		
		$message = ($message) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $message . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		$messageBlock =  "<div class=\"center\">"
			. $message
			. "</div>";
			
		if(!$auth) {
			$messageBlock .= $this->joinForm();
		} else {
			$messageBlock .= "<div class=\"center\"><h1>Add to Profile</h1>" . $this->displayOmniBox($this->classes) . "</div>";
		}
		
		$this->pageOutput = $this->displayWrapper('div', 'frame_wrap', 'frame fixed', $messageBlock);
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
		$this->box_style = "";
		$this->active = isset($item['active']);
		
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
		$this->userTools = "";
		
		//set $this->nodeOutput
		$this->handleItemType();
		$this->output = $this->displayHTML($info_limit);
	}
	
	function handleItemType() {
		switch ($this->class_id) {
			case 2: // item_type: link
				$this->fileOutput = $this->linkOverride();
				break;
			case 3: // item_type: download
				$this->fileOutput = $this->downloadOverride();
				break;
			case 4: // item_type: photo
				$this->fileOutput = $this->photoOverride();
				break;
			case 5: // item_type: audio
				$this->fileOutput = $this->audioOverride();
				break;
			case 6: // item_type: video
				$this->fileOutput = $this->videoOverride();
				break;
			default:
				$this->fileOutput = $this->fileDisplayHTML();
				break;
		}
		$this->nodeOutput = $this->nodeOutputHTML();
	}
	
	function updateAddOns ($addons) {
		$addons->update($this);
	}
	
	function onclickHTML () {
		return ($this->itemLink) ? " onclick=\"window.location='" . $this->webroot . $this->itemLink . "'\"" : "";
	}
	
	function titleDisplayHTML () {
		$onclick = $this->onclick;
		$title_html = "<div class=\"title\"$onclick>" . $this->title . "</div>";
		return $title_html;
	}
	
	function infoDisplayHTML () {
		$limit = $this->info_limit;
		$onclick = $this->onclick;
		$extra = "<div class=\"tools\" title=\"Show more\"$onclick>...</div>";
		$info_string = ($limit) ? chopString($this->info, $limit,  $extra) : $this->info;
		$info_html = '<div class="info"><span>' . nl2br($info_string) . '</span></div>';
		return $info_html;
	}
	
	function fileDisplayHTML () {
		$file_name_text = chopString($this->file, 34, '...');
		$file_display = '<div class="link"><center>'
			  . '<div class="file_text">' . $file_name_text . '</div>'
			  . '<a href="' . $this->file . '" title="' . $this->file . '" target="_blank">'
			  . '<div class="file_button">Go to File</div></a>'
			  . '</center></div>';
		return $file_display;
	}
	
	function itemMetaLinks() {
		$onclick = $this->onclick;
		$item_link_html = "<div class=\"user-link\"><a$onclick>" . $this->webroot . "?item="  . $this->item_id . "</a></div>";
		$date_html = '<div class="date">' . $this->dateService->date_time . '</div>';
		
		return "<div class='meta-links float-left'>" . $item_link_html . $date_html . "</div>";
	}

	function itemUserTools() {
		if($this->owner) { 
			$edit_button = "<form id=\"itemEditForm" . $this->box_class . $this->item_id . "\" action=\"./?id=" . $this->item_id . "\" method=\"post\">"
			. "<input type=\"hidden\" name=\"edit\" value=\"" . $this->item_id ."\"/>"
			. "<div class=\"tools float-left\" onclick=\"domId('itemEditForm" . $this->box_class . $this->item_id . "').submit()\">edit </div>"
			. "</form>";
			
			$edit_form = "<form id=\"itemForm" . $this->box_class . $this->item_id . "\" action=\"./?user=" . $this->item_user_id . "\" method=\"post\">"
			. "<input type=\"hidden\" name=\"delete\" value=\"" . $this->item_id ."\"/>"
			. "<div class=\"tools float-left\" onclick=\"domId('itemForm" . $this->box_class . $this->item_id . "').submit()\">delete</div>"
			. "</form>"; 
			
			return "<div onmouseover=\"domId('userTools" . $this->box_class . $this->item_id . "').style.display='inline-block';\" onmouseout=\"domId('userTools" . $this->box_class . $this->item_id . "').style.display='none';\">"
			. "<div class='settings tools float-left' style='margin: 4px; font-size: 12px;'>&#8942;<div id='userTools" . $this->box_class . $this->item_id . "' style='position: absolute; display: none'>"
					. "<div style='position: relative; top: -2px; width: 100px; ' onmouseout=\"domId('userTools" . $this->box_class . $this->item_id . "').style.display='none';\">" 
			
						. $edit_button . $edit_form 
						
						. "<div class=\"clear\"></div>"
					. "</div>"
			

			. "</div></div></div>";
		}
	}		
	
	function nodeOutputHTML () {
		$item_html = "";
		if($this->title) { $item_html .= $this->titleOutput; }

		$item_html .= "<div class='meta'>";
		$item_html .= $this->metaOutput;
		$item_html .= "<div class='inline-block'>" . $this->itemUserTools() . "</div>";
		$item_html .= "<div class=\"clear\"></div>";	
		$item_html .= "</div>";		
		
		if($this->file) { $item_html .= $this->fileOutput; }
		if($this->info) { $item_html .= $this->infoOutput; }
		return $item_html;
	}
	
	function displayHTML() {
		$onclick = $this->onclick;

		$item_html = "<div class=\"item\">";

		$item_html .= "<div class='nodes'>";
		$item_html .= $this->nodeOutput;
		$item_html .= "<div class='clear'></div>";
		$item_html .= "</div>";

		$item_html .= "<div class='item-toolbar'>";
		$item_html .= $this->userTools;
		$item_html .= "</div>";
			
		$item_html .= "<div class='clear'></div>";
		$item_html .= "</div>";
		
		//create box_html
		if($this->box_class) {
			$box_html = "<div" . $this->box_style . " class=\"" . $this->box_class . " class_" . $this->item['class_id'] . "\">";
			$box_html .= $item_html;
			$box_html .= "</div>";

			return $box_html;
		} else {			
			return $item_html;		
		}
	}
	
	function linkOverride () {
		$file_name_text = chopString($this->file, 54, '...');

		$file_display = '<div class="link"><center>'
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
		
		$file_display = '<div class="link file-link"><center>'
				  . '<div class="file_text">' . $file_name_text . '</div>'
				  . '<a href="' . $this->file . '">'
				  . '<div class="file_button">Download File</div></a>'
				  . '</center></div>';
		return $file_display;
	}
	
	function photoOverride () {
		$onclick = $this->onclick;
		$file_display = "<div $onclick class=\"file\"><div class=\"image-cell\"><img src=\"" . $this->webroot . $this->file . "\" width=\"100%\"></div></div>";
		return $file_display;
	}
	
	function audioOverride () {
		$default_display = '<div class="link"><audio controls><source src="' 
			. $this->webroot .  $this->file . '" type="audio/mpeg">Download to play audio.</audio></div>';
		return $default_display;
	}
	
	function inlineAudioOverride () {		
		$id = $this->item_id . rand(1, 1000);
		
		$autoplay = "";
		$hide_play = " style=\"display: inline-block\"";
		$hide_pause = " style=\"display: none\"";
		if (isset($_GET['id']) && $_GET['id'] == $this->item_id && $this->box_class == 'page') { 
			$autoplay = " autoplay";
			$hide_play = " style=\"display: none\"";
			$hide_pause = " style=\"display: inline-block\"";
		}
		
		$file_display = "<div class=\"file\"><audio id=\"player$id\"$autoplay><source src=\"". $this->webroot .  $this->file . "\"></audio>"
			. "<div>"
			. "<span id=\"play_button$id\" onclick=\"domId('player$id').play(); this.style.display='none'; domId('pause_button$id').style.display='block';\"$hide_play><img src=\"img/ui/play.png\"/></span>"
			. "<span id=\"pause_button$id\" onclick=\"domId('player$id').pause(); this.style.display='none'; domId('play_button$id').style.display='block';\"$hide_pause><img src=\"img/ui/pause.png\"/></span>"
			. "</div></div>";
		return $file_display;			
	}
	
	function videoOverride () {
		$file_display =  '<div class="link"><video width="100%" controls><source src="' 
			. $this->webroot . $this->file . '" type="audio/mpeg">Download to play video.</video></div>';
		return $file_display;
	} 				
}
?>
