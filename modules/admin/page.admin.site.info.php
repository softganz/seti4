<?php
/**
* Admin   :: Site Information
* Created :: 2007-04-22
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/site/info
*/

$debug = true;

class AdminSiteInfo extends Page {
	function build() {
		$config = (Object) post('config');
		if ($config->title) return $this->_save($config);

		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Site information'
			]), // AdminAppBarWidget
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' => 'config',
						'action' => url(q()),
						'id' => 'site-info-form',
						'class' => 'sg-form',
						'rel' => 'notify',
						'children' => [
							'title' => [
								'type' => 'text',
								'label' => 'Site name: ',
								'class' => '-fill',
								'require' => true,
								'maxlength' => 100,
								'value' => cfg('web.title'),
								'description' => 'The name of this website.',
							],
							'email' => [
								'type' => 'text',
								'label' => 'E-mail address: ',
								'class' => '-fill',
								'require' => true,
								'maxlength' => 100,
								'value' => cfg('web.email'),
								'description' => 'The From address in automated e-mails sent during registration and new password requests, and other notifications. (Use an address ending in your site\'s domain to help prevent this e-mail being flagged as spam.)',
							],
							'slogan' => [
								'type' => 'text',
								'label' => 'Slogan: ',
								'class' => '-fill',
								'maxlength' => 200,
								'value' => cfg('web.slogan'),
								'description' => 'Your site\'s motto, tag line, or catchphrase (often displayed alongside the title of the site).',
							],
							'mission' => [
								'type' => 'textarea',
								'label' => 'Mission: ',
								'class' => '-fill',
								'rows' => 2,
								'value' => cfg('web.mission'),
								'description' => 'Your site\'s mission or focus statement (often prominently displayed on the front page).',
							],
							'anonymous' => [
								'type' => 'text',
								'label' => 'Anonymous user: ',
								'class' => '-fill',
								'require' => true,
								'maxlength' => 100,
								'value' => cfg('web.anonymous'),
								'description' => 'The name used to indicate anonymous users.',
							],
							'homepage' => [
								'type' => 'text',
								'label' => 'Default front page: ',
								'require' => true,
								'maxlength' => 100,
								'size' => 20,
								'value' => cfg('web.homepage'),
								'pretext' => cfg('domain').'/',
								'description' => 'The home page displays content from this relative URL. If unsure, specify "paper".',
							],
							'navigator' => [
								'type' => 'textarea',
								'label' => 'Top navigator section: ',
								'class' => '-fill',
								'rows' => 24,
								'value' => cfg('navigator'),
								'description' => 'This text will be displayed at the top section of each page. Useful for top navigator.',
							],
							'submit1' => [
								'type' => 'button',
								'items' => [
									'save' => [
										'type' => 'submit',
										'class' => '-save-continue',
										'value' => '<i class="icon -material">done</i><span>Save & Continue</span>'
									],
									'saveexit' => [
										'type' => 'submit',
										'class' => '-save-exit -primary',
										'value' => '<i class="icon -material -white">done_all</i><span>Save & Done</span>'
									],
								],
								'container' => '{class: "-sg-text-right"}',
							],
							'secondary' => [
								'type' => 'textarea',
								'label' => 'Secondary section: ',
								'class' => '-fill',
								'rows' => 8,
								'value' => cfg('web.secondary'),
								'description' => 'This text will be displayed at the secondary section of each page. Useful for side menu.',
							],
							'footer' => [
								'type' => 'textarea',
								'label' => 'Footer message: ',
								'class' => '-fill',
								'rows' => 4,
								'value' => cfg('web.footer'),
								'description' => 'This text will be displayed at the bottom of each page. Useful for adding a copyright notice to your pages.',
							],
							'submit' => [
								'type' => 'button',
								'items' => [
									// 'reset' => [
									// 	'type' => 'reset',
									// 	'value' => '<i class="icon -material -gray">restore</i><span>{tr:RESET}</span>'
									// ],
									// 'cancel' => [
									// 	'type' => 'cancel',
									// 	'class' => '-cancel',
									// 	'value' => '<i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span>'
									// ],
									'save' => [
										'type' => 'submit',
										'class' => '-save-continue',
										'value' => '<i class="icon -material">done</i><span>Save & Continue</span>'
									],
									'saveexit' => [
										'type' => 'submit',
										'class' => '-save-exit -primary',
										'value' => '<i class="icon -material -white">done_all</i><span>Save & Done</span>'
									],
								],
								'container' => '{class: "-sg-text-right"}',
							],
							$this->_script(),
						], // children
					]), // Form
				],
			]),
		]);
	}

	function _save($config) {
		cfg_db('web.title', $config->title);
		cfg_db('web.email', $config->email);
		cfg_db('web.slogan', $config->slogan);
		cfg_db('web.mission', $config->mission);
		cfg_db('web.footer', $config->footer);
		cfg_db('web.anonymous', $config->anonymous);
		cfg_db('web.homepage', $config->homepage);
		cfg_db('navigator', $config->navigator);
		if (trim($config->secondary) == '' || trim($config->secondary == 1)) {
			cfg_db_delete('web.secondary');
			cfg('web.secondary',true);
		} else {
			cfg_db('web.secondary', $config->secondary);
			cfg('web.secondary', $config->secondary);
		}
		return 'Website information has been save.';
	}

	function _script() {
		return '<script type="text/javascript">
		$(".btn.-save-exit").click(function() {
			console.log("EXIT")
			window.location = url+"admin"
		})
		</script>';
	}
}
?>