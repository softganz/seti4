<?php
/**
* Paper   :: Render Paper Content
* Created :: 2023-07-24
* Modify  :: 2023-07-24
* Version :: 1
*
* @param Object $$topicInfo
* @param Object $options
* @return String
*
* @usage R::View('paper.content.prepare')
*/

import('widget:comment.render.php');

function view_paper_content_prepare($topicInfo, $options = '{}') {
	// Prepare body text by input_format condition
	$topicInfo->info->body=str_replace('<!--break-->','',$topicInfo->info->body);
	$fb_url=cfg('domain').url('paper/'.$topicInfo->tpid);
	$fb_url=preg_replace('/^http\:\/\/www./i','http://',$fb_url);
	$body = (Object)[];

	/*
	preg_match_all('/<page>(.+?)/sm',$topicInfo->info->body,$out);

	$widget_ldq='[page]';
	$widget_rdq='';
	preg_match_all('/\[page\](*)/msU',$topicInfo->info->body,$out);
	*/
	$out=explode('<!-- more -->',$topicInfo->info->body);


	//		preg_match_all('/<!-- page\s(\d)(.*)/',$topicInfo->info->body,$out);

	$pageCount=count($out);
	$currentPage=intval($_GET['p']);
	if ($currentPage==0) $currentPage=1;
	if ($pageCount>1) {
		$topicInfo->info->body=trim($out[$currentPage-1]);
		$ui=new ui();
		for ($i=1;$i<=$pageCount;$i++) $ui->add('<a href="'.($i==1?url(q()):'?p='.$i).'">หน้า '.$i.'</a>');
		$page='<p class="page" align="right">'.$ui->build().'</p>';
	}
	//		$body->a.=$page;
	//		$body->a.='Page count='.$pageCount.' Current Page='.$currentPage.'<br />Body='.htmlspecialchars($topicInfo->info->body).'<hr />';
	//		$body->a.=print_o($out,'$out');
	//		$body->a.='<hr />';
	switch ($topicInfo->property->input_format) {
		case 'html' : /* do nothing */; break;
		case 'php' :
			if (cfg('topic.allow.php')) {
				ob_start();
				eval ('?>' . $topicInfo->info->body);
				$topicInfo->info->body = ob_get_clean();
			}
			break;
		default : $topicInfo->info->body=sg_text2html($topicInfo->info->body);break;
	}

	// move style tag to head section
	if (preg_match_all('/<style.*?>.*?<\/style>/si',$topicInfo->info->body,$out)) {
		foreach ($out[0] as $style) head($style);
		$topicInfo->info->body=preg_replace('/(<style.*?>.*?<\/style>)/si','',$topicInfo->info->body);
	}

	// show timestamp
	if ($topicInfo->property->option->timestamp) {
		$body->timestamp = '<div class="timestamp">';
		$body->timestamp .= 'by <span class="poster'.($topicInfo->uid==i()->uid?' owner':'').'">';
		$body->timestamp .= $topicInfo->uid && user_access('access user profiles')?'<a href="'.url('profile/'.$topicInfo->uid).'" title="view poster profile" data-hovercard="'.url('hovercard/uid/'.$topicInfo->uid).'">'.\SG\getFirst($topicInfo->info->poster,$topicInfo->info->owner).'</a>' : \SG\getFirst($topicInfo->info->poster,$topicInfo->info->owner);
		$body->timestamp .= '</span> ';
		$body->timestamp .= '<span class="timestamp">@'.sg_date($topicInfo->info->created,cfg('dateformat')).'</span>';
		$body->timestamp .= '<span class="ip"> ( IP : '.(user_access('administer contents,administer '.$topicInfo->info->type.' content') ? '<a href="'.url('paper/list', ['ip' => long2ip($topicInfo->info->ip)]).'">'.long2ip($topicInfo->info->ip).'</a>' : sg_sub_ip($topicInfo->info->ip)).' )</span>';

		if ($topicInfo->tags) {
			foreach ($topicInfo->tags as $tag ) $tags[] = '<a href="'.url('tags/'.$tag->tid).'">'.$tag->name.'</a>';
			$body->timestamp .= ' | <span class="tags">Tags : '.implode(' , ',$tags).'</span>';
		}
		$body->timestamp .= '</div>'._NL._NL;
	}
	if ($topicInfo->info->status==_WAITING) $body->message=message('status','หัวข้อนี้อยู่ในสถานะรอการตรวจสอบ : หัวข้อนี้จะยังไม่มีการแสดงผลในหน้าเว็บไซท์ จนกว่าจะได้รับการยืนยันความถูกต้องจากผู้ที่มีสิทธิ์ในการดูแลเนื้อหาของเว็บไซท์');

		//		if ($topicInfo->property->option->container) $body->container='<div class="body" id="paper-body">'._NL;

	if ($topicInfo->video->file && $topicInfo->property->option->show_video) {
		$autostart='false';
		$flashvar='file='.$topicInfo->video->_url.'&autostart='.$autostart.'&stretching=exactfit'.($topicInfo->photo->items[0]->_src?'&amp;image='.$topicInfo->photo->items[0]->_src:'');
		$player='<object type="application/x-shockwave-flash" width="100%" height="100%" data="https://softganz.com/library/mediaplayer.swf?'.$flashvar.'" >
		<param name="movie" value="https://softganz.com/library/mediaplayer.swf?'.$flashvar.'" width="100%" height="100%" />
		<embed src="https://softganz.com/library/mediaplayer.swf" width="100%" height="100%" flashvars="'.$flashvar.'" />
		</object>';

		$body->video='<div id="online-vdo">'.$player.'</div>';
		if (cfg('topic.video.doanloadable')) $body->video.='<div id="online-video-doanload"><a href="'.$topicInfo->video->_url.'">ดาวน์โหลดไฟล์วีดิโอ</a></div>';
		if ($topicInfo->photo->_num_rows) {
			--$topicInfo->photo->_num_rows;
			array_shift($topicInfo->photo->items);
		}
	}

	if ($topicInfo->info->redirect) $body->redirect='<p class="redirect">Redirect &raquo; <a href="'.$topicInfo->info->redirect.'">'.$topicInfo->info->redirect.'</a></p>';

	// show photo with cfg value
	//$body->photo .= print_o($topicInfo->photos,'$topicInfo->photos');
	//debugMsg($topicInfo->info->property);
	//debugMsg($topicInfo->property, '$topicInfo->property');

	if ($topicInfo->photos && $topicInfo->property->show_photo!='no') {
		if ($topicInfo->property->show_photo == 'slide') {
			$body->photo .= _NL.'<!-- show photo -->'._NL;
			$body->photo .= view::photo_slide('paper-slide',$topicInfo->property->slide_width,$topicInfo->property->slide_height,url('get/photoslide/'.$topicInfo->tpid.'/imagerotator'));
		} else {
			$is_single_photo = count($topicInfo->photos) == 1 || $topicInfo->property->show_photo == 'first';
			if (is_numeric($topicInfo->property->show_photo)) {
				$topicInfo->property->show_photo=intval($topicInfo->property->show_photo);
			}

			$no = 1;
			$body->photo .= _NL.'<!-- show photo -->'._NL;
			$body->photo .= '<div class="photo photo-'.($is_single_photo?'single':'multiple').($is_single_photo?(' photo-'.($topicInfo->photos[0]->width > $topicInfo->photos[0]->height ? 'wide' : 'tall')):'').'">'._NL;

			if (!$is_single_photo) $body->photo .= '<ul>'._NL;

			foreach ($topicInfo->photos as $photo) {
				$photo_alt = $photo->pic_photo_file.' , '.$photo->width.'x'.$photo->height.' pixel , '.number_format($photo->size).' bytes.';
				if (!$is_single_photo) $body->photo.='<li>'._NL;
				$body->photo .= '<a href="'.$photo->url.'" class="sg-action" data-rel="img" data-group="topic">';
				$body->photo .= '<img class="photo photo-'.($photo->width > $photo->height ? 'wide' : 'tall').'" src="'.$photo->url.'" alt="photo '.$photo_alt.'" ';

				if ($photo->width < cfg('topic.photo.'.($is_single_photo ? 'single' : 'multiple').'.width')) {
					$body->photo .= ' style="width:'.$photo->width.'px;"';
				}
				$body->photo .= ' />';
				$body->photo .= '</a>'._NL;
				if ($photo->pic_name || $photo->pic_desc) {
					$body->photo .= '<p>';
					if ($photo->pic_name) $body->photo .= $photo->pic_name;
					if ($photo->pic_description) $body->photo .= ($photo->pic_name?' : ':'').$photo->pic_desc;
					$body->photo .= '</p>';
				}
				if (!$is_single_photo) $body->photo .= '</li>'._NL;
				$no++;
				if (is_numeric($topicInfo->property->show_photo) && $no>$topicInfo->property->show_photo) break;
				if ($topicInfo->property->show_photo=='first') break;
			}
			if (!$is_single_photo) $body->photo .= '</ul>'._NL;
			$body->photo .= '</div><!--photo-->'._NL._NL;
		}
	}



	// Show detail
	if ($topicInfo->property->option->container) $body->detail='<div class="detail">'._NL;

	// Show photo in detail section
	if (cfg('topic.photo.in_detail_section')) {
		$body->detail.=$body->photo;
		unset($body->photo);
	}
	if (module_install('voteit')) $body->detail.=do_class_method('voteit','node',$topicInfo);

	if ($topicInfo->property->option->container) $body->detail.='<div class="detail-body">'._NL;
	$body->detail.=$topicInfo->info->body;
	if ($topicInfo->property->option->container) $body->detail.='</div>'.($pageCount?$page:'').'<!-- detail-body -->'._NL;

	if ($topicInfo->poll) {
		$body->detail.='<div id="detail-poll" class="sg-load" data-url="poll/view/'.$topicInfo->tpid.($_POST['poll']['choice']?'?vote='.$_POST['poll']['choice']:'').'"></div><!-- detail-poll -->'._NL;
	}

	// show reference file list
	if ($topicInfo->property->option->docs) {
		//$body->detail .= R::Page('paper.info.file', NULL, $topicInfo);
		$docs = mydb::select('SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "doc" AND `tagname` IS NULL ', ':tpid', $topicInfo->tpid);
		if ($docs->_num_rows) {
			$body->detail .= '<div class="docs"><strong>ไฟล์ประกอบเอกสาร</strong>'
				. '<ol>';
			foreach ($docs->items as $doc) {
				if (cfg('files.log')) {
					$body->detail .= '<li><a href="'.url('files/'.$doc->fid).'">'.\SG\getFirst($doc->title,$doc->file).' - '.tr('Download').'</a></li>';
				} else {
					$body->detail .= '<li><a href="'.cfg('url').'upload/forum/'.sg_urlencode($doc->file).'" target=_blank>'.\SG\getFirst($doc->title,$doc->file).' - '.tr('Download').'</a></li>';
				}
			}
			$body->detail .= '</ol></div>';
		}
	}

	if (cfg('paper.footer')) $body->detail.=cfg('paper.footer');

	// Show ad in topic content
	$showAd=false;
	if ($topicInfo->property->option->ads) {
		//cfg('ad.tags','46'); // for test ad

		//debugMsg('ADS '.cfg('ad.type').' || '.cfg('ad.tags'));
		//debugMsg($topicInfo,'$topicInfo');

		$adTagList = explode(',', cfg('ad.tags'));
		$topicTagList = array();
		foreach ($topicInfo->tags as $value) $topicTagList[] = $value->tid;
		$adInTag = array_intersect($adTagList, $topicTagList);

		//debugMsg($adTagList,'$adTagList');
		//debugMsg($topicTagList,'$topicTagList');
		//debugMsg($adInTag,'$adInTag');

		if (cfg('ad.type') || cfg('ad.tags')) {
			if ($adInTag) {
				$showAd = true;
			} else if (cfg('ad.type') && in_array($topicInfo->info->type, explode(',',cfg('ad.type'))) ) {
				$showAd = true;
			}
		} else {
			$showAd=true;
		}
	}

	if (!$showAd) head('googlead','<script></script>');

	if ($showAd && isset($GLOBALS['ad']->detail_bottom)) {
		$body->detail.='<div id="ad-detail_bottom" class="ads">'.$GLOBALS['ad']->detail_bottom.'</div>';
	}

	if ($topicInfo->property->option->container) $body->detail.='</div><!--detail-->'._NL._NL;

	// show footer
	if ($topicInfo->property->option->footer) {

		$body->footer .= '<nav id="paper-footer" class="nav paper -footer">';
		$body->footer .= '<span class=""><b>'.$topicInfo->info->view.'</b> views ';
		if ($topicInfo->info->reply) $body->footer.='<b>'.$topicInfo->info->reply.'</b> comments @'.sg_date($topicInfo->info->last_reply,cfg('dateformat'));
		$body->footer .= '</span>';
		if (cfg('email.delete_message')) {
			$body->footer .= '&nbsp;&nbsp;<a class="sg-action btn -link" href="'.url('paper/senddelete/'.$topicInfo->tpid).'" data-rel="box" data-width="480"><i class="icon -material -gray">report</i><span>แจ้งลบหัวข้อ</span></a>';
		}
		$body->footer.='</nav><!--footer-->'._NL._NL;
	}

	// Show social network button
	if ( /*_ON_HOST &&*/
		in_array($topicInfo->info->type,explode(',',cfg('social.share.type')))
		&& !is_home()
		&& $topicInfo->property->option->social) {
		$body->social .= view::social(url('paper/'.$topicInfo->tpid));
		cfg('social.googleplus',true);
	}



	// show related topic
	if ($topicInfo->info->type) {
		$relateStr = R::View($topicInfo->info->type.'.relatetopic', $topicInfo);
		$body->relate = $relateStr ? $relateStr : R::View('paper.relatetopic', $topicInfo);
	}


	if ($showAd && isset($GLOBALS['ad']->detail_footer)) $body->ad_detail_footer.='<div id="ad-detail_footer" class="ads">'.$GLOBALS['ad']->detail_footer.'</div>';
		//		if ($topicInfo->property->option->container) $body->container_close .= '</div><!--body-->'._NL._NL;



	// show comment message and form
	if (!$topicInfo->property->option->fullpage) $body->comment.='<a name="comment"></a>'._NL;
	if (user_access('access comments')) {
		$body->comment = '<div class="paper -comment web-comment">';
		// Show reply
		if ($topicInfo->info->reply) {
			$body->comment .= (new CommentRenderWidget(['node' => $topicInfo, 'options' => $options, 'archive' => $archive]))->build()->build();
			// R::View('paper.comment.draw',$topicInfo,$options,$archive);
			if ($showAd && isset($GLOBALS['ad']->comment_after)) $body->comment .= '<div id="ad-comment_after" class="ads">'.$GLOBALS['ad']->comment_after.'</div>';
		}

		if (in_array($topicInfo->info->type,explode(',',cfg('social.comment.facebook.type')))) {
			$body->comment .= '<div class="fb-comments" data-href="'.$fb_url.'" data-numposts="'.cfg('social.comment.facebook.posts').'" data-width="'.cfg('social.comment.facebook.width').'" data-colorscheme="light"></div>';
		}
		// Show comment form and comment ad

		if (!$topicInfo->archived && $topicInfo->info->comment == _COMMENT_READWRITE) {
			if ($showAd && isset($GLOBALS['ad']->comment_form)) $body->comment_form .= '<div id="ad-comment_form" class="ads">'.$GLOBALS['ad']->comment_form.'</div>';
			$body->comment .= R::View('paper.comment.form',$topicInfo);
		}
		$body->comment .= '</div>';
	}

	//debugMsg($topicInfo,'$topicInfo');
	return $body;
}
?>