<?php 
	include "../db_connect.php";
	$obj = new DB_Connect();
    
    $stmt = $obj->con1->prepare("SELECT count(*) as tot FROM `state` WHERE soundex(name)=soundex(?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    echo $data["tot"];
    $stmt->close();
?>