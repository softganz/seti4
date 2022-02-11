<?php
/**
 * paper_model class for paper model
 *
 * @package paper
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-11-21
 * @modify 2010-05-17
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class paper_model {

	public static function get_photo_property($file,$photo) {
		if (isset($photo)) $property=$photo;
		$property->_src=_url.cfg('upload_folder').'pics/'.sg_urlencode($file);
		$property->_file=$photo_location=cfg('folder.abs').cfg('upload_folder').'pics/'.$file;
		$property->_url=cfg('url.abs').cfg('upload_folder').'pics/'.sg_urlencode($file);
		//		echo 'url.abs='.cfg('url.abs').' : '.$property->_url.'<br />';
		$property->_filesize=filesize($photo_location);
		if (file_exists($photo_location)) {
			$size=getimagesize($photo_location);
			$property->_exists=true;
			$property->_size->width=$size[0];
			$property->_size->height=$size[1];
			$property->_size->attr=$size[3];
			$property->_size->bits=$size['bits'];
			$property->_size->channels=$size['channels'];
			$property->_size->mime=$size['mime'];
		} else $property->_exists=false;
		return $property;
	}

	public static function get_topic_by_id($tpid,$para=NULL,$revid=NULL) {
		$sql_cmd = 'SELECT
			  t.*
			, ty.`name` type_name
			, ty.`module`
			, ty.`description` type_description
			, u.`username` as username
			, u.`name` as owner
			, u.`status` owner_status
			, r.`format`
			, r.`body`
			, r.`property`
			, r.`email`
			, r.`homepage`
			, r.`redirect` ';

		if (module_install('voteit')) $sql_cmd.=do_class_method('voteit','get_topic_by_condition','fields',$para,$tpid);

		$sql_cmd .= '  FROM %topic% t
				LEFT JOIN %topic_revisions% r ON r.revid='.SG\getFirst($revid,'t.revid ').'
				LEFT JOIN %users% u ON t.uid=u.uid
				LEFT JOIN %topic_types% ty ON ty.type=t.type ';

		if (module_install('voteit')) $sql_cmd.=do_class_method('voteit','get_topic_by_condition','join',$para,$tpid);

		$sql_cmd .= '  WHERE t.tpid='.$tpid.'
							LIMIT 1';


		$rs = mydb::select($sql_cmd);
		if ($rs->_num_rows) {
			$rs->_archive=false;
		} else if ($rs->_num_rows==0 && mydb::table_exists('%archive_topic%')) {
			$sql_cmd=preg_replace(array('#%topic%#s','#%topic_revisions%#s'),array('%archive_topic%','%archive_topic_revisions%'),$sql_cmd);
			$rs = mydb::select($sql_cmd);
			if ($rs->_num_rows) $rs->_archive=true;
		}

		if ( $rs->_num_rows ) {
			$tags=mydb::select('SELECT tt.`tid`,tt.`vid`,
					t.`name`, t.`description`,
					v.`name` vocab_name
				FROM %'.($rs->_archive?'archive_':'').'tag_topic% tt
					LEFT JOIN %tag% t ON t.tid=tt.tid
					LEFT JOIN %vocabulary% v ON tt.`vid`=v.`vid`
				WHERE tpid='.$rs->tpid);
			foreach ($tags->items as $tag) $rs->tags[]=(object)array('tid'=>$tag->tid,'name'=>$tag->name,'vid'=>$tag->vid,'vocab_name'=>$tag->vocab_name,'description'=>$tag->description?$tag->description:null);

			$rs->photo = mydb::select('SELECT * FROM %'.($rs->_archive?'archive_':'').'topic_files% WHERE `tpid`='.$rs->tpid.' AND `cid`=0 AND `type`="photo" ORDER BY fid');
			foreach ($rs->photo->items as $key=>$photo) $rs->photo->items[$key]=object_merge($rs->photo->items[$key],model::get_photo_property($photo->file));

			if (cfg('topic.video.allow')) {
				$rs->video=mydb::select('SELECT f.*,u.username FROM %topic_files% f LEFT JOIN %users% u ON u.uid=f.uid WHERE tpid=:tpid AND type="movie" LIMIT 1',':tpid',$tpid);
				if ($rs->video->file) {
					if (preg_match('/^http\:\/\//',$rs->video->file)) {
						$rs->video->_url=$rs->video->file;
						$rs->video->_location=NULL;
					} else {
						//$rs->video->_url=cfg('domain').cfg('upload.url').$rs->video->username.'/'.$rs->video->file;
						$rs->video->_url=cfg('upload.url').$rs->video->username.'/'.$rs->video->file;
						$rs->video->_location=sg_user_folder($rs->username).$rs->video->file;
					}
				}
			}

			// Set topic property
			$rs->property = sg_json_decode($rs->property, cfg('topic.property'));

			if ( $rs->profile_picture ) $rs->profile_picture = cfg('url').'upload/member/'.$rs->profile_picture;
			if (module_install('poll')) {
				$poll=mydb::select('SELECT * FROM %poll% WHERE `tpid`=:tpid LIMIT 1',':tpid',$rs->tpid);
				if ($poll->_num_rows) {
					$rs->poll=$poll;
					foreach (mydb::select('SELECT * FROM %poll_choice% WHERE `tpid`=:tpid ORDER BY `choice` ASC',':tpid',$rs->tpid)->items as $pollrs) {
						$rs->poll->items->{$pollrs->choice}=$pollrs;
					}
				}
			}

			// do external module post form
			$rs->_content_type_property=cfg('topic_options_'.$rs->type);
			if (function_exists('module2classname')) {
				$classname=module2classname($rs->module);
				if (module_exists($classname,'__get_topic_by_id')) call_user_func(array($classname,'__get_topic_by_id'),NULL,$rs,$para);
			}
		}
		return $rs;
	}

	public static function get_topic_by_condition($para) {
		$items=SG\getFirst($para->items,10);
		$field=option($para->field);
		$sort=in_array($para->sort,array('asc','desc'))?$para->sort:'desc';

		//debugMsg($para,'$para');
		if ($para->category) {
			$tags=explode(',',mydb::select('SELECT `tid` FROM %tag_synonym% WHERE name=:category',':category',$para->category)->lists->text);
			//debugMsg($tags,'$tags');
		}

		$fld_cmd .= ' DISTINCT t.* ';
		$fld_cmd .= '  , u.username as username,u.name as owner ';
		if ($field->detail) $fld_cmd .= '    , r.format , r.body , r.property , r.email , r.homepage ';
		if ($field->comment) $fld_cmd.=' ,(SELECT COUNT(*) FROM %topic_comments% WHERE `tpid`=t.`tpid`) comments';
		//		if ($field->photo) $fld_cmd .= '    , pics.fid as pic_id , pics.file as photo , pics.title as photo_name , pics.description as photo_description';

		if (module_install('voteit')) $fld_cmd.=do_class_method('voteit','get_topic_by_condition','fields',$para);

		$table_cmd = ' %topic% as t ';
		$table_cmd .= '  LEFT JOIN %users% as u ON t.uid=u.uid ';
		if ($field->detail) $table_cmd .= '  LEFT JOIN %topic_revisions% as r on r.revid=t.revid ';
		if ($para->tag || $para->category) $table_cmd .= '  LEFT JOIN %tag_topic% tp ON tp.tpid=t.tpid ';
		if ($para->category) $table_cmd .= '  LEFT JOIN %tag% tg ON tg.tid=tp.tid ';
		//		if ($field->photo) $table_cmd .= '  LEFT JOIN ( %topic_files% AS pics
		//														LEFT JOIN %topic_files% as pic2 ON pic2.tpid=pics.tpid AND pics.cid=0 AND pic2.type="photo" AND pics.fid>pic2.fid
		//														) ON pics.tpid=t.tpid AND pics.cid=0 AND pics.type="photo" ';

		if (module_install('voteit')) $table_cmd.=do_class_method('voteit','get_topic_by_condition','join',$para);

		$where=array();
		if ($para->type) $where[]=strpos(',',$para->type)?'t.type in ("'.implode('","',explode(',',$para->type)).'")':'t.`type`="'.$para->type.'"';
		if ($para->category) {
			$tags=db_query_one_column('SELECT tid FROM %tag_synonym% WHERE name="'.$para->category.'"');
			$where[]='tg.tid in ('.implode(',',$tags).')';
		}
		if ($para->tag) $where[]='tp.tid in ('.$para->tag.')';
		if ($para->sticky) $where[]='sticky='.$para->sticky;
		if ($para->user) $where[]='t.uid="'.$para->user.'"';
		if ($para->ip) $where[]='t.ip='.ip2long($para->ip);
		if ($para->year) $where[]='YEAR(t.created)="'.addslashes($para->year).'"';
		if (i()->ok) {
			if (!user_access('administer contents,administer papers')) $where[]='t.status in ('._PUBLISH.','._LOCK.') || (t.status in ('._DRAFT.','._WAITING.') AND t.uid='.i()->uid.')';
		} else {
			$where[]='t.status in ('._PUBLISH.','._LOCK.')';
		}
		if ($para->condition) $where[]=$para->condition;

		//		if ($field->photo) $where[]='pic2.fid IS NULL';

		$where_cmd = $where ? '('.implode(') AND (',$where).')' : null;

		$sql_cmd = 'SELECT '.$fld_cmd.' FROM '.$table_cmd.($where_cmd?' WHERE '.$where_cmd:'');
		$sql_cmd .= ' ORDER BY '.SG\getFirst($para->order,'t.tpid').' '.SG\getFirst($sort,'DESC');

		if ($para->limit) {
			$sql_cmd .= '  LIMIT '.$para->limit;
		} else {
			$total_items=mydb::select('SELECT COUNT(*) `total` FROM '.$table_cmd.($where_cmd?' WHERE '.$where_cmd:'').' LIMIT 1')->total;
			$count_query=mydb()->_query;
			$pagenv = new PageNavigator($items,$para->page,$total_items,q());
			$sql_cmd .= '  LIMIT '.($pagenv->FirstItem()<0 ? 0 : $pagenv->FirstItem()).','.$items;
		}

		$topics=mydb::select($sql_cmd);
		$topics->page=$pagenv;
		$topics->_query_count=$count_query;

		$result=sg_clone($topics);
		$result->items=array();
		foreach ($topics->items as $key=>$topic) {
			$topic_list[]=$topic->tpid;
			$topic->summary=sg_summary_text($topic->body);
			//			if ($topic->photo) $topic->photo=model::get_photo_property($topic->photo);
 			$topic->profile_picture=model::user_photo($topic->username);
			//			if ($topic->profile_picture ) $topic->profile_picture = cfg('url').'upload/member/'.$topic->profile_picture;
			$result->items[$topic->tpid]=$topic;
		}
		if ($field->photo && $topic_list) {
			$sql_cmd = 'SELECT `tpid`,`file` FROM %topic_files% WHERE tpid in ('.implode(',',$topic_list).') AND `cid`=0 AND `type`="photo" GROUP BY `tpid` ORDER BY `tpid` ASC';
			$photos=mydb::select($sql_cmd);
			//			echo mydb()->_query;
			//			print_o($photos,'$photos',1);
			foreach ($photos->items as $photo) {
				$result->items[$photo->tpid]->photo=model::get_photo_property($photo->file);
			}
		}
		//		print_o($topic_list,'$topic_list',1);
		//debugMsg($result,'$result');
		return $result;
	}

	public static function get_comment_by_id($cid) {
		$stmt = 'SELECT
				c.tpid AS tpid , t.title , c.*
				, u.name AS owner
				, p.fid , p.file AS photo , p.title AS photo_title , p.description AS photo_description
			FROM %topic_comments% AS c
				LEFT JOIN %topic% AS t ON t.tpid=c.tpid
				LEFT JOIN %topic_files% as p ON p.tpid=c.tpid AND p.cid=c.cid AND p.`type`="photo"
				LEFT JOIN %users% AS u ON u.uid=c.uid
			WHERE c.`cid` = :cid LIMIT 1';
		$rs=mydb::select($stmt, ':cid', $cid);
		return $rs;
	}

	public static function modify_photo($photo,$post) {
		$result->error=false;
		$result->process[]='paper_model::modify_photo request';
		$result->post=print_o($post,'$post');
		$result->photo=print_o($photo,'$photo');

		$photo_description=$post;

		if ($post->photo) {
			$result->upload = R::Model('photo.save',$post->photo);
			if ($result->upload->complete) {
				$photo_description->file=$result->upload->save->_file;
				if ($result->upload->save->_file != $photo->file &&
						file_exists($photo->_file) && is_file($photo->_file) &&
						mydb::select('SELECT COUNT(*) `total` FROM %topic_files% WHERE `file`=:file AND `type`="photo" LIMIT 1',':file',$photo->file)->total<=1) {
					$result->process[]='Delete old photo file <em>'.$photo->_file.'</em>';
					unlink($photo->_file);
				}
			} else $result->error=$result->upload->error;
		}

		unset($photo_description->photo);
		$sql_cmd=mydb::create_update_cmd('%topic_files%',$photo_description,'fid='.$photo->fid.' LIMIT 1');
		mydb::query($sql_cmd,$photo_description);
		$result->query[]=mydb()->_query;
		if (mydb()->_error) $result->error[]='Query error';
		return $result;
	}

	public static function delete_photo($photo_id=array(),$is_simulate=false) {
		$result->error=false;
		$result->process[]='paper_model::delete_photo request';

		if (empty($photo_id)) return $result;
		if (is_string($photo_id)) {$id=$photo_id;unset($photo_id);$photo_id[]=$id;}

		$photos=mydb::select('SELECT * FROM %topic_files% WHERE `fid` IN (:fid) AND `type`="photo"',':fid','SET:'.implode(',',$photo_id));
		$result->query[]=mydb()->_query;
		if ($photos->_num_rows<=0) {
			$result->error='No photo file to delete';
			return $result;
		}

		$result->process[]=($is_simulate?'Simulation ':'').'Process starting to delete '.$photos->_num_rows.' item(s).';
		$stmt = 'DELETE FROM %topic_files% WHERE `fid` IN ('.implode(',',$photo_id).') AND `type`="photo"';
		mydb::query($stmt,$is_simulate);
		$result->query[]=mydb()->_query;

		foreach ($photos->items as $item) {
			$photo=model::get_photo_property($item->file);
			$result->process[]='Start delete file <em>'.$photo->_file.'</em>';
			if (file_exists($photo->_file) and is_file($photo->_file)) {
				$is_inused=mydb::select('SELECT * FROM %topic_files% WHERE `file`=:file AND fid!=:fid AND `type`="photo" LIMIT 1',':file',$item->file,':fid',$item->fid)->fid;
				$result->query[]=mydb()->_query;
				if ($is_inused) {
					$result->process[]='File <em>'.$photo->_file.'</em> was inused by other item.';
				} else {
					if (!$is_simulate) unlink($photo->_file);
					$result->process[]='File <em>'.$photo->_file.'</em> has been deleted.';
					$result->deleted->id[]=$item->fid;
					$result->deleted->file[]=$item->file;
				}
			}
		}

		if ($result->deleted->file) $result->deleted->name=implode(',',$result->deleted->file);
		$result->process[]='paper_model::delete_photo request complete';
		return $result;
	}

	public static function toggle_topic_comment_status($tpid) {
		$sql_cmd = 'UPDATE %topic% SET comment=IF(comment=0,2,0) WHERE tpid='.$tpid.' LIMIT 1';
		mydb::query($sql_cmd);
		return;
	}



	public static function create_category_list($paras="") {
		$sql_cmd  = 'SELECT  f.fid as fid , f.name as forum_name , c.cid as cid , c.name as cat_name ';
		$sql_cmd .= '  FROM %forum_categorys% as c ';
		$sql_cmd .= '  LEFT JOIN %forum_id% as f ON c.fid=f.fid ';
		$sql_cmd .= '  ORDER BY f.sort_order,c.sort_order ASC';
		$cat_list= db_query_array($sql_cmd);


		foreach ( $cat_list as $rs ) $forum[$rs["fid"]][]=$rs;

		$nl = "\n";
		$tab = "\t";
		$script = "[ ['',''],['',''],".$nl;
		$no = 0;

		foreach ( $forum as $fid=>$cat) {
			$script .= "$tab ['$fid','{$cat[0]["forum_name"]}',$nl";
			foreach ( $cat as $cat_rs ) {
				$script .= "$tab $tab ['{$cat_rs["cid"]}' , '{$cat_rs["cat_name"]}'],$nl";
			}
			$script .= "$tab ],$nl$nl ";
		}
		$script .= "]";
		return $script;
	}

	public static function create_archive() {
		/*
		SELECT tpid,created  FROM `sgz_topic` WHERE DATE_FORMAT(`created`,"%Y-%m-%d")<"2008-07-01" ORDER BY created DESC LIMIT 1

		DELETE FROM `sgz_topic` WHERE tpid<=10845;
		DELETE FROM `sgz_tag_topic` WHERE tpid<=10845;
		DELETE FROM `sgz_topic_revisions` WHERE tpid<=10845;
		DELETE FROM `sgz_topic_comments` WHERE tpid<=10845;
		DELETE FROM `sgz_topic_files` WHERE tpid<=10845;

		DELETE FROM `sgz_archive_topic` WHERE tpid>10845;
		DELETE FROM `sgz_archive_tag_topic` WHERE tpid>10845;
		DELETE FROM `sgz_archive_topic_revisions` WHERE tpid>10845;
		DELETE FROM `sgz_archive_topic_comments` WHERE tpid>10845;
		DELETE FROM `sgz_archive_topic_files` WHERE tpid>10845;
		*/
	}

} // end of class paper_model
?>