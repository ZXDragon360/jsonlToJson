<?php

if( $argc >= 2 ){
    $data = getRead($argv[1]);
    $data = array_reverse($data);
    $processed = processJSONL($data);
    parseJSON($processed);
} else {
    echo "No argument\n";
}

function verify( $data ):bool{
    if( is_file($data) && is_readable($data) ){
        $extension = pathinfo($data, PATHINFO_EXTENSION);
        return $extension !== 'jsonl' ? false : true;
    } else{
        return false;
    }
}

function getRead( $file ):array{
    if(verify($file) === false) { exit();}
    
    $data = [];
    $handle = fopen($file,"r");
    if($handle){
        while( ($buffer = fgets($handle)) !== false){ $data[] = $buffer; }
    }
    fclose($handle);
    return $data;
}

function processJSONL( array $array){
    $node = '__parentId';
    $__parentId = []; // collect children node
    $listObj = []; // collect all main objects
    foreach($array as &$v){
        $v = json_decode($v,true);
        if(isset($v[$node])){
            $__parentId[] = $v;
        }else{
            !empty($__parentId) ? $v[$node] = $__parentId : null;
            $listObj[] = $v;
            $__parentId = [];
        }
    }
    // file_put_contents('format.json', json_encode($listObj));
    return $listObj;
}

function parseJSON(array $param){
    $handle = fopen('quantity.csv','w');
    $header = ['inventory_item_id','location_id','available'];
    fputcsv($handle,$header);
    foreach($param as $value){
        if(isset($value['__parentId'])){
            foreach($value['__parentId'] as $v){
                $available = $v['available'];
                $locationId = $v['location']['legacyResourceId'];
                $inventoryItemId = $value['legacyResourceId'];
                $data = [$inventoryItemId,$locationId,$available];
                fputcsv($handle,$data);
            }
        }
    }
    fclose($handle);
}
?>
