<?php
/**
* Watchdog:: Analysis
* Created :: 2020-01-01
* Modify  :: 2023-07-06
* Version :: 2
*
* @return Widget
*
* @usage watchdog/analysis
*/

class WatchdogAnalysis extends Page {
	var $module;
	var $keyword;
	var $days;
	var $whatDelete;
	var $items;
	var $right;

	function __construct() {
		parent::__construct([
			'module' => post('module'),
			'keyword' => post('keyword'),
			'days' => intval(\SG\getFirst(post('d'),30)),
			'whatDelete' => post('delete'),
			'items' => \SG\getFirst(post('items'),1000),
			'right' => (Object)[
				'admin' => user_access('administer watchdogs')
			]
		]);
	}

	function build() {
		if ($this->right->admin && $this->whatDelete) return $this->deleteLogItems();
		else if ($this->module || $this->keyword) return $this->showLogItems();

		$summaryDbs = $this->getSummary();

		// mydb::where('TIMESTAMPDIFF(DAY, `date`, NOW()) < :days', ':days', $this->days);
		mydb::where('`date` >= :date', ':date', date('Y-m-d', strtotime('today - '.$this->days.' days')));

		$dbs = mydb::select(
			'SELECT
			`module`
			, `keyword`
			, COUNT(*) `totals`
			FROM %watchdog%
			%WHERE%
			GROUP BY `module`, `keyword`'
		);

		// debugMsg(mydb()->_query);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Watchdog Anlysis'.' '.number_format($summaryDbs->totalLog).' items in '.number_format($summaryDbs->totalDate).' days',
				'navigator' => new Form([
					'class' => 'form-report',
					'action' => url('watchdog/analysis'),
					'method' => 'GET',
					'children' => [
						'd' => ['type' => 'text', 'label' => 'Show Last ', 'class' => '-numeric', 'size' => 6, 'value' => $this->days, 'posttext' => ' days'],
						'go' => ['type' => 'button', 'value' => '<i class="icon -material">search</i><span>GO</span>'],
						// $this->right->admin ? '<a class="sg-action btn -link" href="'.url('watchdog/analysis',array('delete'=>'emptymodule')).'" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?" data-rel="notify"><i class="icon -material">delete</i><span>Clear empty module on watchlog</span></a>&nbsp;' : NULL,
					], // children
				]), // Form
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'class' => 'watchdog-keyword-list',
						'thead' => ['Module', 'Keyword', 'amt' => 'Count', 'icons' => ''],
						'children' => array_map(
							function($rs) {
								return [
									'<a class="sg-action" href="'.url('watchdog/analysis',array('module'=>$rs->module)).'" data-rel="box" data-width="full">'.$rs->module.'</a>',
									'<a class="sg-action" href="'.url('watchdog/analysis',array('module'=>$rs->module,'keyword'=>urlencode($rs->keyword))).'" data-rel="box" data-width="full">'.$rs->keyword.'</a>',
									$rs->totals,
									$this->right->admin?'<a class="sg-action" href="'.url('watchdog/analysis','delete='.$rs->module.':'.$rs->keyword).'" data-title="Delete logs" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?" data-rel="this" data-done="remove:parent tr"><i class="icon -material">delete</i></a>':'',
								];
							},
							$dbs->items
						),
					]), // Table
				], // children
			]), // Widget
		]);
	}

	function getSummary() {
		return mydb::select(
			'SELECT
			COUNT(*) `totalLog`, COUNT(DISTINCT DATE_FORMAT(`date`, "%Y-%m-%d")) `totalDate`
			FROM %watchdog%
			LIMIT 1'
		);
	}

	function showLogItems() {
			if ($this->module) mydb::where('w.`module` = :module',':module',$this->module);
			if ($this->keyword) mydb::where('w.`keyword` = :keyword',':keyword',$this->keyword);
			if (post('u')) mydb::where('w.`uid` = :uid',':uid',post('u'));
			if (post('msg')) mydb::where('w.`message` LIKE :msg',':msg','%'.post('msg').'%');

			$logs = mydb::select(
				'SELECT SQL_CALC_FOUND_ROWS w.*, u.`uid`, u.`username`
				FROM %watchdog% w
					LEFT JOIN %users% u USING(uid)
				%WHERE%
				ORDER BY wid DESC
				LIMIT '.$this->items
			);

			$ret .= '<header class="header -box"><h3>Watchdog List '.$logs->count().($logs->count() == $this->items ? ' of '.$logs->_found_rows : '').' items</h3></header>';

			$ret .= R::View('watchdog.listing',$logs);
			return $ret;
	}

	function deleteLogItems() {
		if ($this->whatDelete == 'emptymodule') {
			mydb::query('DELETE FROM %watchdog% WHERE `module` IS NULL OR `module` = ""');
		} else {
			list($module, $keyword) = explode(':', $this->whatDelete);
			mydb::query('DELETE FROM %watchdog% WHERE '.($module ? '`module` = :module' : '`module` IS NULL').' AND `keyword` = :keyword', ':module', $module, ':keyword', $keyword);
		}
		$ret .= 'Delete watchdog completed.';
		return $ret;
	}
}
?>