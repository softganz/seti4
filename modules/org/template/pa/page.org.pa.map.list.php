<?php
/**
* Org :: the main page of PA network mapping
* Created 2022-22-02
* Modify  2022-01-01
*
* @param String $arg1
* @return Widget
*
* @usage org/pa/map/
*/
import('widget:org.pa.map.nav.php');
import('model:org.php');
class OrgPaMapList extends Page {
 var $arg1;

 function __construct($arg1 = NULL) {
  $this->arg1 = $arg1;
 }

 function build() {
    $cond = [];
    $opt = [];
    $orgList = OrgModel::items($cond,$opt);
    return new Scaffold([
        'appBar' => new AppBar([
        'title' => 'PA Network Mapping',
        'navigator' => new OrgPaMapNavWidget(),
        ]), // AppBar
        'body' => new Widget([
            'children' =>  $this->createList($orgList),

        ]), // Widget
    ]);
 }
 private function createList($orgList)
 {
    $card = [];
    foreach( $orgList as $item)
    {
        $card[] = new Card([
            'children' => [
                new ListTile([
                    'leading' => '<i class="icon -material" >star</i>',
                    'title' => '<a href="'.url('org/'.$item->orgid.'/pa.map.view').'">'.$item->name.($item->shortname ? '('.$item->shortname.')' : '').'</a>',
                ]),
                //new DebugMsg($item),
            ]
        ]);
    }
    return $card;
 }
}
?>