<?php 

/**
 * Wise Chat admin permissions settings tab class.
 *
 * @author Kainex <contact@kaine.pl>
 */
class WiseChatPermissionsTab extends WiseChatAbstractTab {

	public function getFields() {
		return array(
			array('_section', 'Private Messaging Permissions', 'Rules of private messaging.', array('hideSubmitButton' => true)),

			array('permissions_pm_new_rule', 'New Rule', 'ruleAddCallback', 'void'),
			array('permissions_pm_rules', 'Rules', 'rulesCallback', 'void'),
		);
	}
	
	public function getDefaultValues() {
		return array(

		);
	}
	
	public function getParentFields() {
		return array(

		);
	}

	public function addPMRuleAction() {
		$source = $_GET['newPmRuleSource'];
		$target = $_GET['newPmRuleTarget'];

		WiseChatContainer::load('model/WiseChatPrivateMessagesRule');

		$rule = new WiseChatPrivateMessagesRule();
		$rule->setSource($source);
		$rule->setTarget($target);
		$this->privateMessagesRulesDAO->save($rule);

		$this->addMessage("The rule has been added");
	}

	public function deletePMRuleAction() {
		$id = intval($_GET['id']);

		if (strlen($_GET['id']) > 0) {
			$this->privateMessagesRulesDAO->delete($id);
			$this->addMessage("The rule has been deleted");
		} else {
			$this->addErrorMessage('No rule ID');
		}
	}

	public function ruleAddCallback() {
		$url = admin_url("options-general.php?page=".WiseChatSettings::MENU_SLUG."&wc_action=addPMRule");

		$roles = $this->getRoles();
		$rolesOptions = array();
		foreach ($roles as $symbol => $name) {
			$rolesOptions[] = "<option value='{$symbol}'>{$name}</option>";
		}
		$rolesSelectSource = "<select name='newPmRuleSource'>".implode('', $rolesOptions)."</select>";
		$rolesSelectTarget = "<select name='newPmRuleTarget'>".implode('', $rolesOptions)."</select>";

		printf(
			'%s is allowed to send private messages to %s'.
			' | '.
			'<a class="button-primary new-pm-rule-submit" href="%s">Add Rule</a>',
			$rolesSelectSource, $rolesSelectTarget, wp_nonce_url($url)
		);
	}

	public function rulesCallback() {
		$rules = $this->privateMessagesRulesDAO->getAll();

		$html = "<table class='wp-list-table widefat emotstable'>";
		if (count($rules) == 0) {
			$html .= '<tr><td>No rules created. There are no restrictions to private messaging.</td></tr>';
		} else {
			$html .= '<thead><tr><th>&nbsp;Rule</th><th>Actions</th></tr></thead>';
		}

		$roles = $this->getRoles();

		foreach ($rules as $key => $rule) {
			$classes = $key % 2 == 0 ? 'alternate' : '';

			$sourceRole = array_key_exists($rule->getSource(), $roles) ? $roles[$rule->getSource()] : 'Deleted Role';
			$targetRole = array_key_exists($rule->getTarget(), $roles) ? $roles[$rule->getTarget()] : 'Deleted Role';

			$deleteURL = admin_url("options-general.php?page=".WiseChatSettings::MENU_SLUG."&wc_action=deletePMRule&id=".urlencode($rule->getId())).'&tab=permissions';
			$deleteLink = sprintf(
				'<a class="button-primary" href="%s" title="Delete the rule" onclick="return confirm(\'Are you sure you want to delete this rule?\')">Delete</a>',
				$deleteURL
			);

			$html .= sprintf(
				'<tr class="%s">
					<td><strong>%s</strong> is allowed to send private messages to <strong>%s</strong></td><td>%s</td>
				</tr>',
				$classes, $sourceRole, $targetRole, $deleteLink
			);
		}
		$html .= "</table>";
		if (count($rules) > 0) {
			$html .= "<p class='description'><strong>Notice:</strong> A first matching rule is applied and no further rules are processed.</p>";
		}

		print($html);
	}

	public function getRoles() {
		$rolesSpecialFirst = array(
			'_any' => 'Any User',
			'_anonymous' => 'Anonymous'
		);
		$rolesSpecialLast = array(
			'_fb' => 'Facebook User',
			'_go' => 'Google User',
			'_tw' => 'Twitter User',
		);
		return array_merge($rolesSpecialFirst, $this->getWPRoles(), $rolesSpecialLast);
	}
	
	public function getWPRoles() {
		$editableRoles = array_reverse(get_editable_roles());
		$rolesOptions = array();

		foreach ($editableRoles as $role => $details) {
			$name = translate_user_role($details['name']);
			$rolesOptions[esc_attr($role)] = $name;
		}
	
		return $rolesOptions;
	}
}