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

if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) )
{
//SET DOCUMENT ROOT
$_ROOTdir = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/";
$_ROOTweb = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."../../../";

include('config.php'); //Configuration
require_once('core.php'); //Core MySQL Connection, DateService
require_once('client.php'); //Client extends Core, itemManager, uploadManager
require_once('display.php'); //PageManager extends Document, itemDisplay

//ADD-ONS
foreach (glob($_ROOTdir . "../addons/1.3/*.php") as $filename){
   require_once($filename);
}

//DATABASE::MySQL / MariaDB
$client = new Client();
$client->enableAddOns($addOns);
$client->enableActions($actions);
$client->openConnection();

//AUTHORIZE::USER ACCOUNT
$auth = $client->authorizeUser();
$itemManager = new itemManager($client, $CONFIG['item_count']);
$itemManager->enableActions($actions);

//DATABASE::CHECK FOR ITEM REQUEST IN POST
$items = $itemManager->handleItemRequest();
$client->closeConnection();

//DISPLAY::HTML DOCUMENT
$pageManager = new pageManager($itemManager, $_ROOTweb, $client);
$pageManager->displayClass = "";
$pageManager->enableActions($actions);

if ($items) { echo $pageManager->handlePageItemRequest(); } else { echo "empty"; }

}
?>
