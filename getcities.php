<?php
	include "db_connect.php";
	$obj = new DB_Connect();
	$s=$_REQUEST["sid"];
	$stmt = $obj->con1->prepare("select * from city WHERE status='enable' and stnm=$s");
	$stmt->execute();
	$result = $stmt->get_result();
?>

<option value="">Choose</option>
<?php	
	
			while($row = mysqli_fetch_assoc($result))
			{
?>
	<option value="<?php echo $row["srno"] ?>"><?php echo $row["ctnm"] ?></option>
<?php
			}
?>