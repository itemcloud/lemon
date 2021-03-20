<?php //Add-On for reply display
$audiofeed_addon['addon_title'] = 'Audiofeed (lemon 1.2.9)';
$audiofeed_addon['addon_name'] = 'lemon-audiofeed';
$audiofeed_addon['addon-version'] = '1.0';
$audiofeed_addon['collection_name'] = 'Playlist';
$audiofeed_addon['item_name'] = 'Tracks';
$audiofeed_addon['addon_id'] = '1005';

$audiofeed_addon['post-handler'] = 'addonPostaudiofeedHandler';
$audiofeed_addon['item-display'] = 'addonItemaudiofeedDisplay';
$audiofeed_addon['item-request'] = 'addonItemaudiofeedRequest';
$audiofeed_addon['page-display'] = 'addonaudiofeedPageDisplay';
$audiofeed_addon['add-new'] = true;

//Add to global $addOns variable
//$addOns[] = $audiofeed_addon;

class addonaudiofeedPageDisplay {
	function __construct ($pageManager) {
		$this->pageManager = $pageManager;
		
		$this->output = $this->feedOutput();
	}
	
	function feedOutput() {
		global $audiofeed_addon;
		if(isset($this->pageManager->meta['feed']['feed_addon']) && isset($_GET['feed_id'])) {
			if($this->pageManager->meta['feed']['feed_addon']['addon_id'] == $audiofeed_addon['addon_id']) { 
				
				$this->pageManager->meta['active_page'] = true;
				return "<div class=\"item-section\">" . $this->feedBanner() . $this->addonPlaylistItems() . "</div>";
			}
		}
	}
	
	function feedBanner() {
		$pageManager = $this->pageManager;
		if(isset($_GET['feed_id'])) {
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
			if($feed_owner) { $feed_img .= "<div id=\"$imageRollover\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'; \" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 80px; text-align: center;\">&#8853;</div>"; }
			$feed_img .= "</div>";
			$feed_name = "<div style=\"font-size: 2em; cursor: pointer\" onclick=\"window.location='$_ROOTweb?feed_id=" . $feed['feed_id'] . "'\"><u>" . $feed['name'] . "</u></div>";
	
			$feed_cover = "";
			if(isset($feed['related'])) {
				foreach($feed['related'] as $related) {
					if(count($related['items']) > 0) {
						$feed_cover .= "<div onClick=\"window.location='./?feed_id=" . $related['feed_id'] . "'\" style=\"width: 200px; height: 200px; float: left; background-size: cover; background-image: url(" . $related['items'][0]['link'] . ")\"></div>";
					} else {
						$gallery_owner = ($related['owner_id'] == $client->user_serial) ? $related['owner_id'] : NULL;
						$feed_cover = "<div onmouseover=\"domId('$imageRollover').style.display='block';\" onmouseout=\"domId('$imageRollover').style.display='none'\" style=\"width: 200px; height: 200px; float: left; background-image: url(files/feeds/gallery.png); background-size: cover\">";

						if($feed_owner) { $feed_cover .= "<div id=\"$imageRollover\" onclick=\"window.location='./?feed_id=" . $related['feed_id'] . "'\" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 160px; text-align: center;\">&#8853;</div>"; }
									
						$feed_cover .= "</div>";
					}
				}
			} else {
				if($feed_owner) {
					$feed_cover = "<form id=\"addGalleryFeed\" action=\"./?feed_id=" . $feed['feed_id'] . "\" method=\"post\">"
					. "<input type=\"hidden\" class=\"form\" name=\"itc_gallery\" value=\"new\"/>"
					. "<input type=\"hidden\" class=\"form\" name=\"itc_gallery_add\" value=\"" . $feed['feed_id'] . "\"/>"
					. "<input type=\"hidden\" class=\"form\" name=\"itc_gallery_name\" value=\"Cover Art\"/>"
					. "<div onmouseover=\"domId('$imageRollover').style.display='block';\" onmouseout=\"domId('$imageRollover').style.display='none'\"  style=\"width: 200px; height: 200px; float: left; background-image: url(files/feeds/gallery.png); background-size: cover\">"
					
					. "<div id=\"$imageRollover\" onclick=\"domId('addGalleryFeed').submit()\" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 160px; text-align: center;\">&#8853;</div>"
					. "</div></form>";
				}
			}
		
			$page  = $feed_cover;
			$page .= "<div style=\"width: 580px; float: right; text-align: left; margin: 0px 16px\">";
			$page .= "<div id=\"itc_feed_name_form\" style=\"display: none;\"><form action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input class=\"form\" name=\"itc_feed_name\" value=\"" . $feed['name'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9989; SAVE\"></div>";
			$page .= "<div id=\"itc_feed_name\" style=\"display: inline-block;\">" . $feed_name;	
										

			$page .= "</form></div>";			
			
			
			if($feed_owner){
				$page .= " <span class=\"item-tools\" onclick=\"this.style.display='none'; domId('itc_feed_name').style.display='none'; domId('itc_feed_delete').style.display='none'; domId('itc_feed_name_form').style.display='inline-block';\">&#9998; EDIT</span>";
			}			
			if($feed_owner){			
				$page .= "<div id=\"itc_feed_delete\" style=\"display: inline-block\"><form action=\"./?itc_feed_edit=purge\" method=\"post\"><input type=\"hidden\" name=\"itc_feed_edit\" value=\"purge\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $feed['feed_id'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9988; DELETE\"></form></div>";		
			}
			
			$page .= "</div>";
			return $page;
		}
	}
		
	function addonPlaylistItems() {
		$pageManager = $this->pageManager;
		if(isset($_GET['feed_id'])) {
			global $client;
			global $_ROOTweb;
			
			$profile_feeds = (isset($client->profile)) ? $client->profile['feeds'] : [];
			
			$feed = $pageManager->meta['feed'];
			$feed_owner = ($feed['owner_id'] == $client->user_serial) ? $feed['owner_id'] : NULL;
			
			$item_info_limit = 2800;
			$page = "";
			if(isset($_GET['id'])) { 			
				$page .= "<div class=\"audiofeed feed-" . $feed['display_type'] . "\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit) . "</" . $feed['display_type'] . ">";
				$page .= "<div class=\"clear\"></div>";
				$page .= "</div>";	
				
				if(isset($pageManager->meta['feed']['items'])) {
					$pageManager->items = $pageManager->meta['feed']['items'];
					$page .= "<div class=\"audiofeed feed-list\" style=\"text-align: left\">" . $pageManager->displayItems('list', 140) . "</div>";
				}
				
				$page .= "<div class=\"clear\"></div>";
				if($feed_owner){
					$omniBox = $this->displayOmniBox($pageManager, $pageManager->meta['feed'], $pageManager->items[0]['item_id']);
					$page .= "<div style=\"margin: 14px\">" . $omniBox . "</div>";
				}	
			} else {	
				$items = $pageManager->items;	
				
				if($pageManager->items) {
					$tmp_items[0] = $pageManager->items[0];
					$pageManager->items = $tmp_items;
					
					$page .= "<div class=\"feed-" . $feed['display_type'] . "\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit);
					$page .= "<div class=\"clear\"></div>";
					$page .= "</div>";	
				}
				
				$pageManager->items = $items;
				$page .= "<div class=\"feed-list\" style=\"text-align: left\">" . $pageManager->displayItems('list', 140) . "</div>";
								
				if($feed_owner){
					$omniBox = $this->displayOmniBox($pageManager, $pageManager->meta['feed'], $items[0]['item_id']);
					$page .= "<div style=\"margin: 14px\">" . $omniBox . "</div>";
				}
				
				$page .= "<div class=\"clear\"></div>";
			}
			
			return "<div style=\"width: 580px; float: right; margin: 0px 16px\">" . $page . "</div>" . "<div class=\"clear\"></div>";
		}
	}	
	
	function displayOmniBox($pageManager, $feed, $item_id) {
		if(!$pageManager->classes) { return; }
		
		$feed_id = $feed['feed_id'];
		$classes = isset($feed['feed_item_class']) ? $feed['feed_item_class']: $pageManager->classes;
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController = new OmniFeedBox(" . $class_js_array . ", 'itemOmniBox');\n OmniController.set_active_feed('" . $feed_id . "');\n OmniController.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div style=\"display: inline-block\">";
		$createForm .= "<div style=\"display: none; width: 100%\" class=\"item-page\" id=\"itemOmniBox\">" . "</div>"
			. "<div class=\"float-left\" style=\"display: inline: block\" onclick=\"domId('itemOmniBox').style.display='inline-block'; this.style.display='none'\" style=\"width: 640px; margin: 14px 0px; text-align: center; cursor: pointer\"><div class=\"item-tools\">+ <u>Add an Item</u></div></div>";
		$createForm .= $javascript_omni_box;
		$createForm .= "</div>";
		return $message . $createForm;
	}
}

class addonPostaudiofeedHandler {
	function __construct ($stream) {
		$this->stream = $stream;
	}

	function handleAddOnPost ($itemManager) {
		global $client;	
		$user_id = $client->user_serial;
		$user_level = $client->level;
		
		if (isset($_POST['itc_audiofeed'])) {
			$item_id = $_POST['itc_audiofeed'];
			$feed_id = isset($_POST['itc_audiofeed_add']) ? $_POST['itc_audiofeed_add'] : NULL;
			
			if($feed_id && $item_id) {
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			} else if($item_id) {
				global $audiofeed_addon;
				$name = $audiofeed_addon['collection_name'];
				$feed_id = $this->addItemaudiofeedFeed($user_id, $name, 'audiofeed.png', $item_id);
				
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			}
		}
	}
	
	function addItemaudiofeedFeed ($owner_id, $name, $feed_img, $item_id) {
			global $audiofeed_addon;
			global $gallery_addon;
			
			//Check if item is already in a audiofeed feed
			$addon_check = "SELECT feed_items.item_id, feed.*, addon_feed.*"
			. " FROM feed_items, feed, addon_feed"
			. " WHERE feed_items.item_id=" . $item_id
			. " AND feed_items.feed_id=feed.feed_id"
			. " AND feed_items.feed_id=addon_feed.feed_id"
			. " AND addon_feed.addon_id=" . $audiofeed_addon['addon_id'];
			
			$check = mysqli_query($this->stream, $addon_check);
			if($check !== false) { $addon_feed = $check->fetch_assoc(); }

			if($addon_feed) {
				
				//item is already found in feed_items for addon feed
				$feed_id = $addon_feed['feed_id'];
				return $feed_id;
			} else {
				
				//item needs to be added to feed_items for addon feed
				$quest = "INSERT INTO feed (owner_id, name, feed_img, level) VALUES('$owner_id', '$name', '$feed_img', '0')";
				$success = mysqli_query($this->stream, $quest);
				$parent_feed_id = mysqli_insert_id($this->stream);
				
				if($parent_feed_id) {					
					$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $audiofeed_addon['addon_name'] . "', '" . $audiofeed_addon['addon_id'] . "', '" . $audiofeed_addon['collection_name'] . "', '" . $audiofeed_addon['item_name'] . "', '$parent_feed_id', 0, 0)";
					$success = mysqli_query($this->stream, $user_quest);					
					
					//item needs to be added to feed_items for addon feed
					$quest = "INSERT INTO feed (owner_id, name, feed_img, parent_id, level) VALUES('$owner_id', 'Cover Art', 'gallery.png', $parent_feed_id, '3')";
					$success = mysqli_query($this->stream, $quest);
					$feed_id = mysqli_insert_id($this->stream);

					$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $gallery_addon['addon_name'] . "', '" . $gallery_addon['addon_id'] . "', '" . $gallery_addon['collection_name'] . "', '" . $gallery_addon['item_name'] . "', '$feed_id', 0, 0)";
					$success = mysqli_query($this->stream, $user_quest);

					return $parent_feed_id;
				}
			}
			
	}
}

class addonItemaudiofeedDisplay {
	function updateOutputHTML($itemDisplay) {
		global $_ROOTweb;
		global $client;
				
		if($itemDisplay->class_id != 5) { return; }
				
		//include to use with another add-on
		$raw_input = ($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) ? $itemDisplay->metaOutput : NULL;
		if($raw_input) { $itemDisplay->metaOutput = ""; }
		
		$itemDisplay->metaOutput .= "<div style=\"float: left; padding-left: 4px; font-size: 12px;\">";
		if(isset($itemDisplay->item['audiofeed-feeds'])) {
			$i = 0;
			foreach($itemDisplay->item['audiofeed-feeds'] as $feed) {
			  $feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "&id=" . $itemDisplay->item_id . "\">" 
					. "Go to " . $feed['name'] . " ("  . count($feed['items']) . ")"
					. "</a>";
				
				//Update Image Link
				$itemDisplay->itemLink = "?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "&id=" . $itemDisplay->item_id;
				$itemDisplay->onclick = $itemDisplay->onclickHTML();		
				$itemDisplay->titleOutput = $itemDisplay->titleDisplayHTML();		
				$itemDisplay->fileOutput = $itemDisplay->audioOverride();
					
				$index = $itemDisplay->item['item_id'];
				if(($feed['owner_id'] == $client->user_serial) && $index) {
					//remove feed from item if owner
					$remove_button = "<div style='display: inline-block'><form id='removeAudiofeedForm" . $i . $index . "' action='?id=" . $index. "' method='post'>"
					. "<input type='hidden' name='item_id' value='" . $index . "'/>"
					. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>"
					. "<input type='hidden' name='feed' value='remove'/>"		
					. "<div class='inline-remove' style='padding: 0px'>";

					$remove_button .= " <a onclick=\"domId('removeAudiofeedForm" . $i . $index . "').submit()\">x</a>";	
					$remove_button .= "</div>";
					$remove_button .= "</form></div>";
					
					$itemDisplay->metaOutput .=  $remove_button;
				}	
				$itemDisplay->metaOutput .= $feed_img;					
				$i++;
			}
		} else if($client->user_serial){

			if($itemDisplay->owner) {			
				$count_text = (isset($itemDisplay->item['audiofeed-feeds']['items'])) ? count($itemDisplay->item['audiofeed-feeds']['items']) : 0;
				
				$page_form = "itc_audiofeed_" . $itemDisplay->item['item_id'];
				$link_submit = " onclick=\"domId('$page_form').submit()\"";
				
				$audiofeedInput = " ";
				if(isset($itemDisplay->item['user-audiofeed-feeds'])) { 
					$audiofeedInput = " <select type=\"dropdown\" onchange=\"domId('$page_form').submit()\" onfocus=\"this.selectedIndex = -1\" name=\"itc_audiofeed_add\">";
					foreach($itemDisplay->item['user-audiofeed-feeds'] as $tmp_feed) { 
						$audiofeedInput .= "<option value=\"" . $tmp_feed['feed_id'] . "\">" . $tmp_feed['name'] . "</option>";
					}
					
					$audiofeedInput .= "<option value=\"\">+ New</option>";
					$link_submit = "";
					$audiofeedInput .= "</select>";
				}
				
				global $audiofeed_addon;
				$itemDisplay->metaOutput .= "<form id=\"$page_form\" action=\"$_ROOTweb?id=" . $itemDisplay->item['item_id'] . "\" method=\"post\"><input type=\"hidden\" id=\"itc_audiofeed\" name=\"itc_audiofeed\" value=\"" . $itemDisplay->item['item_id'] . "\"/>"
					. "<a title=\"Add to "  . $audiofeed_addon['collection_name'] . "\"$link_submit>"				
					. "Add to "
					. $audiofeedInput
					. "</a>"
					. "</form>";
			}
		} 
		$itemDisplay->metaOutput .= "</div>";
		
	}
}

class addonItemaudiofeedRequest {
	function __construct ($stream, $items) {
		$this->stream = $stream;
		$this->item_loot = $items;
	}
	
	function getAddOnLoot ($level){
		$tmp_loot_array = NULL;
		$user_feeds = NULL;
		global $audiofeed_addon;
		global $client;
			
		$user_quest = "SELECT feed.*, addon_feed.*"
		. " FROM feed, addon_feed"
		. " WHERE feed.feed_id=addon_feed.feed_id"
		. " AND addon_feed.addon_id=" . $audiofeed_addon['addon_id']
		. " AND feed.owner_id=" . $client->user_serial;
		
		$user_loot = mysqli_query($this->stream, $user_quest);
		if($user_loot !== false) {
			while($loot=$user_loot->fetch_assoc()) {
				$user_feeds[] = $loot;
			}
		}
		
		if($this->item_loot) { foreach($this->item_loot as $item) {
				
			//Check if item is in audiofeed feed
			//Get audiofeed feed and set audiofeed_feeds
			$quest = "SELECT feed_items.item_id, feed.*, addon_feed.*"
			. " FROM feed_items, feed, addon_feed"
			. " WHERE feed_items.item_id=" . $item['item_id']
			. " AND feed_items.feed_id=feed.feed_id"
			. " AND feed_items.feed_id=addon_feed.feed_id"
			. " AND addon_feed.addon_id=" . $audiofeed_addon['addon_id'];

			$feed_loot = mysqli_query($this->stream, $quest);
			$feeds = NULL;
			$item_parent = [];
			if($feed_loot !== false) {
				while($tmp_loot=$feed_loot->fetch_assoc()) {	
										
					$item_count = "SELECT feed_items.*"
					. " FROM feed_items"
					. " WHERE feed_items.feed_id=" . $tmp_loot['feed_id'];
			 
					$tmp_items = NULL;
					$count_loot = mysqli_query($this->stream, $item_count);
					if($count_loot !== false) { 
						while($item_loot = $count_loot->fetch_assoc()) { 
							$tmp_items[] = $item_loot; 
						}
						$tmp_loot['items'] = $tmp_items;
					}				
					$tmp_loot['feed_class'] = $this->getAddOnClasses();
					$feeds[] = $tmp_loot;
				}		
				$item = $this->mergeFeeds($item, $feeds, $user_feeds);
			}

			$tmp_loot_array[] = $item;
		} }
		$this->item_loot = $tmp_loot_array;
		return $this->item_loot;
	}
		
	function getAddOnClasses() {
		global $client;
		global $favorite_addon;
		
		$stream = $this->stream;
		$level = $client->level;
		
		$class_quest = "SELECT addon_class.*, item_class.*, item_nodes.*"
					. " FROM feed_class, item_class, item_nodes"
					. " WHERE item_nodes.class_id=item_class.class_id"
					. " AND item_class.class_id=feed_class.class_id"
					. " AND feed_class.addon_id=" . $favorite_addon['addon_id']
					. " AND item_class.level >= $level";
					
		$class_loot = mysqli_query($stream, $class_quest);
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
						
					$type_loot = mysqli_query($stream, $type_quest);				
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
	
	function mergeFeeds ($item, $feeds, $user_feeds){				
		$item['audiofeed-feeds'] = $feeds;
		$item['user-audiofeed-feeds'] = $user_feeds;
		return $item;
	}	
}
?>
