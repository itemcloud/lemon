<?php //Add-On for reply display
$reply_addon['addon_title'] = 'Lemon Item Reply (Comment Feeds)';
$reply_addon['addon_name'] = 'lemon-reply';
$reply_addon['addon-version'] = '1.0.1';
$reply_addon['collection_name'] = 'Comments';
$reply_addon['item_name'] = 'Item';
$reply_addon['addon_id'] = '1002';

$reply_addon['post-handler'] = 'addonPostReplyHandler';
$reply_addon['page-display'] = 'addonReplyPageDisplay';
$reply_addon['item-display'] = 'addonItemReplyDisplay';
$reply_addon['item-request'] = 'addonItemReplyRequest';
$reply_addon['add-new'] = true;

//Add to global $addOns variable
//$addOns[] = $reply_addon;

class addonPostReplyHandler {
	function __construct ($stream) {
		$this->stream = $stream;
	}

	function handleAddOnPost ($itemManager) {
		global $client;	
		$user_id = $client->user_serial;
		$user_level = $client->level;
		
		if(isset($_POST['itc_add_item_comment'])){ 
			$feed_id = $_POST['itc_add_item_comment'];
			$item_id = $_POST['itc_add_item_comment_id'];
			
			if($item_id) {
				$postLabelHandler = new addonPostLabelHandler($this->stream);
				$new_item_id = $itemManager->handleItemUpload($client);
				$itemManager->insertUserItem($user_id, $new_item_id, 0);
				
				if($itemManager->insertOk == "1" && isset($itemManager->item_id)) {
					$postLabelHandler->addItemLabel($user_id, $itemManager->item_id, $feed_id);
				}
				header("Location: ./?id=" . $item_id);
			}
		} else if (isset($_POST['itc_add_item_comment_id'])) {
			$item_id = $_POST['itc_add_item_comment_id'];
			
			if($item_id) {
				$feed_id = $this->newItemCommentLabel($user_id, 'Comments', 'comments.png', $item_id);
				
				$postLabelHandler = new addonPostLabelHandler($this->stream);
				$new_item_id = $itemManager->handleItemUpload($client);
				$itemManager->insertUserItem($user_id, $new_item_id, 0);
				
				if($itemManager->insertOk == "1" && isset($itemManager->item_id)) {
					$postLabelHandler->addItemLabel($user_id, $item_id, $feed_id);
					$postLabelHandler->addItemLabel($user_id, $itemManager->item_id, $feed_id);
				}
				header("Location: ./?id=" . $item_id);
			}
		}
	}
	
	function newItemCommentLabel ($owner_id, $name, $feed_img, $item_id) {
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
				$user_quest = "INSERT INTO addon_feed (addon_name, addon_id, collection_name, item_name, feed_id, item_id, feed_limit) VALUES('" . $reply_addon['addon_name'] . "', '" . $reply_addon['addon_id'] . "', '" . $reply_addon['collection_name'] . "', '" . $reply_addon['item_name'] . "', '$feed_id', '$item_id', 1)";
				$success = mysqli_query($this->stream, $user_quest);
				return $feed_id;
			}
	}
}

class addonReplyPageDisplay {
	function addonPageItems($pageManager) {	 
		global $client;
		$page = "";
		$createForm = "";
			
		$box_class = "item-box";
		$info_limit = 240;
		$count = 0;

		if(isset($pageManager->items[0]['addon-feeds'][0]['feed_id'])) {
			
			if($client->auth == true && $pageManager->items[0]['addon-classes']) {
				$createForm = $this->displayOmniBox($pageManager, $pageManager->items[0]['addon-feeds'][0]['feed_id'], $pageManager->items[0]['item_id'], $pageManager->items[0]['addon-classes']);			
				if($pageManager->items[0]['level'] > 0) { $page .= $createForm; }
			}
			
			if(isset($pageManager->items[0]['addon-feeds'][0]['items'])) {
				$page .= "<div class=\"item_reply\">"; 
				foreach($pageManager->items[0]['addon-feeds'][0]['items'] as $item) {
					$page .= $pageManager->handleItemType($item, $box_class, $info_limit, $count);
					$count++;
				} 
				$page .= "<div class=\"clear\"></div>";
				$page .= "</div>";
			}
		} else {
			$classes = (isset($pageManager->items[0]['addon-classes'])) ? $pageManager->items[0]['addon-classes'] : [];
			if($client->auth == true) {
				$createForm = $this->displayOmniFirstCommentBox($pageManager, $pageManager->items[0]['item_id'], $classes);
				$page .= "<div class=\"item_reply\">";
				if($pageManager->items[0]['level'] > 0) {
					$page .= $createForm;
					$page .= "<div class=\"clear\"></div>";
					$page .= "</div>";		
				}
			}
		}
		
		if(isset($_GET['id'])) { 
			$pageManager->pageExtra = $page;
		}
	}
	
	function displayOmniBox($pageManager, $feed_id, $item_id, $addon_classes) {
		$classes =($addon_classes) ? $addon_classes : $pageManager->classes; 
		
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController$item_id = new OmniCommentBox(" . $class_js_array . ", 'itemOmniBox$item_id');\n OmniController$item_id.set_active_str('$item_id');\n OmniController$item_id.set_active_feed('" . $feed_id . "');\n  OmniController$item_id.set_active_item_id('" . $item_id . "');\n OmniController$item_id.set_active_item_id('" . $item_id . "');\n OmniController$item_id.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div class=\"item-section\"><div style=\"display: none\" class=\"item-page\" id=\"itemOmniBox$item_id\">" . "</div>"
				. "<div class=\"float-right\" onclick=\"domId('itemOmniBox$item_id').style.display='inline-block'; this.style.display='none'\" style=\"width: 640px; margin: 14px auto; text-align: center; cursor: pointer\"><div>+ <u>Add a Comment</u></div></div>"
				. "</div>";				
		$createForm .= $javascript_omni_box;
		return $message . $createForm . $javascript_omni_box;
	}
	
	function displayOmniFirstCommentBox($pageManager, $item_id, $addon_classes) {
		$classes =(isset($addon_classes)) ? $addon_classes : $pageManager->classes; 
		
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController$item_id = new OmniFirstCommentBox(" . $class_js_array . ", 'itemOmniBox$item_id');\n OmniController$item_id.set_active_str('" . $item_id . "');\n OmniController$item_id.set_active_item_id('" . $item_id . "');\n OmniController$item_id.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
			
		$createForm  = "<div class=\"item-section\" style=\"width: 1200px;\"><div style=\"display: none\" class=\"item-page\" id=\"itemOmniBox$item_id\">" . "</div>"
				. "<div class=\"float-right\" onclick=\"domId('itemOmniBox$item_id').style.display='inline-block'; this.style.display='none'\" style=\"width: 640px; margin: 14px auto; text-align: center; cursor: pointer\"><div>+ <u>Add a Comment</u></div></div>"
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

class addonItemReplyDisplay {
	function updateOutputHTML($itemDisplay) {

		 if($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) {
		 	$itemDisplay->metaOutput = "";
		 }
		$itemDisplay->metaOutput .= "<div style=\"float: left; padding-left: 4px; font-size: 12px;\">";
		if (isset($itemDisplay->item['item-parent'][0]) && $itemDisplay->item['item-parent'][0]['item_id'] != $itemDisplay->item_id) {
			$name = ($itemDisplay->item['item-parent'][0]['title']) ? $itemDisplay->item['item-parent'][0]['title'] : "another item";
			$itemDisplay->metaOutput .= "Added as a reply to <a href=\"./?id="  . $itemDisplay->item['item-parent'][0]['item_id'] . "\">$name</a>.";
		} else if(isset($itemDisplay->item['addon-feeds'])) {
			foreach($itemDisplay->item['addon-feeds'] as $feed) {
				$itemDisplay->metaOutput .= "<a href=\"./?id=" . $itemDisplay->item_id . "\">" . $feed['name'];
				$itemDisplay->metaOutput .= " (" . count($feed['items']) . ")</a>";
			}
		} else {
			$itemDisplay->metaOutput .= "<a href=\"./?id=" . $itemDisplay->item_id . "\">Comments (0)</a> ";
		}
		$itemDisplay->metaOutput .= "</div>";
	}
}

class addonItemReplyRequest {
	function __construct ($stream, $items) {
		$this->stream = $stream;
		$this->item_loot = $items;
	}
	
	function getAddOnLoot ($level){
		$tmp_loot_array = NULL;
		global $reply_addon;
		
		if($this->item_loot) { foreach($this->item_loot as $item) {
			$quest = "SELECT feed_items.*, feed.*, addon_feed.*"
		     . " FROM feed_items, feed, addon_feed"
			 . " WHERE feed_items.item_id='" . $item['item_id'] . "'"
			 . " AND feed.feed_id=feed_items.feed_id"
			 . " AND feed.feed_id=addon_feed.feed_id"
			 . " AND addon_feed.item_id='" . $item['item_id'] . "'"
			 . " AND addon_feed.addon_id='" . $reply_addon['addon_id'] . "'";
			 
			$feed_loot = mysqli_query($this->stream, $quest);
			$feeds = NULL;
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
						while($feed_item=$feed_items_loot->fetch_assoc()) {
							if($feed_item['item_id'] != $item['item_id']) { $item_parent[] = $item; }
							
							$feed_item['item-parent'] = $item_parent;
							$feed_item_loot_array[] = $feed_item;
						}
					}
					//get profiles for feed items (reply add on)
					$profileRequest = new addonItemProfileRequest($this->stream, $feed_item_loot_array);
					$feed_item_loot_array = $profileRequest->getAddOnLoot();
					
					$loot['items'] = $feed_item_loot_array;
					$feeds[] = $loot;
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
			}
			$reply_addon['addon_id'];
			$tmp_loot_array[] = $this->mergeLabels($feeds, $item, $item_parent);
		} }
		$this->item_loot = $tmp_loot_array;
		return $this->item_loot;
	}
	
	function getAddOnClasses() {
		global $client;
		global $reply_addon;
		
		$stream = $this->stream;
		$level = $client->level;
		
		$class_quest = "SELECT addon_class.*, item_class.*, item_nodes.*"
					. " FROM addon_class, item_class, item_nodes"
					. " WHERE item_nodes.class_id=item_class.class_id"
					. " AND item_class.class_id=addon_class.class_id"
					. " AND addon_class.addon_id=" . $reply_addon['addon_id']
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
	
	function mergeLabels ($feeds, $item, $item_parent){
		$item['addon-feeds'] = $feeds;
		$item['addon-classes'] = $this->getAddOnClasses();
		$item['item-parent'] = $item_parent;
		return $item;
	}	
}
?>
