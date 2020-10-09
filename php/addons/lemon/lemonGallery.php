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
$gallery_addon['add-new'] = true;

//Add to global $addOns variable
//$addOns[] = $gallery_addon;

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
			
			if($feed_id && $item_id) {
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			} else if($item_id) {
				$feed_id = $this->addItemGalleryFeed($user_id, 'gallery', 'gallery.png', $item_id);
				
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			}
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
				$itemDisplay->itemLink = "?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'];
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
