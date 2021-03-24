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

class favoriteItems {
	function __construct () {
		$this->addon_title = 'Lemon Favorite';
		$this->addon_name = 'lemon-favorite';
		$this->addon_version = '1.0';
		$this->collection_name = 'Favorites';
		$this->item_name = 'Item';
		$this->addon_id = '1004';
		$this->add_new = true;
	}

	function setActions () {
		global $actions;
		$actions['post-handler'][] = 'addonPostFavoriteHandler';
		//$actions['page-display'][] = 'addonFavoritePageDisplay';
		$actions['item-display'][] = 'addonItemFavoriteDisplay';
		$actions['item-request'][] = 'addonItemFavoriteRequest';
	}
}

//Add to global $addOns variable
//$addOns[] = 'favoriteItems';

class addonPostFavoriteHandler  extends favoriteItems {

	function update ($itemManager) {
		$client = $itemManager->client;	
		
		$this->stream = $itemManager->stream;
		$user_id = $client->user_serial;
		$user_level = $client->level;
		
		if (isset($_POST['itc_favorite'])) {
			$item_id = $_POST['itc_favorite'];
			
			if($item_id) {
				$feed_id = $this->addItemFavoriteFeed($user_id, 'Favorites', 'favorite.png', $item_id);
				
				$postFeedHandler = new addonPostFeedHandler($this->stream);				
				$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
			}
		}
	}
	
	function addItemFavoriteFeed ($owner_id, $name, $feed_img, $item_id) {
		
			//Check if 'default' favorite feed has been created
			$addon_check = "SELECT * FROM addon_feed, feed"
				. " WHERE addon_feed.addon_id='" . $this->addon_id . "'"
				. " AND feed.feed_id=addon_feed.feed_id"
				. " AND feed.owner_id=" . $owner_id;
				
			$check = mysqli_query($this->stream, $addon_check);
			$addon_feed = $check->fetch_assoc();

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
					$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $this->addon_name . "', '" . $this->addon_id . "', '" . $this->collection_name . "', '" . $this->item_name . "', '$feed_id', 0, 1)";
					$success = mysqli_query($this->stream, $user_quest);
					return $feed_id;
				}
			}
			
	}
}

class addonItemFavoriteDisplay extends favoriteItems  {
	function update($itemDisplay) {
		global $_ROOTweb;
		global $client;
		
		//include to use with another add-on
		$raw_input = ($itemDisplay->userTools == $itemDisplay->itemUserTools()) ? $itemDisplay->userTools : NULL;
		//if($raw_input) { $itemDisplay->userTools = ""; }
				
		//Currently grabs feed for active user or most recent feed
		//Link to feed of feed items with this addon_id (all users)
		$userTools = "<div class=\"float-right\" style=\" padding-left: 4px; font-size: 12px;\">";
		if(isset($itemDisplay->item['favorite-feeds'])) {
			$i = 0;
			foreach($itemDisplay->item['favorite-feeds'] as $feed) {
			  $feed_img_src = "files/feeds/" . $feed['feed_img'];
			
			  $feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">" 
					. "<img class=\"pinned feed-image\" src=\"" . $feed_img_src . "\"/>" 
					. "</a>" . $itemDisplay->item['favorite-count'];
					
				$index = $itemDisplay->item['item_id'];
				if(($feed['owner_id'] == $client->user_serial) && $index) {
					//remove feed from item if owner
					$remove_button = "<div style='display: inline-block'><form id='removeFavoriteForm" . $i . $index . "' action='?id=" . $index. "' method='post'>"
					. "<input type='hidden' name='item_id' value='" . $index . "'/>"
					. "<input type='hidden' name='feed_id' value='" . $feed['feed_id'] . "'/>"
					. "<input type='hidden' name='feed' value='remove'/>"		
					. "<div class='inline-remove'>";

					$remove_button .= " <a onclick=\"domId('removeFavoriteForm" . $i . $index . "').submit()\">x</a>";	
					$remove_button .= "</div>";
					$remove_button .= "</form></div>";
					
					$userTools .=  $remove_button;
				}	
				$userTools .= $feed_img;					
				$i++;
			}
		} else if($client->user_serial) {

			$feed_img = "<img class=\"pinned feed-image\" src='files/feeds/favorite-add.png'/>";			
			$count_text = (isset($itemDisplay->item['favorite-count'])) ? $itemDisplay->item['favorite-count'] : "";
			
			$page_form = "itc_fav_" . $itemDisplay->item['item_id'];
			$userTools .= "<form id=\"$page_form\" action=\"$_ROOTweb?id=" . $itemDisplay->item['item_id'] . "\" method=\"post\"><input type=\"hidden\" name=\"itc_favorite\" value=\"" . $itemDisplay->item['item_id'] . "\"/>"
				. "<a title=\"Add to Favorites\" onclick=\"domId('$page_form').submit()\">"				
				. $feed_img
				. "</a>"
				. $count_text
				. "</form>";
		} else {
		
			$feed_img = "<img class=\"feed-image\" src='files/feeds/favorite-add.png'/>";		
			$count_text = (isset($itemDisplay->item['favorite-count'])) ? $itemDisplay->item['favorite-count'] : "";	
			
			$page_form = "itc_fav_" . $itemDisplay->item['item_id'];
			$userTools .= "<a title=\"Add to Favorites\" onclick=\"window.location='./?connect=1'\">"				
				. $feed_img
				. "</a>" . $count_text;		
		}
		$userTools .= "</div>";
		
		$itemDisplay->userTools .= $userTools;
	}
}

class addonItemFavoriteRequest extends favoriteItems {
	function update($itemManager){
		$this->stream = $itemManager->stream;
		$this->item_loot = $itemManager->item_loot;
		
		$level = $itemManager->client->level;
		$tmp_loot_array = NULL;
		global $client;
		
		if($this->item_loot) { foreach($this->item_loot as $item) {
			if ($client->user_serial) {
				//Check if 'default' favorite feed has been created for owner
				$addon_check = "SELECT addon_feed.*, feed.* FROM addon_feed, feed"
					. " WHERE addon_feed.addon_id=" . $this->addon_id
					. " AND addon_feed.feed_id=feed.feed_id"
					. " AND feed.owner_id=" . $client->user_serial;
						
				$check = mysqli_query($this->stream, $addon_check);
				if($check !== false) { $addon_feed = $check->fetch_assoc(); }
							
				if($addon_feed) {
					//Check if item is in favorite feed
					//Get favorite feed and set favorite_feeds
					$quest = "SELECT feed_items.item_id, feed.*"
					. " FROM feed_items, feed"
					. " WHERE feed_items.item_id=" . $item['item_id']
					. " AND feed_items.feed_id=feed.feed_id"
					. " AND feed.feed_id=" . $addon_feed['feed_id'];
			 
					$feed_loot = mysqli_query($this->stream, $quest);
					$feeds = NULL;
					$item_parent = [];
					if($feed_loot->num_rows > 0) {
						while($tmp_loot=$feed_loot->fetch_assoc()) {
							$feeds[] = $tmp_loot;
						}
						$item['favorite-feeds'] = $feeds;
					}
				} 	
			} else {
				//Check if item is in favorite feed
				//Get favorite feed and set favorite_feeds
				$quest = "SELECT feed_items.item_id, feed.*, addon_feed.*"
				. " FROM feed_items, feed, addon_feed"
				. " WHERE feed_items.item_id=" . $item['item_id']
				. " AND feed_items.feed_id=feed.feed_id"
				. " AND feed_items.feed_id=addon_feed.feed_id"
				. " AND addon_feed.addon_id=" . $this->addon_id
				. " ORDER BY feed_items.date DESC" //return most recent feed
				. " LIMIT 1";

				$feed_loot = mysqli_query($this->stream, $quest);
				$feeds = NULL;
				$item_parent = [];
				if($feed_loot !== false) {
					while($tmp_loot=$feed_loot->fetch_assoc()) {
						$feeds[] = $tmp_loot;
					}
					$item['favorite-feeds'] = $feeds;
				}
			}
			
			$item_count = "SELECT feed_items.item_id, feed.*, addon_feed.feed_id"
			. " FROM feed_items, feed, addon_feed"
			. " WHERE feed_items.item_id=" . $item['item_id']
			. " AND feed_items.feed_id=feed.feed_id"
			. " AND feed_items.feed_id=addon_feed.feed_id"
			. " AND addon_feed.addon_id=" . $this->addon_id;
	 
			$count_loot = mysqli_query($this->stream, $item_count);
			$item['favorite-count'] = 0;
			if($count_loot !== false) { $item['favorite-count'] = $count_loot->num_rows; }
			
			$tmp_loot_array[] = $item;
	
		} }
		$this->item_loot = $tmp_loot_array;
		$itemManager->item_loot = $this->item_loot;
		return $this->item_loot;
	}
	
	function mergeFeeds ($feeds, $item, $item_parent){
		$item['favorite-feeds'] = $feeds;
		return $item;
	}	
}
?>
