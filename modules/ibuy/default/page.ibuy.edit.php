<?php
/**
* Paper Edit Main Page
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function ibuy_edit($self, $productInfo, $action = NULL, $tranId = NULL) {
	if (!$productInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $productInfo->tpid;

	if (!$productInfo) return message('error', 'PRODUCT NOT FOUND.');

	$isAdmin = user_access('administer ibuys');
	$isEdit = $productInfo->RIGHT & _IS_EDITABLE;

	if (!$isEdit) return message('error', 'Access Denied');

	switch ($action) {
		case 'status.update' :
			$ret .= 'UPDATE Status';

			$outofsale = post('outofsale');

			if (in_array($outofsale, array('O','Y','N'))) {
				$status = $outofsale == 'N' ? 1 : 0;

				$stmt = 'UPDATE %ibuy_product% SET `available`=:status, `outofsale`=:outofsale WHERE `tpid`=:tpid LIMIT 1';

				mydb::query($stmt, ':status', $status,':tpid', $tpid, ':outofsale', $outofsale);

				//$ret.=mydb()->_query;

			}
			break;

		case 'info.update':
			if (post('topic')) {
				$post = (Object) post('topic');
				$post->tpid = $tpid;
				$post->revid = $productInfo->info->revid;
				$post->title = trim($post->title);
				$post->body = trim($post->body);
				if ($post->minsaleqty <= 1) $post->minsaleqty = NULL;

				mydb::query('UPDATE %topic% SET `title` = :title WHERE `tpid` = :tpid LIMIT 1', $post);
				//$ret.=mydb()->_query;

				mydb::query('UPDATE %topic_revisions% SET `body` = :body WHERE `revid` = :revid LIMIT 1', $post);
				//$ret.=mydb()->_query;

				foreach (cfg('ibuy.price.use') as $key => $value) {
					$post->{$key} = sg_strip_money($post->{$key});
				}

				$stmt = mydb::create_update_cmd('%ibuy_product%', $post, '`tpid` = :tpid LIMIT 1');

				mydb::query($stmt, $post);
				//$ret .= mydb()->_query;
				//$ret .= print_o($post,'$post');
			}
			break;

		default:
			// Update paper information
			if ($_POST) {
				$post = (Object) post();
				$debug = false;

				$result = R::Model('paper.info.update', $topicInfo, $post);

				if ($simulate) {
					$ret .= print_o($result, '$result');
				} else if ($topicInfo->info->module != 'paper') {
					$onViewResult = R::On($topicInfo->info->module.'.paper.edit.complete', $self, $topicInfo, $data);
				}

				if ($debug) {
					$ret .= '<p>UPDATE INFORMATION</p>';
					$ret .= print_o($result, '$result');
					$ret .= print_o(post(),'post()');
					return $ret;
				}
				location('paper/'.$tpid.'/edit');
			} else {
				$ret .= '<header class="header -box"><h3>Paper management</h3></header>';

				$ret .= '<h3>Papar property</h3>
				Topic id : '.$topicInfo->tpid.'<br />
				Topic url : '.cfg('domain').url('paper/'.$topicInfo->tpid).'<br />
				Title : '.$topicInfo->title.'<br />
				Content type : '.$topicInfo->info->type.' => '.$topicInfo->info->type_name.'<br />
				Status : '.$topicInfo->info->status.'<br />
				Create by : '.($topicInfo->uid?'<a href="'.url('profile/'.$topicInfo->uid).'">'.$topicInfo->info->owner.'</a>':$topicInfo->info->owner).'<br />

				Created date :'.$topicInfo->info->created.'<br />
				Changed date :'.$topicInfo->info->changed.'<br />
				Sticky :'.$topicInfo->info->sticky.'<br />
				Promote :'.$topicInfo->info->promote.'<br />
				Rating : '.$topicInfo->info->rating.'<br />
				Liked : '.$topicInfo->info->liketimes.'<br />
				Photo :'.count($topicInfo->photos).'<br />
				View :'.$topicInfo->info->view.' views'.($topicInfo->info->last_view ? ' @'.$topicInfo->info->last_view : '').'<br />
				Comment :'.$topicInfo->info->reply.' replies'.($topicInfo->info->last_reply ? ' @'.$topicInfo->info->last_reply : '').'<br />
				';
			}
			break;
	}
	return $ret;
}
?>