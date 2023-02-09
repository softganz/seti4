<?php
/**
* Admin : Content Menu
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage new AdminMenuContentWidget()
*/

class AdminMenuContentWidget extends Widget {
	function build() {
		return new Column([
			'children' => [
				'<p class="description"><em>Manage your site\'s content.</em></p>',
				new Card([
					'children' => [
						new ListTile([
							'title' => 'Category',
							'leading' => new Icon('category'),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/category'),
								'text' => 'View Content',
							]),
							'subtitle' => 'View, edit, and delete category.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'title' => 'Comments',
							'leading' => new Icon('comment'),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/comment/list'),
								'text' => 'View Comments',
							]),
							'subtitle' => 'List and edit site comments and the comment moderation queue.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Content',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/content/topic'),
								'text' => 'View Content',
							]),
							'subtitle' => 'View, edit, and delete your site\'s content.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Content types',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/content/type'),
								'text' => 'Content types',
							]),
							'subtitle' => 'Manage posts by content type, including default status, front page promotion, etc.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Create new content',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('paper/post'),
								'text' => 'Create new content',
							]),
							'subtitle' => 'Create new content topic.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Post settings',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/content/node-settings'),
								'text' => 'Post settings',
							]),
							'subtitle' => 'Control posting behavior, such as teaser length, requiring previews before posting, and the number of posts on the front page.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'RSS publishing',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/content/rss-publishing'),
								'text' => 'RSS publishing',
							]),
							'subtitle' => 'Configure the number of items per feed and whether feeds should be titles/teasers/full-text.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Taxonomy',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/content/taxonomy'),
								'text' => 'Taxonomy',
							]),
							'subtitle' => 'Manage tagging, categorization, and classification of your content.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Top hit content',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('paper/list/list-style/table/order/view'),
								'text' => 'Top hit content',
							]),
							'subtitle' => 'List top hit contents.',
						]),
					], // children
				]), // Card

			], // children
		]);
	}
}
?>