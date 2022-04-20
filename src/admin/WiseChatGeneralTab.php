<?php 

/**
 * Wise Chat admin general settings tab class.
 *
 * @author Kainex <contact@kaine.pl>
 */
class WiseChatGeneralTab extends WiseChatAbstractTab {

	public function getFields() {
		return array(
			array('_section', 'General Settings'),
			array(
				'mode', 'Mode', 'selectCallback', 'string',
				'Sets overall chat mode:<br />
				<strong>Classic chat:</strong> displays a classic chat window embedded in a page or a post<br />
                <strong>Facebook-like chat:</strong> displays a chat window and users list attached to the right side of the browser\'s window<br />',
				self::getAllModes()
			),
            array('collect_user_stats', 'Collect User Statistics', 'booleanFieldCallback', 'boolean', 'Collects various statistics of users, including country, city, etc.'),
			array(
				'enable_buddypress', 'Enable BuddyPress', 'booleanFieldCallback', 'boolean',
				'Enables BuddyPress integration features.<br />'.
				'<strong>Notice:</strong> Please remember to enable User Groups support in BuddyPress settings otherwise the integration will not work.'
			),
			array('_section', 'Chat Access Settings'),
			array('access_users', 'Access For Users', 'accessUsersCallback', 'void'),
			array('access_user_add', ' ', 'accessUserAddCallback', 'void'),
			array('access_mode', 'Disable Anonymous Users', 'booleanFieldCallback', 'boolean', 'Only regular WP users are allowed to enter the chat. You may choose user roles below. '),
			array('access_roles', 'Access For Roles', 'checkboxesCallback', 'multivalues', 'Access only for these user roles', self::getRoles()),
			array('force_user_name_selection', 'Force Username Selection', 'booleanFieldCallback', 'boolean', 'Forces anonymous user to provide its name.'),
			array('read_only_for_anonymous', 'Read-only For Anonymous', 'booleanFieldCallback', 'boolean', 'Makes the chat read-only to anonymous users. Only logged in WordPress users are allowed to send messages. You can choose read-only roles below.'),
			array('read_only_for_roles', 'Read-only For Roles', 'checkboxesCallback', 'multivalues', 'Selected roles have read-only access to the chat.', self::getRoles()),
			array('_section', 'Chat Opening Hours and Days', 'Server UTC date and time is taken into account. It is currently: '.date('Y-m-d H:i:s')),
			array('enable_opening_control', 'Enable Opening Control', 'booleanFieldCallback', 'boolean', 'Allows to specify when the chat is available for users.'),
			array('opening_days', 'Opening Days', 'checkboxesCallback', 'multivalues', 'Select chat opening days.', self::getOpeningDaysValues()),
			array('opening_hours', 'Opening Hours', 'openingHoursCallback', 'multivalues', 'Specify chat opening hours (HH:MM format)'),
		);
	}
	
	public function getDefaultValues() {
		return array(
			'mode' => 0,
			'access_mode' => 0,
			'access_roles' => array('administrator'),
			'access_users' => array(),
			'force_user_name_selection' => 0,
            'read_only_for_anonymous' => 0,
            'collect_user_stats' => 1,
			'enable_buddypress' => 1,
			'enable_opening_control' => 0,
			'opening_days' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
			'opening_hours' => array('opening' => '8:00', 'openingMode' => 'AM', 'closing' => '4:00', 'closingMode' => 'PM'),
			'read_only_for_roles' => array()
		);
	}
	
	public function getParentFields() {
		return array(
			'opening_days' => 'enable_opening_control',
			'opening_hours' => 'enable_opening_control',
			'read_only_for_roles' => 'read_only_for_anonymous',
			'access_roles' => 'access_mode',
		);
	}

	public function deleteAccessUserAction() {
		$index = $_GET['index'];
		if (strlen($index) > 0) {
			$index = intval($index);
			$accessUsers = (array) $this->options->getOption('access_users', array());
			if ($index < count($accessUsers)) {
				unset($accessUsers[$index]);
				$this->options->setOption('access_users', array_values($accessUsers));
				$this->options->saveOptions();
				$this->addMessage('User has been removed from the access list');
			}
		}
	}

	public function addAccessUserAction() {
		$newAccessUser = trim($_GET['newAccessUser']);
		if (strlen($newAccessUser) === 0) {
			$this->addErrorMessage('Please specify user login');
		} else {
			$wpUser = $this->usersDAO->getWpUserByLogin($newAccessUser);
			if ($wpUser === null) {
				$this->addErrorMessage('The user login is not correct');
			} else {
				$accessUsers = (array) $this->options->getOption('access_users', array());
				$accessUsers[] = $wpUser->ID;
				$this->options->setOption('access_users', $accessUsers);
				$this->options->saveOptions();

				$this->addMessage("User has been added to the access list");
			}
		}
	}

	public function accessUsersCallback() {
		$url = admin_url("options-general.php?page=".WiseChatSettings::MENU_SLUG);
		$users = (array) $this->options->getOption('access_users', array());

		$html = "<div style='height: 150px; overflow-y: auto; border: 1px solid #aaa; padding: 5px;'>";
		if (count($users) == 0) {
			$html .= '<small>No users were added yet</small>';
		} else {
			$html .= '<table class="wp-list-table widefat fixed striped users wcCondensedTable">';
			$html .= '<tr><th style="width:100px">ID</th><th>Login</th><th>Display Name</th><th></th></tr>';
			foreach ($users as $userKey => $userID) {
				$deleteURL = $url . '&wc_action=deleteAccessUser&index=' . $userKey;
				$deleteLink = "<a href='{$deleteURL}' onclick='return confirm(\"Are you sure?\")'>Delete</a><br />";
				$user = $this->usersDAO->getWpUserByID(intval($userID));
				if ($user !== null) {
					$html .= sprintf("<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td></tr>", $userID, $user->user_login, $user->display_name, $deleteLink);
				} else {
					$html .= sprintf("<tr><td>%d</td><td colspan='2'>Unknown user</td><td>%s</td></tr>", $userID, $deleteLink);
				}
			}
			$html .= '</table>';
		}
		$html .= "</div>";
		$html .= '<p class="description">A list of users who have exclusive access to the chat. <br /><strong>Notice:</strong> Empty list means there is no limit unless any other access options are enabled.</p>';
		print($html);
	}

	public function accessUserAddCallback() {
		$url = admin_url("options-general.php?page=".WiseChatSettings::MENU_SLUG."&wc_action=addAccessUser");

		printf(
			'<input type="text" value="" placeholder="User login" id="newAccessUser" name="newAccessUser" class="wcUserLoginHint" />'.
			'<a class="button-secondary" href="%s" title="Adds user to access list" onclick="%s">Add User</a>',
			wp_nonce_url($url),
			'this.href += \'&newAccessUser=\' + encodeURIComponent(jQuery(\'#newAccessUser\').val());'
		);
	}
	
	public function openingHoursCallback($args) {
		$id = 'opening_hours';
		$hint = $args['hint'];
		
		$defaults = $this->getDefaultValues();
		$defaultValue = array_key_exists($id, $defaults) ? $defaults[$id] : '';
		$values = $this->options->getOption($id, $defaultValue);
		$parentId = $this->getFieldParent($id);
		$disabledAttribute = $parentId != null && !$this->options->isOptionEnabled($parentId, false) ? 'disabled="1"' : '';
		
		$modes = array('AM', 'PM', '24h');
		$openingModesSelect = sprintf(
			'<select name="%s[%s][openingMode]" %s data-parent-field="%s">', 
			WiseChatOptions::OPTIONS_NAME, $id,
			$disabledAttribute, $parentId != null ? $parentId : ''
		);
		$closingModesSelect = sprintf(
			'<select name="%s[%s][closingMode]" %s data-parent-field="%s">', 
			WiseChatOptions::OPTIONS_NAME, $id,
			$disabledAttribute, $parentId != null ? $parentId : ''
		);
		foreach ($modes as $mode) {
			$openingModesSelect .= sprintf(
				'<option value="%s" %s>%s</option>', 
				$mode, array_key_exists('openingMode', $values) && $values['openingMode'] == $mode ? 'selected="1"' : '', $mode
			);
			$closingModesSelect .= sprintf(
				'<option value="%s" %s>%s</option>', 
				$mode, array_key_exists('closingMode', $values) && $values['closingMode'] == $mode ? 'selected="1"' : '', $mode
			);
		}
		$openingModesSelect .= '</select>';
		$closingModesSelect .= '</select>';
		
		print(
			sprintf(
				'From: <input type="text" value="%s" placeholder="HH:MM" id="openingHour" name="%s[%s][opening]" pattern="\d{1,2}:\d{2}"
						%s data-parent-field="%s" style="max-width: 90px;" />'.$openingModesSelect,
				array_key_exists('opening', $values) ? $values['opening'] : '',
				WiseChatOptions::OPTIONS_NAME, $id,
				$disabledAttribute,
				$parentId != null ? $parentId : ''
			).
			sprintf(
				'&nbsp;&nbsp; To: <input type="text" value="%s" placeholder="HH:MM" id="closingHour" name="%s[%s][closing]" pattern="\d{1,2}:\d{2}"
						%s data-parent-field="%s" style="max-width: 90px;" />'.$closingModesSelect,
				array_key_exists('closing', $values) ? $values['closing'] : '',
				WiseChatOptions::OPTIONS_NAME, $id,
				$disabledAttribute,
				$parentId != null ? $parentId : ''
			).
			sprintf('<p class="description">%s</p>', $hint)
		);
	}
	
	public static function getOpeningDaysValues() {
		return array(
			'Monday' => 'Monday', 
			'Tuesday' => 'Tuesday', 
			'Wednesday' => 'Wednesday', 
			'Thursday' => 'Thursday', 
			'Friday' => 'Friday', 
			'Saturday' => 'Saturday',
			'Sunday' => 'Sunday'
		);
	}

	public function getRoles() {
		$editableRoles = array_reverse(get_editable_roles());
		$rolesOptions = array();

		foreach ($editableRoles as $role => $details) {
			$name = translate_user_role($details['name']);
			$rolesOptions[esc_attr($role)] = $name;
		}

		return $rolesOptions;
	}

	public static function getAllModes() {
		return array(
			0 => 'Classic chat',
			1 => 'Facebook-like chat',
		);
	}
}