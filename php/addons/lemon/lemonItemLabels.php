<?php //Add-On for feed display
$feeds_addon['addon-name'] = 'Item Feeds';
$feeds_addon['addon_name'] = 'lemon-feeds';
$feeds_addon['addon-version'] = '1.0.2';
$feeds_addon['default_img'] = 'default.png';

$feeds_addon['post-handler'] = 'addonPostLabelHandler';
$feeds_addon['page-display'] = 'addonLabelPageDisplay';
$feeds_addon['profile-request'] = 'addonProfileLabelRequest';
$feeds_addon['page-banner-display'] = 'addonProfileLabelDisplay';
$feeds_addon['item-display'] = 'addonItemLabelDisplay';
$feeds_addon['item-request'] = 'addonItemLabelRequest';
$feeds_addon['banner-display'] = 'addonBannerLabelsDisplay';
$feeds_addon['add-new'] = true;

//Add to global $addOns variable
//$addOns[] = $feeds_addon;

class addonBannerLabelsDisplay {
	function __construct ($user, $auth, $pageManager) { 
		$this->user = $user;
		$this->auth = $auth;
		$this->pageManager = $pageManager;
	}
	
	function updateOutputHTML ($banner) {
		$user = $this->user;
		
		$start = (isset($_GET['banner-feeds'])) ? $_GET['banner-feeds'] : 0;
		$max = 6;
		$links = "";
		if(isset($user->profile['feeds'])) {
			$feeds = $user->profile['feeds'];	
			$total = (count($feeds) <= ($start + $max)) ? count($feeds) : ($start + $max);
			$links_browser = new feedBrowse($user->profile['feeds'], $start, $max, $total);
			$links = $links_browser->outputHTML();
		}
		$banner->links = $links . $banner->links;
	}
}

class feedBrowse {
	function __construct ($feeds, $start, $max, $total) {
		$this->feeds = $feeds;
		$this->start = $start ? $start : 0;
		$this->max = $max;
		$this->total = $total;
		$this->post_name = 'banner-feeds';
	}
	
	function set_active_item_id ($item_id) {
		$this->item_id = $item_id;
	}
	
	function set_active_user_id ($user_id) {
		$this->user_id = $user_id;
	}
	
	function outputHTML() {
		global $_ROOTweb;
		if(!$this->feeds) {
			return;
		}
		
		$post_extra = "";
		$seperator = "";
		foreach($_GET as $key => $value) {
			if($key != $this->post_name) {
				$post_extra .= $seperator . "$key=" . $value;
				$seperator = "&";
			}
		}

		$hide_menu = isset($_COOKIE['menu-mx']) ? $_COOKIE['menu-mx']: NULL;		
		$display_pages = "display: none;";		
		$display_feeds = "display: inline-block;";		
		if($hide_menu) { $display_pages = "display: inline-block"; $display_feeds = "display: none;"; }
		
		$start = $this->start;		
		$new_start = $this->start - $this->max;
		
		$links = "<div id=\"nav_feed_links\" class=\"nav_feed_links\" style=\"$display_pages\">";		
		$start_links = "<a href=\"$_ROOTweb?$post_extra\">&#8943;</a>";
		
		if($post_extra) { $post_extra = "&" . $post_extra; }
		
		if($new_start >= 0) {
			if($new_start > 0) { $start_links = "<a href=\"$_ROOTweb?" . $this->post_name . "=" . ($new_start) . "$post_extra\">&#8943;</a>"; }
			$links .= $start_links;
		}
		
		$extra = isset($_GET[$this->post_name]) ? "&" . $this->post_name . "=" . $_GET[$this->post_name] : "";
		$total = $this->total;
		
		for($i = $start; $i < $total; $i++) {
			$feed = $this->feeds[$i];
			$name = $feed['name'];
			$links .= "<a href=\"$_ROOTweb?feed_id=" . $feed['feed_id'] . "&name=" . $name . $extra . "\">" . $name . "</a>";
		}
		if($this->start + $this->max < count($this->feeds)) { 
			if($post_extra) { $post_extra = "&" . $post_extra; }
			$links .= "<a href=\"$_ROOTweb?banner-feeds=" . ($this->start + $this->max) . "$post_extra\">&#8943;</a>"; 
		}
		$links .= "</div>";
		
		$page_form = "new-page";
		$links .= "<form name=\"$page_form\" id=\"$page_form\" action=\"$_ROOTweb\">"
			. "<div id=\"nav_feed_select\" class=\"nav_menu_links\" style=\"$display_feeds\"><select onchange=\"this.form.submit()\" onfocus=\"this.selectedIndex = -1\" name=\"feed_id\" style=\"margin-left: 10px\">";
		for($i = 0; $i < count($this->feeds); $i++) {	
			$feed = $this->feeds[$i];
			$name = $feed['name'];
			$feed_id = $feed['feed_id'];
			
			$links .= "<option value=\"$feed_id\">$name</option>";
		}
		$links .= "</select>";
		$links .= "</div></form>";
		
		return "<div class=\"nav_links\">" . $links ."</div>";
	}
	
	function itemOutputHTML ($index) {
		global $client;	
		global $_ROOTweb;
		global $feeds_addon;
			
		if(!$this->feeds) {
			return;
		}
		$user_id = ($client->user_serial) ? $client->user_serial : 0;
		
		$feeds_js_array = json_encode($this->feeds);
		$javascript_feed_browser = "<script>itemLabelBrowser['$index'] = new feedBrowse(" . $feeds_js_array . ", '$index', $user_id, 'feed_browse_$index');\n </script>";
		$feedMenu = "<div style=\"display: inline-block\" id=\"feed_browse_$index\">";
			
		$start = $this->start;		
		$new_start = $this->start - $this->max;
		
		if($new_start >= 0) {
			$feedMenu .= "<div onclick=\"itemLabelBrowser['$index'].update(" . $new_start . ")\" class=\"item-tools_grey item_feed_menu\">"
					. "<div class=\"item-tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
		}
		
		$total = (count($this->feeds) <= ($this->start + $this->max)) ? count($this->feeds) : ($this->start + $this->max);	
		
		for($i = $start; $i < $total; $i++) {	
			$feed = $this->feeds[$i];
			
			$feed_img_src = ($feed['feed_img']) ? "files/feeds/" . $feed['feed_img'] : "files/feeds/" . 'default.png';
			
			$feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">";
			$feed_img .= "<img class=\"feed-image\" src=\"" . $feed_img_src . "\"/>";
			$feed_img .= "</a>";
					
			$feed_name = "<div style=\"display: inline-block;\">";					
			if(($feed['owner_id'] == $user_id) && $index) {
				//remove feed from item if owner
				$remove_button = "<div style='display: inline-block'><form id='removeFormInline" . $i . $index . "' action='?id=" . $index. "' method='post'>"
				. "<input type='hidden' name='item_id' value='" . $index . "'/>"
				. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>"
				. "<input type='hidden' name='feed' value='remove'/>"		
				. "<div class='inline-remove'>";

				$remove_button .= " <a onclick=\"domId('removeFormInline" . $i . $index . "').submit()\">x</a>";	
				$remove_button .= "</div>";
				$remove_button .= "</form></div>";
				
				$feed_name .= $remove_button;
			}				
			$feed_window_launch = "window.location='" . $_ROOTweb . "?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "'; ";
			
			$feed_name .= "<div onclick=\"$feed_window_launch\" class=\"inline-name\">" . $feed['name'] . "</div>";
			$feed_name .= "</div>";

			$feed_wrapper = "<div class=\"item-tools_grey item_feed_menu\" >";
			$feed_wrapper .= $feed_img;			
			$feed_wrapper .= $feed_name;
			$feed_wrapper .= "</div>";
			
			$feedMenu .= $feed_wrapper;
		}
		

		if($this->start + $this->max <= count($this->feeds)) {
			$feedMenu .= "<div id=\"browse-feeds-button" . $i . $index . "\" style=\"display: none;\" onclick=\"itemLabelBrowser['$index'].update(" . ($this->start + $this->max) . ")\"  class=\"item-tools_grey item_feed_menu\">"
					. "<div class=\"item-tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
			$feedMenu .= "<script>itemLabelBrowser['$index']; domId('browse-feeds-button" . $i . $index . "').style.display='inline-block';</script>";
		}
		
		$feedMenu .= "</div>";
		$feedMenu .= $javascript_feed_browser;
		return $feedMenu; 
	}	

	function listOutputHTML ($index) {
		global $client;	
		global $_ROOTweb;	
		global $feeds_addon;
		if(!$this->feeds) {
			return;
		}
		$user_id = ($client->user_serial) ? $client->user_serial : 0;
	
		$feedMenu = "<div style=\"display: inline-block\" id=\"feed_browse_$index\">";
			
		$start = $this->start;		
		$new_start = $this->start - $this->max;	
		if($new_start >= 0) {
			$feedMenu .= "<div onclick=\"itemLabelBrowser['$index'].update(" . $new_start . ")\" class=\"\">"
					. "<div class=\"\">" . "&#8943;" . "</div>"
					. "</div>";
		}
		
		$total = (count($this->feeds) <= ($this->start + $this->max)) ? count($this->feeds) : ($this->start + $this->max);	
		
		for($i = $start; $i < $total; $i++) {	
			$feed = $this->feeds[$i];
			
			$feed_img_src = ($feed['feed_img']) ? "files/feeds/" . $feed['feed_img'] : "files/feeds" . 'default.png';
			
			$feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">" 
					. "<img class=\"feed-image\" src='" . $feed_img_src . "'/>" 
					. "</a>";
					
			$feed_name = "<div style=\"display: inline-block;\">";					
			if(($feed['owner_id'] == $user_id) && $index) {
				//remove feed from item if owner
				$remove_button = "<div style='display: inline-block'><form id='removeForm" . $i . $index . "' action='?id=" . $index. "' method='post'>"
				. "<input type='hidden' name='item_id' value='" . $index . "'/>"
				. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>"
				. "<input type='hidden' name='feed' value='remove'/>"		
				. "<div class='inline-remove'>";

				$remove_button .= " <a onclick=\"domId('removeForm" . $i . $index . "').submit()\">x</a>";	
				$remove_button .= "</div>";
				$remove_button .= "</form></div>";
				
				$feed_name .= $remove_button;
			}				
			$feed_window_launch = "window.location='" . $_ROOTweb . "?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "'; ";
			
			$feed_name .= "<div onclick=\"$feed_window_launch\" class=\"inline-name\">" . $feed['name'] . "</div>";
			$feed_name .= "</div>";

			$feed_wrapper = "<div class=\"\" >";
			$feed_wrapper .= $feed_img;			
			$feed_wrapper .= $feed_name;
			$feed_wrapper .= "</div>";
			
			
			$feedMenu .= $feed_wrapper;
		}
		

		if($this->start + $this->max <= count($this->feeds)) {
			$feedMenu .= "<div id=\"browse-feeds-button" . $i . $index . "\" style=\"display: none;\" onclick=\"itemLabelBrowser['$index'].update(" . ($this->start + $this->max) . ")\"  class=\"item-tools_grey item_feed_menu\">"
					. "<div class=\"item-tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
			$feedMenu .= "<script>itemLabelBrowser['$index']; domId('browse-feeds-button" . $i . $index . "').style.display='inline-block';</script>";
		}
		
		$feedMenu .= "</div>";
		return $feedMenu; 
	}
}

class addonProfileLabelDisplay {
	function __construct ($pageManager) {
		$this->profile = (isset($pageManager->meta['profile'])) ? $pageManager->meta['profile'] : NULL;
		$this->feeds = $this->setLabels($this->profile);
	}
	
	function setLabels($profile) {
		if(isset($profile['feeds'])) { return $profile['feeds']; }
		return;
	}
	
	function updateOutputHTML ($pageManager) {
		if(isset($this->profile)) { 
			$profileLabels = "";
			
			$start = 0;
			$max = 6;
			$total = count($this->profile['feeds']);
			$profile_feeds_browser = new feedBrowse($this->profile['feeds'], $start, $max, $total);
			$profile_feeds = $profile_feeds_browser->itemOutputHTML(NULL);
		
			$profileLabels = $profile_feeds;
			$banner = $pageManager->displayWrapper('div', 'section', 'section_inner browse-feeds', $profileLabels);
			$pageManager->pageOutput .= $banner;
		}
	}
}

class addonItemLabelDisplay {

	function mergeRemove ($profile_feeds, $item_feeds) {	
		$tmp_feeds = array(); 
		foreach ($profile_feeds as $profileObject) { 
			$match = false;
			foreach($item_feeds as $itemObject) {
				 if ($itemObject['feed_id'] == $profileObject['feed_id']) { 
					$match = true;
				}
			} if(!$match) { $tmp_feeds[] = $profileObject; }
		}
		return $tmp_feeds;
	}

	function addRelatedLabelTools ($parent_id, $_id) {
		global $feeds_addon;
		if($feeds_addon['add-new'] == false) {
			return "";
		}
		
		$chooseLabel = ""; //"<div class='item-tools_dark' style='width: 120px'>+ New</div>";
		$newLabel = "<div id='feedAddNewLabel" . $_id . "' style='position: relative; display: block'><input name='name' class='form' autofocus></div>";
		$newLabel .= "<input id='feedParentLabel" . $_id . "' type='hidden' name='parent-feed' value='$parent_id'>"; //PARENT LABEL
		
		$newLabel .= "<input id='feedPostLabel" . $_id . "' type='hidden' name='feed' value='new-child'>"; //DEFAULT: POSTING TO 'NEW' LABEL IN POST
		
		$tools = "<div style='float: right'>" 
			. "<div id='feedAdd" . $_id . "' " . " onClick=\"this.style.display='none'; domId('feedAddForm" . $_id . "').style.display='inline-block'\" class=\"item-tools_grey\"><div class=\"item-tools_txt\">&#43;</div></div>"
			. "<div id='feedAddForm" . $_id . "' style='display: none'>"
				. "<form id='feedLabelForm" . $_id . "' action=\"?id=" . $_id . "\" method=\"post\">"
				. "<input type='hidden' name='parent_id' value='" . $parent_id . "'>"
				. "<div style=\"float: left\" onClick=\"domId('feedAdd" . $_id . "').style.display='inline-block'; domId('feedAddForm" . $_id . "').style.display='none'\" class=\"item-tools_grey\"><div class=\"item-tools_txt\">&#8722;</div></div>"				
				. "<div style='display: inline-block; width: 120px; margin-right: 4px'>" . $chooseLabel . "</div>"
				. "<div onclick=\"domId('feedLabelForm" . $_id . "').submit()\" style=\"display: inline-block; font-size: 12px;\" class=\"item-tools_dark\">&#9989; SAVE</div>"		
				. $newLabel
				. "</form>"
			. "</div>" 
			. "</div>";

		return $tools;
	}

	function addRelatedLabelTools_small ($parent_id, $_id) {
		global $feeds_addon;
		if($feeds_addon['add-new'] == false) {
			return "";
		}
		
		$chooseLabel = "";
		$newLabel = "<div id='feedAddNewLabel" . $_id . "' style='position: relative; display: block'><input name='name' class='form_small' autofocus></div>";
		$newLabel .= "<input id='feedParentLabel" . $_id . "' type='hidden' name='parent-feed' value='$parent_id'>"; //PARENT LABEL
		$newLabel .= "<input id='feedPostLabel" . $_id . "' type='hidden' name='feed' value='new-child'>"; //DEFAULT: POSTING TO 'NEW' LABEL IN POST
		
		$tools = "<div style='display: inline-block'>" 
			. "<div id='feedAdd" . $_id . "' " . " onClick=\"this.style.display='none'; domId('feedAddForm" . $_id . "').style.display='inline-block'\"><div class=\"item-tools_txt\">&#43;</div></div>"
			. "<div id='feedAddForm" . $_id . "' style='display: none'>"
				. "<form id='feedLabelForm" . $_id . "' action=\"?id=" . $_id . "\" method=\"post\">"
				. "<input type='hidden' name='parent_id' value='" . $parent_id . "'>"
				. "<div style=\"float: left\" onClick=\"domId('feedAdd" . $_id . "').style.display='inline-block'; domId('feedAddForm" . $_id . "').style.display='none'\"><div class=\"item-tools_txt\">&#8722;</div></div>"				
				. "<div style='display: inline-block; width: 120px; margin-right: 4px'>" . $chooseLabel . "</div>"
				. "<div onclick=\"domId('feedLabelForm" . $_id . "').submit()\" style=\"display: inline-block; font-size: 12px;\" class=\"item-tools_dark\">&#9989; SAVE</div>"		
				. $newLabel
				. "</form>"
			. "</div>" 
			. "</div>";

		return $tools . "<div class=\"clear\"></div>";
	}
		
	function userLabelTools ($profile_feeds, $item_id, $dom_id) {
		global $feeds_addon;
		if($feeds_addon['add-new'] == false && !$profile_feeds) {
			return "";
		}
		
		if($profile_feeds) { //CHECK FOR USER PROFILE LABELS
			$chooseLabel = "<select onchange=\"if(!this.value){"
			. " domId('itemAddNewLabel" . $dom_id. "').style.display='block';"
			. " domId('itemPostLabel" . $dom_id . "').value = 'new';"
			. " domId('itemAddNewLabel" . $dom_id . "').focus();"
			. " } else { "
			. " domId('itemAddNewLabel" . $dom_id . "').style.display='none';"
			. " domId('itemPostLabel" . $dom_id . "').value = 'add'; }"
			. "\" name=\"feed_id\" class=\"item-dropdown\">";
					
			foreach($profile_feeds as $feed) {
				$chooseLabel .= "<option value='". $feed['feed_id'] . "'>". $feed['name'] . "</option>";
			}
			if($feeds_addon['add-new'] != false) {
				$chooseLabel .= "<option value=''>+ New</option>";
			}
			$chooseLabel .= "</select>";
			$newLabel = "<div id='itemAddNewLabel" . $dom_id . "' style='position: relative; display: none'><input name='name' class='form' autofocus></div>";	
			$newLabel .= "<input id='itemPostLabel" . $dom_id . "' type='hidden' name='feed' value='add'>"; //DEFAULT: POSTING TO 'ADD' LABEL IN POST
		} else {
			$chooseLabel = "<div class='item-tools_dark' style='width: 120px'>+ New</div>";
			$newLabel = "<div id='itemAddNewLabel" . $dom_id . "' style='position: relative; display: block'><input name='name' class='form' autofocus></div>";	
			$newLabel .= "<input id='itemPostLabel" . $dom_id . "' type='hidden' name='feed' value='new'>"; //DEFAULT: POSTING TO 'NEW' LABEL IN POST
		}
		
		$tools = "<div style='float: right'>" 
			. "<div id='itemAdd" . $dom_id . "' " . " onClick=\"this.style.display='none'; domId('itemAddForm" . $dom_id . "').style.display='inline-block'\" class=\"item-tools_grey\"><div class=\"item-tools_txt\">&#43;</div></div>"
			. "<div id='itemAddForm" . $dom_id. "' style='display: none'>"
				. "<form id='itemLabelForm" . $dom_id . "' action=\"?id=" . $item_id . "\" method=\"post\">"
				. "<input type='hidden' name='id' value='" . $item_id . "'>"
				. "<div style=\"float: left\" onClick=\"domId('itemAdd" . $dom_id . "').style.display='inline-block'; domId('itemAddForm" . $dom_id . "').style.display='none'\" class=\"item-tools_grey\"><div class=\"item-tools_txt\">&#8722;</div></div>"				
				. "<div style='display: inline-block; width: 140px; margin-right: 4px'>" . $chooseLabel . "</div>"
				. "<div onclick=\"domId('itemLabelForm" . $dom_id . "').submit()\" style=\"display: inline-block; font-size: 12px;\" class=\"item-tools_dark\">&#9989; SAVE</div>"		
				. $newLabel
				. "</form>"
			. "</div>" 
			. "</div>";

		return $tools;
	}
	 
	function updateOutputHTML ($itemDisplay) {
		$item_id = $itemDisplay->item['item_id'];
		global $client;
		global $feeds_addon;
		
		$item_feed_output = "";
		if(isset($itemDisplay->item['feeds'])) {
			$index = 0;
			foreach($itemDisplay->item['feeds'] as $feed) {

				$index++;
				
				$feed_img_src = ($feed['feed_img']) ? "files/feeds/" . $feed['feed_img'] : "files/feeds/" . 'default.png';
				
				//Image
				$feed_image = "<div style=\"display: inline-block\">"
					. "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">" 
					. "<img width='28px' style='vertical-align: middle;' src='" . $feed_img_src . "'/>" 
					. "</a>"
					. "</div>";
				
				//Link (Remove button)
				$feed_link = "<div id=\"removeItemLabel" . $index . $item_id . "\" style='display: none'>"
					. "<form id='removeForm" . $index . $item_id . "' action='?id=" . $item_id. "' method='post'>" 
					. "<input type='hidden' name='item_id' value='" . $item_id . "'/>" 
					. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>" 
					. "<input type='hidden' name='feed' value='remove'/>"				
					. "<div style='padding: 0px 6px 0px 2px;'>";
					
					if($feed['owner_id'] == $client->user_serial) {
						$feed_link .= " <a onclick=\"domId('removeForm" . $index . $item_id . "').submit(); alert('remove');\" class=\"item-tools_txt\">X</a>";	
					}		
					
					$feed_link .= "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">"
						. $feed['name']
						. "</a>";

				$feed_link .= "</div>";
				$feed_link .= "</form>";
				$feed_link .= "</div>";
				
				//Label output
				$feed_output = "<div class=\"item-tools_grey\" style=\"border-radius: 24px; padding: 2px\""
					. " onmouseover=\"domId('removeItemLabel" . $index . $item_id . "').style.display='inline-block'\""
					. " onmouseout=\"domId('removeItemLabel" . $index . $item_id . "').style.display='none'\""
					. " style=\"padding: 0px; display: inline-block\">";					
				$feed_output .= $feed_image;
				$feed_output .= $feed_link;
				$feed_output .= "</div>";
				
				$item_feed_output .= $feed_output;	
			}
	
			$start = 0;
			$max = 6;
			$total = count($itemDisplay->item['feeds']);
			$index = $itemDisplay->item['item_id'];
			$profile_feeds_browser = new feedBrowse($itemDisplay->item['feeds'], $start, $max, $total);
			$item_feed_output = $profile_feeds_browser->set_active_item_id($itemDisplay->item['item_id']);
			$item_feed_output = $profile_feeds_browser->set_active_user_id($client->user_serial);
			
			$item_feed_output = $profile_feeds_browser->itemOutputHTML($index);
			$item_feed_output .= "<script>try { itemLabelBrowser['" . $index . "'].set_active_item_id(" . $itemDisplay->item['item_id'] . "); } catch (e) { }</script>";
		}
		
		$tool_output = "";
		if($itemDisplay->user_id && isset($client->profile)) {
				$profile_feeds = $client->profile['feeds'];
				if($profile_feeds && isset($itemDisplay->item['feeds'])) { 
					$profile_feeds = $this->mergeRemove($profile_feeds, $itemDisplay->item['feeds']); 
				}
				$dom_id = $item_id . "_" . rand(10, 100);
				$tool_output = $this->userLabelTools($profile_feeds, $item_id, $dom_id);
				$item_feed_output .= $tool_output;
		}
		$metaOutput = "<div style='float: right'>" . $item_feed_output . "</div>";
		
		//Only add tools 
		if($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) { $metaOutput = $metaOutput;  }
		else {  $metaOutput = $itemDisplay->metaOutput . $metaOutput; }
		$itemDisplay->metaOutput = $metaOutput;		
		
		if(isset($_GET['banner-feeds'])) { $itemDisplay->itemLink .= "&banner-feeds=" . $_GET['banner-feeds']; }
		$itemDisplay->titleOutput = $itemDisplay->titleDisplayHTML();
		$itemDisplay->output = $itemDisplay->displayHTML();
	}
	
	function titleDisplayHTML () {
		$title_html = "<div class=\"item-title\" onclick=\"window.location='./?id=" . $this->item_id . "';\">" . $this->title . "</div>";
		return $title_html;
	}
}

class addonLabelPageDisplay {
	function addonPageItems($pageManager) {
		if(isset($_GET['feed_id']) && !isset($pageManager->meta['active_page'])) {
			global $client;
			global $_ROOTweb;
			
			$profile_feeds = (isset($client->profile)) ? $client->profile['feeds'] : [];
			
			$feed = $pageManager->meta['feed'];
			$feed_owner = ($feed['owner_id'] == $client->user_serial) ? $feed['owner_id'] : NULL;

			$folder = "files/feeds/";
			$file_dir = $_ROOTweb . $folder;
			
			$feed_img_src = ($feed['feed_img']) ? $file_dir . $feed['feed_img'] : $file_dir . 'default.png';
				
			$imageRollover = "changeImageRollover";
			$feed_img = "<div class='item-user'";
			if($feed_owner) { $feed_img .= " onmouseover=\"domId('$imageRollover').style.display='block';\" onmouseout=\"domId('$imageRollover').style.display='none'\""; }
			$feed_img .= " style='width: 100px; height: 100px; margin: 0px 20px 20px 20px; background-image: url(" . $feed_img_src  . ")'>";
			if($feed_owner) { $feed_img .= "<div id=\"$imageRollover\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'\" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 80px; text-align: center;\">&#8853;</div>"; }
			$feed_img .= "</div>";
			$feed_name = "<div style=\"font-size: 2em; cursor: pointer\" onclick=\"window.location='$_ROOTweb?feed_id=" . $feed['feed_id'] . "'\"><u>" . $feed['name'] . "</u></div>";
			
			$page = "<div class=\"item-section\" style=\"text-align: left;\">" . $feed_img . "<div style=\"display: inline-block; text-align: left;\">";
			$page .= "<div id=\"itc_feed_name_form\" style=\"display: none;\"><form action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input class=\"form\" name=\"itc_feed_name\" value=\"" . $feed['name'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9989; SAVE\"></div>";
			$page .= "<div id=\"itc_feed_name\" style=\"display: inline-block\">" . $feed_name;	
										

			$page .= "</form></div>";
			
			if($feed_owner){
				$page .= " <span class=\"item-tools\" onclick=\"this.style.display='none'; domId('itc_feed_name').style.display='none'; domId('itc_feed_name_form').style.display='inline-block';\">&#9998; EDIT</span>";
			}			
			if($feed_owner){			
				$page .= "<div style=\"display: inline-block\"><form action=\"./?itc_feed_edit=purge\" method=\"post\"><input type=\"hidden\" name=\"itc_feed_edit\" value=\"purge\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $feed['feed_id'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9988; DELETE\"></form></div>";		
			}
			
			$page .= "</div>";

			if($feed_owner) {			
				$page .= "<form enctype=\"multipart/form-data\" action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><div style=\"display: none; margin-top: 4px;\" id=\"itc_feed_image_form\"><input type=\"hidden\" name=\"itc_feed_img\" value=\"change\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $_GET['feed_id'] . "\"/><input type=\"file\" class=\"item-tools\" name=\"itc_feed_upload\" accept=\"image/jpeg,image/png,image/gif\"><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9989; SAVE\"></div></form>";
				$page .= "<div id=\"show-form-button\" class=\"item-tools_dark\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; this.style.display='none'\" style=\"margin: 4px 0px;\">" . "Change the feed image" . "</div>";
			}	
			
			//Show update feed display form 			
			if($feed_owner && isset($feed['display_class'])) {
				$page .= "<div style=\"display: inline-block\"><form id='feedDisplayForm" . $feed['feed_id'] . "' action=\"?feed_id=" . $feed['feed_id'] . "\" method=\"post\">"
					  .  "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'>"
					  .  "<input type='hidden' name='name' value='" . $feed['name'] . "'>"					  
					  .  "<input type='hidden' name='feed' value='display'>";					  
								
				$page .= "<div style=\"display: inline-block\">";
				$page .= "<select id=\"display_id\" name=\"display_id\" class=\"item-dropdown\">";
				foreach($feed['display_class'] as $display_class) {
					$selected = ($display_class['display_id'] == $feed['display_id']) ? " selected" :  "";
					$page .= "<option value=\"" . $display_class['display_id'] . "\"$selected>Feed as " . $display_class['name'] . "</option>";
				}
				$page .= "</select></div>";
								
				$page .= " <div onclick=\"domId('feedDisplayForm" . $feed['feed_id'] . "').submit()\" class=\"item-tools_dark\">&#9989; SAVE</div>";
				$page .= "</form></div>";
			}				
			
			if($feed_owner){
				$userTools = new addonItemLabelDisplay();
				$tmp_feeds[] = $feed;
				if(isset($profile_feeds) && isset($feed)) { 
					$profile_feeds = $userTools->mergeRemove($profile_feeds, $tmp_feeds); 
				}	
				
				$related = $userTools->addRelatedLabelTools($feed['feed_id'], 0);
				//$related = $userTools->addRelatedLabelTools($profile_feeds, $feed['feed_id'], $feed['feed_id']);
				$page .= "<div class=\"feed-tools\" style=\"float: right\">" . $related . "</div>";
			}			
						
			$start = 0;
			$max = 6;
			if(isset($feed['related'])) {
				$total = count($feed['related']);
				$related_feeds_browser = new feedBrowse($feed['related'], $start, $max, $total);
				$page .= "<div class=\"feed-tools\" style=\"float: right\">" . $related_feeds_browser->itemOutputHTML(NULL) . "</div>";
			}
			
			$page .= "<div class=\"clear\"></div>";
			if($feed_owner){
				$omniBox = $this->displayOmniBox($pageManager, $pageManager->meta['feed'], $pageManager->items[0]['item_id']);
				$page .= "<div>" . $omniBox . "</div>";
			}			
			
			$item_info_limit = 2800;
			$page .= "<div class=\"feed-" . $feed['display_type'] . "\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit) . "</" . $feed['display_type'] . ">";
			$page .= "<div class=\"clear\"></div>";
			$page .= "</div>";			
			
			return $page;
		}
	}
	
	function displayOmniBox($pageManager, $feed, $item_id) {
		if(!$pageManager->classes) { return; }
		
		$feed_id = $feed['feed_id'];
		$classes = isset($feed['feed_item_class']) ? $feed['feed_item_class']: $pageManager->classes;
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController = new OmniLabelBox(" . $class_js_array . ", 'itemOmniBox');\n OmniController.set_active_feed('" . $feed_id . "');\n OmniController.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div style=\"display: inline-block\">";
		$createForm .= "<div class=\"item-section\"><div style=\"display: none;\" class=\"item-page\" id=\"itemOmniBox\">" . "</div></div>"
			. "<div class=\"float-left\" style=\"display: inline: block\" onclick=\"domId('itemOmniBox').style.display='inline-block'; this.style.display='none'\" style=\"width: 640px; margin: 14px 0px; text-align: center; cursor: pointer\"><div class=\"item-tools\">+ <u>Add an Item</u></div></div>";
		$createForm .= $javascript_omni_box;
		$createForm .= "</div>";
		return $message . $createForm;
	}
}

class addonItemLabelRequest {
	function __construct ($stream, $items) {
		$this->stream = $stream;
		$this->item_loot = $items;
	}

	function getAddOnLoot ($level){
		$tmp_loot_array = NULL;
		
		if($this->item_loot) { foreach($this->item_loot as $item) {
			$quest = "SELECT feed_items.*, feed.*"
		     . " FROM feed_items, feed"
			 . " WHERE feed_items.item_id='" . $item['item_id'] . "'"
			 . " AND feed.feed_id=feed_items.feed_id"
			 . " AND feed.level >= '$level'";
		
			$feed_loot = mysqli_query($this->stream, $quest);
			$feeds = NULL;	
			if($feed_loot) {
				while($loot=$feed_loot->fetch_assoc()) { $feeds[] = $loot; }
			}
			$tmp_loot_array[] = $this->mergeLabels($feeds, $item);
		} }
		$this->item_loot = $tmp_loot_array;
		return $this->item_loot;
	}
	
	function mergeLabels ($feeds, $item){
		$item['feeds'] = $feeds;
		return $item;
	}
}

class addonPostLabelHandler {
	function __construct ($stream) {
		$this->stream = $stream;
		$this->DEFAULT_USER_LEVEL = 3;
		$this->DEFAULT_MAX_FILESIZE = 10485760; //10MB
		$this->addOns = true;
	}
	
	function handleAddOnPost ($itemManager) {
		global $CONFIG;
		$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
		$count = $CONFIG['item_count'];
		
		global $client;
		$user_id = $client->user_serial;
		$user_level = $client->level;

		if(isset($_POST['delete'])) {
			$this->deleteItemLabel($_POST['delete']);
		}
		
		$active = isset($_POST['feed']);
		if($active) {
				switch ($_POST['feed']) {
				case 'new':
					$feed_id = $this->newLabel($user_id, $_POST['name'], "", 3, NULL);
					$this->addItemLabel($user_id, $_POST['id'], $feed_id);	
					header("Location: ./?feed_id=" . $feed_id . "&name=" . $_POST['name']);
					break;
				case 'add':
					$this->addItemLabel($user_id, $_POST['id'], $_POST['feed_id']);
					header("Location: ./?id=" . $_POST['id']);
					break;
				case 'new-child':
					$feed_id = $this->newLabel($user_id, $_POST['name'], "", 3, $_POST['parent-feed']);
					header("Location: ./?feed_id=" . $feed_id . "&name=" . $_POST['name']);
					break;
				case 'remove':
					$this->removeItemLabel($user_id, $_POST['item_id'], $_POST['feed_id']);
					break;
				case 'display':
					$feed_id = $this->updateLabelDisplay($_POST['display_id'], $_POST['feed_id']);
					header("Location: ./?feed_id=" . $feed_id . "&name=" . $_POST['name']);
					break;
			}
		} else if(isset($_POST['itc_add_item_feed'])){
				$item_id = $itemManager->handleItemUpload($client);
				
				if($itemManager->insertOk == "1" && isset($item_id)) {
					$itemManager->insertUserItem($client->user_serial, $item_id, 3);
					$this->addItemLabel($user_id, $itemManager->item_id, $_POST['itc_add_item_feed']);
				}
				header("Location: ./?feed_id=" . $_POST['itc_add_item_feed']);
		} else if(isset($_POST['itc_feed_edit'])){ 
				$this->purgeLabel($_POST['feed_id']);
				header("Location: ./");
		} else if(isset($_POST['itc_feed_img'])){
				$this->handleLabelUpload($client);
				header("Location: ./?feed_id=" . $_GET['feed_id']);
		} else if (isset($_GET['feed_id'])){
			if(isset($_POST['itc_feed_name'])) {
				$owner = ($_POST['user_id'] == $user_id) ? $user_id : false;	
				if($owner) { $this->changeLabelName($_POST['itc_feed_name'], $_GET['feed_id']);
						header("Location: ./?feed_id=" . $_GET['feed_id'] . "&name=" .  $_POST['itc_feed_name']);
				}		
			} 

			$itemManager->meta['feed'] = $this->getLabel($_GET['feed_id']);
			$itemManager->meta['title'] = $itemManager->meta['feed']['name'];
			$itemManager->meta['feed']['feed_item_class'] = $this->getAddonClasses($itemManager->meta['feed']['feed_id']);
			$itemManager->meta['feed']['display_class'] = $this->getLabelDisplayClasses($user_level);
			
			$display_id = $itemManager->meta['feed']['display_id'];
			$itemManager->meta['feed']['display_type'] = $itemManager->meta['feed']['display_class'][$display_id]['display_type'];
						
			// SHOULD BE USED BY OVERRIDE ONLY
			if (isset($itemManager->meta['feed']['related'])) { 
				$itemManager->meta['feed']['related'] = $this->getChildLabelItems($itemManager->meta['feed']['related'], 0, 5, $user_level);
			}
			
			if(!isset($_POST['page_id']) && !isset($itemManager->meta['feed']['feed_page'])) { 	
				if(isset($_GET['id'])) { $itemManager->items = $itemManager->getItemById($_GET['id']); }
				else { $itemManager->items = $this->getLabelItems($_GET['feed_id'], $start, $count, $user_level); }
				return "active"; 
			}
		}
		
	}
		
	function changeLabelName ($new_name, $feed_id) {
		$stream = $this->stream;
		$input = "UPDATE feed SET name='$new_name' WHERE feed_id='$feed_id'";
		$query = $stream->query($input);
	}

	function updateLabelDisplay($display_id, $feed_id) {
		$stream = $this->stream;
		$input = "UPDATE feed SET display_id='$display_id' WHERE feed_id='$feed_id'";
		$query = $stream->query($input);
		
		return $feed_id;
	}
	
	function purgeLabel ($feed_id) {
		$stream = $this->stream;
		
		$user_feeds = "DELETE FROM user_feeds WHERE feed_id='$feed_id'";
		mysqli_query($stream, $user_feeds);

		$item_feeds = "DELETE FROM feed_items WHERE feed_id='$feed_id'";
		mysqli_query($stream, $item_feeds);
				
		$feed = "DELETE FROM feed WHERE feed_id='$feed_id'";
		mysqli_query($stream, $feed);
	}
	
	function deleteItemLabel ($item_id) {
		$stream = $this->stream;

		$item_feeds = "DELETE FROM feed_items WHERE item_id='$item_id'";
		mysqli_query($stream, $item_feeds);
	}
	
	function getLabel($feed_id) {
		$feed_quest = "SELECT feed.* FROM feed WHERE feed_id='$feed_id'";
		$feed_loot_return = mysqli_query($this->stream, $feed_quest);	
		$feed_loot = $feed_loot_return->fetch_assoc();
		
		$count_quest = mysqli_query($this->stream, "SELECT * FROM feed_items WHERE feed_id='$feed_id'");
		$feed_loot['total'] = mysqli_num_rows($count_quest);
		
		$related_quest = "SELECT feed.* FROM feed WHERE parent_id='$feed_id' AND feed_id != '$feed_id'";
		$related_loot_return = mysqli_query($this->stream, $related_quest);
		
		if($feed_loot['parent_id']) {
		     $parent_id = $feed_loot['parent_id'];
		     $parent_quest = "SELECT feed.* FROM feed WHERE feed_id='$parent_id'";
		     $parent_loot_return = mysqli_query($this->stream, $parent_quest);
		     $feed_loot['parent'] = $parent_loot_return->fetch_assoc();		     
		}

		while($related_loot = $related_loot_return->fetch_assoc()) {
			$feed_loot['related'][] = $related_loot;
		}

		return $feed_loot;
	}

	function getChildLabels($parent_id) {
		$feed_quest = "SELECT * FROM feed WHERE parent_id='$parent_id'";
		$feed_loot_return = mysqli_query($this->stream, $feed_quest);
		while($feed_loot = $feed_loot_return->fetch_assoc()) {
			$top_feeds[] = $feed_loot;
		}
		
		if(!isset($top_feeds)) { return; }

		foreach($top_feeds as $feeds) {
			$feed_id = $feeds['feed_id'];
			$related_quest = "SELECT * FROM feed WHERE parent_id='$feed_id' AND feed_id != '$feed_id' AND feed_id != 0";
			$related_loot_return = mysqli_query($this->stream, $related_quest);
			while($row = $related_loot_return->fetch_array(MYSQLI_ASSOC)) {
				   $feeds['related'][] = $row; 
			}
			$feeds_array[] = $feeds;
		}
		return $feeds_array;
	}
	
	function getChildLabelItems($feeds, $start, $count, $level) {
		if(!$feeds) { return; }
			
		$feeds_array = NULL;
		foreach($feeds as $feed) {
			$quest = "SELECT feed_items.*, item.*, user_items.*"
				. " FROM feed_items, item, user_items"
				. " WHERE feed_items.feed_id='" . $feed['feed_id'] . "'"
				. " AND feed_items.item_id=item.item_id"
				. " AND item.item_id=user_items.item_id"
				. " ORDER BY feed_items.date DESC"
				. " LIMIT $start, $count";

			$items_loot = mysqli_query($this->stream, $quest);
			
 			$feed['items'] = NULL;
			if($items_loot) {
				while($loot=$items_loot->fetch_assoc()) { 
					$feed['items'][] = $loot;
				}
				$addon_class = new addonItemProfileRequest($this->stream, $feed['items']);
				$feed['items'] = $addon_class->getAddOnLoot();
			}
			$addon_request = new addonItemLabelRequest($this->stream, $feed['items']);
			$feed['items'] = $addon_request->getAddOnLoot($level);
			
			$feeds_array[] = $feed;
		}
		return $feeds_array;
	}
		
		
	function getLabelItems($feed_id, $start, $count, $level) {
		if(!$feed_id) { return; }
		
		$feed_quest = "SELECT feed.* FROM feed WHERE feed_id='$feed_id' AND feed.level > $level";
		$feed_loot_return = mysqli_query($this->stream, $feed_quest);	
		$feed_loot = $feed_loot_return->fetch_assoc();
		
		$quest = "SELECT feed_items.*, item.*, user_items.*"
			. " FROM feed_items, item, user_items"
			. " WHERE feed_items.feed_id='" . $feed_id . "'"
			. " AND feed_items.item_id=item.item_id"
			. " AND item.item_id=user_items.item_id"
			. " ORDER BY feed_items.date DESC"
			. " LIMIT $start, $count";
		 
		$items_loot = mysqli_query($this->stream, $quest);
		$item_loot_array = NULL;
		if($items_loot) {
			
			while($loot=$items_loot->fetch_assoc()) { 
				$item_loot_array[] = $loot;
			}
			$addon_class = new addonItemProfileRequest($this->stream, $item_loot_array);
			$item_loot_array = $addon_class->getAddOnLoot();
		}
							
		$addon_request = new addonItemLabelRequest($this->stream, $item_loot_array);
		$item_loot_array = $addon_request->getAddOnLoot($level);
		
		$addon_request = new addonItemReplyRequest($this->stream, $item_loot_array);
		$item_loot_array = $addon_request->getAddOnLoot($level);
		
		$addon_request = new addonItemFavoriteRequest($this->stream, $item_loot_array);
		$item_loot_array = $addon_request->getAddOnLoot($level);
		
		$addon_request = new addonItemGalleryRequest($this->stream, $item_loot_array);
		$item_loot_array = $addon_request->getAddOnLoot($level);		
				
		$addon_request = new addonItemAudiofeedRequest($this->stream, $item_loot_array);
		$item_loot_array = $addon_request->getAddOnLoot($level);
		return $item_loot_array;
	}
		
	function getAddonClasses($feed_id) {
		
		//check for addon feed class
		$check= "SELECT addon_class.class_id, addon_feed.feed_id, feed.feed_id, item_class.*, item_nodes.*"
			. " FROM addon_class, addon_feed, feed, item_class, item_nodes"
			. " WHERE addon_class.addon_id=addon_feed.addon_id"
			. " AND addon_feed.feed_id=feed.feed_id"
			. " AND feed.feed_id=" . $feed_id
			. " AND item_nodes.class_id=item_class.class_id"
			. " AND item_class.class_id=addon_class.class_id";
					
		$class_loot = mysqli_query($this->stream, $check);
		$class_loot_array = NULL;
		if($class_loot) {
			while($class=$class_loot->fetch_assoc()) {
				$class_id = $class['class_id'];		
				if(!isset($class_loot_array[$class_id])) {
					$class_loot_array[$class_id]['class_name'] = $class['class_name'];
					$class_loot_array[$class_id]['class_id'] = $class_id;
				    $class_loot_array[$class_id]['types'] = array();
				    $class_loot_array[$class_id]['ext'] = array();				
					$class_loot_array[$class_id]['nodes'] = array();
				}
				$class_loot_array[$class_id]['nodes'][] = $class;
			}
			
			if($class_loot_array) {
				foreach($class_loot_array as $loot_array) {
					$class_id = $loot_array['class_id'];				
					$type_quest = "SELECT * FROM item_type"
						. " WHERE class_id='" . $class_id . "'";
						
					$type_loot = mysqli_query($this->stream, $type_quest);				
					if($type_loot) {
						while($type=$type_loot->fetch_assoc()) {
							array_push($class_loot_array[$class_id]['types'], $type['file_type']);
							array_push($class_loot_array[$class_id]['ext'], $type['ext']);
						}
					}
				}
			}
		}
		
		if($class_loot_array) { reset($class_loot_array); }
		return $class_loot_array;
	}
			
	function getLabelDisplayClasses($level) {
		
		$quest = "SELECT * FROM feed_display";
			
		$class_loot = mysqli_query($this->stream, $quest);
		$class_loot_array = [];
		if($class_loot && mysqli_num_rows($class_loot) > 0) {
			while($loot = $class_loot->fetch_assoc()) {
				$display_id = $loot['display_id'];
				$class_loot_array[$display_id] = $loot;
			}
			return $class_loot_array;
		}
	}
	
	function newLabel ($owner_id, $name, $feed_img, $level, $parent_id) {
		if($name) {
			$quest = "INSERT INTO feed (owner_id, name, feed_img, parent_id, level) VALUES('$owner_id', '$name', '$feed_img', '$parent_id', '$level')";
			$success = mysqli_query($this->stream, $quest);
			if($success) { 
				$feed_id = mysqli_insert_id($this->stream);
				$user_quest = "INSERT INTO user_feeds (user_id, feed_id) VALUES('$owner_id', '$feed_id')";
				$success = mysqli_query($this->stream, $user_quest);
				return $feed_id;
			}
		}
	}
		
	function addItemLabel ($user_id, $item_id, $feed_id) {
		$check_quest = "SELECT feed_items.* FROM feed_items WHERE item_id='$item_id' AND feed_id='$feed_id'";
		$match = mysqli_query($this->stream, $check_quest);
		if(!$match->fetch_assoc()) {
			$quest = "INSERT INTO feed_items (user_id, item_id, feed_id, date) VALUES('$user_id', '$item_id', '$feed_id', '" . date('Y-m-d h:i:s') . "')";
			$success = mysqli_query($this->stream, $quest);
		}
	}
		
	function removeItemLabel ($user_id, $item_id, $feed_id) {
		$quest = "DELETE FROM feed_items WHERE item_id='$item_id' AND feed_id='$feed_id' AND user_id='$user_id'";
		$success = mysqli_query($this->stream, $quest);
	}

	function changeLabelImage ($new_image, $feed_id) {
		$stream = $this->stream;
		$input = "UPDATE feed SET feed_img='$new_image' WHERE feed_id='$feed_id'";
		$query = $stream->query($input);
	}
	
	function handleLabelUpload($client) {
		 if (isset($_POST['itc_feed_img'])) {
			$insertOk = "1";
			$target_dir = "files/feeds/";
			$filesize = $this->DEFAULT_MAX_FILESIZE;
			$ext = ["jpg", "jpeg", "png", "gif"];
			$file_extensions = $ext;

			 if(isset($_FILES["itc_feed_upload"])) {
				$tmp_file = new uploadManager(
					$_FILES["itc_feed_upload"],
					$target_dir,
					$filesize,
					$file_extensions);

				$tmp_file->handleUploadRequest();
				$tmp_file->uploadFile();
				$file = $tmp_file->target_file_name;
				if($tmp_file->uploadOk == "0") {
					echo "FAILED to upload: " . $tmp_file->errorStatus; 
					$insertOk = "0";
				} $message = $tmp_file->errorStatus;
			}

			if($insertOk && isset($_POST['feed_id'])) {
				$this->changeLabelImage($file, $_POST['feed_id']);
			}
		}
	}
}

class addonProfileLabelRequest {
	function __construct ($stream, $profile, $user_id) {
		$this->stream = $stream;
		$this->profile_loot = $profile;
		$this->user_id = $user_id;
	}
	
	function getAddOnLoot ($user_level){
		$tmp_loot_array = NULL;
		$quest = "SELECT SQL_CALC_FOUND_ROWS user_feeds.*, feed.*"
		 . " FROM user_feeds, feed"
		 . " WHERE user_feeds.user_id='" . $this->user_id . "'"
		 . " AND feed.feed_id=user_feeds.feed_id"
		 //. " AND feed.parent_id=''"
		 . " AND feed.level >'$user_level'";
		 
		$feed_loot = mysqli_query($this->stream, $quest);		
		$feeds = NULL;	
		if($feed_loot) {
			$count_quest = mysqli_query($this->stream, "SELECT FOUND_ROWS() AS count")->fetch_assoc();
			while($loot=$feed_loot->fetch_assoc()) { 
				$loot['total'] = $count_quest['count'];
				$feeds[] = $loot;
			}
		}
		$this->profile_loot['feeds'] = $feeds;
		return $this->profile_loot;
	}
}
?>
