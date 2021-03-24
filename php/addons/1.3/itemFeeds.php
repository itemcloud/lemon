<?php 
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

class itemFeeds {
	function __construct () {
		$this->addon_name = 'Item Feeds';
		$this->addon_version = '1.0.2';
		$this->default_img = 'default.png';
		$this->featured_feed = false;
		$this->add_new = true;
	}
	
	function setActions () {
		global $actions;
		$actions['post-handler'][] = 'addonPostFeedHandler';
		$actions['profile-request'][] = 'addonProfileFeedRequest';
		$actions['user-profile-request'][] = 'addonUserProfileFeedRequest';
		$actions['page-banner-display'][] = 'addonProfileFeedDisplay';
		$actions['item-display'][] = 'addonItemFeedDisplay';
		$actions['item-request'][] = 'addonItemFeedRequest';

		$actions['banner-display'][] = 'addonBannerFeedsDisplay';
		$actions['page-display'][] = 'addonFeedPageDisplay';
	}
}

//Add to global $addOns variable
//$addOns[] = 'itemFeeds';

class addonFeedPageDisplay extends itemFeeds  {
	
	function feedBanner() {
		return;
	}
	
	function update($pageManager) {
		$this->pageManager = $pageManager;
		$this->actions = $pageManager->actions;
		global $client;
		global $_ROOTweb;
		
		$index = $pageManager->index;
		$page = "";	$banner = ""; $left = ""; $bottom = "";		
		if(isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) ) {
			$item_info_limit = 2800;
			$feed = $pageManager->meta['feed'];			
			$page =  $pageManager->displayItems('page', $item_info_limit);

			$page .= "<div class=\"clear\"></div>";
		} elseif(isset($_GET['feed_id']) && !isset($pageManager->meta['active_page'])) {
			$profile_feeds = (isset($client->profile)) ? $client->profile['feeds'] : [];
			
			$feed = isset($pageManager->meta['feed']) ? $pageManager->meta['feed'] : NULL;
			$feed_owner = ($feed['owner_id'] == $client->user_serial) ? $feed['owner_id'] : NULL;
			
			$pageManager->box_class = $feed['display_type'];
			$folder = "files/feeds/";
			$file_dir = $_ROOTweb . $folder;
			
			$feed_img_src = ($feed['feed_img']) ? $file_dir . $feed['feed_img'] : $file_dir . 'default.png';
				
			$imageRollover = "changeImageRollover";
			$feed_img = "<div class='user'";
			if($feed_owner) { $feed_img .= " onmouseover=\"domId('$imageRollover').style.display='block';\" onmouseout=\"domId('$imageRollover').style.display='none'\""; }
			$feed_img .= " style='margin: 0px 20px 20px 0px; background-image: url(" . $feed_img_src  . ")'>";
			if($feed_owner) { $feed_img .= "<div id=\"$imageRollover\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'\" style=\"display: none; width: 100%; height: 100%; text-align: center;\"><div style=\"margin-top: 25%; font-size: 2em\">&#8853;</div></div>"; }
			$feed_img .= "</div>";
			$feed_name = "<div style=\"font-size: 1.2em; cursor: pointer\" onclick=\"window.location='$_ROOTweb?feed_id=" . $feed['feed_id'] . "'\"><u>" . $feed['name'] . "</u></div>";
			
			$parent = "<a href=\"./\">Home</a> / ";
			if(isset($feed['parent'])) { $parent = "<a href=\"./?feed_id=" . $feed['parent']['feed_id'] . "\">" . $feed['parent']['name'] . "</a> / "; }

			$banner_change_img = "";
			$banner_change_img_btn = "";
			if($feed_owner) {			
				$banner_change_img = "<form enctype=\"multipart/form-data\" action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><div style=\"display: none; margin-top: 4px;\" id=\"itc_feed_image_form\"><input type=\"hidden\" name=\"itc_feed_img\" value=\"change\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $_GET['feed_id'] . "\"/><input type=\"file\" class=\"tools\" name=\"itc_feed_upload\" accept=\"image/jpeg,image/png,image/gif\"><input class=\"tools\" type=\"submit\" name=\"submit\" value=\"&#9989; UPLOAD\"></div></form>";
				$banner_change_img_btn = "<div id=\"show-form-button\" class=\"tools\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; this.style.display='none'\" style=\"margin: 4px 0px;\">" . "Change image" . "</div>";				
			}				
			$banner_img = "<div class=\"left feed-icon\">" . $feed_img . "</div>";
	
			$banner_name =  $parent . "<div id=\"itc_feed_name_form\" style=\"display: none;\">" . "<form action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input class=\"inline-block\" name=\"itc_feed_name\" value=\"" . $feed['name'] . "\"/><input class=\"tools\" type=\"submit\" name=\"submit\" value=\"&#9989; SAVE\"></div>";
			$banner_name .= "<div id=\"itc_feed_name\" style=\"font-size: 1.2em; display: inline-block\">" . $feed_name;
			$banner_name .= "</form></div>";
			
			$banner_edit = "";
			if($feed_owner){
				$banner_edit = " <span class=\"tools\" onclick=\"this.style.display='none'; domId('itc_feed_name').style.display='none'; domId('itc_feed_name_form').style.display='inline-block';\">&#9998; EDIT</span>";
			}		
				
			$banner_delete = ($feed_owner) ?			
				"<div class=\"inline-block\"><form action=\"./?itc_feed_edit=purge\" method=\"post\"><input type=\"hidden\" name=\"itc_feed_edit\" value=\"purge\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $feed['feed_id'] . "\"/><input class=\"tools\" type=\"submit\" name=\"submit\" value=\"&#9988; DELETE\"></form></div>"
				: "";

			//Show update feed display form 	
			$banner_display = "";		
			if($feed_owner && isset($feed['display_class'])) {
				$banner_display = "<div class=\"inline-block\"><form id='feedDisplayForm" . $feed['feed_id'] . "' action=\"?feed_id=" . $feed['feed_id'] . "\" method=\"post\">"
					  .  "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'>"
					  .  "<input type='hidden' name='name' value='" . $feed['name'] . "'>"					  
					  .  "<input type='hidden' name='feed' value='display'>";					  

				$banner_display  .= "<div class=\"float-left\" style=\"margin: 8px;\"><small>VIEW MODE:</small></div>";
												
				$banner_display  .= "<div class=\"float-left\">";
				$banner_display  .= "<select onchange=\"domId('feedDisplayForm" . $feed['feed_id'] . "').submit()\" id=\"display_id\" name=\"display_id\" class=\"item-dropdown\"> ";
				foreach($feed['display_class'] as $display_class) {
					$selected = ($display_class['display_id'] == $feed['display_id']) ? " selected" :  "";
					$banner_display  .= "<option value=\"" . $display_class['display_id'] . "\"$selected>" . $display_class['name'] . "</option>";
				}
				$banner_display  .= "</select>";
				$banner_display .= "</div>";
								
				$banner_display  .= "</form></div>";
			}		

			$banner = $banner_img;		
			$banner .= "<div class=\"center\">";
			$banner .= "<div>" . $banner_name . $banner_edit  . $banner_delete . "</div>" . $banner_display . $banner_change_img;
			
			$banner .= "<div class=\"clear\"></div>";
			$banner .= "</div>";
			
			if($feed_owner){
				$userTools = new addonItemFeedDisplay();
				$tmp_feeds[] = $feed;
				if(isset($profile_feeds) && isset($feed)) { 
					$profile_feeds = $userTools->mergeRemove($profile_feeds, $tmp_feeds); 
				}	
				
				$related = $userTools->addRelatedFeedTools($feed['feed_id'], 0);
			}	
					
			$start = 0;
			$max = 6;
			if(isset($feed['related'])) {
				$total = count($feed['related']);
				$related_feeds_browser = new feedBrowse($feed['related'], $start, $max, $total);
				$related_feeds = $related_feeds_browser->itemOutputHTML(NULL);
			}
			
			$related_tools = isset($related) ? $related : "";			
			$related_feeds = isset($related_feeds) ? $related_feeds : "";
			
			if($feed_owner){
				$omniBox = $pageManager->displayOmniFeedBox($pageManager, $pageManager->meta['feed'], $pageManager->items[0]['item_id']);
				$page .= "<div class=\"center\">" . $omniBox . "</div>";
			}			
			
			$item_info_limit = 2800;
			if($feed['display_type']  == 'box' || $feed['display_type']  == 'card' || $feed['display_type']  == 'slide') {
				$item_info_limit = 140;	
			}

			if($feed['display_type']  == 'slide') {
				$page .= "<div class=\"center\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit);
				$page .= "<div class=\"clear\"></div>";
				$page .= "<script>var feed_id = " . $feed['feed_id'] . ";</script>";	
				$page .= "</div>";
								
				$pageManager->section['items']['displayClass'] = "";
				
			} else if($feed['display_type']  == 'box') {
				$page .= "<div class=\"center col-2\">" . $pageManager->displayItemsGrid($feed['display_type'], $item_info_limit, 2);
				$page .= "<div id=\"more-items\"></div>";
				$page .= "<div class=\"clear\"></div>";
				$page .= "<script>var feed_id = " . $feed['feed_id'] . ";</script>";	
				$page .= "</div>";
				
			} else if ($feed['display_type'] == 'list') {	
				$box_class = "page";
				$pageManager->section['items']['displayClass'] = "fixed-right";
				$tmp_items = $pageManager->items;
				$pageManager->uri_prefix = "?feed_id=" . $feed['feed_id'] . "&";	
				
				$itemDisplay = new ItemDisplay(reset($pageManager->items), $pageManager->ROOTweb, $box_class, $pageManager->user_id, 0, '');				
				if(isset($this->actions)) { runAddons($this->actions, $itemDisplay, 'item-display'); }					
				$itemDisplay->nodeOutput = $itemDisplay->nodeOutputHTML();
				$item_html = $itemDisplay->displayHTML();
				
				$page .= "<div class=\"center\" style=\"margin: auto\">" . $item_html;
				$page .= "</div>";
				$tmp_items = (isset($pageManager->meta['feed']['items'])) ? $pageManager->meta['feed']['items'] : $pageManager->items;
				$pageManager->items = $tmp_items;
				$left = "<div class=\"left\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit) . "</div>";
			} else if ($feed['display_type'] == 'topics') {	
				$box_class = "topics";
				$tmp_items = $pageManager->items;
				$pageManager->uri_prefix = "?feed_id=" . $feed['feed_id'] . "&";
				
				$itemDisplay = new ItemDisplay(reset($pageManager->items), $pageManager->ROOTweb, 'page', $pageManager->user_id, 0, '');				
				if(isset($this->actions)) { runAddons($this->actions, $itemDisplay, 'item-display'); }	
				
				$itemDisplay->nodeOutput = $itemDisplay->nodeOutputHTML();
				$item_html = $itemDisplay->displayHTML();
				
				$page .= "<div class=\"center\" style=\"margin: auto\">" . $item_html;
				$page .= "</div>";
				$tmp_items = (isset($pageManager->meta['feed']['items'])) ? $pageManager->meta['feed']['items'] : $pageManager->items;
				$pageManager->items = $tmp_items;
				$page .= "<div style=\"center\" class=\"center\"><div id=\"profile_menu\" class=\"bar menu\"><div class=\"link\" style=\"padding: 22px;\"><a><span class=\"name\">Recent</span></a></div>";
				if($related_feeds) { $page .= "<div class=\"link\" style=\"padding: 16px; border-left: 1px  solid #CCC\">Topics: " . $related_feeds. "</div>"; }
				$page .= "<div class=\"float-right\" style=\"padding: 12px\">" . $related_tools ."</div></div>"
					. $pageManager->displayItems($feed['display_type'], $item_info_limit) . "</div>";
			} else if ($feed['display_type'] == 'page') {			
				$page .= "<div class=\"center\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit);
				$page .= "<div id=\"more-items\"></div>";
				$page .= "<script>var feed_id = " . $feed['feed_id'] . ";</script>";	
				$page .= "<div class=\"clear\"></div>";
				$page .= "</div>";
			}  else  {	
				$page .= "<div class=\"center\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit);
				$page .= "<div class=\"clear\"></div>";
				$page .= "<script>var feed_id = " . $feed['feed_id'] . ";</script>";	
				$page .= "</div>";
			}

		}  else if (isset($_GET['browse'])) {	
			
			$browse_class = $_GET['browse'];
			$page = "<div class=\"center col-2\">";
			$column = 1;
			foreach($pageManager->meta['feeds'] as $feed) {
				$folder = "files/feeds/";
				$file_dir = $_ROOTweb . $folder;
				$feed_url = $_ROOTweb . "?feed_id=" . $feed['feed_id'];
				
				$feed_img_src = ($feed['feed_img']) ? $file_dir . $feed['feed_img'] : $file_dir . 'default.png';	
				$page .= "<div class=\"col--x$column\"><div class=\"box\"><div onclick=\"window.location='" . $feed_url . "'\" class=\"item\" style=\"background-position: center center; background-size: cover; background-image: url($feed_img_src)\">"
					. "<div class=\"nodes top-overlay\" style=\"min-height: 180px\"><div class=\"title\">" . $feed['name'] . "</div></div></div></div></div>";

				if($column % 2 == 0) {
					$column = 0;
				}	$column++;
			}
			$page .= "</div>";
		} else {
			if(isset($pageManager->meta['bottom-section']['items'])) {
				$pageManager->section['bottom']['output'] =  "<div class=\"right\">" . "<h2>" . $pageManager->meta['bottom-section']['name'] . "</h2>"
				. $pageManager->displayItemGrid($pageManager->meta['bottom-section']['items'], 1) . "</div>";
			}
		}
		$this->bannerDisplay = $this->feedBanner();
		if($page) {
			$pageManager->section['items']['output'] = $page;
		}
		if($banner) {
			$pageManager->section['top']['output'] =  $banner;
			$pageManager->section['top']['displayClass'] =  " feed-banner";
		}
		if($left) {
			$pageManager->section['left']['output'] =  $left;
		}
		
		$pageManager->pageOutput = "";
		$this->meta['active_page'] = true;
	}

	function displayOmniBox($pageManager, $feed, $item_id) {
		if(!$pageManager->classes) { return; }
		
		$feed_id = $feed['feed_id'];
		$classes = isset($feed['feed_item_class']) ? $feed['feed_item_class']: $pageManager->classes;
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController = new OmniFeedBox(" . $class_js_array . ", 'itemOmniBox');\n OmniController.set_active_feed('" . $feed_id . "');\n OmniController.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div class=\"center\">";
		$createForm .= "<div class=\"page\"><div class=\"item\" style=\"display: none; padding: 20px;\" id=\"itemOmniBox\">" . "</div></div>"
			. "<div class=\"inline-block\" style=\"display: inline-block\" onclick=\"domId('itemOmniBox').style.display='block'; this.style.display='none'\" style=\"width: auto; margin: 14px 0px; text-align: center; cursor: pointer\"><div class=\"tools\">+ <u>Add an Item</u></div></div>";
		$createForm .= $javascript_omni_box;
		$createForm .= "</div>";
		return $message . $createForm;
	}
}

class addonBannerFeedsDisplay extends  itemFeeds {
	
	function update ($banner) {
		$user = $banner->user;
		
		$start = (isset($_GET['banner-feeds'])) ? $_GET['banner-feeds'] : 0;
		$max = 6;
		$links = "";
		if(isset($user->profile['feeds'])) {
			$feeds = $user->profile['feeds'];	
			$total = (count($feeds) <= ($start + $max)) ? count($feeds) : ($start + $max);
			$links_browser = new feedBrowse($user->profile['feeds'], $start, $max, $total);
			$links = $links_browser->outputHTML();
		}
		$banner->links .= $links . $banner->links;
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
		
		$links = "<div id=\"nav_feed_links\" class=\"feed-icon\" style=\"$display_pages\">";		
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
		
		return "<div class=\"right\"><div class=\"menu\">" . $links ."</div></div>";
	}
	
	function itemOutputHTML ($index) {
		global $client;	
		global $_ROOTweb;
			
		if(!$this->feeds) {
			return;
		}
		$user_id = ($client->user_serial) ? $client->user_serial : 0;
		
		$feeds_js_array = json_encode($this->feeds);
		$javascript_feed_browser = "<script>itemFeedBrowser['$index'] = new feedBrowse(" . $feeds_js_array . ", '$index', $user_id, 'feed_browse_$index');\n " 
			. "itemFeedBrowser['$index'].class_text = 'tools feed_menu';</script>";
		$feedMenu = "<div style=\"display: inline-block\" id=\"feed_browse_$index\">";
			
		$start = $this->start;		
		$new_start = $this->start - $this->max;
		
		if($new_start >= 0) {
			$feedMenu .= "<div onclick=\"itemFeedBrowser['$index'].update(" . $new_start . ")\" class=\"float-left\">"
					. "<div class=\"tools\">" . "&#8943;" . "</div>"
					. "</div>";
		}
		
		$total = (count($this->feeds) <= ($this->start + $this->max)) ? count($this->feeds) : ($this->start + $this->max);	
		
		for($i = $start; $i < $total; $i++) {	
			$feed = $this->feeds[$i];
			
			$feed_img_src = ($feed['feed_img']) ? "files/feeds/" . $feed['feed_img'] : "files/feeds/" . 'default.png';
			
			$feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">";
			$feed_img .= "<img class=\"user\" src=\"" . $feed_img_src . "\"/>";
			$feed_img .= "</a>";
					
			$feed_name = "<div style=\"display: inline-block;\">";					
			
			$feed_window_launch = "window.location='" . $_ROOTweb . "?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "'; ";
			
			$feed_name .= "<div onclick=\"$feed_window_launch\" style=\"margin: 0px 8px 8px 8px\" class=\"inline-name\">" . $feed['name'] . "</div>";
			if(($feed['owner_id'] == $user_id) && $index) {
				//remove feed from item if owner
				$remove_button = "<div title='remove' style='display: inline-block'><form id='removeFormInline" . $i . $index . "' action='".$_SERVER['REQUEST_URI'] . "' method='post'>"
				. "<input type='hidden' name='item_id' value='" . $index . "'/>"
				. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>"
				. "<input type='hidden' name='feed' value='remove'/>"		
				. "<div class='inline-remove'>";

				$remove_button .= " <a onclick=\"domId('removeFormInline" . $i . $index . "').submit()\">x</a>";	
				$remove_button .= "</div>";
				$remove_button .= "</form></div>";
				
				$feed_name .= $remove_button;
			}	
			$feed_name .= "</div>";

			$feed_wrapper = "<div class=\"inline-block feed_menu\" >";
			$feed_wrapper .= $feed_img;		
			$feed_wrapper .= "<div class=\"feed_name_wrapper\"/>";
			$feed_wrapper .= "<div class=\"feed_name\"/>";
			$feed_wrapper .= $feed_name;
			$feed_wrapper .= "</div>";
			$feed_wrapper .= "</div>";
			$feed_wrapper .= "</div>";
			
			$feedMenu .= $feed_wrapper;
		}
		

		if($this->start + $this->max < count($this->feeds)) {
			$feedMenu .= "<div id=\"browse-feeds-button" . $i . $index . "\" style=\"display: none;\" onclick=\"itemFeedBrowser['$index'].update(" . ($this->start + $this->max) . ")\"  class=\"float-left tools feed_menu\">"
					. "<div class=\"tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
			$feedMenu .= "<script>itemFeedBrowser['$index']; domId('browse-feeds-button" . $i . $index . "').style.display='inline-block';</script>";
		}
		
		$feedMenu .= "</div>";
		$feedMenu .= $javascript_feed_browser;
		return $feedMenu; 
	}	

	function listOutputHTML ($index) {
		global $client;	
		global $_ROOTweb;	
		if(!$this->feeds) {
			return;
		}
		$user_id = ($client->user_serial) ? $client->user_serial : 0;
		
		$feeds_js_array = json_encode($this->feeds);
		$javascript_feed_browser = "<script>itemFeedBrowser['$index'] = new feedBrowse(" . $feeds_js_array . ", '$index', $user_id, 'feed_browse_$index');\n itemFeedBrowser['$index'].class_text=\"feed_item\"</script>";

		$feedMenu = "<div style=\"display: inline-block\" id=\"feed_browse_$index\">";
			
		$start = $this->start;		
		$new_start = $this->start - $this->max;	
		if($new_start >= 0) {
			$feedMenu .= "<div onclick=\"itemFeedBrowser['$index'].update(" . $new_start . ")\" class=\"\">"
					. "<div class=\"\">" . "&#8943;" . "</div>"
					. "</div>";
		}
		
		$total = (count($this->feeds) <= ($this->start + $this->max)) ? count($this->feeds) : ($this->start + $this->max);	
		
		for($i = $start; $i < $total; $i++) {	
			$feed = $this->feeds[$i];
			
			$feed_img_src = ($feed['feed_img']) ? "files/feeds/" . $feed['feed_img'] : "files/feeds/" . 'default.png';
			
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

			$feed_wrapper = "<div class=\"feed_item\">";
			$feed_wrapper .= $feed_img;		
			$feed_wrapper .= "<div class=\"feed_name_wrapper\"/>";
			$feed_wrapper .= "<div class=\"feed_name\"/>";
			$feed_wrapper .= $feed_name;
			$feed_wrapper .= "</div>";
			$feed_wrapper .= "</div>";
			$feed_wrapper .= "</div>";	
			
			$feedMenu .= $feed_wrapper;
		}
		

		if($this->start + $this->max < count($this->feeds)) {
			$feedMenu .= "<div id=\"browse-feeds-button" . $i . $index . "\" style=\"display: none;\" onclick=\"itemFeedBrowser['$index'].update(" . ($this->start + $this->max) . ")\"  class=\"\">"
					. "<div class=\"tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
			$feedMenu .= "<script>itemFeedBrowser['$index']; domId('browse-feeds-button" . $i . $index . "').style.display='inline-block';</script>";
		}
		
		$feedMenu .= "</div>";
		$feedMenu .= $javascript_feed_browser;
		return $feedMenu; 
	}

	function menuOutputHTML ($index) {
		global $client;	
		global $_ROOTweb;	
		if(!$this->feeds) {
			return;
		}
		$user_id = ($client->user_serial) ? $client->user_serial : 0;
		
		$feeds_js_array = json_encode($this->feeds);
		$javascript_feed_browser = "<script>itemFeedBrowser['$index'] = new feedBrowse(" . $feeds_js_array . ", '$index', $user_id, 'feed_browse_$index');\n itemFeedBrowser['$index'].class_text=\"\"</script>";

		$feedMenu = "<div style=\"display: none\" id=\"feed_browse_$index\">";
			
		$start = $this->start;		
		$new_start = $this->start - $this->max;	
		if($new_start >= 0) {
			$feedMenu .= "<div onclick=\"itemFeedBrowser['$index'].update(" . $new_start . ")\" class=\"\">"
					. "<div class=\"\">" . "&#8943;" . "</div>"
					. "</div>";
		}
		
		$total = (count($this->feeds) <= ($this->start + $this->max)) ? count($this->feeds) : ($this->start + $this->max);	
		
		for($i = $start; $i < $total; $i++) {	
			$feed = $this->feeds[$i];
			
			$feed_img_src = ($feed['feed_img']) ? "files/feeds/" . $feed['feed_img'] : "files/feeds/" . 'default.png';
			
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

			$feed_wrapper = "<div style=\"display: inline-block; border-left: 1px solid #DCDCDC\">";
			//$feed_wrapper .= $feed_img;			
			$feed_wrapper .= $feed_name;
			$feed_wrapper .= "</div>";
			
			
			$feedMenu .= $feed_wrapper;
		}
		

		if($this->start + $this->max < count($this->feeds)) {
			$feedMenu .= "<div id=\"browse-feeds-button" . $i . $index . "\" style=\"display: none;\" onclick=\"itemFeedBrowser['$index'].update(" . ($this->start + $this->max) . ")\"  class=\"\">"
					. "<div class=\"tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
			$feedMenu .= "<script>itemFeedBrowser['$index']; domId('browse-feeds-button" . $i . $index . "').style.display='inline-block';</script>";
		}
		
		$feedMenu .= "</div>";
		$feedMenu .= $javascript_feed_browser;
		return $feedMenu; 
	}	
}

class addonProfileFeedDisplay extends itemFeeds {
	
	function setFeeds($profile) {
		if(isset($profile['feeds'])) { return $profile['feeds']; }
		return;
	}
	
	function update ($pageManager) {
		$this->profile = (isset($pageManager->meta['profile'])) ? $pageManager->meta['profile'] : NULL;
		$this->feeds = $this->setFeeds($this->profile);
		
		if(isset($this->profile)) { 
			$profileFeeds = "";
			$id = '0';
			$start = 0;
			$max = 6;

			$total = count($this->profile['feeds']);
			$profile_feeds_browser = new feedBrowse($this->profile['feeds'], $start, $max, $total);
			$profile_feeds = $profile_feeds_browser->listOutputHTML($id);
			$banner = "";
		
			if($profile_feeds) {
				$profileFeeds = "<div id=\"profile_menu\" class=\"bar profile-menu\"><div style=\"width: auto; margin: 0px 20px;\">" 
				. "<div onClick=\"window.location='./?user=" . $this->profile['user_id'] . "'\" style=\"margin-left: 8px; margin: 8px 20px; font-weight: bold; display: inline-block; cursor: pointer\">Recent</div>" 
				. "<div style=\"display: inline-block;\" onMouseOver=\"domId('feed_browse_wrapper_$id').style.display='inline-block'\" onMouseOut=\"domId('feed_browse_wrapper_$id').style.display='none'\">" 
				. "<div onClick=\"window.location='./?browse=feeds&user=" . $this->profile['user_id'] . "'\" style=\"margin-left: 18px; padding: 20px; border-left: 1px solid #CCC; font-weight: bold; display: inline-block\">Feeds</div>" 
					. "<div style=\"position: relative; left: 18px;\"><div id=\"feed_browse_wrapper_$id\" class=\"browse-feeds\" style=\"display: none; position: absolute; width: 240px; padding: 4px;\">" 				
						. $profile_feeds 
					. "</div></div>"
					. "</div></div>"
				
				. "</div></div></div>";
				$banner = $pageManager->displayWrapper('div', 'block', 'block', $profileFeeds);
			}
			
			$message = "";
			$banner .= $pageManager->displayWrapper('div', 'float-right', 'inline-block', $message);	
			
			$date = new DateService($this->profile['date']); $n = "\n";
			$user_name = ($this->profile['user_name']) ? $this->profile['user_name'] : "New Member (" . chopString($this->profile['date'], 4, '') . ")";
			$member_since = "<div style=\"margin: 20px;\" onclick=\"document.body.scrollTop = 0; document.documentElement.scrollTop = 0;\"><h2 title=\"Back to Top\">" . $user_name . "</h2><small>MEMBER SINCE</small><br />" . $date->date_time . "</div>$n";
			$member_since = $pageManager->displayWrapper('div', 'center', 'margin-auto tools', $member_since);	
			
			$pageManager->displayClass = " profile";
			$pageManager->section['top']['output'] .= $banner;
			$pageManager->section['bottom']['displayClass'] = " fixed";
			$pageManager->section['bottom']['output'] .= $member_since;
		}
	}
}

class addonItemFeedDisplay extends itemFeeds {

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

	function addRelatedFeedTools ($parent_id, $_id) {
		if($this->add_new == false) {
			return "";
		}
		
		$chooseFeed = "";
		$newFeed = "<div style='position: relative; float: right'><div id='feedAddNewFeed" . $_id . "' style='position: absolute; top: 40px; right: 0px; display: block'><input id='feedPostName" . $_id . "' name='name' class='form' autofocus></div></div>";
		$newFeed .= "<input id='feedParentFeed" . $_id . "' type='hidden' name='parent-feed' value='$parent_id'>"; //PARENT Feed
		
		$newFeed .= "<input id='feedPostFeed" . $_id . "' type='hidden' name='feed' value='new-child'>"; //DEFAULT: POSTING TO 'NEW' Feed IN POST
		
		$tools = "<div style='float: right;'>" 
			. "<div id='feedAdd" . $_id . "' " . " onClick=\"this.style.display='none'; domId('feedAddForm" . $_id . "').style.display='inline-block'\" class=\"tools\"><div class=\"tools_txt\">&#43;</div></div>"
			. "<div id='feedAddForm" . $_id . "' style='display: none'>"
				. "<form id='feedFeedForm" . $_id . "' action=\"" .$_SERVER['REQUEST_URI']. "\" method=\"post\">"
				. "<input type='hidden' name='parent_id' value='" . $parent_id . "'>"
				. "<div onClick=\" domId('feedAdd" . $_id . "').style.display='inline-block'; domId('feedAddForm" . $_id . "').style.display='none'\" class=\"tools inline-block\"><div class=\"tools_txt\">&#8722;</div></div>"				
				. "<div style='display: inline-block; width: 12px; margin-right: 4px'>" . $chooseFeed . "</div>"	
				. $newFeed
				. "<div onclick=\"if(domId('feedPostName" . $_id . "').value) { domId('feedFeedForm" . $_id . "').submit(); } else { domId('feedPostName" . $_id . "').focus(); }\" style=\"font-size: 12px;\" class=\"tools float-right\">&#9989; CREATE</div>"	
				. "</form>"
			. "</div>" 
			. "</div>";

		return $tools;
	}

	function addRelatedFeedTools_small ($parent_id, $_id) {
		if($this->add_new == false) {
			return "";
		}
		
		$chooseFeed = "";
		$newFeed = "<div id='feedAddNewFeed" . $_id . "' style='position: relative; display: block'><input name='name' class='form_small' autofocus></div>";
		$newFeed .= "<input id='feedParentFeed" . $_id . "' type='hidden' name='parent-feed' value='$parent_id'>"; //PARENT Feed
		$newFeed .= "<input id='feedPostFeed" . $_id . "' type='hidden' name='feed' value='new-child'>"; //DEFAULT: POSTING TO 'NEW' Feed IN POST
		
		$tools = "<div style='display: inline-block'>" 
			. "<div id='feedAdd" . $_id . "' " . " onClick=\"this.style.display='none'; domId('feedAddForm" . $_id . "').style.display='inline-block'\"><div class=\"tools_txt\">&#43;</div></div>"
			. "<div id='feedAddForm" . $_id . "' style='display: none'>"
				. "<form id='feedFeedForm" . $_id . "' action=\"" .$_SERVER['REQUEST_URI']. "\"  method=\"post\">"
				. "<input type='hidden' name='parent_id' value='" . $parent_id . "'>"
				. "<div style=\"float: left\" onClick=\"domId('feedAdd" . $_id . "').style.display='inline-block'; domId('feedAddForm" . $_id . "').style.display='none'\"><div class=\"tools_txt\">&#8722;</div></div>"				
				. "<div style='display: inline-block; width: 12px; margin-right: 4px'>" . $chooseFeed . "</div>"
				. "<div onclick=\"domId('feedFeedForm" . $_id . "').submit()\" style=\"display: inline-block; font-size: 12px;\" class=\"dark tools\">&#9989; SAVE</div>"		
				. $newFeed
				. "</form>"
			. "</div>" 
			. "</div>";

		return $tools . "<div class=\"clear\"></div>";
	}
		
	function userFeedTools ($profile_feeds, $item_id, $dom_id) {
		if($this->add_new == false && !$profile_feeds) {
			return "";
		}
		if($profile_feeds) { //CHECK FOR USER PROFILE Feeds
			$chooseFeed = "<select onchange=\"if(!this.value){"
			. " domId('itemAddNewFeed" . $dom_id. "').style.display='block';"
			. " domId('itemPostFeed" . $dom_id . "').value = 'new';"
			. " domId('itemAddNewFeed" . $dom_id . "').focus();"
			. " } else { "
			. " domId('itemAddNewFeed" . $dom_id . "').style.display='none';"
			. " domId('itemPostFeed" . $dom_id . "').value = 'add'; }"
			. "\" name=\"feed_id\" id=\"feed_select" . $dom_id . "\" class=\"item-dropdown\">";
					
			foreach($profile_feeds as $feed) {
				$chooseFeed .= "<option value='". $feed['feed_id'] . "'>". $feed['name'] . "</option>";
			}
			if($this->add_new != false) {
				$chooseFeed .= "<option value=''>+ New</option>";
			}
			$chooseFeed .= "</select>";
			$chooseFeed .= "<div id='itemAddNewFeed" . $dom_id . "' style='position: relative; display: none'><input name='name' class='form' autofocus></div>";	
			$chooseFeed .= "<input id='itemPostFeed" . $dom_id . "' type='hidden' name='feed' value='add'>"; //DEFAULT: POSTING TO 'ADD' Feed IN POST
		} else {
			$chooseFeed = "<div id='itemAddNewFeed" . $dom_id . "' style='position: relative'><input name='name' class='form' autofocus></div>";	
			$chooseFeed .= "<input id='itemPostFeed" . $dom_id . "' type='hidden' name='feed' value='new'>"; //DEFAULT: POSTING TO 'NEW' Feed IN POST
		}
		
		$tools = "<div style='float: right;'>" 
			. "<div id='itemAdd" . $dom_id . "' " . " onClick=\"this.style.display='none'; domId('itemAddForm" . $dom_id . "').style.display='inline-block'\" class=\"tools feed_menu\"><div class=\"tools_txt\">&#43;</div></div>"
			. "<div id='itemAddForm" . $dom_id. "' style='display: none'>"
				. "<form id='itemFeedForm" . $dom_id . "'  action=\"" .$_SERVER['REQUEST_URI']. "\" method=\"post\">"
				. "<input type='hidden' name='id' value='" . $item_id . "'>"
				. "<div style=\"float: left\" onClick=\"domId('itemAdd" . $dom_id . "').style.display='inline-block'; domId('itemAddForm" . $dom_id . "').style.display='none'; if(domId('feed_select" . $dom_id . "')) { domId('feed_select" . $dom_id . "').selectedIndex = 0; }\" class=\"tools feed_menu\"><div class=\"tools_txt\">&#8722;</div></div>"				
				. "<div style='position: absolute; z-index: 10; '><div style='position: absolute; top: 32px; width: 140px; margin-right: 4px;'>" . $chooseFeed . "</div></div>"
				. "<div onclick=\"domId('itemFeedForm" . $dom_id . "').submit()\" style=\"display: inline-block; font-size: 12px;\" class=\"dark tools\">&#9989; SAVE</div>"		
				. "</form>"
			. "</div>" 
			. "</div>";

		return $tools;
	}
	 
	function update ($itemDisplay) {
		$item_id = $itemDisplay->item['item_id'];
		global $client;
		
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
				$feed_link = "<div id=\"removeItemFeed" . $index . $item_id . "\" style='display: none'>"
					. "<form id='removeForm" . $index . $item_id . "' action='?id=" . $item_id. "' method='post'>" 
					. "<input type='hidden' name='item_id' value='" . $item_id . "'/>" 
					. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>" 
					. "<input type='hidden' name='feed' value='remove'/>"				
					. "<div style='padding: 0px 6px 0px 2px;'>";
					
					if($feed['owner_id'] == $client->user_serial) {
						$feed_link .= " <a onclick=\"domId('removeForm" . $index . $item_id . "').submit(); alert('remove');\" class=\"tools_txt\">X</a>";	
					}		
					
					$feed_link .= "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">"
						. $feed['name']
						. "</a>";

				$feed_link .= "</div>";
				$feed_link .= "</form>";
				$feed_link .= "</div>";
				
				//Feed output
				$feed_output = "<div class=\"tools\" style=\"border-radius: 24px; padding: 2px\""
					. " onmouseover=\"domId('removeItemFeed" . $index . $item_id . "').style.display='inline-block'\""
					. " onmouseout=\"domId('removeItemFeed" . $index . $item_id . "').style.display='none'\""
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
			$item_feed_output .= "<script>try { itemFeedBrowser['" . $index . "'].set_active_item_id(" . $itemDisplay->item['item_id'] . "); } catch (e) { }</script>";
		}
		
		$tool_output = "";
		if($itemDisplay->user_id && isset($client->profile)) {
				$profile_feeds = $client->profile['feeds'];
				if($profile_feeds && isset($itemDisplay->item['feeds'])) { 
					$profile_feeds = $this->mergeRemove($profile_feeds, $itemDisplay->item['feeds']); 
				}
				$dom_id = $item_id . "_" . rand(10, 100);
				$tool_output = $this->userFeedTools($profile_feeds, $item_id, $dom_id);
				$item_feed_output .= $tool_output;
		}
		$metaOutput = "<div class='float-left'>" . $item_feed_output . "</div>";
		$itemDisplay->userTools = $metaOutput . $itemDisplay->userTools;
		$itemDisplay->output = $itemDisplay->displayHTML();
	}
	
	function titleDisplayHTML () {
		$title_html = "<div class=\"title\" onclick=\"window.location='./?id=" . $this->item_id . "';\">" . $this->title . "</div>";
		return $title_html;
	}
}


class addonItemFeedRequest extends itemFeeds {
	function update ($itemManager){
		$this->stream = $itemManager->stream;
		$this->item_loot = $itemManager->item_loot;
		$level = $itemManager->client->level;
		
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
			$tmp_loot_array[] = $this->mergeFeeds($feeds, $item);
		} }
		$this->item_loot = $tmp_loot_array;
		
		//IMPORTANT: SET ITEM INDEX FOR FEEDS
		$itemManager->item_index  = 0;
		if(isset($_GET['id']) && is_int($_GET['id'])) { 
			$itemManager->item_index = array_search($_GET['id'], array_column($tmp_loot_array, 'item_id'));	
		}
		
		$itemManager->item_loot = $this->item_loot;
		return $this->item_loot;
	}
	
	function mergeFeeds ($feeds, $item){
		$item['feeds'] = $feeds;
		return $item;
	}
}

class addonPostFeedHandler extends itemFeeds {
	
	function update ($itemManager) {
		$this->stream = $itemManager->stream;
		$this->DEFAULT_USER_LEVEL = 3;
		$this->DEFAULT_MAX_FILESIZE = 10485760; //10MB
		$this->addOns = true;
		
		global $CONFIG;
		$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
		$count = $CONFIG['item_count'];
		
		global $client;
		$user_id = $client->user_serial;
		$user_level = $client->level;

		if(isset($_POST['delete'])) {
			$this->deleteItemFeed($_POST['delete']);
		}
		
		$active = isset($_POST['feed']);
		if($active) {
				switch ($_POST['feed']) {
				case 'new':
					$feed_id = $this->newFeed($user_id, $_POST['name'], "", 3, NULL);
					$this->addItemFeed($user_id, $_POST['id'], $feed_id);	
					header("Location: ./?feed_id=" . $feed_id . "&name=" . $_POST['name']);
					break;
				case 'add':
					$this->addItemFeed($user_id, $_POST['id'], $_POST['feed_id']);
					header("Location: ".$_SERVER['REQUEST_URI']);
					break;
				case 'new-child':
					$feed_id = $this->newFeed($user_id, $_POST['name'], "", 3, $_POST['parent-feed']);
					header("Location: ./?feed_id=" . $feed_id . "&name=" . $_POST['name']);
					break;
				case 'remove':
					$this->removeItemFeed($user_id, $_POST['item_id'], $_POST['feed_id']);
					break;
				case 'display':
					$feed_id = $this->updateFeedDisplay($_POST['display_id'], $_POST['feed_id']);
					header("Location: ./?feed_id=" . $feed_id . "&name=" . $_POST['name']);
					break;
			}
		} else if(isset($_POST['itc_add_item_feed'])){
				$item_id = $itemManager->handleItemUpload($client);
				
				if($itemManager->insertOk == "1" && isset($item_id)) {
					$itemManager->insertUserItem($client->user_serial, $item_id, 3);
					$this->addItemFeed($user_id, $itemManager->item_id, $_POST['itc_add_item_feed']);
				}
				header("Location: ./?feed_id=" . $_POST['itc_add_item_feed'] . '&id=' . $item_id);
				$itemManager->active = true;
		} else if(isset($_POST['itc_feed_edit'])){ 
				$this->purgeFeed($_POST['feed_id']);
				header("Location: ./");
		} else if(isset($_POST['itc_feed_img'])){
				$this->handleFeedUpload($client);
				header("Location: ./?feed_id=" . $_GET['feed_id']);
		} else if (isset($_GET['feed_id'])){
			if(isset($_POST['itc_feed_name'])) {
				$owner = ($_POST['user_id'] == $user_id) ? $user_id : false;	
				if($owner) { $this->changeFeedName($_POST['itc_feed_name'], $_GET['feed_id']);
						header("Location: ./?feed_id=" . $_GET['feed_id'] . "&name=" .  $_POST['itc_feed_name']);
				}		
			} 

			$itemManager->meta['feed'] = $this->getFeed($_GET['feed_id']);
			if($itemManager->meta['feed']) {
				$itemManager->meta['title'] = $itemManager->meta['feed']['name'];
				$itemManager->meta['feed']['feed_item_class'] = $this->getAddonClasses($itemManager->meta['feed']['feed_id']);
				$itemManager->meta['feed']['display_class'] = $this->getFeedDisplayClasses($user_level);
				
				$display_id = $itemManager->meta['feed']['display_id'];
				$itemManager->meta['feed']['display_type'] = $itemManager->meta['feed']['display_class'][$display_id]['display_type'];
							
				// SHOULD BE USED BY OVERRIDE ONLY
				if (isset($itemManager->meta['feed']['related'])) { 
					$itemManager->meta['feed']['related'] = $this->getChildFeedItems($itemManager->meta['feed']['related'], 0, 5, $user_level);
				}
				
				if(!isset($_POST['page_id']) && !isset($itemManager->meta['feed']['feed_page'])) { 	
					if(isset($_GET['id'])) { 
						$itemManager->items = $itemManager->getItemById($_GET['id']);
						$itemManager->meta['feed']['items'] = $this->getFeedItems($_GET['feed_id'], $start, $count, $user_level);
					}
					else { $itemManager->items = $this->getFeedItems($_GET['feed_id'], $start, $count, $user_level); }
					$itemManager->active = true;
				}
			}
		} else if (isset($_GET['browse'])) {
			$browse_class = $_GET['browse'];
			
			if(isset($_GET['user'])) {
				$itemManager->meta['feeds'] = $this->getFeeds($start, $count, $user_level, $_GET['user']);
			} else {
				$itemManager->meta['feeds'] = $this->getFeeds($start, $count, $user_level, NULL);
			}
		} else if (empty($_GET) || isset($_GET['start']) || (isset($_GET['id']) && !isset($_GET['feed_id']))) {
			if(isset($this->featured_feed)) {
				$feed_id = $this->featured_feed;
				$itemManager->meta['bottom-section'] = $this->getFeed($feed_id);
				if($itemManager->meta['bottom-section']) {
					$itemManager->meta['bottom-section']['feed_item_class'] = $this->getAddonClasses($itemManager->meta['bottom-section']['feed_id']);
					$itemManager->meta['bottom-section']['display_class'] = $this->getFeedDisplayClasses($user_level);
				}	
				$itemManager->meta['bottom-section']['items'] = $this->getFeedItems($feed_id, $start, $count, $user_level);
			}
		}
	}
		
	function changeFeedName ($new_name, $feed_id) {
		$stream = $this->stream;
		$input = "UPDATE feed SET name='$new_name' WHERE feed_id='$feed_id'";
		$query = $stream->query($input);
	}

	function updateFeedDisplay($display_id, $feed_id) {
		$stream = $this->stream;
		$input = "UPDATE feed SET display_id='$display_id' WHERE feed_id='$feed_id'";
		$query = $stream->query($input);
		
		return $feed_id;
	}
	
	function purgeFeed ($feed_id) {
		$stream = $this->stream;
		
		$user_feeds = "DELETE FROM user_feeds WHERE feed_id='$feed_id'";
		mysqli_query($stream, $user_feeds);

		$item_feeds = "DELETE FROM feed_items WHERE feed_id='$feed_id'";
		mysqli_query($stream, $item_feeds);
				
		$feed = "DELETE FROM feed WHERE feed_id='$feed_id'";
		mysqli_query($stream, $feed);

		$feed = "DELETE FROM addon_feed WHERE feed_id='$feed_id'";
		mysqli_query($stream, $feed);		
	}
	
	function deleteItemFeed ($item_id) {
		$stream = $this->stream;

		$item_feeds = "DELETE FROM feed_items WHERE item_id='$item_id'";
		mysqli_query($stream, $item_feeds);
	}
	
	function getFeed($feed_id) {
		
		$feed_quest = "SELECT feed.* FROM feed WHERE feed_id='$feed_id'";
		$feed_loot_return = mysqli_query($this->stream, $feed_quest);	
		$feed_loot = $feed_loot_return->fetch_assoc();
		if(!$feed_loot) {
				return;
		}
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

		$feed_loot['feed_addon'] = $this->getAddonMatch($feed_id);
		
		return $feed_loot;
	}
	
	function getFeeds($start, $count, $level, $user_id) {
		$feed_quest = "SELECT * FROM feed WHERE level>='$level'";
		if($user_id) {
			$feed_quest .= " AND owner_id=$user_id";
		}
		$feed_loot_return = mysqli_query($this->stream, $feed_quest);	
		while($feed_loot = $feed_loot_return->fetch_assoc()) {
			$top_feeds[] = $feed_loot;	
		}
		return $top_feeds;
	}
	
	function getAddonMatch($feed_id) {
		$feed_quest = "SELECT * FROM addon_feed WHERE feed_id='$feed_id'";
		$feed_loot_return = mysqli_query($this->stream, $feed_quest);
		$feed_loot = $feed_loot_return->fetch_assoc();
		return $feed_loot;
	}
	
	function getChildFeeds($parent_id) {
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
	
	function getChildFeedItems($feeds, $start, $count, $level) {
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
			
			global $itemManager;
 			$feed['items'] = NULL;
			if($items_loot) {
				while($loot=$items_loot->fetch_assoc()) { 
					$feed['items'][] = $loot;
				}
			}
			$feeds_array[] = $feed;
		}
		return $feeds_array;
	}
		
		
	function getFeedItems($feed_id, $start, $count, $level) {
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
		}
		
		global $itemManager;
		$itemManager->item_loot = $item_loot_array;
		
		if(isset($itemManager->actions)) { runAddons($itemManager->actions, $itemManager, 'item-request'); }	
		return $itemManager->item_loot;
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
			
	function getFeedDisplayClasses($level) {
		
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
	
	function newFeed ($owner_id, $name, $feed_img, $level, $parent_id) {
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
		
	function addItemFeed ($user_id, $item_id, $feed_id) {
		global $client;
		$this->stream = $client->stream;
		$check_quest = "SELECT feed_items.* FROM feed_items WHERE item_id='$item_id' AND feed_id='$feed_id'";
		$match = mysqli_query($this->stream, $check_quest);
		if(!$match->fetch_assoc()) {
			$quest = "INSERT INTO feed_items (user_id, item_id, feed_id, date) VALUES('$user_id', '$item_id', '$feed_id', '" . date('Y-m-d h:i:s') . "')";
			$success = mysqli_query($this->stream, $quest);
		}
	}
		
	function removeItemFeed ($user_id, $item_id, $feed_id) {
		$quest = "DELETE FROM feed_items WHERE item_id='$item_id' AND feed_id='$feed_id' AND user_id='$user_id'";
		$success = mysqli_query($this->stream, $quest);
	}

	function changeFeedImage ($new_image, $feed_id) {
		$stream = $this->stream;
		$input = "UPDATE feed SET feed_img='$new_image' WHERE feed_id='$feed_id'";
		$query = $stream->query($input);
	}
	
	function handleFeedUpload($client) {
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
				$this->changeFeedImage($file, $_POST['feed_id']);
			}
		}
	}
}

class addonUserProfileFeedRequest extends addonProfileFeedRequest {
	
	function update ($profile){
		global $client;
		$this->stream = $client->stream;

		$this->user_id = $client->user_serial;
		$user_level = $client->level;
		$this->hide_child = false;
		
		$feeds = $this->getProfileFeeds($this->stream, $this->user_id, $this->hide_child, $user_level);
		
		$client->profile['feeds'] = $feeds;
	}
}






class addonProfileFeedRequest extends itemFeeds {
	
	function update ($profile){
		global $client;
		$this->stream = $client->stream;

		$this->profile_loot = $profile;
		$this->user_id = $profile['profile_id'];
		$user_level = $client->level;
		$this->hide_child = true;
		
		$tmp_loot_array = NULL;
		$quest = "SELECT SQL_CALC_FOUND_ROWS user_feeds.*, feed.*"
		 . " FROM user_feeds, feed"
		 . " WHERE user_feeds.user_id='" . $this->user_id . "'"
		 . " AND feed.feed_id=user_feeds.feed_id";
		if($this->hide_child) { $quest .= " AND feed.parent_id=''"; }
		$quest .= " AND feed.level >='$user_level'";
		 
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
		$profile = $this->profile_loot;
		return $profile;
	}
	
	function getProfileFeeds ($stream, $user_id, $hide_child, $user_level){
		$quest = "SELECT SQL_CALC_FOUND_ROWS user_feeds.*, feed.*"
		 . " FROM user_feeds, feed"
		 . " WHERE user_feeds.user_id='" . $user_id . "'"
		 . " AND feed.feed_id=user_feeds.feed_id";
		if($hide_child) { $quest .= " AND feed.parent_id=''"; }
		$quest .= " AND feed.level >='$user_level'";
		 
		$feed_loot = mysqli_query($stream, $quest);		
		$feeds = NULL;	
		if($feed_loot) {
			$count_quest = mysqli_query($this->stream, "SELECT FOUND_ROWS() AS count")->fetch_assoc();
			while($loot=$feed_loot->fetch_assoc()) { 
				$loot['total'] = $count_quest['count'];
				$feeds[] = $loot;
			}
		}
		return $feeds;
	}
}
?>
