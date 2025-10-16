<?php
/**
* Paper   :: Show contents
* Created :: 2023-02-08
* Modify  :: 2023-02-08
* Version :: 2
*
* @return Widget
*
* @usage paper/contents
*/

class PaperContents extends Page {
	var $show;

	function __construct() {
		parent::__construct([
			'show' => post('show'),
		]);
	}
	function build() {
		if (!$this->show) {
			$ret .= '
		<style type="text/css"><!--
		#admin_content {margin:0 0 20px 0;font-size:10pt;}
		#admin_content span {color:gray;font-size:8pt;}
		#admin_content ul {margin:0;padding:0 15px;}
		ul#admin_content_tab {width:100%;height: 22px;margin:10px;list-style: none;}
		ul#admin_content_tab li {float: left;margin:0;padding:0 10px;font-size:10pt;border-right:1px gray solid;}
		ul#admin_content_tab li a {display: block;}
		--></style>

		<ul class="tabs">
		<li><a class="sg-action" href="'.url('paper/contents',array('show' => 'last_post')).'" data-rel="#admin_content">Last Content</a></li>
		<li><a class="sg-action" href="'.url('paper/contents',array('show' => 'last_topic')).'" data-rel="#admin_content">Last topic</a></li>
		<li><a class="sg-action" href="'.url('paper/contents',array('show' => 'last_comment')).'" data-rel="#admin_content">Last comment</a></li>
		'.(user_access('access administrator pages')?'<li><a href="'.url('admin/content/topic').'">Contents</a></li>':'').'
		'.(user_access('administer comments')?'<li><a href="'.url('admin/comment/list').'" title="Show all comments">Comments</a></li>':'').'
		<li><a class="sg-action" href="'.url('paper/contents',array('show' => 'not_publish')).'" data-rel="#admin_content">Not publish topic</a></li>
		<li><a class="sg-action" href="'.url('paper/contents',array('show' => 'not_publish_comment')).'" data-rel="#admin_content">Not publish comment</a></li>
		</ul>

		<div id="admin_content" class="contentstyle">'.date('H:i:s');
		}
		$from_date=date('Y-m-d 00:00:00',mktime(0,0,0,date('m')+0,date('d')-1,date('Y')+0));
		$between='"'.$from_date.'" AND "'.date('Y-m-d H:i:s').'"';


		switch ($this->show) {
			case 'last_topic' :
				$ret .= '<strong>Show last topic</strong><br />';
				$sql_cmd='SELECT "topic" as pk,tpid as tpid,status,title,created FROM %topic% WHERE created between '.$between;
				$sql_cmd .= ' ORDER BY tpid DESC';
				$last_post=mydb::select($sql_cmd);
				break;
			case 'last_comment' :
				$ret .= '<strong>Show last comment</strong><br />';
				$sql_cmd='SELECT "comment" as pk,tpid as tpid,cid,status,comment as title,timestamp AS created FROM %topic_comments%  WHERE timestamp between '.$between;
				$sql_cmd .= ' ORDER BY cid DESC';
				$last_post=mydb::select($sql_cmd);
				break;
			case 'not_publish' :
				$ret .= '<strong>Topic not publish</strong><br />';
				$sql_cmd='SELECT "topic" as pk,tpid as tpid,status,title,created FROM %topic% ';
				$sql_cmd .= '  WHERE status in ('._BLOCK.','._DRAFT.') ';
				$sql_cmd .= ' ORDER BY tpid DESC ';
				$last_post=mydb::select($sql_cmd);
				break;
			case 'not_publish_comment' :
				$sql_cmd='SELECT c.*,t.title,t.created FROM %topic_comments% c LEFT JOIN %topic% t on t.tpid=c.tpid WHERE c.status!='._PUBLISH.' ORDER BY t.tpid DESC ';
				$dbs=mydb::select($sql_cmd);
				foreach ($dbs->items as $rs) $topics[$rs->tpid][]=$rs;
				$ret .= '<ul>';
				foreach ($topics as $topic) {
					$title=substr(strip_tags(sg_text2html($topic[0]->title)),0,100);
					$ret .= '<li>';
					$ret .= '<a href="'.url('paper/'.$topic[0]->tpid).'"><b>'.$title.'</b></a> <span>on '.$rs->created.'</span>';
					$ret .= '<ul>';
					foreach ($topic as $rs) $ret .= '<li>'.sg_text2html($rs->comment).'</li>';
					$ret .= '</ul>';
					$ret .= '</li>';
				}
				$ret .= '</ul>';
				break;
			default :
				$ret .= '<strong>Last content</strong><br />';
				$sql_cmd='SELECT "topic" pk, tpid, NULL cid, status, title, created FROM %topic% WHERE created between '.$between;
				$sql_cmd .= ' UNION SELECT "comment" pk, tpid, cid, status, comment title,timestamp AS created FROM %topic_comments%  WHERE timestamp between '.$between;
				$sql_cmd .= ' ORDER BY created DESC';
				$last_post=mydb::select($sql_cmd);
		}

		$ret .= '<ul>';
		//		print_o($last_post,'$last_post',1);
		list($last_date)=explode(' ',$last_post->items[0]->created);
		foreach ($last_post->items as $rs) {
			//			$title=trim(substr(strip_tags(sg_text2html($rs->title)),0,1000));
			$title=trim(sg_text2html($rs->title));
			$title=preg_replace('/<a(|\W[^>]*)>(.*)<\/a>/iusU','\\2',$title);
			list($created)=explode(' ',$rs->created);
			$ret .= '<li>';
			$ret .= '<span><em><strong>'.$rs->pk.'</strong> on '.$rs->created.'</em></span>';
			$ret.='<a href="'.url('paper/'.$rs->tpid.($rs->cid?'/page/last':NULL),NULL,$rs->cid?'comment-'.$rs->cid:NULL).'" style="'.($rs->status==_PUBLISH?'':';text-decoration:line-through;color:#ED6F00;').'">'.($created==$last_date?'<strong>':'').($title?$title:'(paper/'.$rs->tpid.' : empty)').($created==$last_date?'</strong>':'').'</a>';
			$ret.='</li>';
		}
		$ret .= '</ul>';

		if (!$this->show) $ret .= '</div>';

		return new Scaffold([
			// 'appBar' => new AppBar([
			// 	'title' => 'Title',
			// ]), // AppBar
			'body' => new Widget([
				'children' => [$ret], // children
			]), // Widget
		]);
	}
}
?>