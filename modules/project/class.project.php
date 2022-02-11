<?php
/**
 * project class for project management
 *
 * @package project
 * @version 4.20
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2010-05-25
 * @modify 2020-06-06
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */
require_once('manifest.project.php');
//require_once('class.project.init.php');

class project extends project_base {
	var $property;

	function __post_permission($self,$type,$tid,$para) {return true;}

	function __post_save($self,$topic,$para,$form) {
		$post=$topic->post;
		// Save project information
		$project->tpid=$topic->tpid;
		$project->projectset=SG\getFirst($post->projectset,'func.NULL');
		$project->pryear=SG\getFirst($post->pryear,property('project:year:0'));
		$project->objective=SG\getFirst($post->objective,'func.NULL');
		$project->target=SG\getFirst($post->target,'func.NULL');
		$project->area=SG\getFirst($post->area,'func.NULL');
		$project->budget=SG\getFirst($post->budget,'func.NULL');
		$project->changwat=SG\getFirst($post->changwat,'func.NULL');

		$project->orgid=$post->orgid;
		$project->prtype=SG\getFirst($post->prtype,1);

		$post->date_from['date']=SG\getFirst($post->date_from['date'],1);
		$project->date_from=($post->date_from['month'] && $post->date_from['year'])?date('U',mktime(0,0,0,intval($post->date_from['month']),intval($post->date_from['date']),intval($post->date_from['year']))):'func.NULL';

		$post->date_end['date']=SG\getFirst($post->date_end['date'],1);
		$project->date_end=($post->date_end['month'] && $post->date_end['year'])?date('U',mktime(0,0,0,intval($post->date_end['month']),intval($post->date_end['date']),intval($post->date_end['year']))):'func.NULL';

		$project->date_approve='func.NULL';
		$stmt='INSERT INTO %project% (`tpid`, `projectset`, `pryear`, `prtype`, `changwat` , `date_from`, `date_end` ) VALUES (:tpid, :projectset, :pryear, :prtype, :changwat, :date_from, :date_end)';
		mydb::query($stmt,$project);

		mydb::query('UPDATE %topic% SET `orgid`=:orgid, `status`='._LOCK.' WHERE `tpid`=:tpid LIMIT 1',':tpid',$topic->tpid,':orgid',$project->orgid);
		//die(mydb()->_query);

		// Save project owner information
		if ($topic->post->name) {
			$owner->tpid=$topic->tpid;
			$owner->uid=SG\getFirst(i()->uid,'func.NULL');
			$owner->membership='Owner';
			$stmt='INSERT INTO %topic_user% (`tpid`, `uid`, `membership`) VALUES (:tpid, :uid, :membership)';
			mydb::query($stmt,$owner);
		}

		if (i()->ok) {
			// add new user
			mydb::query('INSERT INTO %topic_user% (`tpid`, `uid`, `membership`) VALUES (:tpid, :uid, "'.(in_array('trainer',i()->roles)?'Trainer':'Owner').'" )',':tpid',$topic->tpid,':uid',i()->uid);
		}
	}

	function __post_cancel($self,$topic,$para,$form,$result) {location('project');}

	function __post_complete($self,$topic,$para) {location('paper/'.$topic->tpid);}

	function __post_form($self,$topic,$para,$form) {
		unset($self->theme->header);
		R::View('project.toolbar',$self,'เพิ่มโครงการใหม่');
		$form=R::Page('project.form.postform',$self,$topic,$para,$form);
		return $body;
	}

	function __delete($self,$topic,$para,$result) {
		$tpid=$topic->tpid;
		$simulate = debug('simulate');
		$result->process[]='Delete complain topic'.($simulate?' was simulate':' process');

		mydb::query('DELETE FROM %project% WHERE `tpid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %project_dev% WHERE `tpid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %project_tr_bak% WHERE `tpid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE a FROM %project_activity% a LEFT JOIN %calendar% c ON c.`id`=a.`calid` WHERE c.`tpid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		if (mydb::table_exists('%project_actguide%')) {
			mydb::query('DELETE FROM %project_actguide% WHERE `tpid`=:tpid',':tpid',$tpid);
			$result->process[]=mydb()->_query;
		}

		if (mydb::table_exists('%project_prov%')) {
			mydb::query('DELETE FROM %project_prov% WHERE `tpid`=:tpid',':tpid',$tpid);
			$result->process[]=mydb()->_query;
		}

		mydb::query('DELETE p FROM %property% p LEFT JOIN %calendar% c ON p.`module`="calendar" AND c.`id`=p.`propid` WHERE c.`tpid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %calendar% WHERE `tpid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		mydb::query('DELETE FROM %property% WHERE `module`="project" AND `propid`=:propid',':propid',$tpid);
		$result->process[]=mydb()->_query;

		if (mydb::table_exists('%project_gl%')) {
			mydb::query('DELETE FROM %project_gl% WHERE `tpid`=:tpid',':tpid',$tpid);
			$result->process[]=mydb()->_query;
		}

		if (mydb::table_exists('%project_target%')) {
			mydb::query('DELETE FROM %project_target% WHERE `tpid`=:tpid',':tpid',$tpid);
			$result->process[]=mydb()->_query;
		}

		if (mydb::table_exists('%project_paiddoc%')) {
			mydb::query('DELETE FROM %project_paiddoc% WHERE `tpid`=:tpid',':tpid',$tpid);
			$result->process[]=mydb()->_query;
		}

		mydb::query('DELETE FROM %bigdata% WHERE `keyname` LIKE "project.%" AND `keyid`=:tpid',':tpid',$tpid);
		$result->process[]=mydb()->_query;

		//die(print_o($result,'$result'));

		model::watch_log('project','remove project','Project id '.$topic->tpid.' - '.$topic->title.' was removed by '.i()->name.'('.i()->uid.')');
	}

	function __delete_complete($self,$topic,$para,$result) {
		$url='project';
		if (post('ret')) $url=post('ret');
		location($url);
	}

} // end of class project
?>