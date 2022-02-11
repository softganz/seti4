<?php
function project_admin_planning_issue($self,$groupId=NULL,$action=NULL,$trid=NULL) {
	if (empty($action)) $action=post('act');

	R::View('project.toolbar',$self,'Planning Management','admin');
	$self->theme->sidebar = R::View('project.admin.menu');

	//$ret.='Action='.$action.'<br />'.print_o(post(),'post');

	switch ($action) {
		case 'addproblem':
			$data = (Object) post('data');
			if ($data->problem) {
				$data->taggroup = 'project:problem:'.$groupId;
				$data->indicator = trim(preg_replace('/[\r\n]+/', '', nl2br(trim($data->indicator))));
				$data->datasource = trim(preg_replace('/[\r\n]+/', '', nl2br(trim($data->datasource))));
				if ($data->weight == '') {
					$stmt = 'SELECT MAX(`weight`) `lastWeight` FROM %tag% WHERE `taggroup`=:taggroup LIMIT 1';
					$data->weight = mydb::select($stmt,':taggroup',$data->taggroup)->lastWeight+1;
				}

				if (empty($data->catid)) {
					$stmt = 'SELECT MAX(`catid`) `lastId` FROM %tag% WHERE `taggroup`=:taggroup LIMIT 1';
					$data->catid = mydb::select($stmt,':taggroup',$data->taggroup)->lastId+1;
					$data->description = sg_json_encode($data);

					$stmt = 'INSERT INTO %tag%
						(`taggroup`, `catid`, `name`, `description`, `weight`, `process`)
						VALUES
						(:taggroup, :catid, :problem, :description, :weight, 1)';
				} else {
					//$data->problem=utf8_encode($data->problem);
					//$data->objective=utf8_encode($data->objective);
					//$ret.='JSON_UNESCAPED_UNICODE='.JSON_UNESCAPED_UNICODE.(defined('JSON_UNESCAPED_UNICODE')?'Yes':'No');
					$data->description = sg_json_encode($data);
					$stmt = 'UPDATE %tag% SET `name`=:problem,`description`=:description, `weight`=:weight WHERE `taggroup`=:taggroup AND `catid`=:catid LIMIT 1';
				}
				mydb::query($stmt,$data);
			}
			//$ret.=mydb()->_query;
			$ret.=R::Page('project.admin.planning.issue.view',$self,$groupId);
			//$ret.=print_o($data,'$data');
			break;

		case 'editproblem':
			$ret.=R::Page('project.admin.planning.issue.view',$self,$groupId,$action,$trid);
			break;

		case 'addguideline':
			$data=(object)post('data');
			if ($data->guideline) {
				$data->taggroup='project:guideline:'.$groupId;
				if (empty($data->catid)) {
					$stmt='SELECT MAX(`catid`) `lastId` FROM %tag% WHERE `taggroup`=:taggroup LIMIT 1';
					$data->catid=mydb::select($stmt,':taggroup',$data->taggroup)->lastId+1;
					$data->guideline=preg_replace('/[\r\n]+/', '', trim($data->guideline));
					$data->process=trim(preg_replace('/[\r\n]+/', '', nl2br(trim($data->process))));
					$data->description=sg_json_encode($data);
					$stmt='INSERT INTO %tag% (`taggroup`,`catid`,`name`,`description`) VALUES (:taggroup,:catid,:guideline,:description)';
				} else {
					//$data->problem=utf8_encode($data->problem);
					//$data->objective=utf8_encode($data->objective);
					//$ret.='JSON_UNESCAPED_UNICODE='.JSON_UNESCAPED_UNICODE.(defined('JSON_UNESCAPED_UNICODE')?'Yes':'No');
					$data->guideline=preg_replace('/[\r\n]+/', '', trim($data->guideline));
					$data->process=trim(preg_replace('/[\r\n]+/', '', nl2br(trim($data->process))));
					$data->description=sg_json_encode($data);
					//$ret.=print_o($data,'$data');
					$stmt='UPDATE %tag% SET `name`=:guideline,`description`=:description WHERE `taggroup`=:taggroup AND `catid`=:catid LIMIT 1';
				}
				mydb::query($stmt,$data);
			}
			//$ret.=mydb()->_query;
			$ret.=R::Page('project.admin.planning.issue.view',$self,$groupId);
			//$ret.=print_o($data,'$data');
			break;

		case 'editguideline':
			$ret.=R::Page('project.admin.planning.issue.view',$self,$groupId,$action,$trid);
			break;

		case 'visible':
			$stmt='UPDATE %tag% SET `process`=IF(`process`>0,0,1) WHERE `taggroup`=:taggroup AND `catid`=:catid LIMIT 1';
			$taggroup='project:problem:'.$groupId;
			mydb::query($stmt,':taggroup',$taggroup, ':catid',$trid);
			//$ret.=mydb()->_query;
			break;

		default:
			if ($groupId) {
				$ret.=R::Page('project.admin.planning.issue.view',$self,$groupId);
			} else {
				$stmt='SELECT * FROM %tag% WHERE `taggroup`="project:planning" ORDER BY `catid` ASC';
				$dbs=mydb::select($stmt);

				$tables = new Table();
				$tables->thead=array('center -catid'=>'ID','ประเด็นแผนงาน','พัฒนาโครงการ','ติดตามโครงการ','');

				foreach ($dbs->items as $rs) {
					$tables->rows[]=array(
						$rs->catid,
						'<a href="'.url('project/admin/planning/issue/'.$rs->catid).'">'.$rs->name.'</a>',
						'',
						'',
						'',
					);

				}

				$tables->rows[]=array(
					'',
					'<input class="form-text -fill" type="text" placeholder="เพิ่มประเด็นแผนงาน" />',
					'',
					'',
					'<button class="btn -primary -disabled" value="add"><i class="icon -save -white"></i></button>',
				);

				$ret .= $tables->build();
			}
			break;
	}

	return $ret;
}
?>