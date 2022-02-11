<?php
function view_admin_menu_content() {
	$ret='<h3><a href="'.url('admin/content').'">Content Management</a></h3>
	<p class="description">Manage your site\'s content.</p>';
	$ret.='<dl class="admin-list">
<dt><a href="'.url('admin/comment/list').'">Comments</a></dt><dd>List and edit site comments and the comment moderation queue.</dd>
<dt><a href="'.url('admin/content/topic').'">Content</a></dt><dd>View, edit, and delete your site\'s content.</dd>
<dt><a href="'.url('admin/content/type').'">Content types</a></dt><dd>    Manage posts by content type, including default status, front page promotion, etc.</dd>
<dt><a href="'.url('paper/post').'">Create new content</a></dt><dd>Create new content topic.</dd>
<!--<dt><a href="'.url('admin/content/forum').'">Forums</a></dt><dd>Control forums and their hierarchy and change forum settings.</dd>
<dt><a href="'.url('admin/content/node-settings').'">Post settings</a></dt><dd>Control posting behavior, such as teaser length, requiring previews before posting, and the number of posts on the front page.</dd>
<dt><a href="'.url('admin/content/rss-publishing').'">RSS publishing</a></dt><dd>Configure the number of items per feed and whether feeds should be titles/teasers/full-text.</dd>-->
<dt><a href="'.url('admin/content/taxonomy').'">Taxonomy</a></dt><dd>    Manage tagging, categorization, and classification of your content.</dd>
<dt><a href="'.url('paper/list/list-style/table/order/view').'">Top hit content</a></dt><dd>List top hit contents.</dd>
</dl>';
	return $ret;
}
?>