//----------------------------------//
//------ ITEMCLOUD Lemon 1.1 -------//
//----------------------------------//
//------ JS LIBRARY FUNCTIONS ------//
//----------------------------------//

var md5_stamp;

var auto_more = true;
var reached_bottom = false;
var more_count = 10;
var more_start = 10;
var feed_id = 0;

window.onscroll = function(ev) {
    if ((window.innerHeight + window.scrollY) >= document.body.scrollHeight && !reached_bottom && auto_more == true) {
	setTimeout(display_more_items('more-items', more_count, more_start, feed_id), 5000);
    }
};

// JavaScript Document
function display_more_items(element, count, start, feed_id) {
	reached_bottom = true;
	
    var callback = function (x) {
	if(x.responseText != "empty") {
		document.getElementById(element).innerHTML += x.responseText;
		more_start += count;
		reached_bottom = false;    
	}
    }

    var XObj;
    try { XObj = new XMLHttpRequest(); }
    catch(e) { XObj = new ActiveXObject(Microsoft.XMLHTTP); }
    
    XObj.onreadystatechange = function () {
	if(XObj.readyState == 4) {
	    if(callback) {
		callback(XObj);
	    }
	}
    }
    
    if(document.getElementById(element)) {
		XObj.open('POST','php/db/api.php?count=' + count + '&start=' + start + '&feed_id=' + feed_id, true);
		XObj.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		XObj.send(null);
	}
}

//Display::functions
var domId = function (id) {
	return document.getElementById(id);
}

function showAlertBox(message) {
	if(domId('alertbox')) {
		domId('alertbox').innerHTML = message;
		domId('alertbox').className = "alertbox-show";
	}
}

function auto_expand(element) {
    element.style.height = "4px";
    element.style.height = (element.scrollHeight - 8)+"px";
}

function nl2br (str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

//Display::User:functions
function hasWhiteSpace(s) {
	return s.indexOf(' ') >= 0;
}

function hasSpecialChars(str){
	return !str.match(/^[a-zA-Z0-9]+$/);
}

function isValidEmail(str) {
	 if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(str))  
	  {  
		return (true)  
	  }  
		return (false)  
}

function isValidPassword(str) {
	if(str.length<19 && str.length>5)
	{
		return true;
	}
		return false;
}

//Control::functions
function holdDown(btn, action, start) {
	var t;
	
	var repeat = function () {
		action();
		t = setTimeout(repeat, start);
	}
	
	repeat();
	
    btn.onmousedown = function() {
        repeat();
    }

    btn.onmouseup = function () {
		clearTimeout(t);
    }
	
    btn.onmouseout = function () {
        clearTimeout(t);
    }
}

function popup(url) 
{
 var width  = 800;
 var height = 440;
 var left   = (screen.width  - width)/2;
 var top    = (screen.height - height)/2;
 var params = 'width='+width+', height='+height;
 params += ', top='+top+', left='+left;
 params += ', directories=no';
 params += ', location=no';
 params += ', menubar=no';
 params += ', resizable=no';
 params += ', scrollbars=no';
 params += ', status=no';
 params += ', toolbar=no';

 var newwin=window.open(url,'loading...'); //params
 newwin.opener = window;
 if (!window.focus) {window.focus()}
 newwin.opener.document.title = "loading...";
 return false;
}

//Time::Functions
function formatTime(millis) {
	var minutes = Math.floor(millis / 60);
	var seconds = ((millis % 60)).toFixed(0);
	return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
}

function make_date_time (date_time_string) {
	var string = date_time_string.split(" ");
	var date = make_date(string[0]);
	var time = make_time(string[1]);
	return date; 
}

function make_date (date_string) {
	var calender = date_string.split("-");
	var year = calender[0];
	var month = calender[1];
	var day = calender[2].split(" ");
	day = day[0];

	var month_name = new Array();
	month_name['01'] = "January";
	month_name['02'] = "Febraury";
	month_name['03'] = "March";
	month_name['04'] = "April";
	month_name['05'] = "May";
	month_name['06'] = "June";
	month_name['07'] = "July";
	month_name['08'] = "August";
	month_name['09'] = "September";
	month_name['10'] = "October";
	month_name['11'] = "November";
	month_name['12'] = "December";
	
	return month_name[month] + " " + day + ", " + year;
}

function make_time (time_string) {
	var day = time_string.split(":");
	var hour = day[0];
	var mins = day[1];
	var sec = day[2];
	
	if(hour > 12) {
		hour = hour - 12;
	}
	return parseInt(hour) + ":" + mins;
}

//----------------------------------//
//------- ADD ITEM: OMNIBOX --------//
//----------------------------------//

var OmniBox = class {
	constructor(class_array, parent_div) {
		this.class_array = class_array;
		this.active_class = Object.keys(class_array)[0];
		this.parent_div = parent_div;
		this.str = '';
	}
	
	toggle (class_id) {
		this.active_class = class_id;
		domId(this.parent_div).innerHTML = this.class_form_HTML(this.class_array[class_id]);
	}

	set_active_str (id_string) {
		this.str = id_string;
	}
	
	submitCallback () {
		domId('itc_OmniBoxForm' + this.str).submit();
	}
	
	addItemButton () {
		var button = "<input class=\"item-tools\" type=\"button\" name=\"checkInput" + this.str + "\" onClick=\"OmniController" + this.str + ".checkInput()\"  value=\"&#10004 SAVE\"/><br />";
		return button;	
	}
	
	form_input(class_id) { return "<input type=\"hidden\" name=\"itc_class_id\" value=\"" + class_id + "\"/>"; }
	form_id() { return " id=\"itc_OmniBoxForm" + this.str + "\""; }
	functions_str() { return " action=\"index.php\" method=\"post\""; }
	functions_file() { return " action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\""; }
	
    checkInput () {
		var class_form = this.class_array[this.active_class];
		for (var i = 0; i < class_form.nodes.length; i++) {
			var node = class_form.nodes[i];

			var value = document.getElementById('itc_' + node['node_name'] + '_txt' + this.str).value;
			
			if(node['required'] && !value) {
			var item_name = node['node_name'];

			domId('alertbox' + this.str).innerHTML = "Adding a " + item_name + " is required for new " + class_form['class_name'] + ".";
			domId('alertbox' + this.str).className = "alertbox-show";
			return false;
			}
		}

		this.submitCallback();
    }
	
	class_form_HTML (class_form) {
	    var form_input = this.form_input(class_form['class_id']);
	    var form_id = this.form_id();
	    var functions = this.functions_str();
			
		for (var i = 0; i < class_form.nodes.length; i++) {
			var node = class_form.nodes[i];
			
			if(class_form['types'].length > 0 && node['node_name'] == 'link') {
				var functions = this.functions_file();

				var types = class_form['types'];
				form_input += "<input type=\"file\" class=\"item-tools\" value=\"title\" name=\"itc_" + node['node_name'] + "\" id=\"itc_" + node['node_name'] + "_txt" + this.str + "\" accept=\"";

				//accepted filetypes
				form_input += types.join();
				form_input += "\"/><div><small>Choose " + types.join() + " only.</small></div>";
				form_input += "<hr />";

			} else {
				var domid = "itc_" + node['node_name'];
				var domid_add = "itc_add_" + node['node_name'] + "_" + class_form['class_id'];	
				
				//Toggle display onclick
				var show = "this.style.display='none';"
					  + "domId('" + domid + this.str + "').style.display = 'block'";
				var hide = "domId('" + domid_add + this.str + "').style.display = 'block';"
					  + "domId('" + domid + this.str + "').style.display = 'none';"
					  + "domId('" + domid + "_txt" + this.str + "').value = '';";	
				
				//Use temporary form values
				var tmp_node_value = "";
				var tmp_node_onfocus = "";				
				if(node['node_name'] == 'link') {
					tmp_node_value = "http://";
					tmp_node_onfocus = " onfocus=\"if(this.value=='http://'){this.value='';}\"";	
				} else if(domId(domid + '_txt' + this.str)) {
					tmp_node_value = domId(domid + '_txt' + this.str).value;
				}
				
				//Default display status for non-required nodes
				var link_display = "display: block";
				var input_display = "display: none";
				if(node['required'] || tmp_node_value) { link_display = "display: none"; input_display = "display: block"; }
				
				form_input += "<div id=\"" + domid_add + "" + this.str + "\" style=\"" + link_display + "\" onclick=\"" + show + "\"><a>+ <u>Add " + node['node_name'] + "</u></a></div>";
				form_input += "<div id=\"" + domid + "" + this.str + "\" style=\"" + input_display + "\"><textarea id=\"" + domid + "_txt" + this.str + "\" class=\"form wider\" name=\"itc_" + node['node_name'] + "\""
					+ "onkeyup=\"auto_expand(this)\" maxlength=\"" + node['length']  + "\" style=\"vertical-align: bottom\"" + tmp_node_onfocus + " placeholder=\"Write something here\">";			
				form_input += tmp_node_value;
				form_input += "</textarea>";
				
				//Toggle display status for non-required nodes					
				if(!node['required']) {
					form_input += "<span onClick=\"" + hide + "\" class=\"item-tools\">x</span>";
				}
				
				form_input += "</div>";
				form_input += "<hr />";
			}
			
		    var upload = this.addItemButton();
		}

		var inactive = "_inactive";
		var toggleItemClass = "";
		var x;
		for (x in this.class_array) {
			var item_class = this.class_array[x];
			if(item_class['class_id'] == this.active_class) { inactive = ""; }
			toggleItemClass += "<input class=\"item_tools" + inactive + "\" type=\"button\" onclick=\"OmniController" + this.str + ".toggle('" + item_class['class_id'] + "')\" value=\"" + item_class['class_name'] + "\"/>";
			inactive = "_inactive";	
		}
		
		var form_display = "<form" + form_id + functions + ">"
			+ form_input
			+ "<div style='float: right'>" + upload + "</div>"
			+ toggleItemClass
			+ "</form></div>";
			
		return form_display;
	}
}

//----------------------------------//
//------- EDIT ITEM: OMNIBOX --------//
//----------------------------------//

class OmniFeedBox extends OmniBox {

	set_active_feed (feed_id) {
		this.feed_id = feed_id;
	}
	
	addItemButton () {
		return "<input class=\"item-tools\" type=\"button\" name=\"checkInput" + this.str + "\" onClick=\"OmniController" + this.str + ".checkInput()\"  value=\"&#10004 SAVE\"/><br />";
	}	
	
	form_input(class_id) { 
		var hidden_input = "<input type=\"hidden\" name=\"itc_class_id\" value=\"" + class_id + "\"/>";
			hidden_input += "<input type=\"hidden\" name=\"itc_add_item_feed\" value=\"" + this.feed_id + "\"/>";
			return hidden_input;
	}
	
	form_id() { return " id=\"itc_OmniBoxForm" + this.str + "\""; }
	functions_str() { return " action=\"index.php\" method=\"post\""; }
	functions_file() { return " action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\""; }
}

class OmniPlaylistBox extends OmniFeedBox {

	set_active_feed (feed_id) {
		this.feed_id = feed_id;
	}
	
	addItemButton () {
		return "<input class=\"item-tools\" type=\"button\" name=\"checkInput" + this.str + "\" onClick=\"var value = document.getElementById('itc_link_txt" + this.str + "').value; if(value.match(/youtube.com/g)){ OmniController" + this.str + ".checkInput() }\"  value=\"&#10004 SAVE\"/><br />";
	}	
}

class OmniCommentBox extends OmniFeedBox {
	
	set_active_item_id (item_id) {
		this.item_id = item_id;
	}
	
	form_input(class_id) { 
		var hidden_input = "<input type=\"hidden\" name=\"itc_class_id\" value=\"" + class_id + "\"/>";
			hidden_input += "<input type=\"hidden\" name=\"itc_add_item_comment\" value=\"" + this.feed_id + "\"/>";
			hidden_input += "<input type=\"hidden\" name=\"itc_add_item_comment_id\" value=\"" + this.item_id + "\"/>";
			return hidden_input;
	}
}


class OmniFirstCommentBox extends OmniFeedBox {
	
	set_active_item_id (item_id) {
		this.item_id = item_id;
	}
	
	form_input(class_id) { 
		var hidden_input = "<input type=\"hidden\" name=\"itc_class_id\" value=\"" + class_id + "\"/>";
			hidden_input += "<input type=\"hidden\" name=\"itc_add_item_comment_id\" value=\"" + this.item_id + "\"/>";
			return hidden_input;
	}
}

class OmniEditBox extends OmniBox {
	set_active_item (item_array) {
		this.item_array = item_array;
	}
	
	addItemButton () {
		var upload = "<input onClick=\"window.history.back()\"  type=\"button\" class=\"item-tools\" value=\"&#10008; Cancel\"/>";
		    upload += "<input class=\"item-tools\" type=\"button\" name=\"checkInput\" onClick=\"OmniController" + this.str + ".checkInput()\"  value=\"&#10004 SAVE\"/><br />";
		return upload;
	}

	form_input(class_id) { 
	    var item = this.item_array;
		var hidden_input = "<input type=\"hidden\" name=\"itc_class_id\" value=\"" + class_id + "\"/>";
			hidden_input += "<input type=\"hidden\" name=\"itc_edit_item\" value=\"" + item['item_id'] + "\"/>";
		return hidden_input;
	}

	class_form_HTML (class_form) {
	    var item = this.item_array;		
	    var form_input = "<input type=\"hidden\" name=\"itc_class_id\" value=\"" + class_form['class_id'] + "\"/>";
	    form_input += "<input type=\"hidden\" name=\"itc_edit_item\" value=\"" + item['item_id'] + "\"/>";
	    var form_id = " id=\"itc_OmniBoxForm" + this.str + "\"";
	    var functions = " action=\"?id=" + item['item_id'] + "\" method=\"post\"";
			
		for (var i = 0; i < class_form.nodes.length; i++) {
			var node = class_form.nodes[i];
			
			if(node['node_name'] == 'link') {
			    if(item[node['node_name']]) {
				form_input += "<input class=\"item-tools\" name=\"itc_" + node['node_name'] + "\" id=\"itc_" + node['node_name'] + "_txt" + this.str + "\" type=\"hidden\" value=\"" + item[node['node_name']] + "\"/>";
				form_input += item[node['node_name']];
				form_input += "<hr />";
				} else { 
					//NOT USED - Add file while editing
					var functions = " action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\"";

					var types = class_form['types'];
					form_input += "<input type=\"file\" class=\"item-tools\" name=\"itc_" + node['node_name'] + "_txt" + this.str + "\" id=\"itc_" + node['node_name'] + "\" accept=\"";

					//accepted filetypes
					form_input += types.join();
					form_input += "\"/><div><small>Choose " + types.join() + " only.</small></div>";
					form_input += "<hr />";
				}
				
			} else {
				if(!node['required'] && !item[node['node_name']]) {
					var domid = "itc_" + node['node_name'];
					var domid_add = "itc_add_" + node['node_name'] + "_" + class_form['class_id'];

					var show = "this.style.display='none';"
						  + "domId('" + domid + "').style.display = 'block'";
					var hide = "domId('" + domid_add + "').style.display = 'block';"
						  + "domId('" + domid + "').style.display = 'none';"
						  + "domId('" + domid + "_txt" + this.str + "').value = '" + item[node['node_name']] + "';";
					
					form_input += "<div id=\"" + domid_add + "\" onclick=\"" + show + "\"><a>+ <u>Add " + node['node_name'] + "</u></a></div>";
					form_input += "<div id=\"" + domid + "\" style=\"display: none\"><textarea id=\"" + domid + "_txt" + this.str + "\" class=\"form wider\" name=\"itc_" + node['node_name'] + "\" onkeyup=\"auto_expand(this)\" maxlength=\"" + node['length']  + "\" style=\"vertical-align: bottom\">" + item[node['node_name']] + "</textarea> <span onClick=\"" + hide + "\" class=\"item-tools\">x</span></div>";
					form_input += "<hr />";
				} else {
					form_input += "<textarea class=\"form wider\" id=\"itc_" + node['node_name'] + "_txt" + this.str + "\" name=\"itc_" + node['node_name'] + "\" onkeyup=\"auto_expand(this)\" maxlength=\"" + node['length'] + "\">" + item[node['node_name']] + "</textarea>";
					form_input += "<hr />";
				}
			}
			
		    var upload = "<input onClick=\"window.history.back()\"  type=\"button\" class=\"item-tools\" value=\"&#10008; Cancel\"/>";
		    upload += "<input class=\"item-tools\" type=\"button\" name=\"checkInput\" onClick=\"OmniController" + this.str + ".checkInput()\"  value=\"&#10004 SAVE\"/><br />";
		}

		var inactive = "_inactive";
		var toggleItemClass = "";
		var x;
		for (x in this.class_array) {
			var item_class = this.class_array[x];
			if(item_class['class_id'] == this.active_class) { inactive = ""; }	
			if(item_class['class_id'] == item['class_id']) {
				toggleItemClass += "<input class=\"item_tools" + inactive + "\" type=\"button\" onclick=\"OmniController" + this.str + ".toggle('" + item_class['class_id'] + "')\" value=\"" + item_class['class_name'] + "\"/> ";
			}
			inactive = "_inactive";
		}
		
		var form_display = "<form" + form_id + functions + ">"
			+ form_input
			+ "<div style='float: right'>" + upload + "</div>"
			+ toggleItemClass
			+ "</form></div>";
			
		return form_display;
	}	
	form_id() { return " id=\"itc_OmniBoxForm" + this.str + "\""; }
	functions_str() { return " action=\"index.php\" method=\"post\""; }
	functions_file() { return " action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\""; }
}
