<?php
/**
* Module Method
* Created 2019-08-01
* Modify  2019-08-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function user_signup($self) {
	$ret = '';

	// Step 1 : First page
	$ret .= '<div class="-sg-text-center" style="width:240px; margin:0 auto;"">';
	$ret .= '<a class="btn" href="">Sign up with Google</a><br />';
	$ret .= 'or';
	$ret .= '<div class="form-item"><input class="form-text" placeholder="Email" /></div>';
	$ret .= '<div class="form-item"><input class="form-text" placeholder="Password" /></div>';
	$ret .= '<div class="form-item"><button class="btn">Create account...</button></div>';
	$ret .= 'Already hav an account<br /><a class="btn">Log in</a>';

	// Setp 2 : next page
	$ret .= '<h3>STEP 2</h3><br />Tell us about yourself';
	$ret .= '<div class="form-item"><input type="text" placeholder="Your name" /></div>';
	$ret .= '<div class="form-item"><select class="form-select"><option value="" disabled="">What kind of work do you do? *</option><option value="marketer">Marketing</option><option value="project-manager">Product Management</option><option value="designer">Design</option><option value="developer">Software Development</option><option value="other">Other</option></select></div>';
	$ret .= '<div class="form-item"><button class="btn">Create Account</button></div>';
	$ret .= 'By clicking "Create Account" you agree to our <a>TOS</a> and <a>Privacy Policy</a>.<br />';
	$ret .= 'Already have an account? <a class="btn">Log in</a>';
	$ret .= '</div>';

	$ret .= '<div><form>Sign Up<br />Name<i class="icon -material">person</i><input type="text" placeholder="Name" /><br />Your Email<i class="icon -material">person</i><input type="text" placeholder="Email" /> <Choose Passwordi class="icon -material">person</i><input type="text" placeholder="Password" /><br /><button class="btn">SIGN UP</button></form>';
	$ret .= 'By signing up you agree with our terms and to recieve periodic update and tips';
	return $ret;
}
?>