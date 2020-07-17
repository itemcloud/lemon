
var itemLabelBrowser = [];

var labelBrowse = class {
	constructor(labels_array, index, user_id, parent_div) {
		this.labels = labels_array;
		this.user_id = user_id;
		this.index = index;
		this.start = 0;
		this.max = 6;
		this.parent_div = parent_div;
	}
	
	update (start) {
		this.start = start;
		domId(this.parent_div).innerHTML = this.labelsOutput();
	}
	
	set_active_item_id (item_id) {
		this.item_id = item_id;
	}
	
	labelsOutput() {
		if(!this.labels) {
			return "";
		}
		
		var start = this.start;		
		var new_start = this.start - this.max;
		
		var labelMenu = "";
		if(new_start >= 0) {
			labelMenu += "<div onclick=\"itemLabelBrowser['" + this.index + "'].update(" + new_start + ")\" class=\"item-tools_grey item_label_menu\">"
					+ "<div class=\"item-tools_txt\">" + "&#8943;" + "</div>"
					+ "</div>";
		}
		
		if(this.labels.length <= (this.start + this.max)) { var total = this.labels.length; }
		else { var total = (this.start + this.max); }
		for(var i = start; i < total; i++) {	
			var label = this.labels[i];
			
			var label_img = "<a href=\"?label_id=" + label['label_id'] + "&name=" + label['name'] + "\">" 
					+ "<img class=\"label-image\" src='files/labels/" + label['label_img'] + "'/>" 
					+ "</a>";

			var label_name = "<div style=\"display: inline-block;\">";					
			if((label['owner_id'] == this.user_id) && this.item_id) {			
				//Link (Remove button)
				var remove_button = "<div style='display: inline-block'><form id='removeForm" + i + this.index + "' action='?id=" + this.item_id + "' method='post'>"
				+ "<input type='hidden' name='item_id' value='" + this.item_id + "'/>"
				+ "<input type='hidden' name='label_id' value='" + label['label_id'] + "'/>"
				+ "<input type='hidden' name='label' value='remove'/>"		
				+ "<div class='inline-remove'>";

				remove_button += " <a onclick=\"domId('removeForm" + i + this.index + "').submit()\" style=\"font-size: 10px; padding: 4px; color: #333;\">X</a>";	
				remove_button += "</div>";
				remove_button += "</form></div>";
				
				label_name += remove_button;
			}
			label_name += "<div class=\"inline-name\">" + label['name'] + "</div>";
			label_name += "</div>";

			var label_window_launch = "window.location='./?label_id=" + label['label_id'] + "&name=" + label['name'] + "'; ";
						
			var label_wrapper = "<div onclick=\"" + label_window_launch + "\" class=\"item-tools_grey item_label_menu\" >";
			label_wrapper += label_img;			
			label_wrapper += label_name;
			label_wrapper += "</div>";
			
			labelMenu += label_wrapper;
		}
				
		if(this.start + this.max < this.labels.length) { 
			var end_link = "";
			labelMenu += "<div onclick=\"itemLabelBrowser['" + this.index + "'].update(" + (this.start + this.max) + ")\" '\"  class=\"item-tools_grey item_label_menu\">"
					+ "<div class=\"item-tools_txt\">" + "&#8943;" + "</div>"
					+ "</div>";
		}
		
		return labelMenu;
	}
}