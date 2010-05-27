<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

class pr implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		if($action == 'view') {
			return $this->actionView();
		}
	}

	private function getNewUserThirdPartyRegistrationForm() {
		global $sourceFolder, $moduleFolder, $urlRequestRoot,$cmsFolder;

		$registrationForm = <<<REGISTRATIONFORM
			<script language="javascript" type="text/javascript" src="$urlRequestRoot/$cmsFolder/$moduleFolder/pr/pr.js">
			</script>
			<form name="pruserregistrationform" method="POST" action="./+view" onsubmit="return validatePrRegistrationForm(this)">
				<fieldset style="padding: 8px; margin: 8px">
					<legend>User Information</legend>

					<table>
						<tr>
							<td>Email ID:</label></td>
							<td><input type="text" value="" id="txtUserEmail" name="txtUserEmail" /></td>
						</tr>
						<tr>
							<td>User Full Name:</label></td>
							<td><input type="text" value="" id="txtUserFullName" name="txtUserFullName" /></td>
						</tr>
						<tr>
							<td>Contact Number:</label></td>
							<td><input type="text" value="" id="txtUserPhone" name="txtUserPhone" /></td>
						</tr>
						<tr>
							<td>College/Institution Name:</td>
							<td><input type="text" id="txtUserInstitution" name="txtUserInstitution" /></td>
						</tr>
						<tr>
							<td>Password:</td>
							<td><input type="password" class="" id="txtUserPassword" name="txtUserPassword" /></td>
						</tr>
						<tr>
							<td>Confirm Password:</td>
							<td><input type="password" class="" id="txtUserConfirmPassword" name="txtUserConfirmPassword" /></td>
						</tr>
					</table>
				</fieldset>

				<input type="submit" value="Add User" id="btnAddUser" name="btnAddUser" />
			</form>

REGISTRATIONFORM;

		return $registrationForm;
	}

	private function submitNewUserThirdPartyRegistrationForm() {
		if(
			isset($_POST['txtUserEmail']) && isset($_POST['txtUserPhone']) && 
			isset($_POST['txtUserInstitution']) && isset($_POST['txtUserPassword']) && isset($_POST['txtUserConfirmPassword'])
		) {
			if(getUserIdFromEmail($_POST['txtUserEmail'])) {
				displayerror('The given E-mail Id is already registered on the website. Please use the respective forms\' Edit Registrants view to register the user to events.');
				return;
			}

			if ($_POST['txtUserEmail'] == '' || $_POST['txtUserPassword'] == '') {
				displayerror("Blank e-mail/password NOT allowed");
				return;
			}
			elseif (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['txtUserEmail'])) {
				displayerror("Invalid Email Id");
				return;
			}
			elseif ($_POST['txtUserPassword'] != $_POST['txtUserConfirmPassword']) {
				displayerror("Passwords are not same");
				return;
			}

			$userIdQuery = 'SELECT MAX(`user_id`) FROM `'.MYSQL_DATABASE_PREFIX.'users`';
			$userIdResult = mysql_query($userIdQuery);
			$userIdRow = mysql_fetch_row($userIdResult);

			$newUserId = 1;
			if(!is_null($userIdRow[0]))
				$newUserId = $userIdRow[0] + 1;
			$userEmail = trim($_POST['txtUserEmail']);
			$userPassword = $_POST['txtUserPassword'];
			$userContactNumber = $_POST['txtUserPhone'];
			$userInstitute = $_POST['txtUserInstitution'];

			$insertQuery = 'INSERT INTO `'.MYSQL_DATABASE_PREFIX.'users`(`user_id`, `user_name`, `user_email`, `user_fullname`, `user_password`, `user_regdate`, `user_lastlogin`, `user_activated`) ' .
					"VALUES($newUserId, '{$_POST['txtUserFullName']}', '$userEmail', '{$_POST['txtUserFullName']}', MD5('$userPassword'), NOW(), NOW(), 1)";
			$insertResult = mysql_query($insertQuery);

			if(!$insertResult) {
				displayerror('Error. Could not add user to database.');
				return;
			}

			displayinfo("User $userEmail has been registered to the pragyan website.");

			$contactElementId = 3;
			$instituteElementId = 4;
			$contactInsertQuery = 
					"INSERT INTO `form_elementdata` (`user_id`, `page_modulecomponentid`, `form_elementid`, `form_elementdata`) ".
					"VALUES " .
					"($newUserId, 0, $contactElementId, '$userContactNumber'), " .
					"($newUserId, 0, $instituteElementId, '$userInstitute')";
			$contactInsertResult = mysql_query($contactInsertQuery);
			if(!$contactInsertResult) {
				displayerror('Could not save the contact number of the user.');
			}
		}
		else {
			displayerror('Invalid form submit data.');
		}
	}

	public function actionView() {
		if(isset($_POST['btnAddUser'])) {
			$this->submitNewUserThirdPartyRegistrationForm();
		}

		return $this->getNewUserThirdPartyRegistrationForm();
	}


	public function createModule(&$moduleComponentId) {
		$moduleComponentId = 1;
	}

	public function deleteModule($moduleComponentId){
	}

	public function copyModule($moduleComponentId){
	}
}
