<?PHP
/*
**  _ _                      _                 _
** (_) |_ ___ _ __ ___   ___| | ___  _   _  __| |
** | | __/ _ \ '_ ` _ \ / __| |/ _ \| | | |/ _` |
** | | ||  __/ | | | | | (__| | (_) | |_| | (_| |
** |_|\__\___|_| |_| |_|\___|_|\___/ \__,_|\__,_|
**          ITEMCLOUD (LEMON) Version 1.2
**
** Copyright (c) 2019-2021, ITEMCLOUD http://www.itemcloud.org/
** All rights reserved.
** developers@itemcloud.org
**
** Free Software License
** -------------------
** Lemon is licensed under the terms of the MIT license.
**
** @category   ITEMCLOUD 1.2 (lemon)
** @package    Build Version 1.1-1.2.9 (itemcloud-lemon.sql)
** @copyright  Copyright (c) 2021 ITEMCLOUD (http://www.itemcloud.org)
** @license    https://spdx.org/licenses/MIT.html MIT License
*/

/* -------------------------------------------------------- **
** ---------------------- CORE CLASS ---------------------- **
** -------------------------------------------------------- */

Class Core {
	var $stream;
	
	//DATABASE::Connection
	function __construct() {
	  global $CONFIG;
	  $this->host = $CONFIG['host'];
	  $this->user = $CONFIG['user'];
	  $this->password = $CONFIG['password'];
	  $this->db = $CONFIG['db'];
	  $this->usercookie = 'ICC:UID';
	  $this->addOns = NULL;
	}

	function MyDB_Connect () {
		$connection = new mysqli($this->host, $this->user, $this->password, $this->db);	
		if($connection->connect_error) {
		die("Oops! We have more work to do.");
		}
		
		return $connection;
	}
	
	function MyDB_Close () {
		return $this->stream->close();
	}	
	
	function openConnection() {
		$this->stream = $this->MyDB_Connect();
		return $this->stream;
	}

	function closeConnection() {	
		$this->stream = $this->MyDB_Close();
	}
}

/* -------------------------------------------------------- **
** -------------------- DATE SERVICE ---------------------- **
** -------------------------------------------------------- */

Class DateService {
	var $raw_date;
	var $date_time;
	
	function __construct($raw) {
		$this->raw_date = $raw;
		$this->date_time = $this->make_date_time($raw);
	}
	
	function make_date_time ($date_time_string) {
		if(!$date_time_string) return;
		$string = explode(" ", $date_time_string . " ");
		$date = $this->make_date($string[0]);
		$time = $this->make_time($string[1]);
		return $this->make_box_date($string[0]);
	}

	function make_date ($date_string) {
		if(!$date_string) return;
		$calender = explode("-", $date_string);
		$year = $calender[0];
		$month = $calender[1];
		$day = $calender[2];
		$day = substr($day,0,2);

		$month_name['01'] = "Jan";
		$month_name['02'] = "Feb";
		$month_name['03'] = "March";
		$month_name['04'] = "April";
		$month_name['05'] = "May";
		$month_name['06'] = "June";
		$month_name['07'] = "July";
		$month_name['08'] = "Aug";
		$month_name['09'] = "Sept";
		$month_name['10'] = "Oct";
		$month_name['11'] = "Nov";
		$month_name['12'] = "Dec";	
		return  $month_name[$month] . " " . $day . ", " . $year;
	}

	function make_box_date ($date_string) {
		$calender = explode("-", $date_string);
		$year = $calender[0];
		$month = $calender[1];
		$day = $calender[2];
		$day = (int)substr($day,0,2);
		
		$month_name['01'] = "January";
		$month_name['02'] = "February";
		$month_name['03'] = "March";
		$month_name['04'] = "April";
		$month_name['05'] = "May";
		$month_name['06'] = "June";
		$month_name['07'] = "July";
		$month_name['08'] = "August";
		$month_name['09'] = "September";
		$month_name['10'] = "October";
		$month_name['11'] = "November";
		$month_name['12'] = "December";
		
		$dayth = "th";
		if($day == 1){
			$dayth = "st";
		}
		
		if($day == 2){
			$dayth = "nd";
		}
		
		if($day == 3){
			$dayth = "rd";
		}
		
		return "<div class='item-date'>" . $month_name[$month] . " " . $day.$dayth . ", <span class='item-date-year'>" . $year ."</span></div>";
	}

	function make_time ($time_string) {
		$day = explode(":",$time_string);
		$hour = $day[0];
		$min = $day[1];
		$sec = $day[2];
		
		$am_pm = "am";
		if($hour >= 12) {
			$hour -= 12;
			$am_pm = "pm";
		}
		
		return $hour . ":" . $min . $am_pm;
	} 
}

/* -------------------------------------------------------- **
** ------------------- HELP FUNCTIONS --------------------- **
** -------------------------------------------------------- */

function chopString($str, $length, $extra) {
  $text = strip_tags($str);
  if(strlen($text)>$length) {
    $text = substr($text, 0, $length);
	$text .= (isset($extra)) ? $extra : "";
  }
  return $text;
}
?>
