<?php 
/**
* Org :: Form for add new Organization of PA network
* Created 2022-22-02
* Modify  2022-01-01
*
* @param String $arg1
* @return Widget
*
* @usage org/pa/map/addform 
*/
import('widget:org.pa.map.nav.php');
class OrgPaMapAddform extends Page {
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
    'body' => new Form([
        //'action' => url('org/room/booking/create'),
        'action' => url('org/pa/map/create_org'),
        'class' => 'sg-form',
        'rel' => 'notify',
        'done' => 'close | load:#main',
        'checkValid' => true,
        'children' => [
            'name' => [
                'type' => 'text',
                'label' => 'ชื่อหน่วยองค์กร',
                'require' => true,
            ],
            'save' => [
                'type' => 'button',
                'value' => '<i class="icon -material">done</i><span>บันทึก</span>',
            ]
        ],// children
   ]),// Form
  ]);
 }
}
?>