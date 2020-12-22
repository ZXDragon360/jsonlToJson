<?php

if( $argc >= 2 ){
    $data = getRead($argv[1]);
    $data = array_reverse($data);
    processJSONL($data);
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
            $v[$node] = $__parentId;
            $listObj[] = $v;
            $__parentId = [];
        }
    }
    file_put_contents('format.json', json_encode($listObj));
}
?>