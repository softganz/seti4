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

class OrgPaMapNavWidget extends Widget {
 var $arg1;

 function __construct($arg1 = NULL) {
  $this->arg1 = $arg1;
 }

 function build() {
  return new Widget([
      'children' => [
        '<a href="{url:org/pa/map/list}" ><i class="icon -material" style="color:#fff;">home</i><span>home</span></a>',
        '<a href="{url:org/pa/map/list}"><i class="icon -material" style="color:#fff;">view_list</i><span>รายชื่อองค์กร</span></a>',
        '<a href="#" ><i class="icon -material" style="color:#fff;">person</i><span>ของฉัน</span></a>',
        '<a href="#"><i class="icon -material" style="color:#fff;">hub</i><span>เครือข่ายที่แนะนำ</span></a>',
        '<a href="{url:org/pa/map/addform}" class="sg-action" data-rel="box" data-title="เพิ่มองค์กร" data-width="480" ><i class="icon -material" style="color:#fff;">add</i><span>เพิ่มองค์กร</span></a>'
      ],
  ]);//End Navigator
   
 }
}
?>