<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_event_post($self) {
	$ret['msg']='กรุณาป้อนข้อความ';
	$ret['error']=NULL;
	$ret['html']=NULL;
	$post=(object)post();
	//$ret['msg'].=print_o($post,'$post');
	if ($post->msg || $post->station) {
		$post->uid=i()->uid;
		$post->msg=$post->msg;
		$post->where=$post->where;
		$post->when=$post->when;
		$post->photo='';
		$post->parent=SG\getFirst($post->parent,NULL);
		$post->created=date('U');
		$post->priority=SG\getFirst($post->priority,0);
		$post->staffflag=SG\getFirst($post->flag,NULL);
		if ($post->station) {
			$post->sensorvalue=$post->waterlevel;
			$post->when=$post->recorddate?sg_date($post->recorddate,'Y-m-d').' '.$post->recordtime:date('Y-m-d H:i:s');
			$post->sensorname='waterlevel';
			$post->source=SG\getFirst($post->source,'user');
		} else {
			$post->station=NULL;
			$post->sensorvalue=NULL;
			$post->source=NULL;
		}
		if ($_FILES) {
			$folder=_FLOOD_UPLOAD_FOLDER.'photo/';

			$photo=(object)$_FILES['photoimg'];

			if (is_uploaded_file($photo->tmp_name)) {
				$photo->name=date('YmdHi').'-'.uniqid().'.jpg';
				$photo->overwrite=true;
				$photo->maxFileSize=100;
				$photo->resizeWidth=800;
				$photo->resizeQuality=40;
				$result = R::Model('photo.save',$photo,$folder);
				if ($result->complete && $result->save->_file) {
					$post->photo=$result->save->_file;
				}
			}
		}
		$stmt='INSERT INTO %flood_event%
							(`station`, `parent`, `uid`, `priority`, `source`, `staffflag`, `sensorvalue`, `msg`, `where`, `when`,  `photo`, `created`)
						VALUES
							(:station, :parent, :uid, :priority, :source, :staffflag, :sensorvalue, :msg, :where, :when, :photo, :created)';
		mydb::query($stmt,$post);
		$post->eid=mydb()->insert_id;

		if ($post->station && $post->staffflag) {
			$stmt='UPDATE %flood_station% SET `staffflag`=:staffflag WHERE `station`=:station LIMIT 1';
			mydb::query($stmt,$post);
		}
		if ($post->station && $post->photo) {
			$stmt='UPDATE %flood_station% SET `last_photo`=:photo WHERE `station`=:station LIMIT 1';
			mydb::query($stmt,$post);	
		}
		//$ret['html'].=mydb()->_query.print_o($post,'$post');
		$ret['msg']='<img class="-hidden" src="/library/img/none.gif?closewebview" />บันทึกเรียบร้อย';
		$post->name=i()->name;
		$post->username=i()->username;
		$post->stationTitle=mydb::select('SELECT `title` FROM %flood_station% WHERE `station`=:station LIMIT 1',':station',$post->station)->title;
		if ($post->parent) {
			$ret['html'] .= R::View('flood.event.render.comment',$post);
		} else {
			$ret['html'] .= R::View('flood.event.render',$post);
			//if (i()->username=='softganz') $ret['html'].=print_o($post,'$post').print_o($result,'$result').print_o($_FILES,'$_FILES');
		}
	}
	return $ret;
}
?>