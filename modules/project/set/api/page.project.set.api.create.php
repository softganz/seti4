<?php
/**
* Project :: Create Project Set
* Created 2022-02-01
* Modify  2022-02-01
*
* @return Widget
*
* @usage project/set/api/create
*/

class ProjectSetApiCreate extends Page {
	function build() {
		$isCreatable = user_access('create project set');

		$data = (object)post('data');

		if ($isCreatable && $data->title) {
			$data->prtype='ชุดโครงการ';
			$data->ischild = 1;

			$result = R::Model('project.create', $data);
			$tpid = $result->tpid;

			//$ret .= print_o($result,'$result');

			if ($result->tpid) {
				location('project/set/'.$result->tpid);
			} else {
				$ret .= message('error', 'Error on create project set');
			}

			// Create planning group
			//$stmt='INSERT INTO %project_tr% (`tpid`,`refid`,`formid`,`part`,`uid`,`created`) VALUES (:tpid,:refid,"planning","title",:uid,:created)';
			//mydb::query($stmt,':tpid',$tpid, ':refid',$data->group, ':uid',i()->uid, ':created',date('U'));

			//$ret.=print_o($data,'$data');
			//$ret.=print_o($fundInfo);
			//$ret.=R::View('project.planning.view',$fundInfo,$data);
		} else {
			$ret .= message('error', 'Call create project set but invalid Data');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}
?>