//----------------------------------//
//------ ITEMCLOUD Lemon 1.1 -------//
//----------------------------------//
//------ JS WELCOME FUNCTIONS ------//
//----------------------------------//

//Client::Login
var sendLoginForm = function (go) {
	var email = document.getElementById('LOGIN_email').value;
	var pass  = document.getElementById('LOGIN_pass').value;

	if(!isValidEmail(email) || !isValidPassword(pass)){
		showAlertBox('#@$%&?! Please provide a valid email address.');
		return false;
	}
	domId('sendForm').submit();
}

//Config::Logout
function logout () {
	domId('logoutForm').submit();
}

//Config::Register
function sendForm (div) {
	var email = document.getElementById('REG_email').value;
	var pass  = document.getElementById('REG_pass').value;
	var cpass = document.getElementById('REG_cpass').value;
	var agree = document.getElementById('REG_agree').checked;

	//if(hasWhiteSpace(username) || hasSpecialChars(username)){
		//showAlertBox('#@$%&?! Username can not contain spaces or special characters.');
		//return false;
	//}
	
	if(!isValidEmail(email)) {
		showAlertBox('Enter a valid email to continue: me@email.com');
		return false;
	}
	
	if(!isValidPassword(pass)) {
		showAlertBox('Passcode must contain 6-20 characters.');
		return false;
	}
	
	if(pass != cpass) {
		showAlertBox('Your passcode confirmation does not match.');
		return false;
	}
	
	if(cpass != pass || !email || !pass || !cpass) {
		showAlertBox('You must enter a username, valid email and create a new passcode.');
		return false;
	}
	
	if(!agree) {
		showAlertBox('You must agree to the terms of service.');
		return false;	
	}

	domId('joinForm').submit();
}

//DISPLAY::Register
function joinForm (div) {
    var email = "";
    if(domId('joinEmail')) {
	email = domId('joinEmail').value;
    }
	
    var login_form = "<div style='float: left; width: 540px;'>";
    login_form += "<div style='text-align: left; color: #999; margin: 70px 70px'>";
	
    login_form += "<div style='font-size: 32px; margin-bottom: 20px; width: 100%'>Sign In</div>";
    login_form += "<form id=\"sendForm\" action=\"./index.php?connect=1\" method=\"post\"><div>Email</div><div><input id='LOGIN_email' name='e' class='form' value=''/></div>";
    login_form += "<div>Passcode</div><div><input id='LOGIN_pass' name='p' type='password' class='form' value=''/></div>";
	login_form += "<div><a>Forgot passcode?</a></div>";
	login_form += "<div><input name='REG_signin' type='hidden' value='1'/></div>";	
	login_form += "<div><input class='form_button' type=\"button\" onClick=\"sendLoginForm('./')\" value=\"CONNECT\"/></div></form>";
	login_form += "<div style='width: 80px; text-align: center; color: #DCDCDC; font-size: 10px; margin: 0 auto; padding-top: 20px'></div><div class='arrow-down' style='margin: 0 auto'></div>";

							
	login_form += "</div>";
	login_form += "</div>";
	
	var join_form = "<div style='float: left; width: 540px;'>";
	join_form += "<div style='text-align: left; color: #999; margin: 70px 70px;'>";

	join_form += "<div style='font-size: 32px; margin-bottom: 20px; width: 100%'>Create an Account</div><small>Get connected! Add notes, links, files, photos, audio to your profile and keep track of your favorite things. Thank you.</small><br /><br />";
	join_form += "<form id=\"joinForm\" action=\"./index.php?connect=1\" method=\"post\"><div>Email</div><div><input id='REG_email' name='e' class='form' value='" + email + "'/></div>";
	join_form += "<div>Passcode</div><div><input id='REG_pass' name='p' type='password' class='form' value=''/></div>";
	join_form += "<div>Confirm Passcode</div><div><input id='REG_cpass' type='password' class='form' value=''/></div>";
	join_form += "<div><input name='REG_new' type='hidden' value='1'/></div>";

	join_form += "<div style='font-size: 12px'><input id=\"REG_agree\" type=\"checkbox\" style=\"vertical-align: middle\"/>I agree to the <a onClick=\"alert('SERVICE AGREEMENT. Your account is provided 100% free of charge. "
	+ " By uploading videos, photos, music and posting in community forums the account owner is expected to have ownership or consent to distribute the content. Any copyright violations are solely"
	+ " the responsibility of the account owner. We reserve the right to terminate any account at any time for violations of this agreement and any other behaviour that is deemed inappropriate."
	+ " We are not liable for any damages or lost revenue caused by temporary interruptions in service or uploading errors.')\">Service Agreement</a> and <a onclick=\"alert('We are a tech"
	+ " company that respects your privacy and will never sell your data to third party advertisers. This site uses cookies to provide better services. You may request your account to be closed"
	+ " at any time. All files will be removed from both public and private severs. Your data belongs to you.')\">Privacy Policy</a>.</div>";

	join_form += "<div><input class='form_button' type=\"button\" onClick=\"sendForm('" + div + "')\" value=\"CREATE ACCOUNT\"/></div></form>";
	
	join_form += "</div>";
	join_form += "</div>";
	
	domId(div).innerHTML = login_form + join_form + "<div style='clear: both'></div>";
}
