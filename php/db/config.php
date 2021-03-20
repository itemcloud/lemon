<?PHP
/*
**  _ _                      _                 _
** (_) |_ ___ _ __ ___   ___| | ___  _   _  __| |
** | | __/ _ \ '_ ` _ \ / __| |/ _ \| | | |/ _` |
** | | ||  __/ | | | | | (__| | (_) | |_| | (_| |
** |_|\__\___|_| |_| |_|\___|_|\___/ \__,_|\__,_|
**          ITEMCLOUD (LEMON) Version 1.2
**
** Update host/user/password for database connections.
**
** Keep this configuration private and secure.
** A. Add this file to .gitignore 
** B. Copy this file to addons folder. Do not edit this file.
**
*/

$CONFIG['host'] = 'localhost';
$CONFIG['user'] = 'itemcloud-lemon';
$CONFIG['password'] = 'default';
$CONFIG['db'] = 'itemcloud-lemon';

$CONFIG['ROOTdir'] = $_ROOTdir;
$CONFIG['ROOTweb'] = $_ROOTweb;

$CONFIG['title'] = "lemon";
$CONFIG['item_count'] = 10;
$CONFIG['limit_items'] = true;
?>
