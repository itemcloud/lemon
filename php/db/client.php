<?PHP
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
** Free Software License
** -------------------
** Lemon is licensed under the terms of the MIT license.
** Free to use and share with this copyright included.
** Thanks for your support!
**
** @category   ITEMCLOUD (Lemon)
** @package    Build Version 1.3
** @copyright  Copyright (c) 2019-2021 ITEMCLOUD (http://www.itemcloud.org)
** @license    https://spdx.org/licenses/MIT.html MIT License
*/

/* -------------------------------------------------------- **
** --------------------- CLIENT CLASS --------------------- **
** -------------------------------------------------------- */

class Client extends Core {

	function authorizeUser () {

		$this->auth = false;
		$this->owner = false;
		$this->level = 3; //CONFIG DEFAULT
			
		///-- serial --///
		$this->user_serial = false;
		$uid = $this->usercookie;
		
		if (isset($_COOKIE[$uid])) {
			if (isset($_GET['logout'])) {
				$this->logoutUser();
				//Redirect user back to home
				header("Location: ./");
			}
			
			$this->auth = true;
			///-- serial --///
			$this->user_serial = $_COOKIE[$uid];
						
			///-- getUser() --///
			$this->level = $this->getUser($_COOKIE[$uid]);
		} else {
			
			//new account request
			if (isset($_REQUEST['REG_new']) && isset($_REQUEST['e']) && isset($_REQUEST['p'])) {
				$user_match = $this->registerUser($_REQUEST['e'], $_REQUEST['p']);
			} else if(isset($_REQUEST['REG_signin']) && isset($_REQUEST['e']) && isset($_REQUEST['p'])) {
				$user_match = $this->signIn($_REQUEST['e'], $_REQUEST['p']);
			}
			
			if(isset($user_match)) {
				$this->auth = true;	
				$this->user_serial = $user_match['user_id'];				
				$this->level = $this->getUser($user_match['user_id']);
			}
		} return $this->auth;
	}

	function getUser ($user_id) {
		$stream = $this->stream;
	  	if(!$stream){ return false; }
		$user_auth = "SELECT * FROM user"
			. " WHERE user_id='$user_id'";
					
		$query = $stream->query($user_auth);
		$user = $query->fetch_assoc();
		
		$this->user = $user;
		if(isset($this->actions)) { runAddons($this->actions, $this, 'user-request'); }
		return $this->user['level'];
	}

	function signIn ($e, $p) {
		$stream = $this->stream;
	  	if(!$stream){ return false; }

		$user_auth = "SELECT * FROM user"
			. " WHERE email='" . strtolower($e) . "' AND password='" . md5($p) . "'";
		$user = $stream->query($user_auth);
					
		if (mysqli_num_rows($user) > 0) {
			$user = $user->fetch_assoc();
			
			$user_serial = $user['user_id'];
			$uid = $this->usercookie;
			setcookie($uid, $user_serial, time()+(36000*24), '/', '');
			return $user;			
		} else {
			global $message;
			$message = "Nope. Try another email and passcode.";
		}
	}

	function registerUser($e, $p) {
		$stream = $this->stream;

		$user_auth = "SELECT * FROM user WHERE email='" . strtolower($e) . "'";
		$user = $stream->query($user_auth);
		
		if (mysqli_num_rows($user) > 0) {
			global $message;
			$message = "Email is already in use.";
		} else {
			$date = date('Y-m-d h:i:s');
			$user_insert = "INSERT INTO user (email, password, date) VALUES('" . strtolower($e) . "', '" . md5($p) . "', '" . $date . "')";

			$insert_query = mysqli_query($stream, $user_insert);
			$user_serial = mysqli_insert_id($stream);

			if($user_serial) {
				$uid = $this->usercookie;
				setcookie($uid, $user_serial, time()+(36000*24), '/', '');
				
				$new_user = [
					'user_id' => $user_serial,
					'email' => strtolower($e),
					'password' => md5($p),
					'date' => $date
				];
				
				if($this->addOns) {
					foreach($this->addOns as $addOn) {
						if(isset($addOn['user-account'])) {
							//POST ADDONS
							//$addonClass = new $addOn['user-account']($this->stream);					
							//$new_user = $addonClass->handleAddOnJoin($new_user);
						}
					}
				}
				return $new_user;
			}
		}
	}

	function enableActions ($actions) {
		if(isset($actions)) {
			$this->actions = $actions;
		}
	}
	
	function enableAddOns($addOns) {		
		if(isset($addOns)) {
			$this->addOns = $addOns;
			foreach($addOns as $addon) {
				$addonClass = new $addon;
				$addonClass->setActions();
			}
		}
	}
	
	function logoutUser () {
		$uid = $this->usercookie;
		setcookie($uid, '', time()-3600, '/', '');
		unset($_COOKIE[$uid]);
	}

	function itemManager($count) {
		return new itemManager($this, $count);
	}
}

/* -------------------------------------------------------- **
** --------------------- ITEMS CLASS ---------------------- **
** -------------------------------------------------------- */

class itemManager {

	function __construct ($client, $count) {
		$this->client = $client;
		$this->stream = $client->stream;
		$this->meta = NULL;
		$this->items = NULL;
		$this->item_index = 0; 
		$this->addOns = NULL;
		
		$this->level = $client->level;
		$this->classes = $this->getItemClasses($this->level);
		$this->types = $this->getItemTypes();
		
		$this->meta['message'] = "";
		$this->meta['item_count'] = $count;
		$this->insertOk = "1";
	}

	function enableActions ($actions) {
		if(isset($actions)) {
			$this->actions = $actions;
		}
	}
	
	function handleItemRequest() {
		
		$start = (isset($_GET['start'])) ? $_GET['start'] : 0;
		$count = $this->meta['item_count'];
		$user_level = $this->level;
		
		if(isset($_GET['connect'])) {
		    return;
		}	

		if(isset($this->actions) && !isset($_POST['edit'])) { runAddons($this->actions, $this, 'post-handler'); }
		if(isset($this->active)) { return; }
		
		if (isset($_GET['api'])) {
			$this->items = $this->getAllItems($start, $count, $user_level);
		} else if(isset($_POST['delete'])) {
			global $message;
			$message = "The item has been deleted.";
			$this->deleteUserItem($_POST['delete']);
			$this->items = $this->getUserItems($_GET['user'], $start, $count, $user_level);
		} else if(isset($_POST['itc_edit_item'])) {
			global $message;
			$message = "The item has been edited.";
			$this->updateItem($_POST['itc_edit_item'], $_POST['itc_title'], $_POST['itc_description']);
			$this->items = $this->getItemById($_POST['itc_edit_item']);
		} else if (isset($_POST['itc_class_id'])) {
			global $client;
			$upload = $this->handleItemUpload($client); 
			if($this->insertOk == 1 && isset($this->item_id)) {
				$this->insertUserItem($client->user_serial, $this->item_id, 3);
				$this->meta['message'] = "Another " . "<a href=\"./?id=" . ($this->item_id) . "\">new item</a> has been added.";
			} else {
				$this->meta['message'] = $message;
			}
		} else if(isset($_GET['id'])){
			$this->items = $this->getItemById($_GET['id']);
			$this->items[0]['active'] = true;
			$this->meta['title'] = $this->items[0]['title'];	
			if($this->items[0]['class_id']==4) { $this->meta['active_img'] = $this->items[0]['link']; }
			else if(isset($yt_video_links_addon) && $this->items[0]['class_id']==2) { 
				$ytVideoImage = new youtubeVideoLinks();
				$this->meta['active_img'] = $ytVideoImage->returnImageURL($this->items[0]['link']);
			}
		} else if(isset($_GET['user'])){
			$this->items = $this->getUserItems($_GET['user'], $start, $count, $user_level);
		} else if(!$this->items && (empty($_GET) || isset($_GET['start']) || isset($_GET['grid']))) {
			$this->items = $this->getAllItems($start, $count, $user_level);
		} return $this->items;
	}

	function insertItem($type, $title, $info, $file) {
		$stream = $this->stream;

		$title = strip_tags($stream->real_escape_string($title));
		$info = strip_tags($stream->real_escape_string($info));
		$quest = "INSERT INTO item (class_id, title, description, link)"
				. " VALUES('$type', '$title', '$info', '$file')";
		
		$stream->query($quest);
		$item_id = $stream->insert_id;
		return $item_id;
	}

	function insertUserItem($user_serial, $item_id, $level) {
		$stream = $this->stream;

		$quest = "INSERT INTO user_items (user_id, item_id, level)"
				. " VALUES('$user_serial', '$item_id', '$level')";
		
		$stream->query($quest);
		$item_id = $stream->insert_id;
		return $item_id;
	}
	
	function deleteItem($delete_id) {
		$stream = $this->stream;
		
		$quest = "DELETE FROM item WHERE item_id='$delete_id'";
		$stream->query($quest);		
	}
		
	function updateItem($item_id, $title, $info) {
		$stream = $this->stream;
		
		$title = strip_tags($stream->real_escape_string($title));
		$info = strip_tags($stream->real_escape_string($info));
		$set = "SET title='$title', description='$info'";
		
		$quest = "UPDATE item $set WHERE item_id='$item_id'";
		$stream->query($quest);		
	}

	function getUserItems($user_serial, $start, $count, $level) {
		$stream = $this->stream;	   
		$quest = "SELECT SQL_CALC_FOUND_ROWS item.*, user_items.*"
		       . " FROM item, user_items"
		       . " WHERE user_items.user_id=$user_serial"
		       . " AND item.item_id=user_items.item_id"
		       . " ORDER BY user_items.date DESC"
		       . " LIMIT $start, $count";
		
		$item_loot = mysqli_query($stream, $quest);
		$item_loot_array = NULL;
		if($item_loot) {
			$count_quest = mysqli_query($stream, "SELECT FOUND_ROWS() AS count")->fetch_assoc();
			while($loot=$item_loot->fetch_assoc()) {
				$loot['total'] = $count_quest['count'];
				$item_loot_array[] = $loot;
			}
		}
		
		$this->item_loot = $item_loot_array;
		if(isset($this->actions)) { runAddons($this->actions, $this, 'item-request'); }
		return $this->item_loot;
	}

	function getAllItems($start, $count, $user_level) {
		$stream = $this->stream;
		$quest = "SELECT SQL_CALC_FOUND_ROWS item.*, user_items.*"
		       . " FROM item, user_items"
		       . " WHERE item.item_id=user_items.item_id"
		       . " AND user_items.level >= $user_level"
		       . " ORDER BY user_items.date DESC"
		       . " LIMIT $start, $count";

		$item_loot = mysqli_query($stream, $quest);
		$item_loot_array = NULL;
		if($item_loot) {
			$count_quest = mysqli_query($stream, "SELECT FOUND_ROWS() AS count")->fetch_assoc();
			while($loot=$item_loot->fetch_assoc()) {
				$loot['total'] = $count_quest['count'];
				$item_loot_array[] = $loot;
			}
		}
		
		$this->item_loot = $item_loot_array;
		if(isset($this->actions)) { runAddons($this->actions, $this, 'item-request'); }
		return $this->item_loot;
	}
	
	function getItemsByType() {
		return $items;
	}

	function getItemsByUser() {
		return $items;
	}

	function getItemById($item_id) {
		$quest = "SELECT item.*, user_items.*"
		     . " FROM item, user_items"
			 . " WHERE item.item_id='$item_id'"
			 . " AND item.item_id=user_items.item_id";
		
		$item_loot = mysqli_query($this->stream, $quest);
		$item_loot_array = NULL;
		if($item_loot) {
			while($loot=$item_loot->fetch_assoc()) {
				$item_loot_array[] = $loot;
			}
		}
		
		$this->item_loot = $item_loot_array;
		if(isset($this->actions)) { runAddons($this->actions, $this, 'item-request'); }
		return $this->item_loot;
	}
	
	function getItemsByClass($class_id, $limit, $start, $level) {
		 		
		$quest = "SELECT item.*, user_items.*"
		       . " FROM item, user_items"
		       . " WHERE item.item_id=user_items.item_id"
			   . " AND item.class_id=" . $class_id 
		       . " ORDER BY user_items.date DESC";
		       
		$quest .= " LIMIT $start, $limit";

		$item_loot = mysqli_query($this->stream, $quest);
		$item_loot_array = NULL;
		if($item_loot) {
			while($loot=$item_loot->fetch_assoc()) {
				$item_loot_array[] = $loot;
			}
		}
		
		$this->item_loot = $item_loot_array;
		if(isset($this->actions)) { runAddons($this->actions, $this, 'item-request'); }
		return $this->item_loot;
	}
	
	function deleteUserItem ($delete_id) {
		$stream = $this->stream;
		
		$quest = "DELETE FROM user_items WHERE item_id='$delete_id'";
		$stream->query($quest);
		
		$this->deleteItem($_POST['delete']);
	}

	function getItemClasses($level) {
		$stream = $this->stream;
		$class_quest = "SELECT item_class.*, item_nodes.*"
					. " FROM item_class, item_nodes"
					. " WHERE item_nodes.class_id=item_class.class_id"
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

	function getItemTypes() {
		$stream = $this->stream;
		$quest = " SELECT item_class.*, item_nodes.*"
		       . " FROM item_class, item_nodes"
		       . " WHERE item_nodes.class_id=item_class.class_id";
		
		$type_loot = mysqli_query($stream, $quest);
		$type_loot_array = NULL;
		if($type_loot) {
			while($loot=$type_loot->fetch_assoc()) {
				$loot_str = $loot['class_name'];
				$type_loot_array[$loot_str][] = $loot;
			}
		}			
		return $type_loot_array;
	}
	
	function handleItemUpload($client) {
		 if (isset($_POST['itc_class_id'])) {
			global $_ROOTdir;
									
			$folder = "files/";
			$insertOk = 1;
			$target_dir = $_ROOTdir . $folder;
			$filesize = 30485760; //10MB
			
			$posted_class = $_POST['itc_class_id'];			
			$title = (isset($_POST['itc_title'])) ? $_POST['itc_title'] : "";
			$info = (isset($_POST['itc_description'])) ? $_POST['itc_description'] : "";
			$file = (isset($_POST['itc_link'])) ? $_POST['itc_link'] : "";

			$classes = $this->classes;
			$class_form = $classes[$posted_class];
			$class_id = $class_form['class_id'];
			
			//only handle the posted class
			if($posted_class == $class_id) {
				foreach($class_form['nodes'] as $nodes){					
					if(isset($_FILES["itc_link"]) && $nodes['node_name'] == 'link') {
						if(count($class_form['ext'])) {
							$file_extensions = $class_form['ext'];
						}
					} else if(!$_POST['itc_'.$nodes['node_name']] && $nodes['required'] != NULL){
						//detect required nodes	git add							
						$message = "Sorry, some items have required info.";		
						$this->insertOk = 0;
						return $message;
					}
				}
			}
				
			 if(isset($_FILES["itc_link"])) {
				$tmp_file = new uploadManager(
					$_FILES["itc_link"],
					$target_dir,
					$filesize,
					$file_extensions);

				$tmp_file->handleUploadRequest();
				$tmp_file->uploadFile();
				$file = $folder . $tmp_file->target_file_name;	
				if($tmp_file->uploadOk == 0) {
					$insertOk = 0;
				} $message = $tmp_file->errorStatus;

				if($class_id != 4 && !$title) {
				   $title = $_FILES["itc_link"]["name"];
				}
			}

			if($insertOk) {
				$this->insertOk = $insertOk;
				$id = $this->insertItem($class_id, $title, $info, $file);
				if(isset($id) && $client->user_serial) {
					$this->item_id = $id;
				    return $id;
				}
			} else {							
				$message = "Sorry, your item could not be added.";	
				$this->insertOk = 0;
				return $message;
			}
		}
	}
}

/* -------------------------------------------------------- **
** -------------------- UPLOAD CLASS ---------------------- **
** -------------------------------------------------------- */

class uploadManager {

	function __construct($FILE, $target_dir, $filesize, $filetypes) {
		$rand = mt_rand(99, 999);
		$this->tmp_file = $FILE;
		$this->target_file = $target_dir . $rand . "_" . basename($FILE["name"]);
		$this->target_file_name =  $rand . "_" . basename($FILE["name"]);
		$this->filesize_limit = $filesize;
		$this->filetypes = $filetypes;
		$this->uploadOk = 1;
		$this->imageFileType = strtolower(pathinfo(basename($FILE["name"]),PATHINFO_EXTENSION));
		$this->errorStatus = "Sorry, there was an error uploading your file.";
	}

	function handleUploadRequest() {
		
		// Check if file already exists
		if (file_exists($this->target_file)) {
			$this->errorStatus = "Sorry, file already exists.";
			$this->uploadOk = 0;
		}
		// Check file size
		if ($this->uploadOk && $this->tmp_file["size"] > $this->filesize_limit) {
			$this->errorStatus = "Sorry, your file is too large.";
			$this->uploadOk = 0;
		}
		
		// Allow certain file formats
		if(!in_array($this->imageFileType, $this->filetypes)) {
			$this->errorStatus = "Choose a "
					   . implode(", ", $this->filetypes)
					   . " file.";
			$this->uploadOk = 0;
		}
	}

	function checkImage() {
		
		// Check if image file is a actual image or fake image
		if($this->tmp_file["tmp_name"]) {
			$check = getimagesize($this->tmp_file["tmp_name"]);
			if($check !== false) {
				$this->uploadOk = 1;
			} else {
				$this->errorStatus = "File is not an image.";
				$this->uploadOk = 0;
			}
		}
	}
	
	function formatImage() {
		$filePath = $this->tmp_file['tmp_name'];
		
		if($this->imageFileType == 'jpeg' || $this->imageFileType == 'jpg') {
			$imageResource = imagecreatefromjpeg($filePath);
		} else if ($this->imageFileType == 'png') {
			$imageResource = imagecreatefrompng($filePath);
		} else if ($this->imageFileType == 'gif') {
			$imageResource = imagecreatefromgif($filePath);
		} else {
			return;
		}
		
		$tmp_image = $imageResource;
		//rotate the image (if needed)
		$tmp_image = $this->rotateImage($tmp_image);
		//resize the image
		$tmp_image = $this->resizeImage($tmp_image, 1000, 1000);
				
		if($this->imageFileType == 'jpeg' || $this->imageFileType == 'jpg') {		
			$move_upload = imagejpeg($tmp_image, $this->target_file);
		} else if ($this->imageFileType == 'png') {
			$move_upload = imagepng($tmp_image, $this->target_file);
		} else if ($this->imageFileType == 'gif') {
			$move_upload = imagegif($tmp_image, $this->target_file);
		}
			
		imagedestroy($imageResource);
		imagedestroy($tmp_image);
		return $move_upload;
	}
	
	function rotateImage ($tmp_image) {
	
		$exif = function_exists('exif_read_data') && @exif_imagetype($filePath) == 2 ? exif_read_data($filePath): false;
		if(!$exif) { return $tmp_image; }

		if (!empty($exif['Orientation'])) {	
			switch ($exif['Orientation']) {
				case 3:
				$tmp_image = imagerotate($tmp_image, 180, 0);
				break;
				case 6:
				$tmp_image = imagerotate($tmp_image, -90, 0);
				break;
				case 8:
				$tmp_image = imagerotate($tmp_image, 90, 0);
				break;
				default:
				$tmp_image;
			} 
		} return $tmp_image;
	}
	
	function resizeImage ($tmp_image, $max_width, $max_height) {
		
		$width = imagesx($tmp_image);
		$height = imagesy($tmp_image);

		# taller
		if ($height > $max_height) {
			//Do not make width smaller than max_width
			//(even if height is problematically long?)
			if($max_width <= (($max_height / $height) * $width)) {
				$width = ($max_height / $height) * $width;
				$height = $max_height;
			}
		}

		# wider
		if ($width > $max_width) {
			$height = ($max_width / $width) * $height;
			$width = $max_width;
		}		
		
		return imagescale ($tmp_image , $width);
	}
	
	function uploadFile () {
		if($this->uploadOk) {
			if($this->formatImage()) {
				$this->errorStatus = "Image uploaded and resized.";
				$this->uploadOk = 1;
			} else if (move_uploaded_file($this->tmp_file['tmp_name'], $this->target_file)) {
				$this->errorStatus = "Full size image uploaded";
				$this->uploadOk = 1;
			} else {
				$this->errorStatus = "Sorry, something went wrong.";
				$this->uploadOk = 0;
			}
		}
	}
}
?>
