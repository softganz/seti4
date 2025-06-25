<?php
/**
* Widget  :: Node Share
* Created :: 2021-12-17
* Modify 	:: 2023-02-02
* Version :: 2
*
* @param Array $args
* @return Widget
*
* @usage new NodeShareWidget([])
*/

class NodeShareWidget extends Widget {
	var $copyLink;
	var $shareMember;
	var $members;
	var $membershipValue = 'OWNER';
	var $membershipType = [
		'ADMIN' => 'ADMIN',
		'MANAGER' => 'MANAGER',
		'TRAINER' => 'TRAINER',
		'OWNER' => 'OWNER',
		'FOLLOWER' => 'FOLLOWER',
		'COMMENTATOR' => 'COMMENTATOR',
		'VIEWER' => 'VIEWER',
		'REGULAR MEMBER' => 'REGULAR MEMBER'
	];

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return new Widget([
			'children' => [

				// Share by copy link
				$this->copyLink ? new Card([
					'class' => 'widget-form copy-link',
					'style' => 'margin: 8px;',
					'children' => [
						new ListTile([
							'leading' => '<i class="icon -material">link</i>',
							'title' => 'Get link'
						]), // ListTile
						new Row([
							'mainAxisAlignment' => 'spacearound',
							'class' => '-sg-paddingnorm -sg-padding-8',
							'children' => [
								'<input type="text" id="copy-link" class="form-text -fill" value="'.$this->copyLink.'" />',
								'<a class="btn -link" onClick=\'navigator.clipboard.writeText("'.$this->copyLink.'"); notify("สำเนาลิงก์เรียบร้อย",1000)\'><i class="icon -material -sg-16">content_copy</i><span>Copy link</span></a>',
							], // children
						]), // Row
						'<style>.copy-link>.widget-row>.-item:first-child {flex: 1}</style>',
					], // children
				]) : NULL, // Card

				// Add member form and member list
				$this->shareMember ? new Card([
					'class' => 'widget-share-member-list',
					'style' => 'margin: 8px;',
					'children' => [
						new ListTile([
							'leading' => '<i class="icon -material">person_add_alt</i>',
							'title' => 'Share with people',
						]), // ListTile

						new Form([
							'action' => $this->shareMember['action'],
							'class' => 'sg-form -sg-flex',
							'rel' => 'notify',
							'done' => $this->shareMember['done'],
							'attribute' => ['data-box-resize' => true],
							'children' => [
								'uid' => ['type'=>'hidden','name'=>'uid', 'id'=>'uid'],
								'name' => [
									'type' => 'text',
									'class' => 'sg-autocomplete -fill',
									'require' => true,
									'placeholder' => 'ระบุ ชื่อจริง หรือ อีเมล์ ของสมาชิกที่ต้องการแบ่งปันการใช้งาน',
									'attr' => [
										'data-query' => $this->shareMember['query'],
										'data-altfld' => 'uid',
									],
									'posttext' => '<div class="input-append"><span><select class="form-select" name="membership" style="max-width: 100px;">'.(function() {
										$options = '';
										foreach ($this->membershipType as $optionKey => $optionValue) {
											$options .= '<option value="'.$optionKey.'"'.($optionKey == $this->membershipValue ? ' selected="selected"' : '').'>'.$optionValue.'</option>';
										}
										return $options;
									})().'</select></span>'
										. '<span><button class="btn -primary"><i class="icon -material">add_circle</i></button></span></div>',
									'container' => '{style: "flex: 1", class: "-group"}',
								],
							], // children
						]), // Form

						// Share member list
						$this->members,
					], // children
				]) : NULL, // Card

			], // children
		]);
	}
}
?>