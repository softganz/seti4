<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function project_app_activity_show($self) {
	project_model::init_app_mainpage();

	$ret='';
	$stmt='SELECT
			  tr.`tpid`
			, tr.`trid`
			, tr.`uid`
			, u.`username`
			, u.`name` `poster`
			, t.`title`
			, tr.`text4` `real_work`
			, tr.`calid`
			, c.`title` `activityTitle`
			, tr.`date1` `action_date`
			, GROUP_CONCAT(DISTINCT pf.`fid`, "|" , pf.`file`) photos
			, t.`view`
			, (SELECT COUNT(*) FROM %project_tr% a WHERE a.`tpid`=tr.`tpid` AND `formid`="activity") activitys
			, tr.`created`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
			LEFT JOIN %users% u ON u.`uid`=tr.`uid`
			LEFT JOIN %topic_files% pf
				ON tr.`gallery` IS NOT NULL AND pf.`tpid`=tr.`tpid` AND pf.`gallery`=tr.`gallery` AND pf.`type`="photo"
		WHERE `formid`="activity"
		GROUP BY `trid`
		ORDER BY `trid` DESC
		LIMIT 50';

	$dbs=mydb::select($stmt);

	$ret.='<div class="card">'._NL;
	foreach ($dbs->items as $rs) {
		$isEdit=user_access('administer projects','edit own project content',$rs->uid);
		$ret.='<div class="carditem -activity">'._NL;
		$ret.='<div class="owner">';
		$ret.='<span class="owner-photo"><img class="owner-photo" src="'.model::user_photo($rs->username).'" width="32" height="32" alt="'.$rs->poster.'" /></span>';
		$ret.='<span class="owner-name">';
		$ret.=($rs->username?'<a class="sg-action" href="'.url('profile/'.$rs->uid).'" data-rel="#main">':'').$rs->poster.($rs->username?'</a>':'');
		$ret.='</span>';
		$ret.='<span class="created">'.sg_date($rs->created,'ว ดด ปป').' at '.sg_date($rs->created,'H:i').'</span>';
		$ret.='</div><!-- owner -->'._NL;

		if ($isEdit) {
			//$ret.='<div style="text-align:right;"><a class="sg-action" href="'.url().'" data-removeparent="carditem"><i class="icon -delete"></i></a></div>';
		}

		$ret.='<h3 class="title"><a href="'.url('project/app/view/'.$rs->tpid).'">'.$rs->title.'</a></h3>'._NL;
		if ($rs->activityTitle) $ret.='<h4 class="subtitle">'.$rs->activityTitle.'</h4>'._NL;
		$ret.='<div class="timestamp">'.sg_date($rs->action_date,'ว ดด ปป').'</div>';
		$ret.='<div class="summary">'._NL.sg_text2html($rs->real_work)._NL.'</div>'._NL;


		if (debug('method')) $ret.=$rs->photos.print_o($rs,'$rs');
		if ($rs->photos) {
			$photoList=explode(',',$rs->photos);
			$ret.='<div class="photo">'._NL;
			$ret.='<ul class="photoitem -count'.(count($photoList)<5?count($photoList):5).'">'._NL;
			//$ret.='<p>Photo : '.$rs->photos.'</p>';
			//$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title` FROM %topic_files% f WHERE f.`gallery`=:gallery', ':gallery',$rs->gallery);
			foreach ($photoList as $photoIdx=>$item) {
				if ($photoIdx>=5) break;
				list($photoid,$photo)=explode('|',$item);
				$photo=model::get_photo_property($photo);
				$photo_alt='';
				$ret .= '<li>';
				//$ret.='Width='.$photo->_size->width.' Height='.$photo->_size->height;
				//$ret.=print_o($photo,'$photo');
				$ret.='<a class="sg-action" data-group="photo'.$rs->trid.'" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$ret.='<img class="photo -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" width="100%" alt="photo '.$photo_alt.'" ';
				$ret.=' />';
				$ret.='</a>';
				$photomenu=array();
				$ui=new ui();
				if ($is_item_edit) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li">X</a>');
				}
				$ret.=$ui->build();
				/*
				if ($is_item_edit) {
					$ret.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$item->fid),$item->title,$is_item_edit,'text');
				} else {
					$ret.='<span>'.$item->title.'</span>';
				}
				*/
				$ret .= '</li>'._NL;
			}
			$ret.='</ul>'._NL;
			$ret.='</div><!--photo-->'._NL;
		}
		if ($is_item_edit) {
			$ret.='<form method="post" enctype="multipart/form-data" action="'.url('project/edit/tr',array('action'=>'photo','tr'=>$rs->trid)).'"><span class="btn btn-success fileinput-button"><i class="icon-plus icon-white"></i><span>ส่งภาพหรือไฟล์รายงาน</span><input type="file" name="photo" class="inline-upload" /></span></form>';
		}

		$ret.='<div class="status"><span>'.$rs->activitys.' Activitys '.$rs->view.' Views</span></div>'._NL;
		$ret.='<div class="action"><ul><li><a href="javascript:void(0)">Like</a></li><li><a href="javascript:void(0)">Comment</a></li><li><a href="javascript:void(0)">Share</a></li></ul></div>'._NL;
		$ret.='</div><!-- carditem -->'._NL;
	}
	$ret.='</ul>';
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>