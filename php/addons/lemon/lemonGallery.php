<?php //Add-On for reply display
$gallery_addon['addon_title'] = 'Lemon gallery';
$gallery_addon['addon_name'] = 'lemon-gallery';
$gallery_addon['addon-version'] = '1.0';
$gallery_addon['collection_name'] = 'Image Gallery';
$gallery_addon['item_name'] = 'Image';
$gallery_addon['addon_id'] = '1003';

$gallery_addon['post-handler'] = 'addonPostGalleryHandler';
$gallery_addon['item-display'] = 'addonItemGalleryDisplay';
$gallery_addon['item-request'] = 'addonItemGalleryRequest';
$gallery_addon['page-display'] = 'addonGalleryPageDisplay';
$gallery_addon['add-new'] = true;

//Add to global $addOns variable
//$addOns[] = $gallery_addon;

class addonGalleryPageDisplay {
	function __construct ($pageManager) {
		$this->pageManager = $pageManager;
		
		$this->output = $this->feedOutput();
	}
	
	function feedOutput() {
		global $gallery_addon;
		if(isset($this->pageManager->meta['feed']['feed_addon']) && isset($_GET['feed_id'])) {
			if($this->pageManager->meta['feed']['feed_addon']['addon_id'] == $gallery_addon['addon_id']) { 
				
				$this->pageManager->meta['active_page'] = true;
				return $this->galleryDisplay();
			}
		}
	}
	
	function galleryDisplay() {
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
			if($feed_owner) { $feed_img .= "<div id=\"$imageRollover\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'\" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 80px; text-align: center;\">&#8853;</div>"; }
			$feed_img .= "</div>";
			$feed_name = "<div style=\"font-size: 2em; cursor: pointer\" onclick=\"window.location='$_ROOTweb?feed_id=" . $feed['feed_id'] . "'\"><u>" . $feed['name'] . "</u></div>";
	
			$feed_cover = "";
			if($feed_owner) {			
				$feed_cover .= "<form enctype=\"multipart/form-data\" action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><div style=\"display: none; margin-top: 4px;\" id=\"itc_feed_image_form\"><input type=\"hidden\" name=\"itc_feed_img\" value=\"change\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $_GET['feed_id'] . "\"/><input type=\"file\" class=\"item-tools\" name=\"itc_feed_upload\" accept=\"image/jpeg,image/png,image/gif\"><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9989; SAVE\"></div></form>";
				$feed_cover .= "<div id=\"show-form-button\" class=\"item-tools_dark\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; this.style.display='none'\" style=\"margin: 4px 0px;\">" . "Change the background" . "</div>";
			}	
				
			$page  = "<div style=\"float: right\">" . $feed_cover . "</div>";
			$page .= "<div style=\"text-align: left;\">";
			$page .= "<div id=\"itc_feed_name\" style=\"display: inline-block; padding: 14px;\">" . $feed_name;
			$page .= "<div id=\"itc_feed_name_form\" style=\"display: none;\"><form action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input class=\"form\" name=\"itc_feed_name\" value=\"" . $feed['name'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9989; SAVE\"></form></div>";
			
			$page .= "</div>";
			

			if($feed_owner){
				$page .= " <span class=\"item-tools\" onclick=\"this.style.display='none'; domId('itc_feed_name').style.display='none'; domId('itc_feed_name_form').style.display='inline-block';\">&#9998; EDIT</span>";
			}			
			if($feed_owner){			
				$page .= "<div style=\"display: inline-block\"><form action=\"./?itc_feed_edit=purge\" method=\"post\"><input type=\"hidden\" name=\"itc_feed_edit\" value=\"purge\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $feed['feed_id'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9988; DELETE\"></form></div>";		
			}
			
			$page .= "</div>";				
			$page .= $this->addonGalleryItems();
						
			$return_page = "<div";
			if ($feed_img_src && $feed['feed_img'] != 'gallery.png') { $return_page .= " style=\"background-image: url('$feed_img_src'); background-size: cover; background-attachment: fixed;\""; }
			$return_page .= ">" . $page . "</div>";
			
			return $return_page;
		}
	}
		
	function addonGalleryItems() {
		$pageManager = $this->pageManager;
		if(isset($_GET['feed_id'])) {
			global $client;
			global $_ROOTweb;
			
			$profile_feeds = (isset($client->profile)) ? $client->profile['feeds'] : [];
			
			$feed = $pageManager->meta['feed'];
			$feed_owner = ($feed['owner_id'] == $client->user_serial) ? $feed['owner_id'] : NULL;
			
			$item_info_limit = 200;
			$items = $pageManager->items;
					
			$page = "";
			if(isset($_GET['id'])) { 
				$page .= "<div>";
				$page .= "<div class=\"item-section feed-" . $feed['display_type'] . "\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit);
				$page .= "</div>";	
				
				if($feed_owner){
					$omniBox = $this->displayOmniBox($pageManager, $pageManager->meta['feed'], $pageManager->items[0]['item_id']);
					$page .= "<div>" . $omniBox . "</div>";
				}				
				
				if(isset($pageManager->meta['feed']['items'])) {
					$pageManager->items = $pageManager->meta['feed']['items'];
					$page .= "<div class=\"gallery feed-box\">" . $pageManager->displayItems('box', 30) . "</div>";
				}	
				$page .= "<div class=\"clear\"></div>";
				$page .= "</div>";
			} else {
								
				if($feed_owner){
					$omniBox = $this->displayOmniBox($pageManager, $pageManager->meta['feed'], $pageManager->items[0]['item_id']);
					$page .= "<div>" . $omniBox . "</div>";
				}	
				
				$page .= "<div class=\"gallery feed-box\">" . $pageManager->displayItems('box', 30) . "</div>" . "<div class=\"clear\"></div>";
			}
			
			return $page;
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
		
		$createForm  = "<div style=\"display: inline-block;\">";
		$createForm .= "<div style=\"display: none; width: 100%\" class=\"item-box\" id=\"itemOmniBox\">" . "</div>"
			. "<div class=\"float-left\" style=\"display: inline: block\" onclick=\"domId('itemOmniBox').style.display='inline-block'; this.style.display='none'\" style=\"width: 640px; margin: 14px 0px; text-align: center; cursor: pointer\"><div class=\"item-tools\">+ <u>Add an Item</u></div></div>";
		$createForm .= $javascript_omni_box;
		$createForm .= "</div>";
		return $message . $createForm;
	}			
}

class addonPostGalleryHandler {
	function __construct ($stream) {
		$this->stream = $stream;
	}

	function handleAddOnPost ($itemManager) {
		global $client;	
		$user_id = $client->user_serial;
		$user_level = $client->level;
		
		if (isset($_POST['itc_gallery'])) {
			$item_id = $_POST['itc_gallery'];
			$feed_id = isset($_POST['itc_gallery_add']) ? $_POST['itc_gallery_add'] : NULL;
			$feed_name = isset($_POST['itc_gallery_name']) ? $_POST['itc_gallery_name'] : NULL;
			
			echo $feed_name;
			
			if($feed_id && $item_id && !$feed_name) {
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			} else if($feed_id && $feed_name) {
				$feed_id = $this->addGalleryFeed($user_id, $feed_name, 'gallery.png', $feed_id);
				header("Location: ./?feed_id=" . $feed_id);
			} else if($item_id) {
				$feed_id = $this->addItemGalleryFeed($user_id, 'gallery', 'gallery.png', $item_id);
				
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			}
		}
	}
	
	function addGalleryFeed ($owner_id, $name, $feed_img, $parent_id) {
		global $gallery_addon;
		//item needs to be added to feed_items for addon feed
		$quest = "INSERT INTO feed (owner_id, name, feed_img, parent_id, level) VALUES('$owner_id', '$name', '$feed_img', $parent_id, '0')";
		$success = mysqli_query($this->stream, $quest);
		$feed_id = mysqli_insert_id($this->stream);
		
		if($feed_id) {
			$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $gallery_addon['addon_name'] . "', '" . $gallery_addon['addon_id'] . "', '" . $gallery_addon['collection_name'] . "', '" . $gallery_addon['item_name'] . "', '$feed_id', 0, 0)";
			$success = mysqli_query($this->stream, $user_quest);
			return $feed_id;
		}
			
	}
	
	function addItemgalleryFeed ($owner_id, $name, $feed_img, $item_id) {
			global $gallery_addon;
		
			//Check if item is already in a gallery feed
			$addon_check = "SELECT feed_items.item_id, feed.*, addon_feed.*"
			. " FROM feed_items, feed, addon_feed"
			. " WHERE feed_items.item_id=" . $item_id
			. " AND feed_items.feed_id=feed.feed_id"
			. " AND feed_items.feed_id=addon_feed.feed_id"
			. " AND addon_feed.addon_id=" . $gallery_addon['addon_id'];
			
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
				$feed_id = mysqli_insert_id($this->stream);
				
				if($feed_id) {
					$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $gallery_addon['addon_name'] . "', '" . $gallery_addon['addon_id'] . "', '" . $gallery_addon['collection_name'] . "', '" . $gallery_addon['item_name'] . "', '$feed_id', 0, 0)";
					$success = mysqli_query($this->stream, $user_quest);
					return $feed_id;
				}
			}
			
	}
}

class addonItemGalleryDisplay {
	function updateOutputHTML($itemDisplay) {
		global $_ROOTweb;
		global $client;
				
		if($itemDisplay->class_id != 4) { return; }
				
		//include to use with another add-on
		$raw_input = ($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) ? $itemDisplay->metaOutput : NULL;
		if($raw_input) { $itemDisplay->metaOutput = ""; }
		
		$itemDisplay->metaOutput .= "<div style=\"float: left; padding-left: 4px; font-size: 12px;\">";
		if(isset($itemDisplay->item['gallery-feeds'])) {
			$i = 0;
			foreach($itemDisplay->item['gallery-feeds'] as $feed) {
			  $feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">" 
					. "Go to " . $feed['name'] . " ("  . count($feed['items']) . ")"
					. "</a>";
				
				//Update Image Link
				$itemDisplay->itemLink = "?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "&id=" . $itemDisplay->item_id;
				$itemDisplay->onclick = $itemDisplay->onclickHTML();		
				$itemDisplay->titleOutput = $itemDisplay->titleDisplayHTML();		
				$itemDisplay->fileOutput = $itemDisplay->photoOverride();
					
				$index = $itemDisplay->item['item_id'];
				if(($feed['owner_id'] == $client->user_serial) && $index) {
					//remove feed from item if owner
					$remove_button = "<div style='display: inline-block'><form id='removeGalleryForm" . $i . $index . "' action='?id=" . $index. "' method='post'>"
					. "<input type='hidden' name='item_id' value='" . $index . "'/>"
					. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>"
					. "<input type='hidden' name='feed' value='remove'/>"		
					. "<div class='inline-remove' style='padding: 0px'>";

					$remove_button .= " <a onclick=\"domId('removeGalleryForm" . $i . $index . "').submit()\">x</a>";	
					$remove_button .= "</div>";
					$remove_button .= "</form></div>";
					
					$itemDisplay->metaOutput .=  $remove_button;
				}	
				$itemDisplay->metaOutput .= $feed_img;					
				$i++;
			}
		} else if($client->user_serial){

			if($itemDisplay->owner) {			
				$count_text = (isset($itemDisplay->item['gallery-feeds']['items'])) ? count($itemDisplay->item['gallery-feeds']['items']) : 0;
				
				$page_form = "itc_gallery_" . $itemDisplay->item['item_id'];
				$link_submit = " onclick=\"domId('$page_form').submit()\"";
				$galleryInput = " Gallery";
				if(isset($itemDisplay->item['user-gallery-feeds'])) { 
					$galleryInput = " <select type=\"dropdown\" onchange=\"domId('$page_form').submit()\" onfocus=\"this.selectedIndex = -1\" name=\"itc_gallery_add\">";
					foreach($itemDisplay->item['user-gallery-feeds'] as $tmp_feed) { 
						$galleryInput .= "<option value=\"" . $tmp_feed['feed_id'] . "\">" . $tmp_feed['name'] . "</option>";
					}
					
					$galleryInput .= "<option value=\"\">+ New</option>";
					$link_submit = "";
					$galleryInput .= "</select>";
				}
				
				$itemDisplay->metaOutput .= "<form id=\"$page_form\" action=\"$_ROOTweb?id=" . $itemDisplay->item['item_id'] . "\" method=\"post\"><input type=\"hidden\" id=\"itc_gallery\" name=\"itc_gallery\" value=\"" . $itemDisplay->item['item_id'] . "\"/>"
					. "<a title=\"Add to gallery\"$link_submit>"				
					. "Add to "
					. $galleryInput
					. "</a>"
					. "</form>";
			}
		} 
		$itemDisplay->metaOutput .= "</div>";
		
	}
}

class addonItemGalleryRequest {
	function __construct ($stream, $items) {
		$this->stream = $stream;
		$this->item_loot = $items;
	}
	
	function getAddOnLoot ($level){
		$tmp_loot_array = NULL;
		$user_feeds = NULL;
		global $gallery_addon;
		global $client;
			
		$user_quest = "SELECT feed.*, addon_feed.*"
		. " FROM feed, addon_feed"
		. " WHERE feed.feed_id=addon_feed.feed_id"
		. " AND addon_feed.addon_id=" . $gallery_addon['addon_id']
		. " AND feed.owner_id=" . $client->user_serial;
		
		$user_loot = mysqli_query($this->stream, $user_quest);
		if($user_loot !== false) {
			while($loot=$user_loot->fetch_assoc()) {
				$user_feeds[] = $loot;
			}
		}
		
		if($this->item_loot) { foreach($this->item_loot as $item) {
				
			//Check if item is in gallery feed
			//Get gallery feed and set gallery_feeds
			$quest = "SELECT feed_items.item_id, feed.*, addon_feed.*"
			. " FROM feed_items, feed, addon_feed"
			. " WHERE feed_items.item_id=" . $item['item_id']
			. " AND feed_items.feed_id=feed.feed_id"
			. " AND feed_items.feed_id=addon_feed.feed_id"
			. " AND addon_feed.addon_id=" . $gallery_addon['addon_id'];

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
		$item['gallery-feeds'] = $feeds;
		$item['user-gallery-feeds'] = $user_feeds;
		return $item;
	}	
}
?>
