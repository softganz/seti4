<?php
/**
* Paper   :: Edit Tags
* Created :: 2019-06-02
* Modify  :: 2024-03-20
* Version :: 2
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_tag($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');
	if (!$topicInfo->right->edit) return message('error', 'Access Denied');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>Tag Management</h3></header>';


	if (!user_access('administer contents,administer papers,administer paper tags')) return message('error','Access denied');

	if (post('vocab')) {
		$ret = __paper_edit_tag_list_vocab($topicInfo, post('vocab'));
		return $ret;
	} else if (post('show') == 'current') {
		$ret = __paper_edit_tag_current($topicInfo);
		return $ret;
	}

/*
	// remove old tag
	if ($para->remove) {
		mydb::query('DELETE FROM %tag_topic% WHERE tid=:tid AND tpid=:tpid',':tid',$para->remove,':tpid',$topic->tpid);
		foreach ($topic->tags as $k=>$tag) if ($tag->tid==$para->remove) unset($topic->tags[$k]);
	}

	// add new tag to topic
	if ($para->tag) {
		$oldtags=mydb::select('SELECT tp.tid,tp.vid,v.type FROM %tag_topic% tp LEFT JOIN %vocabulary_types% v ON v.vid=tp.vid WHERE tp.tpid=:tpid',':tpid',$topic->tpid);

		$add_tag=BasicModel::get_taxonomy($para->tag);
		mydb::query('INSERT INTO %tag_topic% (`tpid`,`vid`,`tid`) VALUES (:tpid, :vid, :tid)',':tpid',$topic->tpid,':vid',$add_tag->vid,':tid',$para->tag);
		if (mydb()->affected_rows==1) {
			$topic->tags[]=(object)array('tid'=>$para->tag,'name'=>$add_tag->name);
			// Change content type on topic that no tag
			if ($oldtags->_empty) {
				$newtags=mydb::select('SELECT tg.tid,tg.vid,v.type FROM %tag% tg LEFT JOIN %vocabulary_types% v ON v.vid=tg.vid WHERE tg.tid=:tid',':tid',$para->tag);
				if ($newtags->items[0]->type) {
					mydb::query('UPDATE %topic% SET `type`=:type WHERE `tpid`=:tpid LIMIT 1',':tpid',$topic->tpid,':type',$newtags->items[0]->type);
				}
			}
		}
	}

	*/

	$ret .= '<div class="-sg-flex -co-2">';

	$ret .= '<div id="tags-current">';
	$ret .= __paper_edit_tag_current($topicInfo);
	$ret .= '</div>'._NL;

	$ret .= '<div id="tags-remain" style="height: 600px; overflow: scroll;">'._NL;
	$ret .= __paper_edit_tag_list_vocab($topicInfo);
	$ret .= '</div>'._NL;

	$ret .= '</div>';

	//$ret .= print_o($topicInfo->tags,'tags');
	return $ret;
}

function __paper_edit_tag_current($topicInfo) {
	$tpid = $topicInfo->tpid;

	$ret .= '<h3>Current tags</h3>';
	$tables = new Table();

	$tables->addClass('topic-current-tags');
	$tables->thead = array('ID', 'name  -hover-parent' => 'Tag Name');
	foreach ($topicInfo->tags as $tag) {
		$tables->rows[] = array($tag->tid,
			$tag->name.' :: '.$tag->vocab_name
			. '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('api/paper/'.$tpid.'/tag.remove/'.$tag->tid).'" data-rel="#tags-remain" data-ret="'.url('paper/'.$tpid.'/edit.tag',array('vocab'=>$tag->vid)).'" data-removeparent="tr"><i class="icon -material -gray">cancel</i></a></nav>'
		);
	}

	$ret .= $tables->build();

	return $ret;
}

function __paper_edit_tag_list_vocab($topicInfo, $vocabId = NULL) {
	$tpid = $topicInfo->tpid;

	$vocabs = BasicModel::get_vocabulary();

	if (count($vocabs->items) == 1) $vocabId = reset($vocabs->items)->vid;
	$ret .= '<h3>Unused tags</h3>';

	$tables = new Table();
	$tables->thead = array('ID', 'name  -hover-parent' => 'Unused Tag Name');
	foreach ($vocabs->items as $vocab) {
		$tables->rows[] = array(
											'<td colspan="2" class="-hover-parent"><a class="sg-action -fill" href="'.url('paper/'.$tpid.'/edit.tag',array('vocab'=>$vocab->vid)).'" data-rel="#tags-remain"><b>'.$vocab->name.'</b></a>'
										. '<nav class="nav -icons -hover"><i class="icon -material">keyboard_arrow_right</i></nav>'
										. '</td>'
									);

		if (isset($vocabId) && $vocabId == $vocab->vid) {
			$tree = BasicModel::get_taxonomy_tree($vocabId);
			foreach ($tree as $term) {
				if ($term->process==-1) continue;
				if (isset($topicInfo->tags[$term->tid])) continue;
				else if ($term->process==1) {
					$tables->rows[] = array('','<strong>'.$term->name.'</strong>');
				} else {
					$tables->rows[] = array(
								$term->tid,
								'<a class="sg-action -fill" href="'.url('api/paper/'.$tpid.'/tag.add', ['vocab'=>$vocab->vid,'tag'=>$term->tid]).'" data-rel="notify" data-done="remove:parent tr | load:#tags-current:'.url('paper/'.$tpid.'/edit.tag', ['show'=>'current']).'">'.str_repeat('--', $term->depth).' '.$term->name.'</a>'
								. '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('api/paper/'.$tpid.'/tag.add', ['vocab'=>$vocab->vid,'tag'=>$term->tid]).'" data-rel="notify" data-done="remove:parent tr | load:#tags-current:'.url('paper/'.$tpid.'/edit.tag',['show'=>'current']).'"><i class="icon -material -gray">add_circle</i></a></nav>',
							);
				}
			}
			$ret.='</ul>'._NL;
		}
		$ret.='</li>'._NL;
	}
	$ret .= $tables->build();
	return $ret;
}
?>