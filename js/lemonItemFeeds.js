//----------------------------------//
//------ ITEMCLOUD Lemon 1.1 -------//
//----------------------------------//
//------- ITEM FEED FUNCTIONS ------//
//----------------------------------//

var itemFeedBrowser = [];

var feedBrowse = class {
	constructor(feeds_array, index, user_id, parent_div) {
		this.feeds = feeds_array;
		this.user_id = user_id;
		this.index = index;
		this.start = 0;
		this.max = 6;
		this.parent_div = parent_div;
		this.class_text = "item-tools_grey item_feed_menu";
	}
	
	update (start) {
		this.start = start;
		domId(this.parent_div).innerHTML = this.feedsOutput();
	}
	
	set_active_item_id (item_id) {
		this.item_id = item_id;
	}
	
	feedsOutput() {
		if(!this.feeds) {
			return "";
		}
		
		var start = this.start;		
		var new_start = this.start - this.max;
		
		var feedMenu = "";
		if(new_start >= 0) {
			feedMenu += "<div onclick=\"itemFeedBrowser['" + this.index + "'].update(" + new_start + ")\" class=\"" + this.class_text + "\">"
					+ "<div class=\"item-tools_txt\">" + "&#8943;" + "</div>"
					+ "</div>";
		}
		
		if(this.feeds.length <= (this.start + this.max)) { var total = this.feeds.length; }
		else { var total = (this.start + this.max); }
		for(var i = start; i < total; i++) {	
			var feed = this.feeds[i];
			
			var feed_src = (feed['feed_img']) ? feed['feed_img'] : 'default.png';
			var feed_img = "<a href=\"?feed_id=" + feed['feed_id'] + "&name=" + feed['name'] + "\">" 
					+ "<img class=\"feed-image\" src='files/feeds/" + feed_src + "'/>" 
					+ "</a>";

			var feed_name = "<div style=\"display: inline-block;\">";					
			if((feed['owner_id'] == this.user_id) && this.item_id) {			
				//Link (Remove button)
				var remove_button = "<div style='display: inline-block'><form id='removeForm" + i + this.index + "' action='?id=" + this.item_id + "' method='post'>"
				+ "<input type='hidden' name='item_id' value='" + this.item_id + "'/>"
				+ "<input type='hidden' name='feed_id' value='" + feed['feed_id'] + "'/>"
				+ "<input type='hidden' name='feed' value='remove'/>"		
				+ "<div class='inline-remove'>";

				remove_button += " <a onclick=\"domId('removeForm" + i + this.index + "').submit()\" style=\"font-size: 10px; padding: 4px; color: #333;\">X</a>";	
				remove_button += "</div>";
				remove_button += "</form></div>";
				
				feed_name += remove_button;
			}
			feed_name += "<div class=\"inline-name\">" + feed['name'] + "</div>";
			feed_name += "</div>";

			var feed_window_launch = "window.location='./?feed_id=" + feed['feed_id'] + "&name=" + feed['name'] + "'; ";
						
			var feed_wrapper = "<div onclick=\"" + feed_window_launch + "\"  class=\"" + this.class_text + "\">";
			feed_wrapper += feed_img;			
			feed_wrapper += feed_name;
			feed_wrapper += "</div>";
			
			feedMenu += feed_wrapper;
		}
				
		if(this.start + this.max < this.feeds.length) { 
			var end_link = "";
			feedMenu += "<div onclick=\"itemFeedBrowser['" + this.index + "'].update(" + (this.start + this.max) + ")\" '\"  class=\"" + this.class_text + "\">"
					+ "<div class=\"item-tools_txt\">" + "&#8943;" + "</div>"
					+ "</div>";
		}
		
		return feedMenu;
	}
}
