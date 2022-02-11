<?php
/**
* Flood Chat Post
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_chat_post($self) {
	$ret = array();
	$ret['msg'] = 'กรุณาป้อนข้อความ';
	$ret['error'] = NULL;
	$ret['html'] = NULL;

	$post = (object)post();
	//$ret['msg'].=print_o($post,'$post');

	//$ret['msg'] .= print_o($post,'$post');
	//$ret['msg'] .= print_o($_FILES,'$_FILES');
	//return print_o($_FILES,'$_FILES');
	if ($post->msg || $post->station) {
		$post->uid = i()->uid;
		$post->msg = $post->msg;
		$post->where = $post->where;
		$post->when = $post->when;
		if ($post->location) {
			$post->location = 'func.POINT('.$post->location.')';
		} else {
			$post->location = NULL;			
		}
		$post->photo = '';
		$post->parent = SG\getFirst($post->parent,NULL);
		$post->created = date('U');
		$post->priority = SG\getFirst($post->priority,0);
		$post->staffflag = SG\getFirst($post->flag,NULL);
		if ($post->station) {
			$post->sensorvalue = $post->waterlevel;
			$post->when = $post->recorddate?sg_date($post->recorddate,'Y-m-d').' '.$post->recordtime:date('Y-m-d H:i:s');
			$post->sensorname = 'waterlevel';
			$post->source = SG\getFirst($post->source,'user');
		} else {
			$post->station = NULL;
			$post->sensorvalue = NULL;
			$post->source = NULL;
		}
		if ($_FILES) {
			$folder = _FLOOD_UPLOAD_FOLDER.'photo/';

			$photo = (object)$_FILES['photoimg'];

			if (is_uploaded_file($photo->tmp_name)) {
				$photo->name = date('YmdHi').'-'.uniqid().'.jpg';
				$photo->overwrite = true;
				$photo->maxFileSize = 100;
				$photo->resizeWidth = 800;
				$photo->resizeQuality = 40;
				$result = R::Model('photo.save',$photo,$folder);
				if ($result->complete && $result->save->_file) {
					$post->photo = $result->save->_file;
				}
			}
			$ret['html'] .= $folder;
		}
		$stmt = 'INSERT INTO %flood_event%
				(`station`, `parent`, `uid`, `priority`, `source`, `staffflag`, `sensorvalue`, `msg`, `where`, `when`, `location`,  `photo`, `created`)
			VALUES
				(:station, :parent, :uid, :priority, :source, :staffflag, :sensorvalue, :msg, :where, :when, :location, :photo, :created)';
		mydb::query($stmt,$post);

		$post->eid = mydb()->insert_id;

		if ($post->station && $post->staffflag) {
			$stmt = 'UPDATE %flood_station% SET `staffflag`=:staffflag WHERE `station`=:station LIMIT 1';
			mydb::query($stmt,$post);
		}
		if ($post->station && $post->photo) {
			$stmt = 'UPDATE %flood_station% SET `last_photo`=:photo WHERE `station`=:station LIMIT 1';
			mydb::query($stmt,$post);	
		}

		//$ret['html'].=mydb()->_query.print_o($post,'$post');

		$ret['msg'] = '<img class="-hidden" src="/library/img/none.gif?closewebview=YES" />บันทึกเรียบร้อย';

		$post->name = i()->name;
		$post->username = i()->username;
		$post->stationTitle = mydb::select('SELECT `title` FROM %flood_station% WHERE `station`=:station LIMIT 1',':station',$post->station)->title;

		// Update Google Firebase
		$firebaseCfg = cfg('firebase');
		$firebase = new Firebase($firebaseCfg['projectId'], $firebaseCfg['flood'].'chat');
		$data = array(
			'eventId' => $post->eid,
			'parentId' => $post->parent,
			'msg' => $post->msg,
			'photo' => $post->photo,
			'url'=>_DOMAIN.$post->url,
			'thumb'=>_DOMAIN.$thumburl,
			'date'=>sg_date($post->created,'ว ดด ปป'),
			'time'=>sg_date($post->created,'H:i'),
			'timestamp'=>array('.sv'=>'timestamp')
		);
		$fbresult = $firebase->post($data);


		if ($post->parent) {
			$ret['html'] .= R::View('flood.chat.comment.render',$post);
		} else {
			$ret['html'] .= R::View('flood.chat.render',$post);
			//if (i()->username=='softganz') $ret['html'].=print_o($post,'$post').print_o($result,'$result').print_o($_FILES,'$_FILES');
		}
	}
	return $ret;
}
?>