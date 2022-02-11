<?php
/**
* GoGreen Spp Shop Goods
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function green_category_home($self, $catId = NULL) {
	$ret = '';

	$productOptions = R::Model('category.get', array('vid' => cfg('ibuy.vocab.category')), 'tid', '{debug: false, result: "group"}');

	$ret .= '<section class="green-category-home -sg-flex">';
	$ret .= '<div class="green-category-menu">';

	$mainUi = new Ui();
	foreach ($productOptions as $groupName => $groupItems) {
		$mainUi->add('<b>'.$groupName.'</b>');
		$ui = new Ui(NULL, 'ui-menu');
		foreach ($groupItems as $categoryId => $categoryName) {
			$ui->add('<a href="'.url('green/category/'.$categoryId).'">'.$categoryName.'</a>');
		}
		$mainUi->add($ui->build());
	}
	$ret .= $mainUi->build();
	$ret .= '</div>';

	$ret .= '<div class="green-category-product">';
	$ret .= R::Page('green.goods', NULL);
	$ret .= '</div>';
	//$ret .= print_o($productInfo, '$productInfo');
	return $ret;
}
?>