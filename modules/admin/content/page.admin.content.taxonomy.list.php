<?php
/**
 * Admin    :: Taxonomy List Page
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 20xx-xx-xx
 * Modified :: 2026-11-23
 * Version  :: 2
 *
 * @uses admin/content/taxonomy/list
 */

declare(strict_types=1);

namespace Admin;

class AdminContentTaxonomyList extends \Page {
	public ?int $vocabId = null;
	function __construct(?int $vocabId) {
		parent::__construct([
			'vocabId' => $vocabId
		]);
	}

	#[\Override]
	function build() {
		$vocab = \BasicModel::get_vocabulary($this->vocabId);
		$tree = \BasicModel::get_taxonomy_tree($this->vocabId);

		return new \Scaffold(
			appBar: new \AppBar(),
			body: new \Widget(
				children: [
					'<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Tags in <em>'
					. $vocab->name
					. '</em></h2>
					<ul class="tabs primary">
					<li><a href="'
					. url('admin/content/taxonomy')
					. '">Vocabulary</a></li>
					<li class="-active"><a href="'
					. url('admin/content/taxonomy/list/' . $this->vocabId)
					. '">List</a></li>
					<li><a href="'
					. url('admin/content/taxonomy/add/' . $this->vocabId)
					. '">Add tag</a></li>
					</ul>
					</div><div class="help"></div>',
					new \Table(
						id: 'taxonomy',
						header: [
							'Tag ID',
							'Name',
							'amt' => 'Weight',
							'List style',
							'List class',
							'Operations'
						],
						children: array_map(function ($term) {
							return [
								$term->tid,
								str_repeat('--', $term->depth) . $term->name,
								$term->weight,
								$term->liststyle,
								$term->listclass,
								'<a href="'
								. url('admin/content/taxonomy/edit/' . $term->tid)
								. '"><i class="icon -material">edit</i><span class="-hidden">Edit</span></a> <a href="'
								. url('tags/' . $term->tid)
								. '">topics</a>'
							];
						}, $tree)
					)
				]
			)
		);
	}
}
?>
