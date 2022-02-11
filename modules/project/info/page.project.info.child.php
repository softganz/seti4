<?php
/**
* Project :: Follow Child Project/Proposal Information
* Created 2021-10-26
* Modify  2021-10-26
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.child
*/

$debug = true;

class ProjectInfoChild extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		global $titleNo;

		$projectInfo = $this->projectInfo;
		$isEdit = $projectInfo->info->isEdit;

		if (!$projectInfo->info->ischild) return NULL;

		// Show Project Development

		$stmt='SELECT t.`tpid`, t.`title`, d.`budget`, d.`status`
			FROM %project_dev% d
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE t.`parent` = :tpid';

		$dbs=mydb::select($stmt,':tpid',$projectId);

		$ret .= '<section id="project-detail-childdev" class="project-detail-childdev">';
		$ret .= '<h3>พัฒนาโครงการ</h3>'._NL;
		$ret .= '<div id="develop-child" class="develop-child box">'._NL;
		if ($dbs->_num_rows) {
			$no=0;
			$tables = new Table();
			$tables->thead=array('no' => '', 'ชื่อโครงการ', 'budget -money' => 'งบประมาณ', 'approve -date' => 'วันที่อนุมัติ', 'status -center' => 'สถานภาพ');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('project/develop/'.$rs->tpid).'">'.$rs->title.'</a>',
					number_format($rs->budget,2),
					$rs->date_approve ? sg_date($rs->date_approve, 'ว ดด ปปปป') : '',
					$rs->status,
				);
			}
			$ret.=$tables->build();
		}
		if ($isEdit) {
			$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary" href="'.url('project/develop/create/'.$projectId).'"><i class="icon -addbig -white"></i><span>เพิ่มพัฒนาโครงการ</span></a></nav>'._NL;
		}
		$ret.='</div><!-- develop-child -->'._NL._NL;
		$ret .= '</section>';



		// Show Project Follow
		$stmt = 'SELECT t.`tpid`, t.`title`, p.`prtype`, p.`project_status`, p.`date_approve`, p.`budget`
						FROM %topic% t
							LEFT JOIN %project% p USING(`tpid`)
						WHERE t.`type` = "project" AND t.`parent` = :tpid';
		$dbs = mydb::select($stmt,':tpid',$projectId);

		$ret .= '<section id="project-detail-childproject" class="project-detail-childproject">';
		$ret .= '<h3>โครงการย่อย</h3>'._NL;
		$ret .= '<div id="project-child" class="project-child box">'._NL;
		if ($dbs->_num_rows) {
			$no = 0;
			$tables = new Table();
			$tables->thead=array('no' => '', 'ชื่อโครงการ', 'budget -money' => 'งบประมาณ', 'approve -date' => 'วันที่อนุมัติ', 'status -center' => 'สถานภาพโครงการ');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url(($rs->prtype=='โครงการ'?'paper/':'paper/').$rs->tpid).'">'.$rs->title.'</a>',
					number_format($rs->budget,2),
					$rs->date_approve ? sg_date($rs->date_approve, 'ว ดด ปปปป') : '',
					$rs->project_status,
				);
			}
			$ret .= $tables->build();
		}


		if ($isEdit && $projectInfo->info->ischild) {
			$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary" href="'.url('project/my/project/new',array('parent'=>$projectId)).'"><i class="icon -addbig -white"></i><span>เพิ่มโครงการย่อย</span></a></nav>'._NL;
		}
		$ret.='</div><!-- project-child -->'._NL._NL;
		$ret .= '<section>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]),
			'body' => new Card([
				'children' => [
					new ListTile([
						'title' => ++$titleNo.'. โครงการย่อย',
						'leading' => '<i class="icon -material">stars</i>',
						'trailing' => '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><icon class="icon -material">expand_less</i></a>',
					]), // ListTile
					new Container([
						'tagName' => 'section',
						'id' => 'project-info-child',
						'class' => 'project-info-child',
						'child' => $ret,
					]), // Container
				], // children
			]), // Card
		]);
	}
}
?>