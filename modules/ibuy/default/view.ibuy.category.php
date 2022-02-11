<?php
function view_ibuy_category($catid=NULL) {
	$unfold=$catid;
	$categorys=model::get_taxonomy_tree(cfg('ibuy.vocab.category'));
	$brands=model::get_taxonomy_tree(cfg('ibuy.vocab.brand'));
	if (empty($unfold)) $unfold=$categorys[0]->tid;
	foreach ($categorys as $term) {
		if ($term->depth==0) {
			if ($unfold==$term->tid) break;
			$master_cid=$term->tid;
		} else if ($term->tid==$unfold) {
			$unfold=$master_cid;
			//				$ret.='Sub master ';
			break;
		}
	}
	//		$ret.='Unfold='.$unfold.'<br />';
	//		$ret.=print_o($categorys,'$cat');
	foreach ($categorys as $term) {
		if ($term->depth==0) {
			$clist.='</ul></li>'._NL.'<li><a href="'.url('ibuy/category/'.$term->tid).'"><strong>'.$term->name.'</strong></a><ul>'._NL;
			$cur_fold=$term->tid;
		} else if ($unfold && $unfold==$cur_fold) {
			$clist.='<li'.($term->tid==$category?' class="-active"':'').'><a id="category-'.$term->tid.'" href="'.url('ibuy/category/'.$term->tid).'">'.($catid==$term->tid?'<strong>':'').$term->name.($catid==$term->tid?'</strong>':'').'</a>'._NL;
			if ($term->tid==$category) {
				$clist.='<ul id="ibuy-category-'.$term->tid.'">'._NL;
				foreach ($brands as $brandt) $clist.='<li'.($brandt->tid==$brand?' class="-active"':'').'><a href="'.url('ibuy/category/'.$term->tid.'/brand/'.$brandt->tid).'">'.$brandt->name.'</a></li>'._NL;
				$clist.='</ul>'._NL;
			}
			$clist.='</li>'._NL;
		}
	}
	$clist.='</ul></li>';
	$clist=substr($clist,10);
	$ret.='<ul class="ibuy-category">'.$clist.'</ul>'._NL;

	return $ret;
}
?>