<?php
include "header.php";

if (isset($_COOKIE['viewId'])) {
    $mode = 'view';
    $viewId = $_COOKIE['viewId'];
    $stmt = $obj->con1->prepare("SELECT * FROM `customer_reg` where id=?");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['editId'])) {
    $mode = 'edit';
    $editId = $_COOKIE['editId'];
    $stmt = $obj->con1->prepare("SELECT * FROM `customer_reg` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST['update'])) {
    $editId = $_COOKIE['editId'];
    $fname = $_REQUEST['fname'];
    $lname = $_REQUEST['lname'];
    $email = $_REQUEST['mail'];
    $contact = $_REQUEST['contact_num'];
    $alt_contact = $_REQUEST['alt_contact_num'];
    $map_location = $_REQUEST['map_location'];
    $area = $_REQUEST['area'];
    $address = $_REQUEST['address'];
    $pincode = $_REQUEST['pincode'];
    $service_type = $_REQUEST['service_type'];
    $product_category = $_REQUEST['product_category'];
    $dealer_name = $_REQUEST['dealer_name'];
    $complaint_date = $_REQUEST['complaint_date'];
    $complaint_time = date('h:i A', strtotime($_REQUEST['complaint_time']));
    $complaint_no = $data['complaint_no'];
    $description = $_REQUEST['description'];

    $stmt = $obj->con1->prepare("UPDATE `customer_reg` SET fname=?,lname=?,email=?,contact=?,alternate_contact=?,area=?,map_location=?,address=?,zipcode=?,complaint_no=?,service_type=?,product_category=?,dealer_name=?,description=?, date=?,time=?  WHERE id=?");
    $stmt->bind_param("sssssisssssissssi", $fname, $lname, $email, $contact, $alt_contact, $area, $map_location, $address, $pincode, $complaint_no, $service_type, $product_category, $dealer_name, $description, $complaint_date, $complaint_time, $editId);
    $Res = $stmt->execute();
    $stmt->close();

    if ($Res) {
        setcookie("msg", "update", time() + 3600, "/");
        header("location:complaint_demo.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:complaint_demo.php");
    }
}

if (isset($_POST['save'])) {
    $fname = $_REQUEST['fname'];
    $lname = $_REQUEST['lname'];
    $email = $_REQUEST['mail'];
    $contact = $_REQUEST['contact_num'];
    $alt_contact = $_REQUEST['alt_contact_num'];
    $map_location = $_REQUEST['map_location'];
    $area = $_REQUEST['area'];
    $address = $_REQUEST['address'];
    $pincode = $_REQUEST['pincode'];
    $service_type = $_REQUEST['service_type'];
    $product_category = $_REQUEST['product_category'];
    $dealer_name = $_REQUEST['dealer_name'];
    $complaint_date = $_REQUEST['complaint_date'];
    $complaint_time = date('h:i A', strtotime($_REQUEST['complaint_time']));
    $day = Date("d");
    $month = Date("m");
    $year = Date("y");
    $description = $_REQUEST['description'];
    $source = "web";

    // get max customer id - added by Rachna
    $stmt = $obj->con1->prepare("select IFNULL(count(id)+1,1) as customer_id from customer_reg where date ='" . date("d-m-Y") . "'");
    $stmt->execute();
    $row_dailycounter = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $dailycounter = (int) $row_dailycounter["customer_id"];
    $string = str_pad($dailycounter, 4, '0', STR_PAD_LEFT);
    $complaint_no = "ONL" . $day . $month . $year . $string;
    //--------------//

    echo $complaint_no;
    // echo "----Customer_reg".$fname . " " . $lname . " " . $email . " " . $contact . " " . $alt_contact . " " . $area . " " . $map_location . " " . $address . " " . $pincode . " " . $complaint_no . " " . $service_type . " " . $product_category . " " . $dealer_name . " " . $description . " " . $complaint_date . " " . $complaint_time;

    try {
        $stmt = $obj->con1->prepare("INSERT INTO `customer_reg`(`fname`, `lname`, `email`, `contact`, `alternate_contact`, `area`, `map_location`, `address`, `zipcode`, `complaint_no`, `service_type`, `product_category`, `dealer_name`, `description`, `source`, `date`, `time`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssisssssisssss", $fname, $lname, $email, $contact, $alt_contact, $area, $map_location, $address, $pincode, $complaint_no, $service_type, $product_category, $dealer_name, $description, $source,$complaint_date, $complaint_time);
        $Resp = $stmt->execute();
        $stmt->close();

        // allocate call -added by Rachna
        // get service area
        // echo "----Area = ".$area;
        // echo "\n select * from service_center where area=".$area;
        
        $stmt = $obj->con1->prepare("select * from service_center where area=?");
        $stmt->bind_param("i", $area);
        $stmt->execute();
        $service_center = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        // insert into call allocation
        $product_serial_no = "";
        $product_model = "";
        $purchase_date = "";
        $techinician = 0;
        $allocation_date = "";
        $allocation_time = "";
        $status = "new";
        //---------------//
        // echo "----Call_allocation".$complaint_no." ".$service_center["id"]." ".$product_serial_no." ".$product_model." ".$purchase_date." ".$techinician." ".$allocation_date." ".$allocation_time." ".$status;

        $stmt = $obj->con1->prepare("INSERT INTO `call_allocation`(`complaint_no`, `service_center_id`, `product_serial_no`, `product_model`, `purchase_date`, `technician`, `allocation_date`, `allocation_time`, `status`) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sisssisss", $complaint_no, $service_center["id"], $product_serial_no, $product_model, $purchase_date, $techinician, $allocation_date, $allocation_time, $status);
        $result = $stmt->execute();
        $stmt->close();
        if (!$Resp) {
            throw new Exception("Problem in adding! " . strtok($obj->con1->error,  '('));
        }
    } catch (\Exception  $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
        echo urlencode($e->getMessage());
    }

    if ($Resp) {
        setcookie("msg", "data", time() + 3600, "/");
        header("location:complaint_demo.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:complaint_demo.php");
    }
}
?>
<!--  -->
<div class='p-6'>
    <div class="panel mt-2">
        <div class='flex items-center justify-between mb-5'>
            <h5 class="text-2xl text-primary font-semibold dark:text-white-light">Complaint / Demo -
                <?php echo isset($mode) ? ($mode == 'view' ? 'View' : ($mode == 'edit' ? 'Edit' : 'Add')) : 'Add'; ?>
            </h5>
        </div>
        <div class="mb-5">
            <form method="post" id="mainForm" enctype="multipart/form-data">
                <div class="flex flex-wrap">
                    <div class="w-6/12 px-3 space-y-5">
                        <div>
                            <label for="fname"> First Name </label>
                            <input name="fname" id="fname" type="text" class="form-input"
                                value="<?php echo (isset($mode)) ? $data['fname'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                        <div>
                            <label for="lname"> Last Name </label>
                            <input name="lname" id="lname" type="text" class="form-input"
                                value="<?php echo (isset($mode)) ? $data['lname'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                        <div>
                            <label for="mail">Email</label>
                            <input name="mail" id="mail" type="email" class="form-input"
                                pattern="^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$"
                                title="Invalid Email Format" value="<?php echo (isset($mode)) ? $data['email'] : '' ?>"
                                required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                        <div>
                            <label for="contact_num">Contact </label>
                            <div class="flex">
                                <div
                                    class="bg-[#eee] flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                                    +91</div>
                                <input name="contact_num" id="contact_num" type="tel" placeholder="1234567890"
                                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                    value="<?php echo (isset($mode)) ? $data['contact'] : '' ?>" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> maxlength="10" minlength="10" pattern="[0-9]+"
                                    title="Please enter numbers only" required />
                            </div>
                        </div>
                        <div>
                            <label for="alt_contact_num"> Alternate Contact </label>
                            <div class="flex">
                                <div
                                    class="bg-[#eee] flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                                    +91</div>
                                <input name="alt_contact_num" id="alt_contact_num" type="tel" placeholder="1234567890"
                                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                    value="<?php echo (isset($mode)) ? $data['alternate_contact'] : '' ?>" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> maxlength="10" minlength="10"
                                    pattern="[0-9]+" title="Please enter numbers only" />
                            </div>
                        </div>
                        <div>
                            <label for="area"> City </label>
                            <select name="area" id="area" class="form-select text-white-dark" required>
                                <option value="">Choose City</option>
                                <?php
                                $query = $obj->con1->prepare("SELECT * FROM `city` WHERE status='enable'");
                                $query->execute();
                                $Resp = $query->get_result();
                                while ($row = mysqli_fetch_array($Resp)) {
                                    ?>
                                    <option value="<?php echo $row['srno'] ?>" <?php echo isset($mode) && $row['srno'] == $data['area'] ? 'selected' : '' ?>>
                                        <?php echo $row['ctnm'] ?>
                                    </option>
                                <?php
                                }
                                $query->close();
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="address">Address </label>
                            <textarea autocomplete="on" name="address" id="address" class="form-textarea" rows="2"
                                value="" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo (isset($mode)) ? $data['address'] : '' ?></textarea>
                        </div>
                    </div>
                    <div class="w-6/12 px-3 space-y-5">
                        <div>
                            <label for="pincode"> Pincode </label>
                            <input name="pincode" id="pincode" type="text" class="form-input" pattern="^[1-9][0-9]{5}$"
                                title="enter valid pincode" maxlength="6"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                value="<?php echo (isset($mode)) ? $data['zipcode'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                        <div>
                            <label for="service_type"> Service Type </label>
                            <select name="service_type" id="service_type" class="form-select text-white-dark" required>
                                <option value="">Choose Service Type</option>
                                <?php
                                $query = $obj->con1->prepare("SELECT * FROM `service_type` WHERE status='enable'");
                                $query->execute();
                                $Resp = $query->get_result();
                                while ($row = mysqli_fetch_array($Resp)) {
                                    ?>
                                    <option value="<?php echo $row["id"]; ?>" <?php echo isset($mode) && $row['id'] == $data['service_type'] ? 'selected' : '' ?>>
                                        <?php echo $row["name"]; ?>
                                    </option>
                                <?php
                                }
                                $query->close();
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="product_category">Product Category </label>
                            <select name="product_category" id="product_category" class="form-select text-white-dark"
                                required>
                                <option value="">Choose Product Category</option>
                                <?php
                                $query = $obj->con1->prepare("SELECT * FROM `product_category`");
                                $query->execute();
                                $Resp = $query->get_result();
                                while ($row = mysqli_fetch_array($Resp)) {
                                    ?>
                                    <option value="<?php echo $row["id"]; ?>" <?php echo isset($mode) && $row['id'] == $data['product_category'] ? 'selected' : '' ?>>
                                        <?php echo $row["name"]; ?>
                                    </option>
                                <?php
                                }
                                $query->close();
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="description">Description </label>
                            <input name="description" id="description" type="text" class="form-input"
                                value="<?php echo isset($mode) ? $data['description'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                        <div>
                            <label for="dealer_name">Dealer Name </label>
                            <input name="dealer_name" id="dealer_name" type="text" class="form-input"
                                value="<?php echo (isset($mode)) ? $data['dealer_name'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                        <div>
                            <label for="barcode">Barcode </label>
                            <input name="barcode" id="barcode" type="text" class="form-input"
                                value="<?php echo (isset($mode)) ? $data['barcode'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                        </div>
                        <div x-data="cmplnDate">
                            <label>Date </label>
                            <input x-model="date2" name="complaint_date" id="complaint_date" class="form-input" value=""
                                required <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                        </div>
                        <div x-data="complaintTime">
                            <label>Time </label>
                            <input name="complaint_time" id="complaint_time" class="form-input" required <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                        </div>
                    </div>
                </div>
                <div
                    class="relative inline-flex align-middle gap-3 mt-4 <?php echo isset($mode) && $mode == 'view' ? "hidden" : ""; ?>">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
                        id="save" class="btn btn-success" onclick="return validateAndDisable()">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" id="close_btn" name="close_btn" class="btn btn-danger"
                        onclick="window.location='complaint_demo.php'">Close</button>
                </div>
                <!------ Hidden Inputs ------>
                <input type="hidden" name="map_location" id="map_location">
            </form>
        </div>
    </div>
</div>


<script>
    function resetForm() {
        window.location = "complaint_demo.php"
    }

    document.addEventListener("alpine:init", () => {
        let todayDate = new Date();
        let formattedToday = todayDate.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }).split('/').join('-')

        Alpine.data("cmplnDate", () => ({
            date2: '<?php echo (isset($mode)) ? $data['date'] : date("d-m-Y") ?>',
            init() {
                flatpickr(document.getElementById('complaint_date'), {
                    dateFormat: 'd-m-Y',
                    minDate: formattedToday,
                    defaultDate: this.date2,
                    minDate: "today",
                })
            }
        }));

        Alpine.data("complaintTime", () => ({
            <?php if (!isset($mode)) { ?>
                    time: todayDate.toLocaleTimeString('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                }),
            <?php } ?>
            init() {
                flatpickr(document.getElementById('complaint_time'), {
                    defaultDate: '<?php echo isset($mode) ? $data['time'] : date("h:i a") ?>',
                    noCalendar: true,
                    enableTime: true,
                    dateFormat: 'h:i K'
                });
            }
        }));
    });

    function showPosition(position) {
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;
        const mapLocation = `${latitude},${longitude}`;
        document.getElementById('map_location').value = mapLocation;
    }

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        getLocation();
    });
</script>

<?php
include "footer.php";
?>