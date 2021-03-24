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

class itemReply {
	function __construct() {
		$this->addon_title = 'Lemon Item Reply (Comment Feeds)';
		$this->addon_name = 'lemon-reply';
		$this->addon_version = '1.0.2';
		$this->collection_name = 'Comments';
		$this->item_name = 'Item';
		$this->addon_id = '1002';
		$this->add_new = true;
	}

	function setActions() {global $actions;
		$actions['post-handler'][] = 'addonPostReplyHandler';
		$actions['page-display'][] = 'addonReplyPageDisplay';
		$actions['item-display'][] = 'addonItemReplyDisplay';
		$actions['item-request'][] = 'addonItemReplyRequest';
	}
}

//Add to global $addOns variable
//$addOns[] = 'itemReply';

class addonReplyPageDisplay extends itemReply  {
	
	function update ($pageManager) {	
		$this->pageManager = $pageManager;
		 
		global $client;
		$page = "";
		$createForm = "";
			
		$this->box_class = "box";
		$info_limit = 240;
		$count = 0;
		
		$pageManager = $this->pageManager;
		$index = $pageManager->index;
		
		$page_class = isset($_GET['item_id']) && isset($_GET['feed_id']) ? "right" : "center";
		if(isset($this->pageManager->meta['feed']['feed_addon']) && isset($_GET['feed_id'])) {
			if($this->pageManager->meta['feed']['feed_addon']['addon_id'] == '1003') { 
				$page_class = "right";
			}
		} 
		$this->page_class = $page_class;

		if(isset($pageManager->items[$index]['addon-feeds'][0]['feed_id'])) {
			if($client->auth == true && $pageManager->items[$index]['addon-classes']) {
				$page .= "<div class=\"$page_class\">"; 
				$createForm = $this->displayOmniCommentBox($pageManager, $pageManager->items[$index]['addon-feeds'][0]['feed_id'], $pageManager->items[$index]['item_id'], $pageManager->items[$index]['addon-classes']);			
				if($pageManager->items[$index]['level'] > 0) { $page .= $createForm; }
				$page .= "<div class=\"clear\"></div>";
				$page .= "</div>";
			}
			if(isset($pageManager->items[$index]['addon-feeds'][0]['items'])) {
				$page .= "<div class=\"$page_class\">"; 
				foreach($pageManager->items[$index]['addon-feeds'][0]['items'] as $item) {
					
					$itemDisplay = new ItemDisplay($item, $pageManager->ROOTweb, $this->box_class, $pageManager->user_id, $info_limit, $pageManager->uri_prefix);				
					if(isset($pageManager->actions)) { runAddons($pageManager->actions, $itemDisplay, 'item-display'); }	
					$itemDisplay->nodeOutput = $itemDisplay->nodeOutputHTML();
					$item_html = $itemDisplay->displayHTML();
					
					$page .= $item_html;
					$count++;
				} 
				$page .= "<div class=\"clear\"></div>";
				$page .= "</div>";
			}
		} else if(!isset($pageManager->items[$index]['item-parent'][0])) {
			//Only allows comments on items without parents
			$classes = (isset($pageManager->items[$index]['addon-classes'])) ? $pageManager->items[$index]['addon-classes'] : [];
			if($client->auth == true) {
				$createForm = $this->displayOmniFirstCommentBox($pageManager, $pageManager->items[$index]['item_id'], $classes);
				$page .= "<div class=\"$page_class\">";
				if($this->add_new) {
					$page .= $createForm;
					$page .= "<div class=\"clear\"></div>";	
				}
				$page .= "</div>";	
			}
		}
		
		if(isset($this->pageManager->meta['feed']['feed_addon']) && isset($_GET['id']) && isset($_GET['feed_id'])) {
			if($this->pageManager->meta['feed']['feed_addon']['addon_id'] == '1003') { 
				$pageManager->section['right']['output'] .= $page;
			}
		} else if (isset($_GET['id']) && isset($_GET['feed_id'])) {
			$pageManager->section['right']['output'] .= $page;
		} else if(isset($_GET['id'])) { 
			$pageManager->section['right']['output'] = $page;
		} 
	}
	
	function displayOmniCommentBox($pageManager, $feed_id, $item_id, $addon_classes) {
		$box_class = $this->box_class;
		$classes =($addon_classes) ? $addon_classes : $pageManager->classes; 
		
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController$item_id = new OmniCommentBox(" . $class_js_array . ", 'itemOmniBox$item_id');\n OmniController$item_id.set_active_str('$item_id');\n OmniController$item_id.set_active_feed('" . $feed_id . "');\n  OmniController$item_id.set_active_item_id('" . $item_id . "');\n OmniController$item_id.set_active_item_id('" . $item_id . "');\n OmniController$item_id.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div>" : "<div id=\"alertbox\" class=\"alertbox-hide\"></div>";
		
		$createForm  = "<div class=\"$box_class\"><div style=\"display: none; padding: 8px\" class=\"item\" id=\"itemOmniBox$item_id\">" . "</div>"
				. "<div onclick=\"domId('itemOmniBox$item_id').style.display='block'; this.style.display='none'\" style=\"padding: 8px; text-align: center; cursor: pointer\"><div>+ <u>Add a Comment</u></div></div>"
				. "</div>";				
		$createForm .= $javascript_omni_box;
		return $message . $createForm . $javascript_omni_box;
	}
	
	function displayOmniFirstCommentBox($pageManager, $item_id, $addon_classes) {
		$box_class = $this->box_class;
		$classes =(isset($addon_classes)) ? $addon_classes : $pageManager->classes; 
		
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController$item_id = new OmniFirstCommentBox(" . $class_js_array . ", 'itemOmniBox$item_id');\n OmniController$item_id.set_active_str('" . $item_id . "');\n OmniController$item_id.set_active_item_id('" . $item_id . "');\n OmniController$item_id.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div>" : "<div id=\"alertbox\" class=\"alertbox-hide\"></div>";
			
		$createForm  = "<div class=\"$box_class\"><div style=\"display: none; padding: 8px\" class=\"item\" id=\"itemOmniBox$item_id\">" . "</div>"
				. "<div class=\"float-right\" onclick=\"domId('itemOmniBox$item_id').style.display='inline-block'; this.style.display='none'\" style=\"padding: 8px; text-align: center; cursor: pointer\"><div>+ <u>Add a Comment</u></div></div>"
				. "</div>";
		$createForm .= $javascript_omni_box;
		return $message . $createForm . $javascript_omni_box;
	}
	
	function addonClassesById($addon_id) {
		$classes = "SELECT addon_class.class_id, feed_items.item_id"
			. " FROM addon_feed, feed_items"
			. " WHERE feed_items.item_id='$item_id'"
			. " AND addon_feed.item_id='$item_id'";
			
		$check = mysqli_query($this->stream, $user_check);
	}
}

class addonPostReplyHandler extends itemReply  {
	function update ($itemManager) {
		$this->stream = $itemManager->stream;
		$client = $itemManager->client;	
		$user_id = $client->user_serial;
		$user_level = $client->level;
		
		if(isset($_POST['itc_add_item_comment'])){ 
			$feed_id = $_POST['itc_add_item_comment'];
			$item_id = $_POST['itc_add_item_comment_id'];
			
			if($item_id) {
				$postFeedHandler = new addonPostFeedHandler($this->stream);
				$new_item_id = $itemManager->handleItemUpload($client);
				$itemManager->insertUserItem($user_id, $new_item_id, 0);
				
				if($itemManager->insertOk == "1" && isset($itemManager->item_id)) {
					$itemManager->active = true;
					$postFeedHandler->addItemFeed($user_id, $itemManager->item_id, $feed_id);
				}
				header("Location: ./?id=" . $item_id);
			}
		} else if (isset($_POST['itc_add_item_comment_id'])) {
			$item_id = $_POST['itc_add_item_comment_id'];
			
			if($item_id) {
				$feed_id = $this->newItemCommentFeed($user_id, 'Comments', 'comments.png', $item_id);
				
				$postFeedHandler = new addonPostFeedHandler($this->stream);
				$new_item_id = $itemManager->handleItemUpload($client);
				$itemManager->insertUserItem($user_id, $new_item_id, 0);
				
				if($itemManager->insertOk == "1" && isset($itemManager->item_id)) {
					$itemManager->active = true;
					$postFeedHandler->addItemFeed($user_id, $item_id, $feed_id);
					$postFeedHandler->addItemFeed($user_id, $itemManager->item_id, $feed_id);
				}
				header("Location: ./?id=" . $item_id);
			}
		}
	}
	
	function newItemCommentFeed ($owner_id, $name, $feed_img, $item_id) {
			global $reply_addon;
			
			$user_check = "SELECT addon_feed.*, feed_items.item_id"
				. " FROM addon_feed, feed_items"
				. " WHERE feed_items.item_id='$item_id'"
				. " AND addon_feed.item_id='$item_id'";
				
			$check = mysqli_query($this->stream, $user_check);
			
			if(mysqli_num_rows($check) > 0) {
				$addon_feed = $check->fetch_assoc();
				$feed_id = $addon_feed['feed_id'];
			} else {
				$quest = "INSERT INTO feed (owner_id, name, feed_img, level) VALUES('$owner_id', '$name', '$feed_img', '0')";
				$success = mysqli_query($this->stream, $quest);
				$feed_id = mysqli_insert_id($this->stream);
			}
			
			if($feed_id) {
				$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $reply_addon['addon_name'] . "', '" . $this->addon_id . "', '" . $this->collection_name . "', '" . $this->item_name . "', '$feed_id', '$item_id', 1)";
				$success = mysqli_query($this->stream, $user_quest);
				return $feed_id;
			}
	}
}

class addonItemReplyDisplay extends itemReply  {
	function update ($itemDisplay) {
		$metaOutput = "";
		if($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) {
			$metaOutput  = "";
		}	
			
		$metaOutput  .= "<div style=\"float: right; padding: 0px 4px; \">";
		if (isset($itemDisplay->item['item-parent'][0]) && $itemDisplay->item['item-parent'][0]['item_id'] != $itemDisplay->item_id) {
			$name = ($itemDisplay->item['item-parent'][0]['title']) ? $itemDisplay->item['item-parent'][0]['title'] : "a post";
			$name = chopString($name, '14', '...') ;
			
			if(isset($_GET['id']) && $itemDisplay->item['item-parent'][0]['item_id'] != $_GET['id']) {
				$itemDisplay->userTools .= "<div title=\"$name\" class=\"float-left padding-med\">Comment @ <a href=\"./?id=" . $itemDisplay->item['item-parent'][0]['item_id'] . "\">$name</a></div><div class=\"clear\"></div>";			
			}else if(!isset($_GET['id'])) {
				global $actions;

				//Show the parent item below
				$tmpItem = new ItemDisplay($itemDisplay->item['item-parent'][0], $itemDisplay->webroot, 'card' . ' parent' . $itemDisplay->item['item-parent'][0]['item_id'], $itemDisplay->user_id, null, '?');	
				if(isset($actions)) { runAddons($actions, $tmpItem, 'item-display'); }	
				$tmpItem->nodeOutput = $tmpItem->nodeOutputHTML();
				$item_html = $tmpItem->displayHTML();	

				$itemDisplay->userTools .= "<div title=\"$name\" class=\"float-left padding-med\">Comment @ <a href=\"./?id=" . $itemDisplay->item['item-parent'][0]['item_id'] . "\">$name</a></div><div class=\"clear\"></div>";								
				$itemDisplay->userTools .= 	$item_html;
			}
			
		} else if(isset($itemDisplay->item['addon-feeds'])) {
			foreach($itemDisplay->item['addon-feeds'] as $feed) {
				$metaOutput .= "<a title=\"" . $feed['name'] . "(" . count($feed['items']) . ")" . "\" onclick=\"window.location='./?id=" . $itemDisplay->item_id . "'\">";
				$metaOutput .= "&#9776;</a> (" . count($feed['items']) . ")";
			}
		} else if (!isset($_GET['id'])) {
			$metaOutput .= "<a title=\"Comments (0)\" onclick=\"window.location='./?id=" . $itemDisplay->item_id . "'\">&#9776;</a> (0)";
		}
		$metaOutput  .= "</div>";
		$itemDisplay->metaOutput = $metaOutput . $itemDisplay->metaOutput;
	}
}

class addonItemReplyRequest extends itemReply {
	
	function update ($itemManager){
		$this->stream = $itemManager->stream;
		$this->item_loot = $itemManager->item_loot;
		
		$feeds = NULL;
		$tmp_loot_array = NULL;
		if(!$this->item_loot) { 
				return;
		}

		foreach($this->item_loot as $item) {
				$feeds = NULL;
			if((isset($_GET['feed_id']) && isset($_GET['id']) && $_GET['id'] == $item['item_id']) 
				|| !isset($_GET['feed_id'])) { 
				
				$quest = "SELECT feed_items.*, feed.*, addon_feed.*"
				 . " FROM feed_items, feed, addon_feed"
				 . " WHERE feed_items.item_id=" . $item['item_id'] 
				 . " AND feed.feed_id=feed_items.feed_id"
				 . " AND feed.feed_id=addon_feed.feed_id"
				 . " AND addon_feed.item_id=" . $item['item_id'] 
				 . " AND addon_feed.addon_id=" . $this->addon_id;
				 
				$feed_loot = mysqli_query($this->stream, $quest);
				$item_parent = [];			
				
				if($feed_loot->num_rows > 0) {
					while($loot=$feed_loot->fetch_assoc()) {
						$quest = "SELECT feed_items.*, item.*"
							. " FROM feed_items, item"
							. " WHERE feed_items.feed_id='" . $loot['feed_id'] . "'"
							. " AND feed_items.item_id=item.item_id"
							. " AND item.item_id != '" . $item['item_id'] . "'"
							. " ORDER BY feed_items.date DESC";
							
						$feed_items_loot = mysqli_query($this->stream, $quest);
						$feed_item_loot_array = NULL;
						if($feed_items_loot) {
							$feeds = NULL;
							while($feed_item=$feed_items_loot->fetch_assoc()) {
								if($feed_item['item_id'] != $item['item_id']) { $item_parent[] = $item; }
								
								$feed_item['item-parent'] = $item_parent;
								$feed_item_loot_array[] = $feed_item;	
							}	
							
							//get profiles for feed items (reply add on)
							//$profileRequest = new addonItemProfileRequest();
							//$profileRequest->set_item_loot($feed_item_loot_array);
							//$feed_item_loot_array = $profileRequest->update($itemManager);
							$loot['items'] = $feed_item_loot_array;
							$feeds[] = $loot;
						}
					}
				} else {			

					$addon_quest = "SELECT feed_items.*, addon_feed.*, item.*"
						. " FROM feed_items, addon_feed, item"
						. " WHERE feed_items.item_id=" . $item['item_id']
						. " AND feed_items.feed_id=addon_feed.feed_id"
						. " AND addon_feed.item_id=item.item_id";
						
					$addon_feed_loot = mysqli_query($this->stream, $addon_quest); 
					while($parent = $addon_feed_loot->fetch_assoc()) {
						
						
						$item_parent[] = $parent;
						
						
					}
						//get profiles for feed items (reply add on)
						$profileRequest = new addonItemProfileRequest();
						$profileRequest->set_item_loot($item_parent);
						$item_parent = $profileRequest->update($itemManager);
						
					//$item = $this->mergeFeeds($feeds, $item, $item_parent);
				}
				$tmp_loot_array[] = $this->mergeFeeds($feeds, $item, $item_parent);
			} else {
				$tmp_loot_array[] = $item;
			}
		} 
		
		if($tmp_loot_array) {
			$itemManager->item_loot = $tmp_loot_array;
		}
	}
	
	function getAddOnClasses() {
		global $client;
		
		$stream = $this->stream;
		$level = $client->level;
		
		$class_quest = "SELECT addon_class.*, item_class.*, item_nodes.*"
					. " FROM addon_class, item_class, item_nodes"
					. " WHERE item_nodes.class_id=item_class.class_id"
					. " AND item_class.class_id=addon_class.class_id"
					. " AND addon_class.addon_id=" . $this->addon_id 
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
	
	function mergeFeeds ($feeds, $item, $item_parent){
		$item['addon-feeds'] = $feeds;
		$item['addon-classes'] = $this->getAddOnClasses();
		$item['item-parent'] = $item_parent;
		return $item;
	}	
}
?>
