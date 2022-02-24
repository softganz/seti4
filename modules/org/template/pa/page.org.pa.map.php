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
class OrgPaMap extends Page {
 var $arg1;

 function __construct($arg1 = NULL) {
  $this->arg1 = $arg1;
 }

 function build() {
  return new Scaffold([
    'appBar' => new AppBar([
        'title' => 'PA Network Mapping',
        'navigator' => new OrgPaMapNavWidget(),
    ]), // AppBar
    'body' => new Widget([
        'children' => [
            'children',
        ], // children
    ]), // Widget
  ]);
 }
}
?>