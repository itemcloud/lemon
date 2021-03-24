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

class Audiofeeds {
	function __construct () {
		$this->addon_title = 'Lemon audiofeed';
		$this->addon_name = 'lemon-audiofeed';
		$this->addon_version = '1.0';
		$this->collection_name = 'Album';
		$this->item_name = 'Tracks';
		$this->addon_id = '1005';
		$this->add_new = true;
	}
	function setActions () {
		global $actions;
		$actions['post-handler'][] = 'addonPostaudiofeedHandler';
		$actions['item-display'][] = 'addonItemaudiofeedDisplay';
		$actions['item-request'][] = 'addonItemaudiofeedRequest';
		$actions['page-display'][] = 'addonaudiofeedPageDisplay';
	}
}

//Add to global $addOns variable
//$addOns[] = 'Audiofeeds';

class addonaudiofeedPageDisplay extends Audiofeeds {
	
	function update($pageManager) {
		$this->pageManager = $pageManager;
		if(isset($this->pageManager->meta['feed']['feed_addon']) && isset($_GET['feed_id'])) {
			if($this->pageManager->meta['feed']['feed_addon']['addon_id'] == $this->addon_id) { 
				
				$this->pageManager->meta['active_page'] = true;
				
				$this->pageManager->section['items']['output'] = $this->feedBanner() . $this->addonPlaylistItems() . $this->pageManager->section['items']['output'];
				$this->pageManager->section['items']['displayClass'] = "fixed";
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
			$feed_img .= " style=\"width: 100px; height: 100px; margin: 0px 20px 20px 20px; background-image: url('" . $feed_img_src  . "')\">";
			if($feed_owner) { $feed_img .= "<div id=\"$imageRollover\" onclick=\"domId('itc_feed_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'; \" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 80px; text-align: center;\">&#8853;</div>"; }
			$feed_img .= "</div>";
			$feed_name = "<div style=\"font-size: 2em; cursor: pointer\" onclick=\"window.location='$_ROOTweb?feed_id=" . $feed['feed_id'] . "'\"><u>" . $feed['name'] . "</u></div>";
	
			$feed_cover = "";
			if(isset($feed['related'])) {
				foreach($feed['related'] as $related) {
					if(count($related['items']) > 0) {
						$feed_cover .= "<div class=\"left\"><div onClick=\"window.location='./?feed_id=" . $related['feed_id'] . "'\" class=\"margin-med\" style=\"width: 200px; height: 200px; margin: 20px; background-size: cover; background-image: url('" . $related['items'][0]['link'] . "')\"></div></div>";
					} else {
						$gallery_owner = ($related['owner_id'] == $client->user_serial) ? $related['owner_id'] : NULL;
						$feed_cover = "<div class=\"left\"><div onmouseover=\"domId('$imageRollover').style.display='block';\" onmouseout=\"domId('$imageRollover').style.display='none'\" class=\"margin-med\" style=\"width: 200px; height: 200px; background-image: url(files/feeds/gallery.png); background-size: cover\">";

						if($feed_owner) { $feed_cover .= "<div id=\"$imageRollover\" onclick=\"window.location='./?feed_id=" . $related['feed_id'] . "'\" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 160px; text-align: center;\">&#8853;</div>"; }
									
						$feed_cover .= "</div></div>";
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
			$page .= "<div class=\"center\" style=\" text-align: left; margin: 0px 16px\">";
			$page .= "<div id=\"itc_feed_name_form\" style=\"display: none;\"><form action=\"./?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\" method=\"post\"><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input class=\"form\" name=\"itc_feed_name\" value=\"" . $feed['name'] . "\"/><input class=\"tools\" type=\"submit\" name=\"submit\" value=\"&#9989; SAVE\"></div>";
			$page .= "<div id=\"itc_feed_name\" style=\"display: inline-block;\">" . $feed_name;	
										

			$page .= "</form></div>";			
			
			
			if($feed_owner){
				$page .= " <span class=\"tools\" onclick=\"this.style.display='none'; domId('itc_feed_name').style.display='none'; domId('itc_feed_delete').style.display='none'; domId('itc_feed_name_form').style.display='inline-block';\">&#9998; EDIT</span>";
			}			
			if($feed_owner){			
				$page .= "<div id=\"itc_feed_delete\" style=\"display: inline-block\"><form action=\"./?itc_feed_edit=purge\" method=\"post\"><input type=\"hidden\" name=\"itc_feed_edit\" value=\"purge\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input type=\"hidden\" name=\"feed_id\" value=\"" . $feed['feed_id'] . "\"/><input class=\"tools\" type=\"submit\" name=\"submit\" value=\"&#9988; DELETE\"></form></div>";		
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
			$index = $pageManager->index;
			$item_info_limit = 2800;
			$page = "";		
			
			if(isset($_GET['id'])) { 
				$pageManager->index = array_search($_GET['id'], array_column($pageManager->meta['feed']['items'], 'item_id'));	
				$index = $pageManager->index; 
				$page .= "<div class=\"audiofeed feed-" . $feed['display_type'] . "\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit) . "</" . $feed['display_type'] . ">";
				$page .= "<div class=\"clear\"></div>";
				$page .= "</div>";	
				
				if(isset($pageManager->meta['feed']['items'])) {
					$pageManager->items = $pageManager->meta['feed']['items'];
					$page .= "<div class=\"audiofeed feed-list\" style=\"text-align: left\">" . $pageManager->displayItems('list', 140) . "</div>";
				}
				
				$page .= "<div class=\"clear\"></div>";
				if($feed_owner){
					$omniBox = $pageManager->displayOmniFeedBox($pageManager, $pageManager->meta['feed'], $pageManager->items[$index]['item_id']);
					$page .= "<div style=\"margin: 14px\">" . $omniBox . "</div>";
				}	
			} else {	
				$items = $pageManager->items;	
				
				if($pageManager->items) {
					$tmp_items[$index] = $pageManager->items[$index];
					$pageManager->items = $tmp_items;
					
					$page .= "<div class=\"feed-" . $feed['display_type'] . "\">" . $pageManager->displayItems($feed['display_type'], $item_info_limit);
					$page .= "<div class=\"clear\"></div>";
					$page .= "</div>";	
				}
				
				$pageManager->items = $items;
				$page .= "<div class=\"feed-list\" style=\"text-align: left\">" . $pageManager->displayItems('list', 140) . "</div>";
								
				if($feed_owner){
					$omniBox = $pageManager->displayOmniFeedBox($pageManager, $pageManager->meta['feed'], $items[$index]['item_id']);
					$page .= "<div style=\"margin: 14px\">" . $omniBox . "</div>";
				}
				
				$page .= "<div class=\"clear\"></div>";
			}
			
			return "<div class=\"center\" style=\"\">" . $page . "</div>" . "<div class=\"clear\"></div>";
		}
	}	
}

class addonPostaudiofeedHandler extends Audiofeeds {

	function update ($itemManager) {
		$client = $itemManager->client;	
		
		$this->stream = $itemManager->stream;
		$user_id = $client->user_serial;
		$user_level = $client->level;
		
		if (isset($_POST['itc_audiofeed'])) {
			$item_id = $_POST['itc_audiofeed'];
			$feed_id = isset($_POST['itc_audiofeed_add']) ? $_POST['itc_audiofeed_add'] : NULL;
			
			if($feed_id && $item_id) {
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			} else if($item_id) {
				$name = $this->collection_name;
				$feed_id = $this->addItemaudiofeedFeed($user_id, $name, 'audiofeed.png', $item_id);
				
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			}
		}
	}
	
	function addItemaudiofeedFeed ($owner_id, $name, $feed_img, $item_id) {
			
			$gallery_addon = new imageGallery();
			
			//Check if item is already in a audiofeed feed
			$addon_check = "SELECT feed_items.item_id, feed.*, addon_feed.*"
			. " FROM feed_items, feed, addon_feed"
			. " WHERE feed_items.item_id=" . $item_id
			. " AND feed_items.feed_id=feed.feed_id"
			. " AND feed_items.feed_id=addon_feed.feed_id"
			. " AND addon_feed.addon_id=" . $this->addon_id;
			
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
					$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $this->addon_name . "', '" . $this->addon_id . "', '" . $this->collection_name . "', '" . $this->item_name . "', '$parent_feed_id', 0, 0)";
					$success = mysqli_query($this->stream, $user_quest);					
					
					//item needs to be added to feed_items for addon feed
					$quest = "INSERT INTO feed (owner_id, name, feed_img, parent_id, level) VALUES('$owner_id', 'Cover Art', 'gallery.png', $parent_feed_id, '3')";
					$success = mysqli_query($this->stream, $quest);
					$feed_id = mysqli_insert_id($this->stream);

					$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $gallery_addon->addon_name . "', '" . $gallery_addon->addon_id . "', '" . $gallery_addon->collection_name . "', '" . $gallery_addon->item_name . "', '$feed_id', 0, 0)";
					$success = mysqli_query($this->stream, $user_quest);

					return $parent_feed_id;
				}
			}
			
	}
}

class addonItemaudiofeedDisplay extends Audiofeeds {
	function update($itemDisplay) {
		global $_ROOTweb;
		global $client;
				
		if($itemDisplay->class_id != 5) { return; }
				
		//include to use with another add-on
		$metaOutput = ($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) ? $itemDisplay->metaOutput : "";
		
		$feed_thumbnail = "files/feeds/audiofeed.png";
		$metaOutput = "<div style=\"float: left; padding-left: 4px; font-size: 12px;\">";
		if(isset($itemDisplay->item['audiofeed-feeds'])) {
			$i = 0;
			foreach($itemDisplay->item['audiofeed-feeds'] as $feed) {
			  $feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "&id=" . $itemDisplay->item_id . "\">" 
					. $feed['name'] 
					. "</a>" . " ("  . count($feed['items']) . ")";
				
				$feed_thumbnail = isset($feed['related'][0]['items'][0]) ? $feed['related'][0]['items'][0]['link'] : $feed_thumbnail;  

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
					. "<div class='inline-remove'>";

					$remove_button .= " <a onclick=\"domId('removeAudiofeedForm" . $i . $index . "').submit()\">x</a>";	
					$remove_button .= "</div>";
					$remove_button .= "</form></div>";
					
					$metaOutput .=  $remove_button;
				}	
				$metaOutput .= $feed_img;					
				$i++;
			}
		} else if($client->user_serial){

			if($itemDisplay->owner) {			
				$count_text = (isset($itemDisplay->item['audiofeed-feeds']['items'])) ? count($itemDisplay->item['audiofeed-feeds']['items']) : 0;
				
				$page_form = "itc_audiofeed_" . $itemDisplay->item['item_id'];
				$link_submit = " onclick=\"domId('$page_form').submit()\"";
				
				$audiofeedInput = " " . $this->collection_name;
				if(isset($itemDisplay->item['user-audiofeed-feeds'])) { 
					$audiofeedInput = " <select type=\"dropdown\" onchange=\"domId('$page_form').submit()\" onfocus=\"this.selectedIndex = -1\" name=\"itc_audiofeed_add\">";
					foreach($itemDisplay->item['user-audiofeed-feeds'] as $tmp_feed) { 
						$audiofeedInput .= "<option value=\"" . $tmp_feed['feed_id'] . "\">" . $tmp_feed['name'] . "</option>";
					}
					
					$audiofeedInput .= "<option value=\"\">+ New</option>";
					$link_submit = "";
					$audiofeedInput .= "</select>";
				}
				
				$metaOutput .= "<form id=\"$page_form\" action=\"$_ROOTweb?id=" . $itemDisplay->item['item_id'] . "\" method=\"post\"><input type=\"hidden\" id=\"itc_audiofeed\" name=\"itc_audiofeed\" value=\"" . $itemDisplay->item['item_id'] . "\"/>"
					. "<a title=\"Add to "  . $this->collection_name . "\"$link_submit>"				
					. "Add to "
					. $audiofeedInput
					. "</a>"
					. "</form>";
			}
		} 
		$metaOutput .= "</div>";
		
		$thumbnail_style = "";
		if(!isset($_GET['feed_id'])){
			$itemDisplay->box_class = "card";
			$thumbnail_style = " style=\"position: relative; background-size: cover; background-image: url('" . $feed_thumbnail . "');\"";
		}
		if(isset($_GET['id']) && $itemDisplay->item_id == $_GET['id']) {
			$itemDisplay->box_style = " ";
		}
		$itemDisplay->fileOutput = $this->inlineAudioOverride($itemDisplay, $thumbnail_style);
		$itemDisplay->userTools .= $metaOutput;
	}
	
	function inlineAudioOverride ($itemDisplay, $thumbnail_style) {		
		$id = $itemDisplay->item_id . rand(1, 1000);
		
		$autoplay = "";
		$hide_play = " style=\"display: inline-block\"";
		$hide_pause = " style=\"display: none\"";
		if (isset($_GET['id']) && $_GET['id'] == $itemDisplay->item_id && $itemDisplay->box_class == 'item-page') { 
			$autoplay = " autoplay";
			$hide_play = " style=\"display: none\"";
			$hide_pause = " style=\"display: inline-block\"";
		}
		
		$file_display = "<div class=\"file\"$thumbnail_style><audio id=\"player$id\"$autoplay><source src=\"". $itemDisplay->webroot .  $itemDisplay->file . "\"></audio>"
			. "<div class=\"file_text\">"
			. "<span class=\"light\" id=\"play_button$id\" onclick=\"domId('player$id').play(); this.style.display='none'; domId('pause_button$id').style.display='inline-block';\"$hide_play><img src=\"img/ui/play.png\"/></span>"
			. "<span class=\"light\" id=\"pause_button$id\" onclick=\"domId('player$id').pause(); this.style.display='none'; domId('play_button$id').style.display='inline-block';\"$hide_pause><img src=\"img/ui/pause.png\"/></span>"
			. "</div></div>";
		return $file_display;			
	}	
}

class addonItemaudiofeedRequest extends Audiofeeds {
	function update($itemManager){
		$this->stream = $itemManager->stream;
		$this->item_loot = $itemManager->item_loot;
		
		$level = $itemManager->client->level;
		$tmp_loot_array = NULL;
		$user_feeds = NULL;
		global $client;
			
		$user_quest = "SELECT feed.*, addon_feed.*"
		. " FROM feed, addon_feed"
		. " WHERE feed.feed_id=addon_feed.feed_id"
		. " AND addon_feed.addon_id=" . $this->addon_id
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
			. " AND addon_feed.addon_id=" . $this->addon_id;

			$feed_loot = mysqli_query($this->stream, $quest);
			$feeds = NULL;
			$item_parent = [];
			if($feed_loot !== false) {
				while($tmp_loot=$feed_loot->fetch_assoc()) {	
					$feed_id = $tmp_loot['feed_id'];			
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
					
					//SHOULD BE UTILIZING getFeed by Parent function
					$related_quest = "SELECT feed.* FROM feed WHERE parent_id='$feed_id' AND feed_id != '$feed_id'";
					$related_loot_return = mysqli_query($this->stream, $related_quest);
					
					if($tmp_loot['parent_id']) {
						 $parent_id = $tmp_loot['parent_id'];
						 $parent_quest = "SELECT feed.* FROM feed WHERE feed_id='$parent_id'";
						 $parent_loot_return = mysqli_query($this->stream, $parent_quest);
						 $tmp_loot['parent'] = $parent_loot_return->fetch_assoc();		     
					}

					while($related_loot = $related_loot_return->fetch_assoc()) {
						
						//SHOULD BE UTILIZING getChildFeedItems
						$related_items = "SELECT feed_items.*, item.*, user_items.*"
							. " FROM feed_items, item, user_items"
							. " WHERE feed_items.feed_id='" . $related_loot['feed_id'] . "'"
							. " AND feed_items.item_id=item.item_id"
							. " AND item.item_id=user_items.item_id";
				 
						$tmp_items = NULL;
						$related_items_loot = mysqli_query($this->stream, $related_items);
						if($related_items_loot !== false) { 
							while($tmp_related_loot = $related_items_loot->fetch_assoc()) { 
								$related_loot['items'][] = $tmp_related_loot; 
							}
						}
						$tmp_loot['related'][] = $related_loot;
					}
					
					$feeds[] = $tmp_loot;
				}		
				$item = $this->mergeFeeds($item, $feeds, $user_feeds);
			}

			$tmp_loot_array[] = $item;
		} }
		$this->item_loot = $tmp_loot_array;
		$itemManager->item_loot = $this->item_loot;
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
