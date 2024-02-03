<?php
include "header.php";

if(isset($_REQUEST['viewId'])){
    $mode = 'view';
    $viewId = $_REQUEST['viewId'];
    $stmt = $obj->con1->prepare("SELECT sc1.*, c1.ctnm AS city, s1.id AS state_id FROM service_center sc1, city c1, state s1 WHERE c1.srno=sc1.area AND c1.state_id=s1.id AND sc1.id=?");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $name = $_REQUEST["name"];
    $email = $_REQUEST["email"];
    $contact = $_REQUEST["contact"];
    $user_id = $_REQUEST["userid"];
    $pass = $_REQUEST["password"];
    $status = $_REQUEST["default_radio"];
    $address = $_REQUEST["address"];
    $state = $_REQUEST["state"];
    $city = $_REQUEST['cityName'];

    try {
        $stmt = $obj->con1->prepare(
            "INSERT INTO `service_center`(`name`,`email`,`contact`,`userid`,`password`,`status`,`address`,`area`) VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            "sssssssi",
            $name,
            $email,
            $contact,
            $user_id,
            $pass,
            $status,
            $address,
            $city
        );
        $Resp = $stmt->execute();

        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data", time() + 3600, "/");
        header("location:service_center.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:service_center.php");
    }
}
?>

<div class='p-6'>

    <div class="panel border shadow-md shadow-slate-200">
        <div class="mb-5 flex items-center justify-between">
            <h5 class="text-xl text-primary font-semibold dark:text-white-light">Service Center Add</h5>
        </div>
        <form class="space-y-5" method="post">
            <div>
                <label for="groupFname">Name</label>
                <input id="groupFname" type="text" name="name" placeholder="Enter First Name" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required 
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?>    
                />
            </div>
            <div>
                <label for="ctnEmail">Email Address</label>
                <input id="ctnEmail" type="email" name="email" placeholder="name@example.com" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['email'] : '' ?>" required 
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?>
                />
            </div>
            <div>
                <label for="groupFname">Contact</label>
                <input id="groupFname" type="number" name="contact" placeholder="" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['contact'] : '' ?>" required 
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?>    
                />
            </div>

            <div>
                <label for="address">Address</label>
                <textarea autocomplete="on" name="address" id="address" 
                    class="form-textarea" rows="2" cols="0"
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?>
                >
                    <?php echo (isset($mode)) ? $data['address'] : ''; ?>
                </textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div>
                    <label for="gridState">State</label>
                    <select id="gridState" class="form-select text-white-dark" name="state"
                        onchange="loadCities(this.value)" 
                        <?php echo isset($mode) && $mode == 'view' ? 'disabled' : ''?> required>
                        <option value="">Choose State</option>
                        <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM `state` ");
                            $stmt->execute();
                            $Res = $stmt->get_result();
                            $stmt->close();
                            while ($result = mysqli_fetch_assoc($Res)) { 
                        ?>

                        <option value="<?php echo $result["id"]; ?>"
                            <?php echo isset($mode) && $data['state_id'] == $result['id'] ? 'selected' : '' ?>
                        >
                            <?php echo $result["name"]; ?>
                        </option>

                        <?php } ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="cityName">City</label>
                    <select id="cityName" name="cityName" 
                        class="form-select text-white-dark"
                        <?php echo isset($mode) && $mode == 'view' ? 'disabled' : ''?>
                    >
                        <option value=""><?php echo isset($mode) ? $data['city'] : 'Choose City' ?></option>
                    </select>
                </div>

                <!-- <div>
                    <label for="gridZip">Pincode</label>
                    <input id="gridZip" type="text" placeholder="Enter Pincode" class="form-input"
                        value="<?php echo (isset($mode)) ? $data[''] : '' ?>" 
                        required />
                </div> -->
            </div>
            <div>
                <label for="gridUID">Username</label>
                <input type="text" name="userid" placeholder="" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['userid'] : '' ?>" required 
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?>
                />
            </div>
            <div>
                <label for="gridpass">Password</label>
                <input type="password" name="password" placeholder="Enter Password" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['password'] : '' ?>" required 
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?>    
                />
            </div>

            <div>
                <label for="gridStatus">Status</label>
                <label class="inline-flex mr-3">
                    <input type="radio" name="default_radio" class="form-radio text-primary" value="enable" checked
                        required />
                    <span>Enable</span>
                </label>
                <label class="inline-flex mr-3">
                    <input type="radio" name="default_radio" class="form-radio text-danger" value="disable" required />
                    <span>Disable</span>
                </label>
            </div>

            <div class="relative inline-flex align-middle gap-3 mt-4 <?php echo (isset($mode)) ? 'hidden' : '' ?>">
                <button type="submit" class="btn btn-success" name="save" id="save">Save</button>
                <button type="button" class="btn btn-danger" onclick="window.location='service_type.php'">Close</button>
            </div>
        </form>
    </div>
</div>
<script>
function loadCities(stid) {
    const xhttp = new XMLHttpRequest();
    xhttp.open("GET", "getcities.php?sid=" + stid);
    xhttp.send();
    xhttp.onload = function() {
        document.getElementById("cityName").innerHTML = xhttp.responseText;
    }
}
</script>

<?php
include "footer.php"; 
?>