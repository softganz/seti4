<?php
/**
* Module Method
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_chat($self) {
	$ret = '';
	$ret .= '<div style="flex:1 0 100%>"
<form>
    <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
      <thead>
        <tr>
          <th class="mdl-data-table__cell--non-numeric">Segments</th>
          <th><div class="material-icons mdl-badge mdl-badge--overlap" data-badge="♥">account_box</div></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="mdl-data-table__cell--non-numeric">Email</td>
          <td>
            <div class="mdl-textfield mdl-js-textfield">
              <input class="mdl-textfield__input" type="email" id="email">
              <label class="mdl-textfield__label" for="email">Email...</label>
            </div>
          </td>
        </tr>
        <tr>
          <td class="mdl-data-table__cell--non-numeric">Password</td>
          <td>
            <div class="mdl-textfield mdl-js-textfield">
              <input class="mdl-textfield__input" type="password" id="password">
              <label class="mdl-textfield__label" for="password">...</label>
              <span class="mdl-textfield__error"></span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="mdl-data-table__cell--non-numeric">
            <button id="sign-in" class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent">
              Submit
            </button>
          </td>
          <td>
            
          </td>
        </tr>
        <tr>
          <td class="mdl-data-table__cell--non-numeric">
              Console:
          </td>
          <td>
            <div class="panel panel-danger" id ="errors" style="display: none;">       
            </div>
            <div class="panel panel-primary" id ="success" style="display: none;">
            </div>
            <div class="col-xs-2"></div>
            <div class="row"></div>
          </td>
        </tr>
      </tbody>
    </table>
    </form>';

  $ret .= '</div>';
	$ret .= '<script>
		var provider = new firebase.auth.GoogleAuthProvider();

	
document.getElementById("sign-in").onclick = function(e) {
    e.preventDefault();
    handleSignUp();
  }
 
  function handleSignUp() {
    var errors = document.getElementById("errors");
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    var warnings = "";
 
    if (email.length < 4) {
      warnings += "You need a valid email chief <br/>";
    }
    if (password.length < 4) {
      warnings += "You need a valid password boss <br/>";
    }
 
    if (warnings != "") {
      console.log(warnings);
      errors.style.display = "block";
      errors.innerHTML = warnings;
    } else {
      errors.style.display = "none";
      firebase.auth().createUserWithEmailAndPassword(email, password).catch(function(error) {
        errors.style.display = (errors.style.display === "none" || errors.style.display === "") ? "block" : "none";
        console.log(error);
        errors.innerHTML = error + "<br/>";
      });
    }
  }
	</script>';
	return $ret;
}
?>