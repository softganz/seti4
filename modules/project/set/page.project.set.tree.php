<?php
function project_set_tree($self, $projectInfo = NULL) {
	$projectId = $projectInfo->projectId;
	R::View('project.toolbar',$self,'แผนงาน/ชุดโครงการ');

	$dbs = mydb::select(
		'SELECT t.`tpid`, t.`title`, p.`projectset`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE p.`prtype` IN ("ชุดโครงการ","แผนงาน")
		ORDER BY p.`projectset` ASC, CONVERT(`title` USING tis620) ASC'
	);

	$ui=new Ui();
	foreach ($dbs->items as $rs) {
		if ($rs->projectset) continue;
		$ui->add('<a class="sg-action '.($rs->projectset?'':'btn -primary').'" href="'.url('project/set/'.$rs->tpid).'" data-rel="#main">'.$rs->title.'</a>');
		//$sui=new Ui();
		foreach ($dbs->items as $srs) {
			if ($srs->projectset==$rs->projectset) continue;
			if ($srs->projectset!=$rs->tpid) continue;
			$ui->add('<a class="sg-action" href="'.url('project/set/'.$srs->tpid).'" data-rel="#main">'.$srs->title.'</a>');
		}
	}
	$self->theme->sidebar.=$ui->build();

	if ($projectId) {
		//mydb::where('p.`projectset`=:projectset',':projectset',$projectId);
		mydb::where('p.`prtype` IN ("โครงการ")');

		$childProjectSet = mydb::select(
			'SELECT `tpid`
			FROM %project%
			WHERE `projectset` = :projectset;
			-- {reset:false}',
			[':projectset' => $projectId]
		);

		// debugMsg($childProjectSet,'$childProjectSet');

		mydb::where('(p.`projectset`=:prset'.($childProjectSet->_empty?'':' OR p.`projectset` IN (:child)').')',':prset',$projectId,':child','SET:'.$childProjectSet->lists->text);

		$dbs = mydb::select(
			'SELECT
			  p.*
			, t.`title`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			%WHERE%'
		);

		$tables = new Table([
			'thead' => ['no'=>'','ชื่อโครงการ','สถานะ'],
			'children' => array_map(
				function($rs) {
					static $no = 0;
					return [
						++$no,
						$rs->prtype=='โครงการ'?'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>':'<a href="'.url('project/set/'.$rs->tpid).'">'.$rs->title.'</a>',
						$rs->project_status
					];
				},
				$dbs->items
			), // children
		]);

		$ret.=$tables->build();

	}
	$ret.='<style type="text/css">
	.sidebar .ui-action {margin:0; padding:0;}
	.sidebar .ui-item>a {display:block;border-radius:0;}
	.sidebar .ui-item {padding:0;}
	</style>';
	return $ret;
}
?>