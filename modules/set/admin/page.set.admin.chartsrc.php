<?php
/**
* Config graph
*
*/
function set_admin_chartsrc($self) {
	$srcList=array(
							'invertor'=>'Invertor',
							'bidchart/tv'=>'BidChart/tv',
							'bidchart/chart'=>'BidChart/chart',
							'marketanyware/script'=>'MarketAnyware/script',
							'marketanyware/app'=>'MarketAnyware/app',
							'investorz'=>'Investorz',
							'siamchart'=>'SiamChart'
							);
	$cfgSetGraph='set.graph';
	if (post('chartsrc')) {
		cfg_db($cfgSetGraph,post('chartsrc'));
	}

	$form=new Form('admin',url(q()),'edit-config','sg-form');
	$form->config->attr='data-rel="#set-info"';

	$form->chartsrc->name='chartsrc';
	$form->chartsrc->type='radio';
	$form->chartsrc->label='Chart source from :';
	$form->chartsrc->options=$srcList;
	$form->chartsrc->value=cfg($cfgSetGraph);

	$form->submit->type='submit';
	$form->submit->items->save='Save configuration';
	$form->submit->items->restore='Reset to defaults';

	$ret.= $form->build();

	return $ret;
}
?>