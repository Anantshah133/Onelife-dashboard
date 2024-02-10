<?php
include "header.php";
error_reporting(E_ALL);
?>

<!-- echo "UPDATE `call_allocation` SET complaint_no=$complaintNum, service_center_id=$serviceCenterId, product_serial_no=$productSerialNum, serial_no_img=$serialNumImg, product_model=$productModel, product_model_img=$productModelImg, purchase_date=$purchaseDate, purchase_date_img=$purchaseDateImg, technician=$technician, allocation_date=$allocationDate, allocation_time=$allocationTime, status=$callStatus, reason=$reason WHERE call_allocation.id=$editId"; -->

<?php 
if(isset($_REQUEST['viewId'])){
    $mode = 'view';
    $viewId = $_REQUEST['viewId'];
    $query = $obj->con1->prepare("SELECT c1.*, sc1.name AS service_center, t1.name AS tech 
    FROM call_allocation c1 
        INNER JOIN service_center sc1 ON c1.service_center_id = sc1.id
        LEFT JOIN technician t1 ON (c1.technician != 0 AND c1.technician = t1.id)
    WHERE c1.id=?");
    $query->bind_param("i", $viewId);
    $query->execute();
    $Res = $query->get_result();
    $data = $Res->fetch_assoc();
    $query->close();
}

if(isset($_REQUEST['editId'])){
    $mode = 'edit';
    $editId = $_REQUEST['editId'];
    $query = $obj->con1->prepare("SELECT c1.*, sc1.name AS service_center, t1.name AS tech 
    FROM call_allocation c1 
        INNER JOIN service_center sc1 ON c1.service_center_id=sc1.id
        LEFT JOIN technician t1 ON (c1.technician != 0 AND c1.technician=t1.id)
    WHERE c1.id=?");
    $query->bind_param("i", $editId);
    $query->execute();
    $Res = $query->get_result();
    $data = $Res->fetch_assoc();
    $query->close();
}

if(isset($_REQUEST['update'])){
    $complaintNum = $_REQUEST['complaint_num'];
    $serviceCenterId = $_REQUEST['service_center'];
    $productSerialNum = $_REQUEST['product_srno'];
    $productModel = $_REQUEST['product_model'];
    $purchaseDate = $_REQUEST['purchase_date'];
    $technician = $_REQUEST['technician'];
    $callStatus = $_REQUEST['call_status'];
    $reason = $_REQUEST['rson'];
    $allocationDate = $_REQUEST['allocation_date'];
    $allocationTime = $_REQUEST['allocation_time'];
    $editId = $_REQUEST['editId'];

    if($_FILES['srno_img']['size'] > 0) {
        // Process image upload
        $serialNumImg = uploadImage('srno_img', 'images/serial_no_img');
        
        // Update the image file name in the database
        $stmt = $obj->con1->prepare("UPDATE `call_allocation` SET serial_no_img=? WHERE call_allocation.id=?");
        $stmt->bind_param("si", $serialNumImg, $editId);
        $Resp = $stmt->execute();
        $stmt->close();
        
        // Remove the old image file
        if(isset($data['serial_no_img'])) {
            $oldSerialNumImg = $data['serial_no_img'];
            unlink("images/serial_no_img/".$oldSerialNumImg);
        }
        
        move_uploaded_file($_FILES['srno_img']['tmp_name'], "images/serial_no_img/".$serialNumImg);
    }

    if($_FILES['product_model_img']['size'] > 0) {
        $productModelImg = uploadImage('product_model_img', 'images/product_model_img');
        
        $stmt = $obj->con1->prepare("UPDATE `call_allocation` SET product_model_img=? WHERE call_allocation.id=?");
        $stmt->bind_param("si", $productModelImg, $editId);
        $Resp = $stmt->execute();
        $stmt->close();
        
        if(isset($data['product_model_img'])) {
            $oldSerialNumImg = $data['product_model_img'];
            unlink("images/product_model_img/".$oldSerialNumImg);
        }
        
        move_uploaded_file($_FILES['product_model_img']['tmp_name'], "images/product_model_img/".$productModelImg);
    }
    
    if($_FILES['purchase_date_img']['size'] > 0) {
        $purchaseDateImg = uploadImage('purchase_date_img', 'images/purchase_date_img');

        $stmt = $obj->con1->prepare("UPDATE `call_allocation` SET purchase_date_img=? WHERE call_allocation.id=?");
        $stmt->bind_param("si", $purchaseDateImg, $editId);
        $Resp = $stmt->execute();
        $stmt->close();

        if(isset($data['purchase_date_img'])) {
            $oldSerialNumImg = $data['purchase_date_img'];
            unlink("images/purchase_date_img/".$oldSerialNumImg);
        }

        move_uploaded_file($_FILES['purchase_date_img']['tmp_name'], "images/purchase_date_img/".$purchaseDateImg);
    }

    try {
        $stmt = $obj->con1->prepare("UPDATE `call_allocation` SET complaint_no=?, service_center_id=?, product_serial_no=?, serial_no_img=?, product_model=?, product_model_img=?, purchase_date=?, purchase_date_img=?, technician=?, allocation_date=?, allocation_time=?, status=?, reason=? WHERE call_allocation.id=?");

        $stmt->bind_param("sissssssissssi", $complaintNum, $serviceCenterId, $productSerialNum, $serialNumImg, $productModel, $productModelImg, $purchaseDate, $purchaseDateImg, $technician, $allocationDate, $allocationTime, $callStatus, $reason, $editId);
        $Resp=$stmt->execute();

        if(!$Resp) {
            throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
        }
        $stmt->close();
    }
    catch(\Exception  $e) {
        setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
    }

    if($Resp) {
        setcookie("msg", "update",time()+3600,"/");
        header("location:call_allocation.php");
    }
    else {
        setcookie("msg", "fail",time()+3600,"/");
        header("location:call_allocation.php");
    }
}

if(isset($_REQUEST['save'])){
    $complaintNum = $_REQUEST['complaint_num'];
    $serviceCenterId = $_REQUEST['service_center'];
    $productSerialNum = $_REQUEST['product_srno'];
    $productModel = $_REQUEST['product_model'];
    $purchaseDate = $_REQUEST['purchase_date'];
    $technician = $_REQUEST['technician'];
    $callStatus = $_REQUEST['call_status'];
    $reason = $_REQUEST['rson'];
    $allocationDate = $_REQUEST['allocation_date'];
    $allocationTime = $_REQUEST['allocation_time'];


    try {
        $serialNumImg = uploadImage('srno_img', 'images/serial_no_img');
        $productModelImg = uploadImage('product_model_img', 'images/product_model_img');
        $purchaseDateImg = uploadImage('purchase_date_img', 'images/purchase_date_img');

        $stmt = $obj->con1->prepare("INSERT INTO `call_allocation`(`complaint_no`, `service_center_id`, `product_serial_no`, `serial_no_img`, `product_model`, `product_model_img`, `purchase_date`, `purchase_date_img`, `technician`, `allocation_date`, `allocation_time`, `status`, `reason`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sissssssissss", $complaintNum, $serviceCenterId, $productSerialNum, $serialNumImg, $productModel, $productModelImg, $purchaseDate, $purchaseDateImg, $technician, $allocationDate, $allocationTime, $callStatus, $reason);
        $Resp=$stmt->execute();

        if(!$Resp) {
            throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
        }
        $stmt->close();
    }
    catch(\Exception  $e) {
        setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
    }

    if($Resp) {
        move_uploaded_file($_FILES['srno_img']['tmp_name'], "images/serial_no_img/".$serialNumImg);
        move_uploaded_file($_FILES['product_model_img']['tmp_name'], "images/product_model_img/".$productModelImg);
        move_uploaded_file($_FILES['purchase_date_img']['tmp_name'], "images/purchase_date_img/".$purchaseDateImg);

        setcookie("msg", "data",time()+3600,"/");
        header("location:call_allocation.php");
    }
    else {
        setcookie("msg", "fail",time()+3600,"/");
        header("location:call_allocation.php");
    }
}

function uploadImage($inputName, $uploadDirectory) {
    $fileName = $_FILES[$inputName]['name'];
    $tmpFilePath = $_FILES[$inputName]['tmp_name'];
    echo $fileName.$tmpFilePath;
    if ($fileName != "") {
        $targetDirectory = $uploadDirectory . '/';
        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }
        $i = 0;
        $newFileName = $fileName;
        while (file_exists($targetDirectory . $newFileName)) {
            $i++;
            $newFileName = $i . '_' . $fileName;
        }
        $targetFilePath = $targetDirectory . $newFileName;
        return $newFileName;
    }

    return null;
}
?>

<div class='p-6'>
    <div class="panel mt-2">
        <div class='flex items-center justify-between mb-5'>
            <h5 class="text-2xl text-primary font-semibold dark:text-white-light">Call Allocation - 
                <?php echo isset($mode) ? ($mode == 'edit' ? 'Edit' : 'View') : 'Add' ?>
            </h5>
        </div>
        <div class="mb-5">
            <form id="mainForm" method="post" enctype="multipart/form-data">
                <div class="flex flex-wrap">
                    <div class="w-6/12 px-3 space-y-5">
                        <div>
                            <label for="complaint_num"> Complaint No. </label>
                            <input name="complaint_num" id="complaint_num" type="text" class="form-input"
                            required <?php echo isset($mode) ? 'readonly' : '' ?> value="<?php echo isset($mode) ? $data['complaint_no'] : ''?>" />
                        </div>
                        <div>
                            <label for="service_center">Service Center</label>
                            <select name="service_center" id="service_center" class="form-select text-white-dark"
                                required <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?>>
                                <option value="">Choose service center</option>
                                <?php
                                    $stmt = $obj->con1->prepare("SELECT * FROM `service_center` WHERE status='enable'");
                                    $stmt->execute();
                                    $Resp = $stmt->get_result();
                                    $stmt->close();
                                    while ($result = mysqli_fetch_array($Resp)) { 
                                ?>
                                    <option value="<?php echo $result["id"]; ?>"
                                        <?php echo isset($mode) && $result['id'] == $data['service_center_id'] ? 'selected' : '' ?>
                                    >
                                        <?php echo $result["name"]; ?>
                                    </option>
                                <?php 
                                    } 
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="product_srno"> Product Serial NO. </label>
                            <input name="product_srno" id="product_srno" type="text" class="form-input" 
                            required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> 
                            value="<?php echo isset($mode) ? $data['product_serial_no'] : ''?>" />
                        </div>
                        <div>
                            <label for="srno_img">Serial NO. Image</label>
                            <input name="srno_img" id="srno_img" type="file" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-primary/90 ltr:file:mr-5 file:text-white file:hover:bg-primary" value="<?php echo isset($mode) ? $data['serial_no_img'] : "" ?>" required onchange="readURL(this, 'srNoImg', 'errSrNoImg')">

                            <img src="<?php echo isset($mode) && isset($data['serial_no_img']) ? 'images/serial_no_img/'.$data['serial_no_img'] : '' ?>" 
                            class="mt-8 <?php echo isset($mode) && isset($data['serial_no_img']) ? '' : 'hidden' ?> w-80 preview-img" alt="" id="srNoImg">
                            <h6 id='errSrNoImg' class='error-elem'></h6>
                        </div>
                        <div>
                            <label for="product_model"> Product Model </label>
                            <input name="product_model" id="product_model" type="text" class="form-input" 
                            required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> value="<?php echo isset($mode) && isset($data['product_model']) ? $data['product_model'] : "" ?>" />
                        </div>
                        <div>
                            <label for="product_model_img"> Product Model Image </label>
                            <input name="product_model_img" id="product_model_img" type="file" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-primary/90 ltr:file:mr-5 file:text-white file:hover:bg-primary" value="<?php echo isset($mode) ? $data['product_model_img'] : "" ?>"
                            required onchange="readURL(this, 'previewModalImage', 'errModalImg')" />

                            <img src="<?php echo isset($mode) && isset($data['product_model_img']) ? 'images/product_model_img/'.$data['product_model_img'] : '' ?>" 
                            class="mt-8 <?php echo isset($mode) && isset($data['serial_no_img']) ? '' : 'hidden' ?> w-80 preview-img" alt="" id="previewModalImage">
                            <h6 id='errModalImg' class='error-elem'></h6>
                        </div>
                    </div>
                    <div class="w-6/12 px-3 space-y-5">
                        <div x-data="purchaseDate">
                            <label for="purchase_date"> Purchase Date </label>
                            <input x-model="date1" name="purchase_date" id="purchase_date" class="form-input" 
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> value="<?php echo isset($mode) && isset($data['purchase_data']) ? $data['purchase_data'] : '' ?>" />
                        </div>
                        <div>
                            <label for="purchase_date_img">Purchase Date Image</label>
                            <input id="purchase_date_img" name="purchase_date_img" type="file" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-primary/90 ltr:file:mr-5 file:text-white file:hover:bg-primary" value="<?php echo isset($mode) ? $data['purchase_date_img'] : "" ?>"
                            required onchange="readURL(this, 'purDateImg', 'errPurDateImg')" />

                            <img src="<?php echo isset($mode) && isset($data['purchase_date_img']) ? 'images/purchase_date_img/'.$data['purchase_date_img'] : '' ?>" 
                            class="mt-8 <?php echo isset($mode) && isset($data['serial_no_img']) ? '' : 'hidden' ?> w-80 preview-img" alt="" id="purDateImg">
                            <h6 id='errPurDateImg' class='error-elem'></h6>
                        </div>
                        <div>
                            <label for="technician">Technician </label>
                            <select class="form-select text-white-dark" name="technician" 
                            required <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> >
                                <option value="">Choose Technician</option>
                                <?php
                                    $stmt = $obj->con1->prepare(
                                        "SELECT * FROM `technician` WHERE status='enable'"
                                    );
                                    $stmt->execute();
                                    $Res = $stmt->get_result();
                                    $stmt->close();

                                    while ($result = mysqli_fetch_assoc($Res)) { 
                                ?>
                                    <option value="<?php echo $result["id"]; ?>" 
                                        <?php echo isset($mode) && $result['id'] == $data['technician'] ? 'selected' : '' ?>
                                    >
                                        <?php echo $result["name"]; ?>
                                    </option>
                                <?php 
                                    } 
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="call_status">Status</label>
                            <select name="call_status" id="call_status" class="form-select text-white-dark" 
                            required <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> >
                                <option value="">Choose Status</option>
                                <option value="new" <?php echo isset($mode) && $data['status'] == 'new' ? 'selected' : '' ?>>
                                    New
                                </option>
                                <option value="pending" <?php echo isset($mode) && $data['status'] == 'pending' ? 'selected' : '' ?>>
                                    Pending
                                </option>
                                <option value="cancelled" <?php echo isset($mode) && $data['status'] == 'cancelled' ? 'selected' : '' ?>>
                                    Cancelled
                                </option>
                                <option value="closed" <?php echo isset($mode) && $data['status'] == 'closed' ? 'selected' : '' ?>>
                                    Closed
                                </option>
                                <option value="allocated" <?php echo isset($mode) && $data['status'] == 'allocated' ? 'selected' : '' ?>>
                                    Allocated
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="rson">Reason </label>
                            <input id="rson" name="rson" type="text" class="form-input" 
                            required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>
                            value="<?php echo isset($mode) ? $data['reason'] : "" ?>" />
                        </div>

                        <div x-data="allocationDate">
                            <label for="allocation_date">Allocation Date </label>
                            <input x-model="date2" name="allocation_date" id="allocation_date" class="form-input" value="" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                        </div>

                        <div x-data="allocationTime">
                            <label for="allocation_time"> Allocation Time </label>
                            <input x-model="time" name="allocation_time" id="allocation_time" class="form-input" value=""
                            <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> />
                        </div>
                    </div>
                </div>
                <div class="relative inline-flex align-middle gap-3 mt-10 <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
                    <button type="submit" id="save" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" class="btn btn-success" onclick="return validateAndDisable()"><?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?></button>
                    <button type="button" id="close_btn" name="close_btn" class="btn btn-danger"
                        onclick="window.location=`call_allocation.php`">Close</button>
                </div>
                <!-- <input type="hidden" name="allocation_date" x-bind:value="date2"> -->
                <!-- <input type="hidden" name="allocation_time" x-bind:value="time"> -->
            </form>
        </div>
    </div>
</div>


<div class='px-6 py-4' x-data='exportTable'>
    <div class="panel">
        <div class='flex items-center justify-between mb-3'>
            <h1 class='text-primary text-2xl font-bold'>Call History</h1>

            <div class="flex flex-wrap items-center">
                <button type="button" class="p-2 btn btn-primary btn-sm m-1" onclick="location.href='add_call_history.php'">
                    <i class="ri-add-line mr-1"></i> Add Call History
                </button>
                <button type="button" class="p-2 btn btn-primary btn-sm m-1" @click="printTable">
                    <i class="ri-printer-line mr-1"></i> PRINT
                </button>
                <button type="button" class="p-2 btn btn-primary btn-sm m-1" @click="exportTable('csv')">
                    <i class="ri-file-line mr-1"></i> CSV
                </button>
            </div>
        </div>
        <table id="myTable" class="table-hover whitespace-nowrap"></table>
    </div>
</div>

<script>
document.addEventListener("alpine:init", () => {
    let todayDate = new Date();
    let formattedToday = todayDate.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).split('/').join('-');


    Alpine.data("purchaseDate", () => ({
        date1: formattedToday,
        init() {
            flatpickr(document.getElementById('purchase_date'), {
                dateFormat: 'd-m-Y',
                defaultDate: this.date1,
            })
        }
    }));

    Alpine.data("allocationDate", () => ({
        date2: formattedToday,
        init() {
            flatpickr(document.getElementById('allocation_date'), {
                dateFormat: 'd-m-Y',
                minDate: formattedToday,
                defaultDate: this.date2,
            })
        }
    }));

    Alpine.data("allocationTime", () => ({
        time: todayDate.toLocaleTimeString('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }),
        init() {
            flatpickr(document.getElementById('allocation_time'), {
                defaultDate: this.time,
                noCalendar: true,
                enableTime: true,
                dateFormat: 'H:i'
            });
        }
    }));
});


function readURL(input, preview, errElement) {
    if (input.files && input.files[0]) {
        var filename = input.files[0].name;
        var reader = new FileReader();
        var extn = filename.split('.').pop().toLowerCase();

        var allowedExtns = ["jpg", "jpeg", "png", "bmp", "webp"];

        if (allowedExtns.includes(extn)) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('#' + preview).src = e.target.result;
                document.getElementById(preview).style.display = "block";
            };

            reader.readAsDataURL(input.files[0]);
            document.getElementById(errElement).innerHTML = "";
            document.getElementById('save').disabled = false;
        } else {
            document.getElementById(preview).style.display = "none";
            document.getElementById(errElement).innerHTML = "Please Select Image Only";
            document.getElementById('save').disabled = true;
        }
    }
}

const resetForm = (formElement) => {
    formElement.reset();
    let preview = document.querySelectorAll('.preview-img');
    preview.forEach(img => img.style.display = 'none');
}
</script>


<!-- script -->
<script>
checkCookies();

document.addEventListener('alpine:init', () => {
    Alpine.data('exportTable', () => ({
        datatable: null,
        init() {
            console.log('Initalizing datatable')
            this.datatable = new simpleDatatables.DataTable('#myTable', {
                data: {
                    headings: ['Sr.no','Complaint No.', 'Service Center', 'Technician', 'Parts Used', 'Call Type', 'Service Charges', 'Parts Charges',
                        'Status', 'Reason',  'Date Time'
                    ],
                    data: [
                        <?php 
                            $stmt = $obj->con1->prepare("SELECT * FROM `call_history`");
                            $stmt->execute();
                            $Resp=$stmt->get_result();
                            $id=1;
                            while($row = mysqli_fetch_array($Resp)){
                        ?>
                            [
                                <?php echo $id ?>,
                                '<?php echo $row['complaint_no'] ?>',
                                '<?php echo $row['service_center'] ?>',
                                '<?php echo $row['technician'] ?>',
                                '<?php echo $row['parts_used'] ?>',
                                '<?php echo $row['call_type'] ?>',
                                '<?php echo $row['service_charge'] ?>',
                                '<?php echo $row['parts_charge'] ?>',
                                '<?php echo $row['status'] ?>',
                                '<?php echo $row['reason'] ?>',
                                '<?php echo $row['date_time'] ?>',
        
                            ],
                        <?php
                        $id++;
                            }
                        ?>
                    ],
                },
                perPage: 10,
                perPageSelect: [10, 20, 30, 50, 100],
                columns: [{
                        select: 0,
                        sort: 'asc',
                    },
                ],
                firstLast: true,
                firstText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                lastText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M11 19L17 12L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M6.99976 19L12.9998 12L6.99976 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                prevText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                nextText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                labels: {
                    perPage: '{select}',
                },
                layout: {
                    top: '{search}',
                    bottom: '{info}{select}{pager}',
                },
            });
        },

        exportTable(eType) {
            var data = {
                type: eType,
                filename: 'table',
                download: true,
            };

            if (data.type === 'csv') {
                data.lineDelimiter = '\n';
                data.columnDelimiter = ';';
            }
            this.datatable.export(data);
        },

        printTable() {
            this.datatable.print();
        },

        formatDate(date) {
            if (date) {
                const dt = new Date(date);
                const month = dt.getMonth() + 1 < 10 ? '0' + (dt.getMonth() + 1) : dt.getMonth() +
                    1;
                const day = dt.getDate() < 10 ? '0' + dt.getDate() : dt.getDate();
                return day + '/' + month + '/' + dt.getFullYear();
            }
            return '';
        },
    }));
})

</script>
<?php 
include "footer.php";
?>