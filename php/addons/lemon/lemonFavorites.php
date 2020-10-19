<?php //Add-On for reply display
$favorite_addon['addon_title'] = 'Lemon Favorite';
$favorite_addon['addon_name'] = 'lemon-favorite';
$favorite_addon['addon-version'] = '1.0';
$favorite_addon['collection_name'] = 'Favorites';
$favorite_addon['item_name'] = 'Item';
$favorite_addon['addon_id'] = '1004';

$favorite_addon['post-handler'] = 'addonPostFavoriteHandler';
//$favorite_addon['page-display'] = 'addonFavoritePageDisplay';
$favorite_addon['item-display'] = 'addonItemFavoriteDisplay';
$favorite_addon['item-request'] = 'addonItemFavoriteRequest';
$favorite_addon['add-new'] = true;

//Add to global $addOns variable
//$addOns[] = $favorite_addon;

class addonPostFavoriteHandler {
	function __construct ($stream) {
		$this->stream = $stream;
	}

	function handleAddOnPost ($itemManager) {
		global $client;	
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
			global $favorite_addon;
		
			//Check if 'default' favorite feed has been created
			$addon_check = "SELECT * FROM addon_feed, feed"
				. " WHERE addon_feed.addon_id='" . $favorite_addon['addon_id'] . "'"
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
					$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $favorite_addon['addon_name'] . "', '" . $favorite_addon['addon_id'] . "', '" . $favorite_addon['collection_name'] . "', '" . $favorite_addon['item_name'] . "', '$feed_id', 0, 1)";
					$success = mysqli_query($this->stream, $user_quest);
					return $feed_id;
				}
			}
			
	}
}

class addonItemFavoriteDisplay {
	function updateOutputHTML($itemDisplay) {
		global $_ROOTweb;
		global $client;
		global $favorite_addon;
		
		//include to use with another add-on
		$raw_input = ($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) ? $itemDisplay->metaOutput : NULL;
		if($raw_input) { $itemDisplay->metaOutput = ""; }
				
		//Currently grabs feed for active user or most recent feed
		//Link to feed of feed items with this addon_id (all users)
		$itemDisplay->metaOutput .= "<div style=\"float: right; padding-left: 4px; font-size: 12px;\">";
		if(isset($itemDisplay->item['favorite-feeds'])) {
			$i = 0;
			foreach($itemDisplay->item['favorite-feeds'] as $feed) {
			  $feed_img_src = "files/feeds/" . $feed['feed_img'];
			
			  $feed_img = "<a href=\"?feed_id=" . $feed['feed_id'] . "&name=" . $feed['name'] . "\">" 
					. "<img class=\"feed-image\" src=\"" . $feed_img_src . "\"/>" 
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
					
					$itemDisplay->metaOutput .=  $remove_button;
				}	
				$itemDisplay->metaOutput .= $feed_img;					
				$i++;
			}
		} else if($client->user_serial) {

			$feed_img = "<img class=\"feed-image\" src='files/feeds/favorite-add.png'/>";			
			$count_text = (isset($itemDisplay->item['favorite-count'])) ? $itemDisplay->item['favorite-count'] : "";
			
			$page_form = "itc_fav_" . $itemDisplay->item['item_id'];
			$itemDisplay->metaOutput .= "<form id=\"$page_form\" action=\"$_ROOTweb?id=" . $itemDisplay->item['item_id'] . "\" method=\"post\"><input type=\"hidden\" name=\"itc_favorite\" value=\"" . $itemDisplay->item['item_id'] . "\"/>"
				. "<a title=\"Add to Favorites\" onclick=\"domId('$page_form').submit()\">"				
				. $feed_img
				. "</a>"
				. $count_text
				. "</form>";
		} else {
		
			$feed_img = "<img class=\"feed-image\" src='files/feeds/favorite-add.png'/>";		
			$count_text = (isset($itemDisplay->item['favorite-count'])) ? $itemDisplay->item['favorite-count'] : "";	
			
			$page_form = "itc_fav_" . $itemDisplay->item['item_id'];
			$itemDisplay->metaOutput .= "<a title=\"Add to Favorites\" onclick=\"window.location='./?connect=1'\">"				
				. $feed_img
				. "</a>" . $count_text;		
		}
		$itemDisplay->metaOutput .= "</div>";
	}
}

class addonItemFavoriteRequest {
	function __construct ($stream, $items) {
		$this->stream = $stream;
		$this->item_loot = $items;
	}
	
	function getAddOnLoot ($level){
		$tmp_loot_array = NULL;
		global $favorite_addon;
		global $client;
		
		if($this->item_loot) { foreach($this->item_loot as $item) {
			if ($client->user_serial) {
				//Check if 'default' favorite feed has been created for owner
				$addon_check = "SELECT addon_feed.*, feed.* FROM addon_feed, feed"
					. " WHERE addon_feed.addon_id=" . $favorite_addon['addon_id']
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
				. " AND addon_feed.addon_id=" . $favorite_addon['addon_id']
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
			. " AND addon_feed.addon_id=" . $favorite_addon['addon_id'];
	 
			$count_loot = mysqli_query($this->stream, $item_count);
			$item['favorite-count'] = 0;
			if($count_loot !== false) { $item['favorite-count'] = $count_loot->num_rows; }
			
			$tmp_loot_array[] = $item;
	
		} }
		$this->item_loot = $tmp_loot_array;
		return $this->item_loot;
	}
	
	function mergeFeeds ($feeds, $item, $item_parent){
		$item['favorite-feeds'] = $feeds;
		return $item;
	}	
}
?>
