<?php
/********************************************
 * Class :: PageNavigator
 * PageNavigator class for create page navigator
 *
 * Created :: 2020-10-01
 * Modify  :: 2025-11-11
 * Version :: 2
 *
 * Property
 * config {nav: "nav -icons"}
 *
 * @usage new PageNavigator($class)
 ********************************************/

class PageNavigator {
	var $items_per_page = 10;
	var $total_items = 0;
	var $current_page = 1;
	var $page_to_show = 5;
	var $link_url = '';
	var $total_page=0;
	var $cleanurl=true;

	function __construct($itemsPerPage=NULL,$currentPage=NULL,$total=NULL,$url=NULL,$cleanurl=true,$linkPara=array()) {
		if ( isset($total) ) $this->TotalItems($total);
		if ( isset($itemsPerPage) ) $this->ItemsPerPage($itemsPerPage);
		if ( isset($currentPage) ) $this->CurrentPage($currentPage);
		if ( isset($url) ) $this->LinkURL($url);
		if ( isset($cleanurl)) $this->cleanurl = $cleanurl;
		if ( $linkPara ) $this->linkPara = $linkPara;

		if ($this->linkPara) {
			$this->linkPara['attribute'] = (Array) SG\getFirst($this->linkPara['attribute'], $this->linkPara['attr']);
			unset($this->linkPara['attr']);

			if ($this->linkPara['attribute']['class']) {
				$this->class .= trim(' '.$this->linkPara['attribute']['class']);
				unset($this->linkPara['attribute']['class']);
			}
		};

		$this->_make();
	}

	/** Public Property ItemsPerPage : items per page */
	function itemsPerPage($newValue=NULL) {
		if ( isset($newValue) ) {
			if ( $newValue == "all" ) $this->items_per_page = $this->TotalItems();
			else $this->items_per_page = IntVal($newValue);
		}
		if ( $this->items_per_page == 0 ) $this->items_per_page = 5;
		return $this->items_per_page;
	}

	/** Public Property TotalItems : Total items */
	function totalItems($newValue=NULL) {
		if ( isset($newValue) ) $this->total_items = IntVal($newValue);
		return $this->total_items;
	}

	/** Public Property CurrentPage : current page */
	function currentPage($newValue=NULL) {
		if ( isset($newValue) ) $this->current_page = IntVal($newValue);
		return $this->current_page;
	}

	/** Public Property PageToShow : page to show */
	function pageToShow($newValue=NULL) {
		if ( isset($newValue) ) $this->page_to_show = IntVal($newValue);
		return $this->page_to_show;
	}

	/** Public Property TotalPage : find total page */
	function totalPage() {
		if ( $this->ItemsPerPage() == 0 ) $this->ItemsPerPage();
		$this->total_page=Ceil($this->TotalItems() / $this->ItemsPerPage());
		return $this->total_page;
	}
	function firstItem() {
		return $this->first_item=($this->CurrentPage()-1)*$this->ItemsPerPage();
	}

	/** Public Property IsDisplayItem */
	function isDisplayItem($no) {
		return $this->is_display_item=Ceil(++$no/$this->ItemsPerPage()) == $this->CurrentPage();
	}

	/** Public Property IsOverCurrentPage */
	function isOverCurrentPage($no) {
		return Ceil(++$no/$this->ItemsPerPage()) > $this->CurrentPage();
	}

	/** Public Property LinkUrl */
	function linkURL($newValue=NULL) {
		if ( isset($newValue) ) $this->link_url = $newValue;
		return $this->link_url;
	}

	function linkAddress($page='1') {
		if ($this->linkPara) {
			$url = $this->linkUrl();
			$linkPara = $this->linkPara;
			unset($linkPara['attribute']);
			$linkPara['page'] = $page;
		} else if (preg_match('/\%page\%/',$this->LinkURL())) {
			$linkUrl = str_replace("%page%",$page,$this->LinkURL());
			$url = (preg_match("/ /",$linkUrl) ? Preg_Replace("/ /","%20",$linkUrl) : $linkUrl);
		} else if (preg_match('/page\/[0-9]*/',$this->LinkURL())) {
			$url=preg_replace('/page\/[0-9]*/','page/'.$page,$this->LinkURL());
			//	echo 'page : '.$page.' of '.$this->LinkUrl().' = '.$url.'<br />';
		} else {
			$url=$this->LinkURL().'/page/'.$page;
		}
		return Url::link($url, $linkPara);
	}

	/** Public Property toString */
	function toString() {
		$class = $this->class;
		$linkAttr = sg_implode_attr($this->linkPara['attribute']);
		// debugMsg($linkAttr);
		// debugMsg($this->linkPara, '$this->linkPara');
		$totalPage = $this->TotalPage();
		$currentPage = $this->CurrentPage() == 0 ? 1 : $this->CurrentPage();
		$startPage = $currentPage - Floor($this->PageToShow() / 2);
		if ( $startPage < 1 ) $startPage = 1;
		$endPage = $startPage + $this->PageToShow() - 1;
		if ( $endPage > $totalPage ) $endPage = $totalPage;
		$showStr = '<!-- start of page Navigator -->'._NL;
		$showStr .= '<div class="page-nv">'._NL;
		$showStr .= '<span class="page-items">'.$this->TotalItems().' items</span>';
		if ( $currentPage > 1 ) {
			$showStr .= '<a class="btn page-first active '.$class.'" href="'.$this->LinkAddress('1').'" '.$linkAttr.' title="first page">';
			$showStr .= '|&laquo; First';
			$showStr .= '</a>'._NL;
			$showStr .= '<a class="btn page-prev active '.$class.'" href="'.$this->LinkAddress($currentPage-1).'" '.$linkAttr.' title="previous page is '.($currentPage-1).'">';
			$showStr .= '&laquo; Prev';
			$showStr .= '</a>'._NL;
		}
		for ( $i = $startPage; $i <= $endPage; $i++ ) {
			if ( $i == $this->CurrentPage() ) {
				$showStr .= '<span class="page-current">('.$i.'/'.$totalPage.')</span>'._NL;
			} else {
				$showStr .= '<a class="btn page-other active '.$class.'" href="'.$this->LinkAddress($i).'" '.$linkAttr.' title="page '.$i.' from '.$totalPage.'">'.$i.'</a>'._NL;
			}
		}
		if ( $currentPage < $totalPage ) {
			$showStr .= '<a class="btn page-next active '.$class.'" href="'.$this->LinkAddress($currentPage+1).'" '.$linkAttr.' title="next page is '.($currentPage+1).'">';
			$showStr .= 'Next &raquo;';
			$showStr .= '</a>'._NL;
			$showStr .= '<a class="btn page-last active '.$class.'" href="'.$this->LinkAddress($totalPage).'" '.$linkAttr.' title="last page is '.$totalPage.'">';
			$showStr .= 'Last &raquo;|';
			$showStr .= '</a>'._NL;
		}
		$showStr .= '</div><!--page-nv-->';
		$showStr .= '<!-- end of page Navigator -->'._NL;
		return $showStr;
	}

	function _make() {
		if ( $this->TotalPage() > 1 ) $this->show = $this->toString();
	}

	/** Public Method Show */
	function build() { return $this->toString(); }

} // End Of Class PageNavigator

?>