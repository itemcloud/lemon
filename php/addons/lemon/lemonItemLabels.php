<?php //Add-On for label display
$labels_addon['addon-name'] = 'Item Labels';
$labels_addon['addon-version'] = '1.0';
$labels_addon['post-handler'] = 'addonPostLabelHandler';

$labels_addon['page-display'] = 'addonLabelPageDisplay';
$labels_addon['profile-request'] = 'addonProfileLabelRequest';
$labels_addon['page-banner-display'] = 'addonProfileLabelDisplay';
$labels_addon['item-display'] = 'addonItemLabelDisplay';
$labels_addon['item-request'] = 'addonItemLabelRequest';
$labels_addon['banner-display'] = 'addonBannerLabelsDisplay';
$labels_addon['add-new'] = true;

//Add to global $addOns variable
$addOns[] = $labels_addon;

class addonBannerLabelsDisplay {
	function __construct ($user, $auth) { 
		$this->user = $user;
		$this->auth = $auth;
	}
	
	function updateOutputHTML ($banner) {
		$user = $this->user;
		
		$start = (isset($_GET['banner-labels'])) ? $_GET['banner-labels'] : 0;
		$max = 6;
		$links = "";
		if(isset($user->profile['labels'])) {
			$links_browser = new labelBrowse($user->profile['labels'], $start, $max);
			$links = $links_browser->outputHTML();
		}
		$banner->links = $links;
	}
}

class labelBrowse {
	function __construct ($labels, $start, $max) {
		$this->labels = $labels;
		$this->start = $start ? $start : 0;
		$this->max = $max;
		$this->post_name = 'banner-labels';
	}
	
	function set_active_item_id ($item_id) {
		$this->item_id = $item_id;
	}
	
	function set_active_user_id ($user_id) {
		$this->user_id = $user_id;
	}
	
	function outputHTML() {
		global $_ROOTweb;
		if(!$this->labels) {
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
		
		$start = $this->start;		
		$new_start = $this->start - $this->max;
			
		$links = "";		
		$start_links = "<a href=\"$_ROOTweb?$post_extra\">&#8943;</a>";
		
		if($post_extra) { $post_extra = "&" . $post_extra; }
		
		if($new_start >= 0) {
			if($new_start > 0) { $start_links = "<a href=\"$_ROOTweb?" . $this->post_name . "=" . ($new_start) . "$post_extra\">&#8943;</a>"; }
			$links .= $start_links;
		}
		
		$extra = isset($_GET[$this->post_name]) ? "&" . $this->post_name . "=" . $_GET[$this->post_name] : "";
		$total = (count($this->labels) <= ($this->start + $this->max)) ? count($this->labels) : ($this->start + $this->max);
		for($i = $start; $i < $total; $i++) {		
			$label = $this->labels[$i];
			$name = $label['name'];
			$links .= "<a href=\"$_ROOTweb?label_id=" . $label['label_id'] . "&name=" . $name . $extra . "\">" . strtoupper($name) . "</a>";
		}
		if($this->start + $this->max < count($this->labels)) { 
			if($post_extra) { $post_extra = "&" . $post_extra; }
			$links .= "<a href=\"$_ROOTweb?banner-labels=" . ($this->start + $this->max) . "$post_extra\">&#8943;</a>"; 
		}
		
		return "<div class=\"nav_links\">" . $links ."</div>";
	}
	
	function itemOutputHTML ($index) {
		global $client;	
		global $_ROOTweb;	
		if(!$this->labels) {
			return;
		}
		$user_id = ($client->user_serial) ? $client->user_serial : 0;
		
		$labels_js_array = json_encode($this->labels);
		$javascript_label_browser = "<script>try{ itemLabelBrowser['$index'] = new labelBrowse(" . $labels_js_array . ", '$index', $user_id, 'label_browse_$index');\n  } catch (e) { }</script>";
		$labelMenu = "<div style=\"display: inline-block\" id=\"label_browse_$index\">";
			
		$start = $this->start;		
		$new_start = $this->start - $this->max;
		
		if($new_start >= 0) {
			$labelMenu .= "<div onclick=\"try { itemLabelBrowser['" . $this->index . "'].update(" . $new_start . ")  } catch (e) { }\" class=\"item-tools_grey item_label_menu\">"
					. "<div class=\"item-tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
		}
		
		$total = (count($this->labels) <= ($this->start + $this->max)) ? count($this->labels) : ($this->start + $this->max);
		for($i = $start; $i < $total; $i++) {	
			$label = $this->labels[$i];
			
			$label_img = "<a href=\"?label_id=" . $label['label_id'] . "&name=" . $label['name'] . "\">" 
					. "<img class=\"label-image\" src='files/labels/" . $label['label_img'] . "'/>" 
					. "</a>";
					
			$label_name = "<div style=\"display: inline-block;\">";					
			if(($label['owner_id'] == $user_id) && $index) {
				//remove label from item if owner
				$remove_button = "<div style='display: inline-block'><form id='removeForm" . $i . $index . "' action='?id=" . $index. "' method='post'>"
				. "<input type='hidden' name='item_id' value='" . $index . "'/>"
				. "<input type='hidden' name='label_id' value='" . $label['label_id'] . "'/>"
				. "<input type='hidden' name='label' value='remove'/>"		
				. "<div class='inline-remove'>";

				$remove_button .= " <a onclick=\"domId('removeForm" . $i . $index . "').submit()\">x</a>";	
				$remove_button .= "</div>";
				$remove_button .= "</form></div>";
				
				$label_name .= $remove_button;
			}	
			$label_name .= "<div  class=\"inline-name\">" . $label['name'] . "</div>";
			$label_name .= "</div>";
			
			$label_window_launch = "window.location='" . $_ROOTweb . "?label_id=" . $label['label_id'] . "&name=" . $label['name'] . "'; ";
			
			$label_wrapper = "<div class=\"item-tools_grey item_label_menu\" >";
			$label_wrapper .= $label_img;			
			$label_wrapper .= $label_name;
			$label_wrapper .= "</div>";
			
			$labelMenu .= $label_wrapper;
		}
				
		if($this->start + $this->max < count($this->labels)) { 		
			$labelMenu .= "<div id=\"browse-labels-button" . $i . $index . "\" style=\"display: none;\" onclick=\"try { itemLabelBrowser['" . $index . "'].update(" . ($this->start + $this->max) . ") } catch (e) { }\"  class=\"item-tools_grey item_label_menu\">"
					. "<div class=\"item-tools_txt\">" . "&#8943;" . "</div>"
					. "</div>";
			$labelMenu .= "<script>try { itemLabelBrowser; domId('browse-labels-button" . $i . $index . "').style.display='inline-block'; } catch (e) { }</script>";
		}
		
		$labelMenu .= "</div>";
		$labelMenu .= $javascript_label_browser;
		return $labelMenu; 
	}	
}

class addonProfileLabelDisplay {
	function __construct ($pageManager) {
		$this->profile = (isset($pageManager->meta['profile'])) ? $pageManager->meta['profile'] : NULL;
		$this->labels = $this->setLabels($this->profile);
	}
	
	function setLabels($profile) {
		if(isset($profile['labels'])) { return $profile['labels']; }
		return;
	}
	
	function updateOutputHTML ($pageManager) {
		if(isset($this->profile)) { 
			$profileLabels = "";
			
			$start = 0;
			$max = 6;
			$profile_labels_browser = new labelBrowse($this->profile['labels'], $start, $max);
			$profile_labels = $profile_labels_browser->itemOutputHTML(NULL);
		
			$profileLabels = $profile_labels;
			$banner = $pageManager->displayWrapper('div', 'section', 'section_inner browse-labels', $profileLabels);
			$pageManager->pageOutput .= $banner;
		}
	}
}

class addonItemLabelDisplay {

	function mergeRemove ($profile_labels, $item_labels) {	
		$tmp_labels = array(); 
		foreach ($profile_labels as $profileObject) { 
			$match = false;
			foreach($item_labels as $itemObject) {
				 if ($itemObject['label_id'] == $profileObject['label_id']) { 
					$match = true;
				}
			} if(!$match) { $tmp_labels[] = $profileObject; }
		}
		return $tmp_labels;
	}

	function userLabelTools ($profile_labels, $item_id) {
		global $labels_addon;
		if($labels_addon['add-new'] == false && !$profile_labels) {
			return "";
		}
		
		if($profile_labels) { //CHECK FOR USER PROFILE LABELS
			$chooseLabel = "<select onchange=\"if(!this.value){"
			. " domId('itemAddNewLabel" . $item_id . "').style.display='block';"
			. " domId('itemPostLabel" . $item_id . "').value = 'new';"
			. " domId('itemAddNewLabel" . $item_id . "').focus();"
			. " } else { "
			. " domId('itemAddNewLabel" . $item_id . "').style.display='none';"
			. " domId('itemPostLabel" . $item_id . "').value = 'add'; }"
			. "\" name=\"label_id\" class=\"item-dropdown\">";
					
			foreach($profile_labels as $label) {
				$chooseLabel .= "<option value='". $label['label_id'] . "'>". $label['name'] . "</option>";
			}
			if($labels_addon['add-new'] != false) {
				$chooseLabel .= "<option value=''>+ New</option>";
			}
			$chooseLabel .= "</select>";
			$newLabel = "<div id='itemAddNewLabel" . $item_id . "' style='position: relative; display: none'><input name='name' class='form' autofocus></div>";	
			$newLabel .= "<input id='itemPostLabel" . $item_id . "' type='hidden' name='label' value='add'>"; //DEFAULT: POSTING TO 'ADD' LABEL IN POST
		} else {
			$chooseLabel = "<div class='item-tools_dark' style='width: 120px'>+ New</div>";
			$newLabel = "<div id='itemAddNewLabel" . $item_id . "' style='position: relative; display: block'><input name='name' class='form' autofocus></div>";	
			$newLabel .= "<input id='itemPostLabel" . $item_id . "' type='hidden' name='label' value='new'>"; //DEFAULT: POSTING TO 'NEW' LABEL IN POST
		}
		
		$tools = "<div style='float: right'>" 
			. "<div id='itemAdd" . $item_id . "' style='display: inline-block'" . " onClick=\"this.style.display='none'; domId('itemAddForm" . $item_id . "').style.display='inline-block'\" class=\"item-tools_grey\"><div class=\"item-tools_txt\">&#43;</div></div>"
			. "<div id='itemAddForm" . $item_id . "' style='display: none'>"
				. "<form id='itemLabelForm" . $item_id . "' action=\"?id=" . $item_id . "\" method=\"post\">"
				. "<input type='hidden' name='id' value='" . $item_id . "'>"
				. "<div style=\"float: left\" onClick=\"domId('itemAdd" . $item_id . "').style.display='inline-block'; domId('itemAddForm" . $item_id . "').style.display='none'\" class=\"item-tools_grey\"><div class=\"item-tools_txt\">&#8722;</div></div>"				
				. "<div style='display: inline-block; width: 140px; margin-right: 4px'>" . $chooseLabel . "</div>"
				. "<div onclick=\"domId('itemLabelForm" . $item_id . "').submit()\" style=\"display: inline-block; font-size: 12px;\" class=\"item-tools_dark\">&#9989; SAVE</div>"		
				. $newLabel
				. "</form>"
			. "</div>" 
			. "</div>";

		return $tools;
	}
	 
	function updateOutputHTML ($itemDisplay) {
		$item_id = $itemDisplay->item['item_id'];
		global $client;
		
		$item_label_output = "";
		if(isset($itemDisplay->item['labels'])) {
			$index = 0;
			foreach($itemDisplay->item['labels'] as $label) {

				$index++;
					
				//Image
				$label_image = "<div style=\"display: inline-block\">"
					. "<a href=\"?label_id=" . $label['label_id'] . "&name=" . $label['name'] . "\">" 
					. "<img width='28px' style='vertical-align: middle;' src='files/labels/" . $label['label_img'] . "'/>" 
					. "</a>"
					. "</div>";
				
				//Link (Remove button)
				$label_link = "<div id=\"removeItemLabel" . $index . $item_id . "\" style='display: none'>"
					. "<form id='removeForm" . $index . $item_id . "' action='?id=" . $item_id. "' method='post'>" 
					. "<input type='hidden' name='item_id' value='" . $item_id . "'/>" 
					. "<input type='hidden' name='label_id' value='" . $label['label_id'] . "'/>" 
					. "<input type='hidden' name='label' value='remove'/>"				
					. "<div style='padding: 0px 6px 0px 2px;'>";
					
					if($label['owner_id'] == $client->user_serial) {
						$label_link .= " <a onclick=\"domId('removeForm" . $index . $item_id . "').submit(); alert('remove');\" class=\"item-tools_txt\">X</a>";	
					}		
					
					$label_link .= "<a href=\"?label_id=" . $label['label_id'] . "&name=" . $label['name'] . "\">"
						. $label['name']
						. "</a>";

	
				
				$label_link .= "</div>";
				$label_link .= "</form>";
				$label_link .= "</div>";
				
				//Label output
				$label_output = "<div class=\"item-tools_grey\" style=\"border-radius: 24px; padding: 2px\""
					. " onmouseover=\"domId('removeItemLabel" . $index . $item_id . "').style.display='inline-block'\""
					. " onmouseout=\"domId('removeItemLabel" . $index . $item_id . "').style.display='none'\""
					. " style=\"padding: 0px; display: inline-block\">";					
				$label_output .= $label_image;
				$label_output .= $label_link;
				$label_output .= "</div>";
				
				$item_label_output .= $label_output;	
			}

		
		$start = 0;
		$max = 6;
		$index = $itemDisplay->item['item_id'];
		$profile_labels_browser = new labelBrowse($itemDisplay->item['labels'], $start, $max);
		$item_label_output = $profile_labels_browser->set_active_item_id($itemDisplay->item['item_id']);
		$item_label_output = $profile_labels_browser->set_active_user_id($client->user_serial);
		
		$item_label_output = $profile_labels_browser->itemOutputHTML($index);
		$item_label_output .= "<script>try { itemLabelBrowser['" . $index . "'].set_active_item_id(" . $itemDisplay->item['item_id'] . "); } catch (e) { }</script>";
		}
		
		$tool_output = "";
		if($itemDisplay->user_id && isset($client->profile)) {
				$profile_labels = $client->profile['labels'];
				if($profile_labels && isset($itemDisplay->item['labels'])) { 
					$profile_labels = $this->mergeRemove($profile_labels, $itemDisplay->item['labels']); 
				}
				$tool_output = $this->userLabelTools($profile_labels, $item_id);
				$item_label_output .= $tool_output;
		}
		$metaOutput = "<div style='float: right'>" . $item_label_output . "</div>";
		
		//Only add tools 
		if($itemDisplay->metaOutput == $itemDisplay->itemMetaLinks()) { $metaOutput = $metaOutput;  }
		else {  $metaOutput = $itemDisplay->metaOutput . $metaOutput; }
		$itemDisplay->metaOutput = $metaOutput;		
		
		if(isset($_GET['banner-labels'])) { $itemDisplay->itemLink .= "&banner-labels=" . $_GET['banner-labels']; }
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
		if(isset($_GET['label_id'])) {
			global $client;
			global $_ROOTweb;
			
			$label = $pageManager->meta['label'];
			$label_owner = ($label['owner_id'] == $client->user_serial) ? $label['owner_id'] : NULL;

			$folder = "files/labels/";
			$file_dir = $_ROOTweb . $folder;
			
			$imageRollover = "changeImageRollover";
			$label_img = "<div class='item-user'";
			if($label_owner) { $label_img .= " onmouseover=\"domId('$imageRollover').style.display='block';\" onmouseout=\"domId('$imageRollover').style.display='none'\""; }
			$label_img .= " style='width: 100px; height: 100px; margin: 0px 20px 20px 20px; background-image: url(" . $file_dir . $label['label_img']  . ")'>";
			if($label_owner) { $label_img .= "<div id=\"$imageRollover\" onclick=\"domId('itc_label_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'\" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 80px; text-align: center;\">&#8853;</div>"; }
			$label_img .= "</div>";
			$label_name = "<div style=\"font-size: 2em\" onclick=\"window.location.reload()\"><u>" . $label['name'] . "</u></div>";
			
			$page = "<div class=\"item-section\" style=\"text-align: left;\">" . $label_img . "<div style=\"display: inline-block; text-align: left;\">";
			$page .= "<div id=\"itc_label_name_form\" style=\"display: none;\"><form action=\"./?label_id=" . $label['label_id'] . "&name=" . $label['name'] . "\" method=\"post\"><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input class=\"form\" name=\"itc_label_name\" value=\"" . $label['name'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"✅ SAVE\"></div>";
			$page .= "<div id=\"itc_label_name\" style=\"display: inline-block\">" . $label_name;	
										

			$page .= "</form></div>";
			
			if($label_owner){
				$page .= " <span class=\"item-tools\" onclick=\"this.style.display='none'; domId('itc_label_name').style.display='none'; domId('itc_label_name_form').style.display='inline-block';\">&#9998; EDIT</span>";
			}			
			if($label_owner){			
				$page .= "<div style=\"display: inline-block\"><form action=\"./?itc_label_edit=purge\" method=\"post\"><input type=\"hidden\" name=\"itc_label_edit\" value=\"purge\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $client->user_serial . "\"/><input type=\"hidden\" name=\"label_id\" value=\"" . $label['label_id'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"&#9988; DELETE\"></form></div>";		
			}
			
			if ($pageManager->items) { $page .= "<div class=\"itc_label_count\">Items (" . $pageManager->items[0]['item_count'] . ")" . "</div>"; }
			$page .= "</div>";

			if($label_owner) {			
				$page .= "<form enctype=\"multipart/form-data\" action=\"./?label_id=" . $label['label_id'] . "&name=" . $label['name'] . "\" method=\"post\"><div style=\"display: none; margin-top: 4px;\" id=\"itc_label_image_form\"><input type=\"hidden\" name=\"itc_label_img\" value=\"change\"/><input type=\"hidden\" name=\"label_id\" value=\"" . $_GET['label_id'] . "\"/><input type=\"file\" class=\"item-tools\" name=\"itc_label_upload\" accept=\"image/jpeg,image/png,image/gif\"><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"✅ SAVE\"></div></form>";
				$page .= "<div id=\"show-form-button\" class=\"item-tools_dark\" onclick=\"domId('itc_label_image_form').style.display='inline-block'; this.style.display='none'\" style=\"margin: 4px 0px;\">" . "Change the label image" . "</div>";
			}
			
			if($label_owner){
				$omniBox = $this->displayOmniBox($pageManager);
				$page .= "<div>" . $omniBox . "</div>";
			}			
			$page .= $pageManager->displayItemBlog();
			$page .= "</div>";
				
			if(!isset($_GET['forum'])) { return $page; }
		}
	}
	
	function displayOmniBox($pageManager) {
		if(!$pageManager->classes) { return; }
		
		$classes = $pageManager->classes;
		$class_js_array = json_encode($classes);
		$class_id = (isset($_POST['itc_class_id'])) ? $_POST['itc_class_id'] : key($classes);
		
		$javascript_omni_box = "<script>var OmniController = new OmniLabelBox(" . $class_js_array . ", 'itemOmniBox');\n OmniController.set_active_label('" . $pageManager->meta['label']['label_id'] . "');\n OmniController.toggle('" . $class_id . "');\n</script>";
		$message = (isset($pageManager->meta['message'])) ? "<center><div id=\"alertbox\" class=\"alertbox-show\">" . $pageManager->meta['message'] . "</div></center>" : "<center><div id=\"alertbox\" class=\"alertbox-hide\"></div></center>";
		
		$createForm  = "<div class=\"item-section\"><div class=\"item-page\" id=\"itemOmniBox\">" . "</div></div>";
		$createForm .= $javascript_omni_box;
		return $message . $createForm . $javascript_omni_box;
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
			$quest = "SELECT item_labels.*, label.*"
		     . " FROM item_labels, label"
			 . " WHERE item_labels.item_id='" . $item['item_id'] . "'"
			 . " AND label.label_id=item_labels.label_id"
			 . " AND label.level > $level";
		
			$label_loot = mysqli_query($this->stream, $quest);
			$labels = NULL;	
			if($label_loot) {
				while($loot=$label_loot->fetch_assoc()) { $labels[] = $loot; }
			}
			$tmp_loot_array[] = $this->mergeLabels($labels, $item);
		} }
		$this->item_loot = $tmp_loot_array;
		return $this->item_loot;
	}
	
	function mergeLabels ($labels, $item){
		$item['labels'] = $labels;
		return $item;
	}
}

class addonPostLabelHandler {
	function __construct ($stream) {
		$this->stream = $stream;
		$this->DEFAULT_USER_LEVEL = 3;
		$this->DEFAULT_MAX_FILESIZE = 10485760; //10MB
	}
	
	function handleAddOnPost ($itemManager) {
		global $CONFIG;
		$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
		$count = $CONFIG['item_count'];
		
		global $client;	
		$user_id = $client->user_serial;
		$user_level = $client->level;
		
		$active = isset($_POST['label']);
		if($active) {
			switch ($_POST['label']) {
				case 'new':
					$label_id = $this->newLabel($user_id, $_POST['name'], "default.png");
					$this->addItemLabel($user_id, $_POST['id'], $label_id);				
					header("Location: ./?label_id=" . $label_id . "&name=" . $_POST['name']);
					break;
				case 'add':
					$this->addItemLabel($user_id, $_POST['id'], $_POST['label_id']);
					header("Location: ./?id=" . $_POST['id']);
					break;
				case 'remove':
					$this->removeItemLabel($user_id, $_POST['item_id'], $_POST['label_id']);
					break;
			}
		} else if(isset($_POST['itc_add_item_label'])){ 
				$message = $itemManager->handleItemUpload($client);
				if($itemManager->insertOk == "1" && isset($itemManager->item_id)) { $this->addItemLabel($user_id, $itemManager->item_id, $_POST['itc_add_item_label']); }
				header("Location: ./?label_id=" . $_POST['itc_add_item_label']);
		} else if(isset($_POST['itc_label_edit'])){ 
				$this->purgeLabel($_POST['label_id']);
				header("Location: ./");
		} else if(isset($_POST['itc_label_img'])){ 
			$this->handleLabelUpload($client);
				header("Location: ./?label_id=" . $_GET['label_id']);
		} else if (isset($_GET['label_id'])){ 		
			if(isset($_POST['itc_label_name'])) {
				$owner = ($_POST['user_id'] == $user_id) ? $user_id : false;	
				if($owner) { $this->changeLabelName($_POST['itc_label_name'], $_GET['label_id']);
						header("Location: ./?label_id=" . $_GET['label_id'] . "&name=" .  $_POST['itc_label_name']);
				}		
			} 

			$itemManager->meta['label'] = $this->getLabel($_GET['label_id']);
			$itemManager->items = $this->getLabelItems($_GET['label_id'], $start, $count, $user_level);	
			return "active";
		}
	}
		
	function changeLabelName ($new_name, $label_id) {
		$stream = $this->stream;
		$input = "UPDATE label SET name='$new_name' WHERE label_id='$label_id'";
		$query = $stream->query($input);
	}
	
	function purgeLabel ($label_id) {
		$stream = $this->stream;
		
		$user_labels = "DELETE FROM user_labels WHERE label_id='$label_id'";
		mysqli_query($stream, $user_labels);

		$item_labels = "DELETE FROM item_labels WHERE label_id='$label_id'";
		mysqli_query($stream, $item_labels);
				
		$label = "DELETE FROM label WHERE label_id='$label_id'";
		mysqli_query($stream, $label);
	}
		
	function getLabel($label_id) {
		$label_quest = "SELECT label.* FROM label WHERE label_id='$label_id'";
		$label_loot_return = mysqli_query($this->stream, $label_quest);	
		$label_loot = $label_loot_return->fetch_assoc();
		return $label_loot;
	}
	
	function getLabelItems($label_id, $start, $count, $level) {
		$label_quest = "SELECT label.* FROM label WHERE label_id='$label_id' AND label.level > $level";
		$label_loot_return = mysqli_query($this->stream, $label_quest);	
		$label_loot = $label_loot_return->fetch_assoc();
		
		$quest = "SELECT SQL_CALC_FOUND_ROWS item_labels.*, item.*, user_items.user_id"
			. " FROM item_labels, item, user_items"
			. " WHERE item_labels.label_id='" . $label_id . "'"
			. " AND item_labels.item_id=item.item_id"
			. " AND item.item_id=user_items.item_id"
			. " ORDER BY item_labels.date DESC"
			. " LIMIT $start, $count";
		 
		$items_loot = mysqli_query($this->stream, $quest);
		$item_loot_array = NULL;
		if($items_loot) {
			while($loot=$items_loot->fetch_assoc()) { 
				$count_quest = mysqli_query($this->stream, "SELECT FOUND_ROWS() AS count")->fetch_assoc();
				$loot['item_count'] = $count_quest['count'];
				$item_loot_array[] = $loot;
			}
			$addon_class = new addonItemProfileRequest($this->stream, $item_loot_array);
			$item_loot_array = $addon_class->getAddOnLoot();
		}					
		$addon_request = new addonItemLabelRequest($this->stream, $item_loot_array);
		$item_loot_array = $addon_request->getAddOnLoot($level);
		return $item_loot_array;
	}
	
	function newLabel ($owner_id, $name, $label_img) {
		if($name) {
			$quest = "INSERT INTO label (owner_id, name, label_img) VALUES('$owner_id', '$name', '$label_img')";
			$success = mysqli_query($this->stream, $quest);
			if($success) { 
				$label_id = mysqli_insert_id($this->stream);
				$user_quest = "INSERT INTO user_labels (user_id, label_id) VALUES('$owner_id', '$label_id')";
				$success = mysqli_query($this->stream, $user_quest);
				return $label_id;
			}
		}
	}
		
	function addItemLabel ($user_id, $item_id, $label_id) {
		$check_quest = "SELECT item_labels.* FROM item_labels WHERE item_id='$item_id' AND label_id='$label_id'";
		$match = mysqli_query($this->stream, $check_quest);
		if(!$match->fetch_assoc()) {
			$quest = "INSERT INTO item_labels (user_id, item_id, label_id, date) VALUES('$user_id', '$item_id', '$label_id', '" . date('Y-m-d h:i:s') . "')";
			$success = mysqli_query($this->stream, $quest);
		}
	}
		
	function removeItemLabel ($user_id, $item_id, $label_id) {
		$quest = "DELETE FROM item_labels WHERE item_id='$item_id' AND label_id='$label_id' AND user_id='$user_id'";
		$success = mysqli_query($this->stream, $quest);
	}

	function changeLabelImage ($new_image, $label_id) {
		$stream = $this->stream;
		$input = "UPDATE label SET label_img='$new_image' WHERE label_id='$label_id'";
		$query = $stream->query($input);
	}
	
	function handleLabelUpload($client) {
		 if (isset($_POST['itc_label_img'])) {
			$insertOk = "1";
			$target_dir = "files/labels/";
			$filesize = $this->DEFAULT_MAX_FILESIZE;
			$ext = ["jpg", "jpeg", "png", "gif"];
			$file_extensions = $ext;

			 if(isset($_FILES["itc_label_upload"])) {
				$tmp_file = new uploadManager(
					$_FILES["itc_label_upload"],
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

			if($insertOk && isset($_POST['label_id'])) {
				$this->changeLabelImage($file, $_POST['label_id']);
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
		$quest = "SELECT user_labels.*, label.*"
		 . " FROM user_labels, label"
		 . " WHERE user_labels.user_id='" . $this->user_id . "'"
		 . " AND label.label_id=user_labels.label_id"
		 . " AND label.level > $user_level";
		
		$label_loot = mysqli_query($this->stream, $quest);		
		$labels = NULL;	
		if($label_loot) {
			while($loot=$label_loot->fetch_assoc()) { 
				$labels[] = $loot;
			}
		}
		$this->profile_loot['labels'] = $labels;
		return $this->profile_loot;
	}
}
?>
