<?PHP
/*
**  _ _                      _                 _
** (_) |_ ___ _ __ ___   ___| | ___  _   _  __| |
** | | __/ _ \ '_ ` _ \ / __| |/ _ \| | | |/ _` |
** | | ||  __/ | | | | | (__| | (_) | |_| | (_| |
** |_|\__\___|_| |_| |_|\___|_|\___/ \__,_|\__,_|
**          ITEMCLOUD (LEMON) Version 1.0
**
** Copyright (c) 2019, ITEMCLOUD http://www.itemcloud.org/
** All rights reserved.
** developers@itemcloud.org
**
** Free Software License
** -------------------
** Lemon is licensed under the terms of the MIT license.
**
** @category   ITEMCLOUD (Lemon)
** @package    Build Version 1.0
** @copyright  Copyright (c) 2019 ITEMCLOUD (http://www.itemcloud.org)
** @license    https://spdx.org/licenses/MIT.html MIT License
*/

//SET DOCUMENT ROOT
$_ROOTdir = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/";
$_ROOTweb = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";

include($_ROOTdir . 'php/db/config.php'); //Configuration
require_once($_ROOTdir .'php/db/core.php'); //Core MySQL Connection, DateService
require_once($_ROOTdir .'php/db/client.php'); //Client extends Core, itemManager, uploadManager
require_once($_ROOTdir .'php/db/display.php'); //PageManager extends Document, itemDisplay

//ADD-ONS
foreach (glob($_ROOTdir . "/php/addons/lemon/*.php") as $filename){
   require_once($filename);
}

//DATABASE: MySQL Connection
$client = new Client();
$client->enableAddOns();
$client->openConnection();

//AUTHORIZE USER ACCOUNT
$auth = $client->authorizeUser();
$itemManager = $client->itemManager();
$itemManager->enableAddOns();

$itemManager->meta['message'] = (isset($_POST['itc_class_id'])) ? $itemManager->handleItemUpload($client) : false;
$client->closeConnection();
	      
$pageManager = new pageManager($itemManager, $_ROOTweb);
$itemManager->enableAddOns();

$pageManager->displayDocumentHeader([
	'title' => 'lemon',
	'scripts' => ['./js/lib.js',
		     './js/welcome.js'],
	'styles' => ['./frame.css']
]);

$pageManager->displayPageBanner($client, $auth);
if (!$auth){ $pageManager->displayJoinForm(); }
else { $pageManager->displayPageOmniBox(); }

$pageManager->displayDocumentFooter([
	'copyright' => 'Copyright &copy;' . date("Y"),
	'copyleft' => 'Powered by <a href="http://www.itemcloud.org" target="_blank">'
				. '<img src="img/itemcloud-icon.png" class="footer_icon">'
				. '</a><sup>&trade;</sup>'
]);
?>
