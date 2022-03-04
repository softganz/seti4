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

// $debug = true;
import('model:org.php');
import('package:org/template/pa/models/model.pa.php');
import('package:org/template/pa/widgets/widget.org.pa.map.nav.php');

class OrgPaMapList extends Page {
 var $arg1;
 var $style = '';

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
            'children' =>
            //  [
            //      $this->createListUl($orgList),
            //  ] //Ul list
            $this->createList($orgList),// Card obj

        ]), // Widget
    ]);
 }
 private function createList($orgList)
 {
    $card = [];
    foreach( $orgList as $item)
    {
        $subjects = PaModel::getSubject($item->orgid);
        $card[] = new Card([
            'children' =>
            [
                new ListTile([
                    'leading' => '<i class="icon -material" >description</i>',
                    'title' => '<a href="'.url('org/pa/map/api/'.$item->orgid.'/delete/NULL').'" class="sg-action" data-title="ชื่อ" data-confirm="คุณแน่ใจที่จะลบแล้วใช่หรือไม่" data-done="remove:parent .widget-card" data-rel="none" style="float:right;"><i class="icon -material" >delete</i></a><a href="'.url('org/'.$item->orgid.'/pa.map.view').'">'.$item->name.($item->shortname ? '('.$item->shortname.')' : '').
                    '</a>',

                ]),
                $this->createSubjRow($subjects),'<hr style="border-top:#ddd solid 1px; margin:8px 16px;">'
                //new DebugMsg($item),
            ]
        ]);
    }
    $card[] = new Card([
        'children' => [
            new ListTile([
                'title' => $this->paTagStyle(),
             ])
        ]
    ]);
    return $card;
 }
 private function createListUl($orgList)
 {
     $li = '<ul>';

     foreach( $orgList as $item)
     {
        $subjects = PaModel::getSubject($item->orgid);
        $li .=  '<li><a href="'.url('org/'.$item->orgid.'/pa.map.view').'">
                '.$item->name.' '.$this->createSubjDiv($subjects).'</li></a>';

     }
     return $li.'</ul>'.$this->paTagStyle();
}

private function createSubjDiv($subjects)//subject is org_subject table return row
{
    $re = '';
    $tag = ['เดินวิ่ง','ปั่นจักรยาน',  'สุขภาวะ', 'NCD','เปิดรับทั่วไป'];
    $re .='<div class="tag-label">';
    foreach($subjects as $subj)
    {

        $re .= '<span>'.$tag[$subj->subject-1].'</span> ';
    }
    $re .= '</div>';
    return $re;
}
private function createSubjRow($subjects)//subject is org_subject table return row
{
    $str = '';
    $tag = ['เดินวิ่ง','ปั่นจักรยาน',  'สุขภาวะ', 'NCD','เปิดรับทั่วไป'];

    foreach($subjects as $subj)
    {

        $str .= '<span>'.$tag[$subj->subject-1].'</span> ';
    }

    $row = new Row([
        'style' => 'padding-left:1em;align-items: center;',
        'class' => 'tag-label',
        'children' => [
        $str,
        ]
    ]);
    return $row;
}
private function paTagStyle()
{
    $re = '
    <style>
        .tag-label span{

            border-radius:0.25rem;
            background-color:#17a2b8;
            color:#fff;
            padding:4px;
            font-size:12px;
        }
    </style>
    ';

    return $re;
}
}
?>
