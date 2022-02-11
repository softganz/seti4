<?php

/**
 * Send Document Report
 *
 */
function project_report_depart($self) {
	R::View('project.toolbar', $self, 'รายชื่อโครงการแยกตามหน่วยงาน', 'report');

	$year=SG\getFirst(post('y'),date('Y'));
	$province=post('p');

	$ret.='<form method="get" action="'.url('project/report/depart').'">';
	$ret.='ปีงบประมาณ <select name="y">';
	for ($i=2015;$i<=date('Y');$i++) {
		$ret.='<option value="'.$i.'" '.($i==$year?'selected="selected"':'').'>'.($i+543).'</option>';
	}
	$ret.='</select>';
	$ret.=' <input type="submit" value="ดู" />';
	$ret.='</form>';
	$stmt='SELECT t.`orgid`, o.`name`, o.`parent`,
						COUNT(IF(p.`prtype`="แผนงาน",1,NULL)) amtPlan,
						COUNT(IF(p.`prtype`="ชุดโครงการ",1,NULL)) amtSet,
						COUNT(IF(p.`prtype`="โครงการ",1,NULL)) amtProject,
						SUM(IF(p.`prtype`="โครงการ",`budget`,0)) `totalBudget`,
						SUM(IF(p.`prtype`="แผนงาน",`budget`,0)) `totalBudgetPlan`,
						SUM(IF(p.`prtype`="ชุดโครงการ",`budget`,0)) `totalBudgetSet`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE t.`orgid`>0
						AND `pryear`=:year
						AND o.`sector`=1
					GROUP BY o.`parent`,t.`orgid`';
	$dbs=mydb::select($stmt,':year',$year);
	foreach ($dbs->items as $rs) {
		$tree[$rs->orgid]=$rs->parent;
		$items[$rs->orgid]=$rs;
	}
	$lists=parseTree($items,$tree);
	$orgItems=printTree($items,$lists);

	$ret.='<div id="chart" style="width:100%; height:500px;"></div>';

	$graphType='col';
	$data[]=array('หน่วยงาน', 'โครงการ', 'งบประมาณ');
	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');

	$tables = new Table();
	$tables->thead=array('แผนก/สำนัก', 'amt plans'=>'แผนงาน', 'amt sets'=>'ชุดโครงการ', 'amt projects'=>'โครงการ','money budgets'=>'งบประมาณ');
	//$tables->rows=__list($dbs->items);
	foreach ($orgItems as $rs) {
		//if (empty($rs->parent)) $tables->rows=__list($rs,$dbs);
		$departmentName=SG\getFirst($rs->name,'ไม่ระบุ');
		$tables->rows[]=array(
											str_repeat('--', $rs->level).'<a href="'.url('project/list','org='.$rs->orgid).'">'.$departmentName.'</a>',
											$rs->amtPlan>0?$rs->amtPlan:'-',
											$rs->amtSet>0?$rs->amtSet:'-',
											$rs->amtProject>0?number_format($rs->amtProject):'-',
											number_format($rs->totalBudget,2)
											);
		$totalPlan+=$rs->amtPlan;
		$totalSet+=$rs->amtSet;
		$totalProjects+=$rs->amtProject;
		$totalBudgets+=$rs->totalBudget;

		$data[]=array($departmentName,$rs->amtProject,intval(SG\getFirst($rs->totalBudget,'0')));

	}
	$tables->rows[]=array(
										'<strong>รวม</strong>',
										$totalPlan>0?$totalPlan:'-',
										$totalSet>0?$totalSet:'-',
										'<strong>'.($totalProjects?number_format($totalProjects):'-').'</strong>',
										'<strong>'.number_format($totalBudgets,2).'</strong>'
										);
	$ret .= $tables->build();

	head('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

		$ret.='<script type="text/javascript"><!--

google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart() {
var data = google.visualization.arrayToDataTable('.json_encode($data).');
var options = {
								title: "งบประมาณ",
								tooltip: {isHtml: true},
								series:[
									{targetAxisIndex:0},
									{targetAxisIndex:1},
									],
								};

var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart"));
chart.draw(data, options);

}</script>';
	//$ret.=print_o($data,'$data');
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function parseTree($items,$tree, $root = null) {
    $return = array();
    # Traverse the tree and search for direct children of the root
    foreach($tree as $child => $parent) {
        # A direct child is found
        if($parent == $root) {
            # Remove item from tree (we don't need to traverse this again)
            unset($tree[$child]);
            # Append the child into result array and parse its children
            $return[] = array(
                'name' => $child,
                'rs'=>$items[$child],
                'children' => parseTree($items,$tree, $child)
            );
        }
    }
    return empty($return) ? null : $return;    
}

function printTree($items,$tree=NULL,&$rows=NULL,$level=0) {
    if(!is_null($tree) && count($tree) > 0) {
        foreach($tree as $node) {
        		$row=$items[$node['name']];
        		$row->level=$level;
            $rows[]=$row;
            printTree($items,$node['children'],$rows,$level+1);
        }
    }
    return $rows;
}


function parseAndPrintTree($root, $tree) {
    $return = array();
    if(!is_null($tree) && count($tree) > 0) {
        echo '<ul>';
        foreach($tree as $child => $parent) {
            if($parent == $root) {                    
                unset($tree[$child]);
                echo '<li>'.$child;
                parseAndPrintTree($child, $tree);
                echo '</li>';
            }
        }
        echo '</ul>';
    }
}

function __p($items,$lists=array()) {
	foreach ($items as $rs) {
		if ($rs->parent) {
			//$lists[$rs->orgid]['item']=$rs;
			$lists[$rs->parent]['childs']=__p($items,$lists);//[$rs->orgid]=$rs;
		}
		else $lists[$rs->orgid]['item']=$rs;
	}
	return $lists;
}
function __list($items,$level=0) {
	$un=array();
	foreach ($items as $key=>$rs) {
		if (in_array($key,$un)) continue;
		$departmentName=SG\getFirst($rs->name,'ไม่ระบุ');
		$rows[]=array(
						str_repeat('--', $level).$level.'<a href="'.url('project/list','org='.$rs->orgid).'">'.$departmentName.'</a>',
						$rs->amtPlan>0?$rs->amtPlan:'-',
						$rs->amtSet>0?$rs->amtSet:'-',
						$rs->amtProject>0?number_format($rs->amtProject):'-',
						number_format($rs->totalBudget,2)
						);
		$sub=array();
		foreach ($items as $k=>$item) {
			if ($item->parent==$rs->orgid) {
				$sub[]=$item;
				$un[]=$k;
				//echo 'Parent='.$item->parent.' orgid='.$rs->orgid;print_o($item,'$item',1);
			}
		}
		if ($sub) $rows=array_merge($rows,__list($sub,$level+1));
	}
	return $rows;
}
?>