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
import('package:org/template/pa/models/model.pa.php');
class OrgPaMapCreate_org extends Page {
 var $arg1;

    function __construct($arg1 = NULL)
    {
        $this->arg1 = $arg1;
    }
    function build()
    {
        //echo print_o(post());
        PaModel::orgCreate(post('name'));
    }
}