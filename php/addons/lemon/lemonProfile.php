<?php //Add-On for profile display
$profile_addon['addon-name'] = 'Lemon User Profiles';
$profile_addon['addon-version'] = '1.0';
$profile_addon['post-handler'] = 'addonPostProfileHandler';

$profile_addon['user-request'] = 'addonUserProfileRequest';
$profile_addon['user-account'] = 'addonUserProfileAccount';
$profile_addon['page-banner-display'] = 'addonProfileDisplay';
$profile_addon['item-display'] = 'addonItemProfileDisplay';
$profile_addon['item-request'] = 'addonItemProfileRequest';
$profile_addon['banner-display'] = 'addonBannerDisplay';

//Add to global $addOns variable
$addOns[] = $profile_addon;

class addonUserProfileAccount {
	function __construct ($stream) {
		$this->stream = $stream;
		$this->DEFAULT_USER_LEVEL = 3;
	}
	
	function handleAddOnJoin ($user) {		
		$level = $this->DEFAULT_USER_LEVEL;
		$profile_insert = "INSERT INTO user_profile (user_id, level) VALUES('" . $user['user_id'] . "', '$level')";
		mysqli_query($this->stream, $profile_insert);
		
		return $user;
	}
}

class addonPostProfileHandler {
	function __construct ($stream) {
		$this->stream = $stream;
		$this->DEFAULT_USER_LEVEL = 3;
		$this->DEFAULT_MAX_FILESIZE = 10485760; //10MB
	}
	
	function handleAddOnPost ($itemManager) {
		global $client;
		$level = $this->DEFAULT_USER_LEVEL;
		
		if(isset($_POST['itc_profile_name'])) {
			$this->changeProfileName($_POST['itc_profile_name'], $client->user_serial);
		} elseif(isset($_POST['itc_profile_img'])) {
			$this->handleProfileUpload($client);
		}

		if(!isset($_GET['user'])) { $this->meta['owner'] = false; return false; }
		$itemManager->meta['owner'] = ($_GET['user'] == $client->user_serial) ? true : false;
		$itemManager->meta['profile'] = $this->getUserProfile($_GET['user']);
		
		$profile = $itemManager->meta['profile'];
		$user_name = (isset($profile['user_name']) && $profile['user_name'] != false) ? $profile['user_name'] : "New Member (" . date('Y') . ")";		
		$itemManager->meta['title'] = $user_name;
			
		//Create a new profile if empty (when owner requests profile)
		if($itemManager->meta['owner'] && !isset($itemManager->meta['profile']['user_id'])) {
			$profile_insert = "INSERT INTO user_profile (user_id) VALUES('" . $client->user_serial . "')";
			$new_profile = mysqli_query($this->stream, $profile_insert);
			
			if($new_profile) { $itemManager->meta['profile'] = $this->getUserProfile($_GET['user']); }
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
		
	function getUserProfile ($user_id) {
		$stream = $this->stream;
		$input = "SELECT user_profile.*, user.user_id, user.date, user.level"
			. " FROM user_profile, user"
			. " WHERE user_profile.user_id='$user_id'"
			. " AND user_profile.user_id=user.user_id";
			
		$profile_loot = $stream->query($input);
		
		if($profile_loot) {
			$profile = $profile_loot->fetch_assoc();
		
			$feed_class = new addonProfileFeedRequest($this->stream, $profile, $user_id);
			$profile = $feed_class->getAddOnLoot($profile['level']);
		
			return $profile;
		}
	}
		
	function handleProfileRequest() {
		if(!isset($_GET['user'])) { return false; }
		return $this->getUserProfile($_GET['user']);
	}
}

class addonBannerDisplay {
	function __construct ($user, $auth, $pageManager) { 
		$this->user = $user;
		$this->auth = $auth;
		$this->pageManager = $pageManager;
	}
	
	function updateOutputHTML ($banner) {
		$user_links = '<div class="user_links">';
		$user = $this->user;
		if($this->auth) {
			  $user_links .= '+ <a href="./add.php"><u>New</u></a>' . ' &nbsp;';
			  $user_links .= '<a href="./?user=' . $user->user_serial . '"><u>Profile</u></a>';
			  if(!isset($user->profile)) { $user_links .= ' &nbsp;<a onclick="logout()"><u>Sign Out</u></a><form id="logoutForm" action="./?connect=1&logout=1" method="post"><input name="logout" type="hidden"/></form>'; }
		}
		else { $user_links .=  '<a href="./?connect=1">Sign In</a>'; }
		$user_links .= '</div>';
		
		$banner->user_links = $user_links;
	}
}

class addonProfileDisplay {
	function __construct ($pageManager) {
		$this->profile = (isset($pageManager->meta['profile'])) ? $pageManager->meta['profile'] : NULL;
		$this->owner = (isset($pageManager->meta['owner'])) ? $pageManager->meta['owner'] : NULL;
	}
	
	function updateOutputHTML ($pageManager) {
		if(isset($this->profile['user_id'])) { 
			$profileBanner = $this->profileBanner($pageManager);
			$banner = $pageManager->displayWrapper('div', 'section', 'section_inner', $profileBanner);
			$pageManager->pageOutput = $banner . $pageManager->pageOutput;
		}
	}
	
	function profileBanner ($pageManager) {
		$rootFiles = $pageManager->ROOTweb;
		$profile = $this->profile;
		
		$date = new DateService($profile['date']);
		$user_banner_html = (isset($profile['user_img'])) ? " style=\"background-image: url('" . $rootFiles . $profile['user_img'] . "')\"" : ""; 
		$user_name = $profile['user_name'] ? $profile['user_name'] : "New Member (" . chopString($profile['date'], 4, '') . ")";
		 
		$profile_link = "./?user=" . $profile['user_id'];
		 
		$n = "\n";
		$banner_html = "<div class=\"profile-banner\" onlick=\"window.location='" . $profile_link . "'\">$n";
		$banner_html .= "<div class=\"profile-banner_inner\">$n";

		$imageRollover = "changeImageRollover";
		$banner_html .= "<div style=\"float: left; width: 210px;\"><div class=\"avatar item-user\"";
		if($this->owner) { $banner_html .= " onmouseover=\"domId('$imageRollover').style.display='block'\" onmouseout=\"domId('$imageRollover').style.display='none'\""; }
		$banner_html .= $user_banner_html;
		$banner_html .= ">";
			
		if($this->owner) { $banner_html .= "<div id=\"$imageRollover\" onclick=\"domId('itc_banner_image_form').style.display='inline-block'; domId('show-form-button').style.display='none'\" style=\"display: none; width: 100%; height: 100%; opacity: 0.5; font-size: 92px\">&#8853;</div>"; }
			
		$banner_html .= "</div></div>$n";
		
		$banner_html .= "<div style=\"float: left; text-align: left; margin-top: 20px;\">$n"
			. "<form action=\"./?user=" . $profile['user_id'] . "\" method=\"post\"><div id=\"itc_banner_name_form\" style=\"display: none;\"><input type=\"hidden\" name=\"user_id\" value=\"" . $profile['user_id'] . "\"/><input class=\"form\" name=\"itc_profile_name\" value=\"" . $profile['user_name'] . "\"/><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"✅ SAVE\"></div><div id=\"itc_banner_name\"><span class=\"profile-name\"><div style=\"font-size: 2em; display: inline-block\" onclick=\"window.location.reload()\"><u>" . $user_name . "</u></div></span>";
		
		if($this->owner) { $banner_html .= " <span class=\"item-tools\" onclick=\"domId('itc_banner_name').style.display='none'; domId('itc_banner_name_form').style.display='block';\">&#9998; EDIT</span>"; }
		$banner_html .= "</div></form>";
		
		$banner_html .= "<div class=\"item-tools_dark\"><small>MEMBER SINCE</small><br />" . $date->date_time . "</div>$n";
		
		if($this->owner) {
			$banner_html .= "<form enctype=\"multipart/form-data\" action=\"./?user=" . $profile['user_id'] . "\" method=\"post\"><div style=\"display: none; margin-top: 4px;\" id=\"itc_banner_image_form\"><input type=\"hidden\" name=\"itc_profile_img\" value=\"change\"/><input type=\"hidden\" name=\"user_id\" value=\"" . $profile['user_id'] . "\"/><input type=\"file\" class=\"item-tools\" name=\"itc_user_img\" accept=\"image/jpeg,image/png,image/gif\"><input class=\"item-tools\" type=\"submit\" name=\"submit\" value=\"✅ SAVE\"></div></form>";
			$banner_html .= "<div id=\"show-form-button\" class=\"item-tools_dark\" onclick=\"domId('itc_banner_image_form').style.display='inline-block'; this.style.display='none'\" style=\"margin: 4px 0px;\">" . "Change the profile image" . "</div>";
		}

		$banner_html .= "</div>";

		if($this->owner) { 
			$banner_html .= "<div style=\"float: right; margin: 10px 28px 10px 10px; text-align: right\">";
			$banner_html .= "<a onclick=\"logout()\"><small><u>Sign Out</u></small></a><form id=\"logoutForm\" action=\"./?connect=1&logout=1\" method=\"post\"><input name=\"logout\" type=\"hidden\"/></form>";		
			$banner_html .= "</div>";
		}
		$banner_html .= "<div class=\"clear\"></div>$n";
		
		//RSS Feed: Link Generator
		$feed_url = $rootFiles . "?user=" . $profile['user_id'] . "&RSS=2.0";		
		$pop_up = "<div id=\"RSS_popup\" style=\"display: none; background-color: #222; opacity: 0.8; position: absolute; z-index: 100; left: 0px; width: 100%; min-height: 60%\">" 
			. "<div onClick=\"domId('RSS_popup').style.display='none'\" style=\"float: right; margin: 10px 28px 10px 10px; font-size: 14px\">" . "&#10005; Close"  . "</div>"
			. "<div style=\"margin: 40px 20%;\"><h2>" . $profile['user_name'] . " Feed (RSS)</h2></div>"		
			. "<div style=\"margin: 40px 20%;\">Feed Url: <input class=\"wider\" value=\"" . $feed_url . "\"/></div>"
			. $pageManager->displayItemXML()			
			. "</div>";
		$pop_up .= "<div onClick=\"domId('RSS_popup').style.display='block'\" style=\"float: right; margin: 10px 28px 10px 10px; font-size: 14px;\">&#9776; RSS</div>";
		
		$banner_html .= $pop_up;
		$banner_html .= "</div>$n";		
		$banner_html .= "</div>$n";
		return $banner_html;
	}
}

class addonItemProfileDisplay {
	function updateOutputHTML ($itemDisplay) {
		global $_ROOTweb;
		$user_img = ($itemDisplay->item_user_img) ? " style=\"background-image: url(" . $_ROOTweb . $itemDisplay->item_user_img . ")\"" : "";
		$item_user_html = "<div onclick=\"window.location='./?user=" . $itemDisplay->item_user_id . "';\">";
		$item_user_html .= "<span class=\"item-user\"$user_img></span>";
		$item_user_html .= "</div>";
		
		$user_name = "New Member";
		if(isset($itemDisplay->item['profile'])) {			
			$user_name .= " (" . chopString($itemDisplay->item['profile']['date'], 4, '') . ")";
			if($itemDisplay->item['profile']['user_name']) { $user_name = $itemDisplay->item['profile']['user_name']; }
		}
		$item_link_html = '<div class="item-user-link"><a href="./?user=' . $itemDisplay->item_user_id . '">' . $user_name . '</a></div>';
		$date_html = '<div class="item-date">' . $itemDisplay->dateService->date_time . '</div>';
		
		$metaOutput = $item_user_html . "<div style='float: left;'>" . $item_link_html . $date_html . "</div>";

		//Only replace metaOutput if set to default value	
		if($itemDisplay->metaOutput != $itemDisplay->itemMetaLinks()) { $metaOutput = $metaOutput . $itemDisplay->metaOutput;  }
		$itemDisplay->metaOutput = $metaOutput;
		$itemDisplay->output = $itemDisplay->displayHTML();
	}
}

class addonItemProfileRequest {
	function __construct ($stream, $items) {
		$this->stream = $stream;
		$this->item_loot = $items;
	}
	
	function getAddOnLoot (){
		$tmp_loot_array = NULL;
		if($this->item_loot) { 
			foreach($this->item_loot as $item) {
				$quest = "SELECT user_profile.*, user.date"
					. " FROM user_profile, user"
					. " WHERE user_profile.user_id=" . $item['user_id']
					. " AND user_profile.user_id=user.user_id";
			
				$profile_loot = mysqli_query($this->stream, $quest);
				if($profile_loot) { 
					$item['profile'] = $profile_loot->fetch_assoc();					
				} $tmp_loot_array[] = $item;
			} 
		}
		$this->item_loot = $tmp_loot_array;
		return $this->item_loot;
	}
}

class addonUserProfileRequest {
	function __construct ($stream, $user_loot) {
		$this->stream = $stream;
		$this->user_loot = $user_loot;
	}
	
	function getAddOnLoot ($client){
		$tmp_loot_array = NULL;
		if($this->user_loot) {
			$user_id = $this->user_loot['user_id'];
			$quest = "SELECT user_profile.*, user.user_id, user.date, user.level"
				. " FROM user_profile, user"
				. " WHERE user_profile.user_id='" . $user_id . "'"
				. " AND user_profile.user_id=user.user_id";
		
			$profile_loot = mysqli_query($this->stream, $quest);
			if($profile_loot->fetch_assoc()) { 
				$profile = $profile_loot->fetch_assoc();
				global $addOns;
				foreach($addOns as $addOn) {
					if(isset($addOn['profile-request'])) { 
						$feed_class = new $addOn['profile-request']($this->stream, $profile, $user_id);
						$profile = $feed_class->getAddOnLoot($profile['level']);
					}
				}		
				$client->profile = $profile;
			}
		}
		return $this->user_loot;
	}
}
?>
