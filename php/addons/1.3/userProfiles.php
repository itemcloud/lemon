<?php //AddOn for profile display
class profileManager {
	function __construct() {
		$addon_name = 'Lemon User Profiles';
		$addon_version = '1.0';
		
		$this->DEFAULT_USER_LEVEL = 3;
		$this->DEFAULT_MAX_FILESIZE = 10485760; //10MB
	}
	
	function setActions() {
		global $actions;
		$actions['post-handler'][] = 'addonPostProfileHandler';
		$actions['user-request'][] = 'addonUserProfileRequest';
		
		$actions['user-account'][] = 'addonUserProfileAccount';
		if($actions['item-display']) { array_unshift($actions['item-display'], 'addonItemProfileDisplay'); }
		else { $actions['item-display'][] = 'addonItemProfileDisplay'; }
		$actions['item-request'][] = 'addonItemProfileRequest';
		
		$actions['banner-display'][] = 'addonBannerDisplay';
		$actions['page-banner-display'][] = 'addonProfileDisplay';
	}
}

$addOns[] = 'profileManager';

class addonUserProfileAccount extends profileManager {
	function update ($user) {	
		$this->stream = $user->stream;
		$this->DEFAULT_USER_LEVEL = 3;
			
		$level = $this->DEFAULT_USER_LEVEL;
		$profile_insert = "INSERT INTO user_profile (user_id, level) VALUES('" . $user['user_id'] . "', '$level')";
		mysqli_query($this->stream, $profile_insert);
	}
}

class addonPostProfileHandler extends profileManager  {
	
	function update ($itemManager) {
		$this->itemManager = $itemManager;
		$this->stream = $itemManager->stream;
		global $client;
		
		$level = $this->DEFAULT_USER_LEVEL;
		$user_id = $client->user_serial;				
		
		if(isset($_POST['itc_profile_name'])) {
			$this->changeProfileName($_POST['itc_profile_name'], $client->user_serial);
		} elseif(isset($_POST['itc_profile_img'])) {
			$this->handleProfileUpload($client);
		}

		if(!isset($_GET['user'])) { $itemManager->meta['owner'] = false; return false; }
		$itemManager->meta['owner'] = ($_GET['user'] == $client->user_serial) ? true : false;
		$this->getUserProfile($_GET['user'], $itemManager);
		$profile = $itemManager->meta['profile'];
		$user_name = (isset($profile['user_name']) && $profile['user_name'] != false) ? $profile['user_name'] : "New Member (" . date('Y') . ")";		
		$itemManager->meta['title'] = $user_name;
			
		//Create a new profile if empty (when owner requests profile)
		if($user_id && $itemManager->meta['owner'] && !isset($itemManager->meta['profile']['user_id'])) {
			$profile_insert = "INSERT INTO user_profile (user_id) VALUES('" . $client->user_serial . "')";
			$new_profile = mysqli_query($this->stream, $profile_insert);
			
			if($new_profile) { $itemManager->meta['profile'] = $this->getUserProfile($_GET['user'], $itemManager); }
		}
		
		global $CONFIG;
		$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
		$count = $CONFIG['item_count'];
		return $itemManager->getUserItems($_GET['user'], $start, $count, $client->level);
	}
		
	function changeProfileName ($new_name, $user_id) {
		$stream = $this->stream;
		$input = "UPDATE user_profile SET user_name='$new_name' WHERE user_id='$user_id'";
		$query = $stream->query($input);
	}

	function changeProfileImage ($new_image, $user_id) {
		$stream = $this->stream;
		$input = "UPDATE user_profile SET user_img='$new_image' WHERE user_id='$user_id'";
		$query = $stream->query($input);
	}
	
	function handleProfileUpload($client) {
		 if (isset($_POST['itc_profile_img'])) {
			$insertOk = "1";
			$target_dir = "files/feeds/";
			$filesize = $this->DEFAULT_MAX_FILESIZE;
			$ext = ["jpg", "jpeg", "png", "gif"];
			$file_extensions = $ext;

			 if(isset($_FILES["itc_user_img"])) {
				$tmp_file = new uploadManager(
					$_FILES["itc_user_img"],
					$target_dir,
					$filesize,
					$file_extensions);

				$tmp_file->handleUploadRequest();
				$tmp_file->uploadFile();
				$file = $tmp_file->target_file;	
				if($tmp_file->uploadOk == "0") {
					echo "FAILED to upload: " . $tmp_file->errorStatus; 
					$insertOk = "0";
				} $message = $tmp_file->errorStatus;

			}

			if($insertOk) {
				$this->changeProfileImage($file, $client->user_serial);
			}
		}
	}
		
	function getUserProfile ($user_id, $itemManager) {
		$stream = $this->stream;
		$input = "SELECT user_profile.*, user.user_id, user.date, user.level"
			. " FROM user_profile, user"
			. " WHERE user_profile.user_id='$user_id'"
			. " AND user_profile.user_id=user.user_id";
			
		$profile_loot = $stream->query($input);
		
		if($profile_loot) {
			$profile = $profile_loot->fetch_assoc();
			$profile['profile_id'] = $user_id;
			if(isset($itemManager->actions)) { $profile = $this->runAddons($itemManager->actions, $profile, 'profile-request'); }
			$itemManager->meta['profile'] = $profile;
			return $itemManager->meta['profile'];
		}
	}
		
	function runAddons($actions, $object, $addon_name) {
		if(isset($actions[$addon_name])) { 
			foreach($actions[$addon_name] as $update) {
				$display = new $update;
				return $display->update($object);
			}
		}
	}

	function handleProfileRequest() {
		if(!isset($_GET['user'])) { return false; }
		return $this->getUserProfile($_GET['user'], $itemManager);
	}
}

class addonBannerDisplay extends profileManager  {
		
	function update ($banner) {
		$user_links = '<div class="right">';
		$user_links .= '<div class="menu">';
		$user = $banner->user;

		if($banner->auth) {
			  $user_links .= '<div class="link">+ <a href="index.php?add=new"><span class="name">New</span></a></div>';
			  $user_links .= '<div class="link"><a href="./?user=' . $user->user_serial . '"><span class="name"><u>Profile</u></span></a></div>';
			  if(!isset($user->profile)) { $user_links .= '<div class="link"><a onclick="logout()"><span class="name"><u>Sign Out</u></span></a></div><form id="logoutForm" action="./?connect=1&logout=1" method="post"><input name="logout" type="hidden"/></form>'; }
		}
		else { $user_links .=  '<div class="link"><a href="./?connect=1"><span class="name">Sign In</span></a></div>'; }
		$user_links .= '</div>';
		$user_links .= '</div>';
		
		$banner->user_links = $user_links;
	}
}

class addonProfileDisplay extends profileManager  {
	function update ($pageManager) {

		$this->profile = (isset($pageManager->meta['profile'])) ? $pageManager->meta['profile'] : NULL;
		$this->owner = (isset($pageManager->meta['owner'])) ? $pageManager->meta['owner'] : NULL;
		
		if(isset($this->profile['user_id'])) { 
			$profileBanner = $this->profileBanner($pageManager);
			$banner = $profileBanner;
			$pageManager->section['top']['output'] = $banner . $pageManager->section['top']['output'];
		}
	}
	
	function profileBanner ($pageManager) {
		$rootFiles = $pageManager->ROOTweb;
		$profile = $this->profile;
		
		$date = new DateService($profile['date']);
		$user_banner_html = (isset($profile['user_img'])) ?  $rootFiles . $profile['user_img'] : ""; 
		$user_name = $profile['user_name'] ? $profile['user_name'] : "New Member (" . chopString($profile['date'], 4, '') . ")";
		 
		$profile_link = "./?user=" . $profile['user_id'];
		$n = "\n";
				  
		//RSS Feed: Link Generator
		$feed_url = $rootFiles . "?user=" . $profile['user_id'] . "&RSS=2.0";		
		$pop_up = "<div id=\"RSS_popup\" style=\"color: #FFF; display: none; background-color: #222; opacity: 0.8; position: absolute; z-index: 100; left: 0px; width: 100%; min-height: 60%\">" 
			. "<div onClick=\"domId('RSS_popup').style.display='none'\" style=\"float: right; margin: 10px 28px 10px 10px; font-size: 14px\">" . "&#10005; Close"  . "</div>"
			. "<div style=\"margin: 40px 20%;\"><h2>" . $profile['user_name'] . " Feed (RSS)</h2></div>"		
			. "<div style=\"margin: 40px 20%;\">Feed Url: <input class=\"wider\" value=\"" . $feed_url . "\"/></div>"
			. $pageManager->displayItemXML()			
			. "</div>";
		$pop_up_button = "<div onClick=\"domId('RSS_popup').style.display='block'\" style=\"float: right; margin: 10px 28px 10px 10px; font-size: 14px;\">&#9776; RSS</div>";
		$pop_up = $pageManager->displayWrapper('div', 'float-right', 'inline-block', $pop_up);

		$imageRollover = "changeImageRollover";
		$image_html  = "<div class=\"avatar\"><div class=\"user\"";
		if($this->owner) { $image_html .= " onmouseover=\"domId('$imageRollover').style.display='block'\" onmouseout=\"domId('$imageRollover').style.display='none'\""; }
		$image_html .= " style=\"background-image: url('" . $user_banner_html . "')\">";
					
		if($this->owner) { $image_html .= "<div id=\"$imageRollover\" onclick=\"domId('itc_banner_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'\" style=\"display: none; width: auto; text-align: center; opacity: 0.5; font-size: 92px\">&#8853;</div>"; }
		$image_html .= "</div>";
		$image_html .= "<div class=\"clear\"></div>$n";
		$image_html .= "<div id=\"show-form-button\" class=\"dark tools\" onclick=\"domId('itc_banner_image_form').style.display='inline-block'; this.style.display='none'\" style=\"margin: 4px 0px;\">" . "Change image" . "</div>";
			
		$image_html .= "</div>$n";
		
		$name_html = "<div style=\"float: left; text-align: left; margin: 60px 0px 20px 20px;\">$n"
			. "<form action=\"./?user=" . $profile['user_id'] . "\" method=\"post\"><div id=\"itc_banner_name_form\" style=\"display: none;\"><input type=\"hidden\" name=\"user_id\" value=\"" . $profile['user_id'] . "\"/><input class=\"form\" name=\"itc_profile_name\" value=\"" . $profile['user_name'] . "\"/><input class=\"tools\" type=\"submit\" name=\"submit\" value=\"✅ SAVE\"></div><div id=\"itc_banner_name\"><span class=\"profile-name\"><div style=\"font-size: 2em; display: inline-block\" onclick=\"window.location.reload()\"><u>" . $user_name . "</u></div></span>";
		
		if($this->owner) { $name_html .= " <span class=\"tools\" onclick=\"domId('itc_banner_name').style.display='none'; domId('itc_banner_name_form').style.display='block';\">&#9998; EDIT</span>"; }
		$name_html .= "</div></form>";
		
		$name_html .= "<div class=\"float-left\"><div class=\"dark tools\" style=\"margin: 4px\"><small>MEMBER SINCE</small><br />" . $date->date_time . "</div></div>$n";
		
		if($this->owner) {
			$name_html .= " <div class=\"\">";
			$name_html .= "<div class=\"inline-block \"><form enctype=\"multipart/form-data\" action=\"./?user=" . $profile['user_id'] . "\" method=\"post\"><div style=\"display: none; margin-top: 4px;\" id=\"itc_banner_image_form\" class=\"dark tools\"><input type=\"hidden\" name=\"itc_profile_img\" value=\"change\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $profile['user_id'] . "\"/><input type=\"file\" class=\"item-tools\" name=\"itc_user_img\" accept=\"image/jpeg,image/png,image/gif\"><input class=\"tools\" type=\"submit\" name=\"submit\" value=\"✅ SAVE\"></div></form></div>";
			$name_html .= "<div class=\"clear\"></div>$n";
			$name_html .= "</div>";
		}
				
		$name_html .= "</div>$n";

		$banner_html = "<div class=\"strip\" onlick=\"window.location='" . $profile_link . "'\">$n";
		
		$banner_html .= $pop_up;	
		$banner_html .= "<div class=\"strip_inner\" style=\"position: relative; overflow: hidden;\">$n";		
		$banner_html .= "<div class=\"strip_overlay\">$n";
		$banner_html .= $pop_up_button;	
		
		$banner_html .= $image_html;
		$banner_html .= $name_html;

		$tools_html  = "";
		if($this->owner) { 
			$tools_html  = "<div class=\"float-right\" style=\"margin: 10px 28px 10px 10px; text-align: right\">";
			$tools_html .= "<a onclick=\"logout()\"><small><u>Sign Out</u></small></a><form id=\"logoutForm\" action=\"./?connect=1&logout=1\" method=\"post\"><input name=\"logout\" type=\"hidden\"/></form>";		
			$tools_html .= "</div>";
		}	
		$banner_html .= "<div class=\"clear\"></div>$n";
		
		$banner_html .= "</div>$n";	
		
		$banner_html .= "<div class=\"profile-banner_wrapper\" style=\"display: none; background-size: cover; background-image: url('" . $user_banner_html . "'); overflow: hidden\" $user_banner_html;>$n";	
		$banner_html .= $image_html;	
		$banner_html .= "<div class=\"clear\"></div>$n";	
		$banner_html .= "</div>$n";
		
		$banner_html .= "</div>$n";
		$banner_html .= "<div class=\"clear\"></div>$n";	
		$banner_html .= "</div>$n";
		//return $banner_html;
		
		$banner_html = "<div class=\"frame inline bar\""
			. " onlick=\"window.location='" . $profile_link . "'\">$n";	
		$banner_html .= $pop_up;
		$banner_html .= "<div class=\"left\">" . $image_html . "</div>";
		$banner_html .= "<div class=\"center\">" . $name_html . "</div>";
		$banner_html .= "<div class=\"right\">" .  $pop_up_button . "</div>";
		$banner_html .= "<div class=\"right\">" . $tools_html . "</div>";
		$banner_html .= "</div>$n";	
		return $banner_html;
		
	}
}

class addonItemProfileDisplay extends profileManager {
	function update ($itemDisplay) {
		global $_ROOTweb;
		$user_img = ($itemDisplay->item_user_img) ? " style=\"background-image: url(" . $_ROOTweb . $itemDisplay->item_user_img . ")\"" : "";
		$item_user_html = "<div onclick=\"window.location='./?user=" . $itemDisplay->item_user_id . "';\">";
		$item_user_html .= "<div class=\"user float-left\" $user_img></div>";
		$item_user_html .= "</div>";
		
		$user_name = "New Member";
		if(isset($itemDisplay->item['profile'])) {			
			$user_name .= " (" . chopString($itemDisplay->item['profile']['date'], 4, '') . ")";
			if($itemDisplay->item['profile']['user_name']) { $user_name = $itemDisplay->item['profile']['user_name']; }
		}
		$item_link_html = '<div class="user-link"><a href="./?user=' . $itemDisplay->item_user_id . '">' . $user_name . '</a></div>';
		$date_html = '<div class="date">' . $itemDisplay->dateService->date_time . '</div>';
		
		$metaOutput = $item_user_html . "<div class='meta-links float-left'>" . $item_link_html . $date_html . "</div>";

		//Only replace metaOutput if set to default value	
		if($itemDisplay->metaOutput != $itemDisplay->itemMetaLinks()) { $metaOutput = $metaOutput . $itemDisplay->metaOutput;  }
		else { $itemDisplay->metaOutput = "";  }
		$itemDisplay->metaOutput = $metaOutput;
	}
}

class addonItemProfileRequest extends profileManager {

	function set_item_loot($items){
		$this->item_loot = $items;
		$this->set = true;
	}
	
	function update($itemManager){
		$stream = $itemManager->stream;
		if(!isset($this->set)) { $this->item_loot = $itemManager->item_loot; }

		$tmp_loot_array = NULL;
		if($this->item_loot) { 
			foreach($this->item_loot as $item) {
				$quest = "SELECT user_profile.*, user.date"
					. " FROM user_profile, user"
					. " WHERE user_profile.user_id=" . $item['user_id']
					. " AND user_profile.user_id=user.user_id";
			
				$profile_loot = mysqli_query($stream, $quest);
				if($profile_loot) { 
					$item['profile'] = $profile_loot->fetch_assoc();					
				} $tmp_loot_array[] = $item;
			} 
		}
		$this->item_loot = $tmp_loot_array;
		$itemManager->item_loot = $this->item_loot;
		return $this->item_loot;
	}
}

class addonUserProfileRequest extends profileManager {
	function update($client){
		$stream = $client->stream;
		$user_loot = $client->user;
		$tmp_loot_array = NULL;
		
		if($user_loot) {
			$user_id = $user_loot['user_id'];
			$quest = "SELECT user_profile.*, user.user_id, user.date, user.level"
				. " FROM user_profile, user"
				. " WHERE user_profile.user_id='" . $user_id . "'"
				. " AND user_profile.user_id=user.user_id";
		
			$profile_loot = mysqli_query($stream, $quest);
			if($profile_loot->fetch_assoc()) { 
				$profile = $profile_loot->fetch_assoc();
				
				//set profile_id for profile addon
				$profile['profile_id'] = $user_id;
				$client->profile = $profile;
				
				if(isset($client->actions)) { runAddons($client->actions, $client->profile, 'user-profile-request'); }
			}
		}
	}
}
?>
