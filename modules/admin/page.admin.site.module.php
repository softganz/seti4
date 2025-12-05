<?php
/**
 * Admin   :: Site Modules Page
 * Created :: 2016-11-08
 * Modify  :: 2025-12-05
 * Version :: 3
 *
 * @return Widget
 *
 * @usage admin/site/module
 */

class AdminSiteModule extends Page {
	#[\Override]	
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Site Modules',
				'leading' => new Icon('admin_panel_settings'),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Container([
						'class' => 'help',
						'child' => 'Add / Remove / Configuration site modules',
					]), // Container

					$this->list(),

					new Form([
						'method' => 'post',
						'class' => 'sg-form',
						'action' => Url::link('api/admin/module.add'),
						'rel' => '#result',
						'done' => 'load->replace:#list:'.url('admin/site/module..list').' | moveto: 0,0',
						'children' => [
							'module' => [
								'type' => 'text',
								'size' => '20',
								'placeholder' => 'Enter module name',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">add</i><span>Add new module</span>'
							]
						], // children
					]), // Form
					new Container([
						'id' => 'result',
					]), // Container
				], // children
			]), // Widget
		]);
	}

	function list() {
		return new Table([
			'id' => 'list',
			'thead' => ['Modules','Permissions','Operations'],
			'colgroup' => [['width' => '20%'], ['width' => '80%'], ['width'=>'1%']],
			'children' => array_map(
				function($module, $perm) {
					return [
						'<b>'.$module.'</b>',
						$perm,
						new Nav([
							'children' => [
								new Button([
									'type' => 'link',
									'href' => Url::link($module.'/admin'),
									'title' => 'Module configuration',
									'icon' => new Icon('settings'),
								]),
								$module != 'system' ? new Button([
									'type' => 'link',
									'class' => 'sg-action',
									'href' => Url::link('admin/site/module/remove/'.$module),
									'rel' => 'none',
									'done' => 'remove:parent tr',
									'data-title' => 'Remove module',
									'data-confirm' => 'Remove module <b>'.$module.'</b> Please confirm?',
									'icon' => new Icon('cancel')
								]) : NULL,

							]
						]),
					];
				},
				array_keys((Array) cfg('perm')), (Array) cfg('perm')
			),
		]); // Table
	}
}
?>