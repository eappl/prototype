<?php
require("help.php");

$array = array();

foreach($_POST['blname'] as $k=>$v){    
    $array[$v] = $_POST['blvalue'][$k];
}

//$array['Time'] = time();
//$array['ReturnType'] = RETURNTYPE;
$rerutn['testsign'] = test_sign($array,$_REQUEST['ApiKey']);
$array['sign'] = set_sign($array,$_REQUEST['ApiKey']);

foreach($_POST['blname'] as $k=>$v){
    if(isset($_POST['blfunction'][$k]) && !empty($_POST['blfunction'][$k])){
        $array[$v] = $_POST['blfunction'][$k]($array[$v]);
    }
}

$res = splice_url($array);

$rerutn['url'] = app_url($_REQUEST['ApiAddress'],$_REQUEST['ApiCtl'],$_REQUEST['ApiAc'],$res);

echo json_encode($rerutn);
?>