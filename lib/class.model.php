<?php
/**
 * model class for CMV
 *
 * @package core
 * @version 0.20
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-07-09
 * @modify 2009-06-23
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */
// @deprecated
// All function will deprecate and move this class to be base class of all model

class Model {

	public static function member_menu() {
		if (!i()->ok) {
			user_menu(
				'signin',
				'Sign in',
				url('my', ['showGuide' => 0, 'showInfo' => 0, 'showRegist' => 0]),
				'{"class":"sg-action", "data-rel":"box", "data-width":"320"}'
			);
			return;
		}

		$uid = i()->uid;
		user_menu('member',tr('Member').' ['.i()->username.']','javascript:void(0)');
		user_menu('member','member_profile',tr('Profile'),url('profile/'.$uid));
		user_menu('member','member_commentorder',tr('Show comment').' <strong><em>'.(cfg('comment.order')=='ASC'?tr('Lastest'):tr('First')).'</em></strong> '.tr('on top'),url(q(),'change_comment_order'));
		if (module_install('blog') && user_access('create blog content')) {
			user_menu('member','blogpost',sg_client_convert('เพิ่ม/เขียนบันทึกในบล็อก'),url('blog/post'));
			user_menu('member','dashboard',sg_client_convert('จัดการบล็อก'),url('dashboard'));
		}
		if (module_install('paper')) {
			user_menu('member','member_mytopic',tr('My Documents'),url('paper/user/'.$uid));
			if (user_access('administer contents,administer papers')) {
				user_menu('member','member_paper_content','Site content status',url('paper/contents'),'{"class":"sg-action","data-rel":"ribbon-toolbar"}');
			}
			if (cfg('member.menu.paper.add') && i()->ok) {
				$perms=cfg('perm');
				foreach (explode(',',$perms->paper) as $perm) {
					if (preg_match('/create ([\w].*) paper/',$perm,$out) && user_access($perm)) {
						$forum=$out[1];
						user_menu('member','member_post_'.$forum,'Create <strong><em>'.$forum.'</em></strong> topic',url('paper/post/'.$forum));
					}
				}
			}
		}
		if (user_access('access administrator pages')) user_menu('member','member_admin','Administrator pages',url('admin'));
		user_menu('member','member_signout',tr('Sign Out'),url('signout'));
	}

	public static function get_topic_type($tid = NULL, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		static $types = array();

		if (!isset($tid)) {
			$result = mydb::select('SELECT t.*, IF(t.`module` IS NULL OR t.`module` = "","paper",t.`module`) `module` FROM %topic_types% AS t ORDER BY t.name');
			return $result;
		} else if (!array_key_exists($tid, $types)) {
			$result = mydb::select('SELECT t.*, IF(t.`module` IS NULL OR t.`module` = "","paper",t.`module`) `module`  FROM %topic_types% AS t WHERE t.`type` = :tid LIMIT 1',':tid',$tid);
			if (!$result->_empty) {
				if (!$debug) mydb::clearprop($result);
				$types[$tid] = $result;
				$types[$tid]->topic_options = SG\getFirst(cfg('topic_options_'.$tid),NULL);
			}
		}
		return $types[$tid];
	}

	public static function get_category($taggroup,$key='tid',$fullDesc=false,$process=NULL) {
		if (is_null($key)) $key='tid';
		$stmt="SELECT `$key` `catkey`, t.*
					FROM %tag% t
					WHERE `taggroup`=:taggroup".(isset($process)?' AND `process`=:process':'')."
					ORDER BY `weight` ASC, `$key` ASC";
		$dbs=mydb::select($stmt,':taggroup',$taggroup,':process',$process);
		$result=array();
		foreach ($dbs->items as $rs) $result[$rs->catkey]=$fullDesc?$rs:$rs->name;
		return $result;
	}

	public static function get_category_by_group($taggroup, $key = 'tid', $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		mydb::where('tg.`taggroup` = :taggroup',':taggroup',$taggroup);
		if ($options->condition) mydb::where($options->condition);
		$stmt = "SELECT
						  tg.`$key` `catkey`
						, tg.`name`
						, p.`name` `parentName`
						FROM %tag% tg
							LEFT JOIN %tag% p ON p.`taggroup` = :taggroup AND p.`catid` = tg.`catparent`
						%WHERE%
						ORDER BY tg.`weight` ASC, tg.`$key` ASC;";
		$dbs=mydb::select($stmt);

		$result=array();
		foreach ($dbs->items as $rs) {
			if (empty($rs->parentName)) $result[$rs->name]=array();
		}
		foreach ($dbs->items as $rs) {
			if (empty($rs->parentName)) continue;
			$result[$rs->parentName][$rs->catkey]=$rs->name;
		}
		return $result;
	}

	public static function get_vocabulary($vid = NULL) {
		static $vocabularies = array();
		if (!isset($vid)) {
			$result=mydb::select('SELECT * FROM %vocabulary% ORDER BY weight,name ASC');
			return $result;
		}
		if (!array_key_exists($vid, $vocabularies)) {
			$dbs = mydb::select('SELECT v.*, n.`type` FROM %vocabulary% AS v LEFT JOIN %vocabulary_types% AS n ON v.`vid` = n.`vid` WHERE v.`vid`=:vid ORDER BY v.`weight`, v.`name`',':vid',$vid);
			$node_types = array();
			foreach ($dbs->items as $voc) {
				$node_types[$voc->type] = $voc->type;
				unset($voc->type);
				$voc->topics = $node_types;
				$vocabularies[$vid] = $voc;
			}
		}
		return $vocabularies[$vid];
	}

	public static function get_taxonomy($tid,$child=false) {
		static $taxonomies = array();
		if (!array_key_exists($tid, $taxonomies)) {
			$taxonomy = mydb::select(
				'SELECT
				t.*
				, v.`name` as `vocabulary_name`
				, h.`parent`
				, ht.`name` as `parent_name`
				FROM %tag% AS t
					LEFT JOIN %vocabulary% AS v ON t.vid = v.vid
					INNER JOIN %tag_hierarchy% AS h ON h.tid=t.tid
					LEFT JOIN %tag% AS ht ON h.parent=ht.tid
				WHERE t.tid=:tid ORDER BY t.weight, t.name LIMIT 1',
				[':tid' => $tid]
			);

			if ($taxonomy->_num_rows) {
				$taxonomy->synonym=mydb::select('SELECT COUNT(*) `total` FROM %tag_synonym% s WHERE s.tid='.$tid.' LIMIT 1')->total;
				$taxonomy->child=array();
				if ($taxonomy->parent_name) {
					$hierachys[$taxonomy->parent]=$taxonomy->parent_name;
					$parents = (Object) ['parent' => $taxonomy->parent];
					do {
						$parents=mydb::select('SELECT h.tid,h.parent,t.name FROM %tag_hierarchy% h LEFT JOIN %tag% t ON t.tid=h.tid WHERE h.tid='.$parents->parent.' LIMIT 1');
						if ($parents->_num_rows) {
							$hierachy[]=$parents->tid;
							$hierachys[$parents->tid]=$parents->name;
						}
					} while ($parents->parent);
				}

				$child=mydb::select('SELECT tid,name FROM %tag% t INNER JOIN %tag_hierarchy% AS h USING(tid) WHERE parent=:parent',':parent',$tid);
				foreach ($child->items as $crs) $taxonomy->child[$crs->tid]=$crs->name;

				unset($taxonomy->parent);
				$taxonomy->parent = $hierachy;
				if ($hierachys) $taxonomy->parents=$hierachys;
				if ($taxonomy->synonym>0) {
					unset($taxonomy->synonym);
					$synonyms=mydb::select('SELECT tsid,name FROM %tag_synonym% WHERE tid='.$tid.' ORDER BY tsid ASC');
					foreach ($synonyms->items as $synonym) $taxonomy->synonym[$synonym->tsid]=$synonym->name;
				} else $taxonomy->synonym=array();
				$taxonomies[$tid] = $taxonomy;
			}
		}
		return $taxonomies[$tid];
	}

	public static function get_taxonomy_tree($vid, $parent = 0, $depth = -1, $max_depth = NULL) {
		static $children, $parents, $terms;

		$depth++;

		// We cache trees, so it's not CPU-intensive to call get_tree() on a term
		// and its children, too.
		if (!isset($children[$vid])) {
			$children[$vid] = array();
			$dbs = mydb::select('SELECT t.`tid`, t.*, `parent` FROM %tag% AS t INNER JOIN  %tag_hierarchy% AS h USING(`tid`) WHERE t.`vid`=:vid ORDER BY `weight`, CONVERT(`name` USING tis620)',':vid',$vid);
			foreach ($dbs->items as $term) {
				$children[$vid][$term->parent][] = $term->tid;
				$parents[$vid][$term->tid][] = $term->parent;
				$terms[$vid][$term->tid] = $term;
			}
		}
		$max_depth = (is_null($max_depth)) ? count($children[$vid]) : $max_depth;
		if ($children[$vid][$parent]) {
			foreach ($children[$vid][$parent] as $child) {
			  if ($max_depth > $depth) {
				$term = sg_clone($terms[$vid][$child]);
				$term->depth = $depth;
				// The "parent" attribute is not useful, as it would show one parent only.
				unset($term->parent);
				$term->parents = $parents[$vid][$child];
				$tree[] = $term;

				if ($children[$vid][$child]) {
				  $tree = array_merge($tree, (array)model::get_taxonomy_tree($vid, $child, $depth, $max_depth));
				}
			  }
			}
		}
		return $tree ? $tree : array();
	}

	public static function add_taxonomy($vid,$name,$parent=0,$description='',$weight=0) {
		mydb::query('INSERT INTO %tag% ( vid , name , description , weight ) VALUES ( '.$vid.' , "'.addslashes($name).'" , "'.addslashes($description).'" , '.$weight.' ) ');
		$tid=mydb()->_error ? NULL : mydb()->insert_id;
		if ($tid) mydb::query('INSERT INTO %tag_hierarchy% ( tid , parent ) VALUES ( '.$tid.' , '.$parent.' ) ');
		return $tid;
	}

	/**
	 * Send e-mail on demand
	 *
	 * @param String/Object $to
	 * @param String $title
	 * @param String $body
	 */
	public static function sendmail($to, $title = NULL, $body = NULL, $option = NULL) {
		if (is_object($to)) {
			$message=$to;
			$module=$title;
			switch (strtoupper($module)) {
				case 'PHPMAILER' :  $mail_result=model::sendmail_by_PHPMailer($message); break;
				default :  $mail_result=model::sendmail_by_SMTP($message); break;
			}
			return $mail_result;
		} else {
			$send_to=explode(',',cfg('alert.email'));
			$mail->title=strip_tags($title);
			$mail->name=i()->name ? i()->name : $topic->post->poster;
			$mail->from='alert@'.cfg('domain.short');
			if (cfg('alert.cc')) $mail->cc=cfg('alert.cc');
			if (cfg('alert.bcc')) $mail->bcc=cfg('alert.bcc');

			$mail->body='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
<title>'.$topic->post->title.'</title>
</head>
<body>
<a href="'.cfg('domain').url('paper/'.$topic->post->tpid).'" target=_blank><strong>'.$topic->post->title.'</strong></a> ('.sg_status_text($topic->post->status).') | <a href="'.cfg('domain').url('paper/'.$topic->post->tpid).'" target=_blank>view</a><br />
Submit by <strong>'.($topic->post->poster?$topic->post->poster.(i()->name && i()->name!=$topic->post->poster?'('.$i()->name.')':'') : i()->name).(i()->uid?'('.i()->uid.')':'').'</strong> on <strong>'.date('Y-m-d H:i:s').'</strong> | paper id : <strong>'.$topic->post->tpid.'</strong> in ';
			foreach ($topic->tags as $tag) $mail->body.='<strong>'.$tag->name.'</strong> , ';
			$mail->body=trim($mail->body,' , ');
			$mail->body.='<br />
<em>poster host : '.gethostbyaddr(long2ip($topic->post->ip)).' ('.long2ip($topic->post->ip).')</em> '.($photo? ' | <strong>'.count($photo).'</strong> Photo(s).':'').'
<hr size=1>'.
sg_text2html($topic->post->body).'
<hr /><p>เมล์ฉบับนี้เป็นการส่งเมล์อัตโนมัติจากการตั้งหัวข้อใหม่ในเว็บไซท์ '.cfg('domain').'</p><p>หากท่านไม่ต้องการให้มีการส่งเมล์นี้มาให้ท่านอีกต่อไป กรุณาติดต่อผู้ดูแลเว็บไซท์เพื่อทำการยกเลิก</p>
</body>
</html>';
				foreach ($send_to as $to) {
					$to=trim($to);
					if (empty($to)) continue;
					$mail->to=$to;
					$mail->result.=model::sendmail($mail).'<br /><br />';
				}
			return $mail;
		}
	}

	public static function sendmail_by_SMTP($message) {
		$mail_result = false;
		if (!load_lib('class.mail.php', 'lib')) return false;

		$mail = new Mail();
		$mail->FromName($message->name);
		$mail->FromEmail($message->from);
		if ($message->cc) $mail->CC($message->cc);
		if ($message->bcc) $mail->BCC($message->bcc);
		if ($message->encoding) $mail->encoding=$message->encoding;


		if (cfg('server') || debug('simulate')) {
			$mail_result = $mail->Send($message->to,$message->title,$message->body,false);
			//$mail_result=$mail->sendHTMLemail($message->to,$message->title,$message->body,false);
			if (debug('simulate')) $mail_result = 'mail result : '.($mail_result ? 'ok':'<font color=red>send mail error.</font>').'<br />'.$mail->send_message;
		}
		return $mail_result;
	}

	public static function sendmail_by_PHPMailer($message) {
		include_once 'modules/phpmail/class.phpmailer.php';
		$mail = new PHPMailer(); // สร้าง object class ครับ
		$mail->IsHTML(true);

		// Send from Yahoo
		$mail->IsSMTP(); // กำหนดว่าเป็น SMTP นะ
		$mail->SMTPSecure='ssl';
		$mail->Host = 'smtp.mail.yahoo.com'; // กำหนดค่าเป็นที่ mail server ได้เลยครับ
		$mail->Port = 465; // กำหนด port เป็น 465 ตามที่ mail server บอกครับ
		$mail->SMTPAuth = true; // กำหนดให้มีการตรวจสอบสิทธิ์การใช้งาน
		$mail->Username = 'softganznoreply@yahoo.com'; // ต้องมีเมล์ของ mail server ที่สมัครไว้ด้วยนะครับ
		$mail->Password = 'sgnz2010'; // ใส่ password ที่เราจะใช้เข้าไปเช็คเมล์ที่ mail server ล่ะครับ
		$mail->SetFrom('softganznoreply@yahoo.com',$message->name);
		$mail->AddReplyTo($message->from,$message->name);

		$mail->Subject  = $message->title; // กำหนด subject ครับ
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML =  $message->body; // ใส่ข้อความเข้าไปครับ
		$mail->Body = $message->body;
		$mail->AddAddress($message->to); // ส่งไปที่ใครดีครับ
		if ($message->cc) $mail->AddCC($message->cc);
		if ($message->bcc) $mail->AddBCC($message->bcc);

		return $mail->Send()?true:false;
	}

	public static function user_photo($username=NULL,$fullphoto=true) {
		$filename=$fullphoto?'profile.photo.jpg':'small.avatar.jpg';
		$photo_file=cfg('upload.folder').'/'.$username.'/'.$filename;
		$photo_url=cfg('upload.url').$username.'/'.$filename;
		if ($username && file_exists($photo_file)) {
			$time = filemtime($photo_file);
			return $photo_url.'?t='.$time;
		} else {
			return '/css/img/photography.png';
		}
		return $photo;
	}

	public static function get_photo_property($file, $folder = null) {
		if (is_object($file)) {
			$property = $file;
		} else if (is_string($file)) {
			$property = (Object) ['file' => $file, '_size' => (Object) []];
		} else {
			return false;
		}

		if (substr($property->file,0,2)=='./') {
			$folderName = substr(dirname($property->file),2).'/';
			$filename = basename($property->file);
		} else {
			$dirname = dirname($property->file);
			$filename = basename($property->file);
		}

		if ($dirname == '.') unset($dirname);

		//debugMsg('file='.$file.' , dirname='.$dirname.' , folder='.$folder.' , filename='.$filename.' , cfg(upload.url)='.cfg('upload.url').' , cfg(upload_folder) = '.cfg('upload_folder'));

		if ($dirname) {
			$property->_src = $dirname.'/'.sg_urlencode($filename);
			$property->_file = cfg('folder.abs').$dirname.'/'.sg_tis620_file($filename);
		} else if ($folder && preg_match('/^upload/', $folder)) {
			$property->_src = $folder.'/'.sg_urlencode($filename);
			$property->_url = cfg('url.abs').$folder.'/'.sg_urlencode($filename);
			$property->_file = cfg('folder.abs').$folder.'/'.sg_tis620_file($filename);
		} else if ($folder) {
			$property->_src = cfg('upload.url').$folder.'/'.sg_urlencode($filename);
			$property->_file = cfg('upload.folder').$folder.'/'.sg_tis620_file($filename);
		} else {
			$property->_src = _URL.cfg('upload_folder').'pics/'.$folderName.sg_urlencode($filename);
			$property->_file = $photo_location = cfg('folder.abs').cfg('upload_folder').'pics/'.$folderName.$filename;
		}

		if (file_exists($property->_file)) {
			$property->_filesize=filesize($photo_location);
			if (!isset($property->_url)) $property->_url=cfg('url.abs').cfg('upload_folder').'pics/'.$folderName.sg_urlencode($filename);
			$size=getimagesize($property->_file);
			$property->_exists=true;
			$property->_size->width=$size[0];
			$property->_size->height=$size[1];
			$property->_size->attr=$size[3];
			$property->_size->bits=$size['bits'];
			$property->_size->channels=$size['channels'];
			$property->_size->mime=$size['mime'];
		} else {
			$property->_exists=false;
		}
		return $property;
	}

	public static function get_topic_by_id($tpid,$para=NULL,$revid=NULL) {
	//	if (is_object($tpid)) $tpid=$tpid->tpid;
		$sql_cmd = 'SELECT t.* , ty.name type_name , ty.module , ty.description type_description ,
								u.username as username,u.name as owner , u.status owner_status,
								r.format , r.body , r.property , r.email , r.homepage , r.redirect ';

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
			$tags=mydb::select('SELECT tt.tid,tt.vid,t.name,t.description
												FROM %'.($rs->_archive?'archive_':'').'tag_topic% tt
													LEFT JOIN %tag% t ON t.tid=tt.tid
												WHERE tpid='.$rs->tpid);
			foreach ($tags->items as $tag) $rs->tags[]=(object)array('tid'=>$tag->tid,'name'=>$tag->name,'vid'=>$tag->vid,'description'=>$tag->description?$tag->description:null);

			// Set topic property
			$rs->property = sg_json_decode($rs->property, cfg('topic.property'));

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
				if (function_exists('module_exists') && module_exists($classname,'__get_topic_by_id')) call_user_func(array($classname,'__get_topic_by_id'),$this,$rs,$para);
			}
		}
		return $rs;
	}

	public static function get_blog_topic_by_id($tpid=NULL) {
		$topic=model::get_topic_by_id($tpid);
		if ($topic->_empty) return $topic;
		$topic->blog=mydb::select('SELECT * FROM %blog% WHERE bid='.$topic->bid.' LIMIT 1');
		$topic->blogname=$topic->blog->name;
		return $topic;
	}

	public static function get_category_tag($cid) {
		$categorys=cfg('categorys');
		return array_key_exists($cid,$categorys)?$categorys[$cid]:NULL;
	}

	/**
	 * Get paper by condition
	 *
	 * @param Record Set $tpid
	 * Where condition
	 * @param String tpid | data-tpid
	 * @param String type | data-type				Value = story or story,forum
	 * @param String tag | data-tag					Value = 2 or 2,4,5
	 * @param String category | data-category	Value =
	 * @param String user
	 * @param String sticky
	 * @param String condition
	 *
	 * Data sort
	 * @param String order | data-order-by	Value =table.field					Default = t.tpid
	 * @param String sort | data-sort				Value = ASC , DESC					Default = DESC
	 * @param String limit | data-limit				Value = 1 or 5,10						Default = 10
	 *
	 * Data field
	 * @param String field | data-field			Value = topic,body,photo		Default = topic
	 *
	 * @return String
	 *
	 */
	public static function get_paper() {
		$args=func_get_args();
		if (is_numeric($args[0])) {
			$tpid=array_shift($args);
			$para=para($args,'$tpid='.$tpid,'order=`t`.`tpid`','sort=DESC','limit=1','field=topic,body,photo');
		} else {
			$para=para($args,'order=tpid','sort=DESC','limit=10','field=topic');
		}

		// Convert widget parameter to old parameter
		if ($para->{'data-tpid'}) $para->tpid = $para->{'data-tpid'};
		if ($para->{'data-org'}) $para->org = $para->{'data-org'};
		if ($para->{'data-type'}) $para->type = $para->{'data-type'};
		if ($para->{'data-tag'}) {
			$para->tag = $para->{'data-tag'};
			if (strpos($para->tag, '+')) {
				$para->tag = str_replace('+', ',', $para->tag);
				$allTagList = $para->tag;
			}
		}
		if ($para->{'data-category'}) $para->category = $para->{'data-category'};
		if ($para->{'data-user'}) $para->user = $para->{'data-user'};
		if ($para->{'data-sticky'}) $para->sticky = $para->{'data-sticky'};
		if ($para->{'data-condition'}) $para->condition = $para->{'data-condition'};

		if ($para->{'data-limit'}) $para->limit = $para->{'data-limit'};
		if ($para->{'data-sort'}) $para->sort = $para->{'data-sort'};
		if ($para->{'data-order-by'}) $para->order = $para->{'data-order-by'};
		if ($para->{'data-field'}) $para->field = $para->{'data-field'};
		if ($para->{'data-havephoto'}) $para->havephoto = $para->{'data-havephoto'};

		$para->field = option($para->field);

		if ($para->debug) debugMsg($para,'$para');

		$sql_cmd  = 'SELECT DISTINCT
			t.`tpid` , t.`status` , t.`uid` , t.`poster` , t.`title` , t.`promote` , t.`sticky` , t.`comment`
			, t.`created` , t.`view` , t.`last_view` , t.`reply` , t.`last_reply` , t.`ip` ';
		if ($para->field->body) $sql_cmd .= ' , r.`body` , r.`property` ';
		if ($para->havephoto) $sql_cmd .= '		, f.`file` `photofile` ';
		if ($allTagList) $sql_cmd .= '	, GROUP_CONCAT(tp.`tid` ORDER BY `tid`) `allTagList` ';

		$sql_cmd .= ' FROM %topic% AS t ';


		if ($para->field->body) $sql_cmd .= '   LEFT JOIN %topic_revisions% AS r ON r.revid=t.revid';
		if ($para->category || $para->tag) $sql_cmd .= '  LEFT JOIN %tag_topic% tp ON tp.tpid=t.tpid ';
		if ($para->havephoto) $sql_cmd .= '		LEFT JOIN %topic_files% f ON f.`tpid`=t.`tpid` AND (`cid` IS NULL OR `cid` = 0) AND f.`type`="photo"';
		// check query condition
		if ($para->tpid) $where[] = 't.`tpid` = '.$para->tpid;
		if ($para->org) $where[] = 't.`orgid` IN ( '.$para->org.' )';
		if ($para->category) $where[] = 'tp.`tid` in ('.model::get_category_tag($para->category).')';
		if ($para->tag) $where[] = 'tp.`tid` IN ('.$para->tag.')';
		if ($para->type) $where[] = 't.`type` IN ("'.implode('","',explode(',',$para->type)).'")';
		if ($para->user) $where[] = 't.`uid` IN ('.$para->user.')';
		if ($para->sticky) $where[] = 't.sticky='.$para->sticky;
		if ($para->condition) $where[] = $para->condition;
		if (!user_access('administer contents,administer papers')) $where[] = 'status in ('._PUBLISH.','._LOCK.')';

		if ($para->havephoto) $having[] = '`photofile` IS NOT NULL';
		if ($allTagList) $having[] = '`allTagList` = "'.$allTagList.'"';

		if ($where) {
			$where='('.implode(') AND (',$where).')';
			$sql_cmd .= ' WHERE '.$where;
		}
		$sql_cmd.='		GROUP BY t.`tpid`';
		if ($having) $sql_cmd.='		HAVING '.implode(' AND ', $having);
		$sql_cmd .= ' ORDER BY '.$para->order.' '.$para->sort.' LIMIT '.$para->limit;

		$topics = mydb::select($sql_cmd);
		// debugMsg(mydb()->_query);

		if ($para->debug) debugMsg($topics,'$topics');

		if (preg_match('/(LIMIT[\s].*1|LIMIT[\s].*1;)$/i',$sql_cmd)) {
			if (!$topics->_num_rows) return $topics;
			$result=$topics;
			$result->property=sg_json_decode($topics->property);
			$result->summary=sg_summary_text($topics->body);
			if ($para->field->photo) {
				$result->photo = mydb::select('SELECT `file` FROM %topic_files% WHERE `tpid` = :tpid AND (`cid` IS NULL OR `cid` = 0) AND `type` = "photo"', ':tpid', $topics->tpid);
				foreach ($result->photo->items as $key=>$photo) {
					$result->photo->items[$key]=model::get_photo_property('upload/pics/'.$photo->file);
				}
			}
		} else {
			$topic_list=array();
			$result=sg_clone($topics);

			unset($result->items);
			foreach ($topics->items as $rs) {
				$rs->property = sg_json_decode($rs->property);
				if (!$rs->poster) $rs->poster=$rs->owner_name;
				$rs->summary=sg_summary_text($rs->body);
				$topic_list[]=$rs->tpid;
				$result->items[$rs->tpid]=$rs;
			}
			if ($para->field->photo && $topic_list) {
				$stmt = 'SELECT `fid`, `cover`, `tpid`,`file`
					FROM %topic_files%
					WHERE `tpid` in ( :tpid )
						AND (`cid` IS NULL OR `cid` = 0)
						AND `type` = "photo"
					ORDER BY `cover` DESC, `fid` ASC';

				$photos = mydb::select($stmt, ':tpid', 'SET:'.implode(',',$topic_list));

				// debugMsg($photos,'$photos');

				foreach ($photos->items as $photo) {
					if (!isset($result->items[$photo->tpid]->photo)) {
						$result->items[$photo->tpid]->photo = (Object) ['_num_rows' => 0, 'items' => []];
					}
					$result->items[$photo->tpid]->photo->_num_rows++;
					$result->items[$photo->tpid]->photo->items[] = model::get_photo_property('upload/pics/'.$photo->file);
				}
			}
		}
		return $result;
	}

	public static function explode_record($group,$topics) {
		if (preg_match('/(.*)=(.*)/',$group,$out)) {
			$group_by_key=$out[1];
			$group_request=explode(',',$out[2]);
		} else {
			return false;
		}

		foreach ($group_request as $group_name) $result[$group_name]=new record_set();
		foreach ($topics->items as $key=>$rs) {
			if (isset($rs->{$group_by_key}) && in_array($rs->{$group_by_key},$group_request)) {
				$result[$rs->{$group_by_key}]->appen($rs,$key);
			}
		}
		return $result;
	}

	public static function save_upload_photo($upload,$folder=null,$simulate=false) {
		$result->error=false;
		$result->complete=false;
		$result->process[]='paper_model::save_upload_photo '.($simulate?'<strong>simulation</strong> ':'').'request';

		if (!is_uploaded_file($upload->tmp_name)) $result->error[]='No upload file';
		if ($upload->error) $result->error[]='upload file error';
		if (empty($upload->tmp_name)) $result->error[]='Invalid temporary name';
		if ($upload->size<=0) $result->error[]='empty file size';
		if (!in_array($upload->type,cfg('photo.file_type'))) $result->error[]='Invalid file format';

		if ($result->error) return $result;


		if (empty($folder)) $folder=cfg('upload.folder').i()->username.'/';

		$result->process[]='Start saving upload file <em>'.$upload->name.'</em> to folder <em>'.$folder.'</em>';

		// if sender is admin , do not resize picture file
		if ( !user_access('administer contents') &&
			($upload->size > cfg('photo.max_file_size')*1024) ) {
			sg_photo_resize($upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
			$result->process[]='Resize photo file to '.cfg('photo.resize.width').' pixel';
		}

		$filename = sg_valid_filename($upload->name);
		$ext = sg_file_extension($filename);
		$filename = substr($filename,0,strrpos($filename,'.'));
		if (empty($filename)) $filename = 'pic_';

		$pic_index = 1;
		$upload_filename = $folder.$filename.'.'.$ext;
		$is_copynewfile = true;
		if (file_exists($upload_filename)) {
			// check duplicate file
			$old_filesize = filesize($upload_filename);
			$upload_filesize = filesize($upload->tmp_name);
			$result->process[]='Check file size of old file '.$old_filesize.' bytes and new file '.$upload_filesize.' bytes';
			if ($upload_filesize != $old_filesize) {
				while (file_exists($upload_filename)) {
					$new_filename = $filename.'_'.sprintf('%04d',$pic_index);
					$upload_filename = $upload_folder.$new_filename.'.'.$ext;
					$pic_index++;
				}
				$result->process[]='Set upload file to new name <em>'.$new_filename.'.'.$ext.'</em>';
			} else $is_copynewfile = false;
		}

		$filename = basename($upload_filename);
		$result->save->file=$filename;
		$result->save->location=$upload_filename;
		if ( $is_copynewfile ) {
			$result->process[]='Save upload file to <em>'.$upload_filename.'</em>';
			if (!$simulate) {
				if (!file_exists($folder)) {
					mkdir($folder);
					if (cfg('upload.folder.chmod')) chmod($folder,cfg('upload.folder.chmod'));
				}
				if (copy($upload->tmp_name, $upload_filename)) {
					// change mode to config->upload.file.chmod
					if (cfg('upload.file.chmod')) chmod($upload_filename,cfg('upload.file.chmod'));
					$result->save->type='new';
				} else $result->error[]='Save upload error';
			}
		} else {
			$result->process[]='Upload file <em>'.$upload_filename.'</em> is same old file , no need to save new file';
			$result->save->type='same';
		}

		$result->complete=true;
		$result->process[]='module::__save_upload_photo_file complete';
		return $result;
	}

	public static function watch_log($module=NULL,$keyword=NULL,$message=NULL,$uid=NULL,$keyid=NULL,$fldname=NULL) {
		mydb()->_watchlog = false;
		if (!mydb()->table_exists('watchdog')) return;

		$watch = (Object) [
			'date' => 'func.NOW()',
			'uid' => SG\getFirst($uid, i()->uid),
			'ip' => ip2long(GetEnv('REMOTE_ADDR')),
			'module' => SG\getFirst($module),
			'keyword' => $keyword,
			'keyid' => $keyid,
			'fldname' => $fldname,
			'message' => SG\getFirst($message),
			'url' => preg_match('/IIS/i',$_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'],
			'referer' => SG\getFirst($_SERVER['HTTP_REFERER']),
			'browser' => $_SERVER['HTTP_USER_AGENT'],
		];

		mydb()->_watchlog = false;
		mydb::query(
			'INSERT INTO %watchdog%
			( `date` , `uid` , `ip` , `module` , `keyword` , `message` , `url` , `referer` , `browser`'.($keyid?', `keyid`':'').($fldname?', `fldname`':'').' )
			VALUES
			(:date, :uid, :ip, :module, :keyword, :message, :url, :referer, :browser'.($keyid?',:keyid':'').($fldname?',:fldname':'').' );',
		$watch
		);
	}

	/**
	 * Create content type
	 *
	 * @param Object $content
	 * @return Boolean
	 */
	public static function create_content_type($content) {
		mydb::query(
			'INSERT INTO %topic_types%
			(`type`, `name`, `module`, `has_title`, `title_label`, `has_body`, `body_label`, `custom`, `modified`, `locked`)
			VALUES
			(:type, :name, :module, :has_title, :title_label, :has_body, :body_label, :custom, :modify, :locked)
			ON DUPLICATE KEY UPDATE
			`name` = :name',
			[
			':type' => $content->type,
			':name' => $content->name,
			':module' => $content->module,
			':has_title' => $content->has_title,
			':title_label' => $content->title_label,
			':has_body' => $content->has_body,
			':body_label' => $content->body_label,
			':custom' => $content->custom,
			':modify' => $content->modify,
			':locked' => $content->locked
			]
		);
		if (cfg('topic_options_'.$content->type) == NULL) {
			$topic_options = (Object) [
				'publish' => $content->publish,
				'comment' => $content->comment,
			];
			cfg_db('topic_options_'.$content->type,$topic_options);
		}
		return true;
	}

	/**
	 * Send message to twitter
	 *
	 * @param String $user username:password
	 * @param String $title
	 * @param String $url
	 * @param String $tag
	 * @return String result buffer
	 */
	public static function twitter_send($user,$title,$url=null,$tag=null) {

		if (!function_exists('curl_init')) return false;
		list($twitter_user,$twitter_pwd) = explode(':',$user);

		$twitter_url    =    'https://twitter.com/statuses/update.xml';

		# สร้าง Link ให้เป็นแบบสั้น
		//$tiny_url        =   file_get_contents("http://tinyurl.com/api-create.php?url=" . $url_post);

		# รวมข้อความทั้งหมด
		$twitter_msg    =    $title.' '.$url;

		$curl_handle = curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$twitter_url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl_handle,CURLOPT_POST,1);
		curl_setopt($curl_handle,CURLOPT_POSTFIELDS,'status='.$twitter_msg);
		curl_setopt($curl_handle,CURLOPT_USERPWD,$twitter_user.':'.$twitter_pwd);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $buffer;
	}

	/**
	 * Send e-mail alert on new post was complete
	 *
	 * @param Object $self
	 * @param Object $topic
	 * @param Object $para
	 */
	public static function send_alert_on_new_post($self = NULL,$topic = NULL, $para = NULL) {
		$send_to=explode(',',cfg('alert.email'));
		$mail->title='++'.strip_tags($topic->post->title).' : '.$topic->tags[0]->name;
		$mail->name=i()->name?i()->name:$topic->post->poster;
		$mail->from='alert@'.cfg('domain.short');
		if (cfg('alert.cc')) $mail->cc=cfg('alert.cc');
		if (cfg('alert.bcc')) $mail->bcc=cfg('alert.bcc');

		$mail->body='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
<title>'.$topic->post->title.'</title>
</head>
<body>
<a href="'.cfg('domain').url('paper/'.$topic->post->tpid).'" target=_blank><strong>'.$topic->post->title.'</strong></a> ('.sg_status_text($topic->post->status).') | <a href="'.cfg('domain').url('paper/'.$topic->post->tpid).'" target=_blank>view</a><br />
Submit by <strong>'.($topic->post->poster?$topic->post->poster.(i()->name && i()->name!=$topic->post->poster?'('.i()->name.')':''):i()->name).(i()->uid?'('.i()->uid.')':'').'</strong> on <strong>'.date('Y-m-d H:i:s').'</strong> | paper id : <strong>'.$topic->post->tpid.'</strong> in ';
	foreach ($topic->tags as $tag) $mail->body.='<strong>'.$tag->name.'</strong> , ';
	$mail->body=trim($mail->body,' , ');
	$mail->body.='<br />
<em>poster host : '.gethostbyaddr(long2ip($topic->post->ip)).' ('.long2ip($topic->post->ip).')</em> '.($photo? ' | <strong>'.count($photo).'</strong> Photo(s).':'').'
<hr size=1>'.
sg_text2html($topic->post->body).'
<hr /><p>เมล์ฉบับนี้เป็นการส่งเมล์อัตโนมัติจากการตั้งหัวข้อใหม่ในเว็บไซท์ '.cfg('domain').'</p><p>หากท่านไม่ต้องการให้มีการส่งเมล์นี้มาให้ท่านอีกต่อไป กรุณาติดต่อผู้ดูแลเว็บไซท์เพื่อทำการยกเลิก</p>
</body>
</html>';

		foreach ($send_to as $to) {
			$to=trim($to);
			if (empty($to)) continue;
			$mail->to=$to;
			$mail->result.=model::sendmail($mail).'<br /><br />';
		}
		return $mail;
	}

} // end of class model
?>