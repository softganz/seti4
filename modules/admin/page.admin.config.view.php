<?php
/**
 * Admin    :: Page
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 20xx-xx-xx
 * Modified :: 2026-07-24
 * Version  :: 2
 *
 * @return Widget
 *
 * @uses admin/config/view
 */

class AdminConfigView extends Page {
	function __construct() {
		parent::__construct();
	}

	/**
	 * Build page
	 *
	 * return object
	 */
	#[\Override]
	function build(): object {
		if ($key) {
			$cfg = cfg($key);
		} else {
			$cfg = cfg();
		}
		ksort($cfg);
		
		$not_show = ['db', 'encrypt_key', 'counter', 'online', 'firebase'];

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'View configuration',
			]), // AppBar
			'body' => new Table([
				'caption' => 'Configuration value' . ($para->view ? ' of <em>' . $para->view . '</em>' : ''),
				'header' => ['variable', 'value'],
				'children' => array_map(
					function($key, $value) use($not_show) {
						if (in_array($key, $not_show)) return null;

						if (is_array($value) || is_object($value)) {
							$valueShow = print_o($value);
						} else if (is_bool($value)) {
							$valueShow = $value ? 'True' : 'False';
						} else {
							$valueShow = str_replace('&nbsp;', ' ', $value);
						}

						return [
							'<a class="sg-action" href="' . Url::link('admin/config/edit', ['name' => $key]) . '" data-rel="box" data-width="480">' . $key . '</a> <font color=gray>[' . gettype($value) . ']</font>',
							$valueShow,
							'config' => ['attr' => 'valign="baseline"']
						];
					},
					array_keys((array) $cfg), (array) $cfg
				)
			]),
		]);
	}
}
?>