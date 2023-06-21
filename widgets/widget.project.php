<?php
/**
 * Widget widget_project
 *
 * @package core
 * @version 0.01
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2013-05-17
 * @modify 2013-05-17
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 *
 * Draw content list in any format
 *
 * @param Argument list in many format
 *
 * @param Integer data-limit					Default = 5
 * @param String data-show-style					Value = ul					Default = ul
 * @param String sdata-how-readall					Format = text:url , 	Example show-readall=text:url
 * @param String data-show-dateformat		Value = d-m-Y H:i		Default = config date format
 * @param Integer data-show-photo-width			Default = 100
 * @param Integer data-show-photo-height			Default = 80
 *
 * @return String $ret
 *	@usage widget::content(['para1=value1'[,[para2=value2][para3,value3]...)
 * @example <div class="widget project" data-limit="20" data-header="Project Activities" data-footer="By SoftGanz"></div>
 */
function widget_project() {
	$para = para(func_get_args(),'data-limit=5','data-show-style=ul','data-show-photo-width=100','data-show-photo-height=80');
	$dateformat = \SG\getFirst($para->{'data-show-dateformat'},cfg('dateformat'));

	$projectId = \SG\getFirst($para->{'data-projectId'}, $para->{'data-projectid'});
	$projectSet = \SG\getFirst($para->{'data-set'});

	mydb::where('tr.`formid` = "activity" AND tr.`part` IN ("owner","trainer")');
	mydb::where('t.`status` IN ( :status )', ':status', [_PUBLISH, _LOCK]);
	// mydb::where('(t.`status` = :publish OR t.`status` = :lock)', ':publish', _PUBLISH, ':lock', _LOCK);

	if ($projectId) {
		// mydb::where('p.`tpid` IN ( :projectId )', ':projectId', 'SET:'.$projectId);
		mydb::where('tr.`tpid` IN ( :projectId )', ':projectId', 'SET:'.$projectId);
	}

	if ($projectSet) {
		// mydb::where('(p.`projectset` IN ( :projectset ) OR t.`parent` IN ( :projectset ))', ':projectset', 'SET:'.$projectSet);
		mydb::where('(t.`parent` IN ( :projectset ))', ':projectset', 'SET:'.$projectSet);
	}

	mydb::value('$LIMIT$', 'LIMIT '.$para->{"data-limit"});

	$dbs = mydb::select(
		'SELECT
		action.`trid`, action.`calid`, action.`tpid`
		, p.`projectset`, t.`title`
		, c.`title` `actionTitle`
		, action.`gallery`
		, action.`outputOutcome`, action.`actionDetail`
		, action.`created`
		, GROUP_CONCAT(DISTINCT f.`file`) photos
		FROM (
			SELECT tr.*
				, tr.`text4` `outputOutcome`, tr.`text2` `actionDetail`
			FROM %project_tr% tr
				LEFT JOIN %topic% t ON t.`tpid` = tr.`tpid`
			%WHERE%
			ORDER BY tr.`trid` DESC
			$LIMIT$
		) `action`
			LEFT JOIN %topic% t ON t.`tpid` = action.`tpid`
			LEFT JOIN %project% p ON p.`tpid` = t.`tpid`
			LEFT JOIN %calendar% c ON c.`tpid` = action.`tpid` AND c.`id` = action.`calid`
			LEFT JOIN %topic_files% f ON f.`tpid` = action.`tpid`
				AND f.`gallery` = action.`gallery` AND f.`type` = "photo"
				AND (f.`tagname` IS NULL OR f.`tagname` LIKE "project,action")
		GROUP BY `trid`
		ORDER BY `trid` DESC
		'
	);
	// if (i()->username == 'softganz') {
		// debugMsg($para, '$para');
		// debugMsg(mydb()->_query);
		// debugMsg($dbs, '$dbs');
	// }

	// return [$para->{"data-limit"}, $para];

	if ($dbs->_empty) return [$ret,$para];

	$tagName = $para->{'data-show-style'};

	$cardUi = new Ui([
		'tagName' => $tagName,
		'class' => 'ui-card topic-list'.($para->{'data-class'} ? ' '.$para->{'data-class'} : ''),
		'children' => array_map(
			function ($rs) {
				list($photo) = explode(',', $rs->photos);
				$linkUrl = url('project/'.$rs->tpid.'/action.view/'.$rs->trid);
				$linkTitle = htmlspecialchars($rs->title);

				return [
					'text' => ($photo ? '<a href="'.$linkUrl.'" title="'.$linkTitle.'"><img class="photo" src="'.cfg('paper.upload.photo.url').$photo.'" width="'.$para->{"data-show-photo-width"}.'" height="'.$para->{"data-show-photo-height"}.'" alt="'.$linkTitle.'" /></a>' : '')
						. '<h3><a href="'.$linkUrl.'" title="'.$linkTitle.'">'.$rs->title.'</a></h3>'
						. '<span class="summary">'
						. ($rs->actionTitle ? '<span class="-subtitle">'.$rs->actionTitle.' : </span>' : '')
						. '<span class="-output">'.trim(strip_tags(sg_text2html($rs->outputOutcome ? $rs->outputOutcome : $rs->actionDetail)))
						. '</span>'
						. '<span class="timestamp">@'.sg_date($rs->created,'d ดด ปป H:i').'</span>'._NL,
					'options' => [
						'class' => 'sg-action'
							. ($para->{'data-item-class'} ? ' '.$para->{'data-item-class'} : ''),
						'href' => $linkUrl,
					]
				];
			}, $dbs->items
		), // children
	]);

	$ret .= $cardUi->build()._NL;

	if ($para->{'show-readall'}) {
		list($readalltext, $readallurl) = explode(':',$para->{'show-readall'});
		$ret .= '<p class="readall"><a href="'.url($readallurl).'">'.$readalltext.'</a></p>';
	}
	if ($para->{'data-footer'}) $ret .= $para->{'data-footer'}._NL;
	return [$ret, $para];
}
?>