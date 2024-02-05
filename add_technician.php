<?php
include "header.php";

if (isset($_REQUEST["viewId"])) {
    $mode = 'view';
    $viewId = $_REQUEST["viewId"];
    $stmt = $obj->con1->prepare("SELECT * FROM `technician` where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $name = $_REQUEST["name"];
    $email = $_REQUEST["email"];
    $contact = $_REQUEST["contact"];
    $serviceCenterId = $_REQUEST["service_center"];
    $user_id = $_REQUEST["userid"];
    $pass = $_REQUEST["password"];
    $status = $_REQUEST["default_radio"];
    $date_time = date("d-m-Y h:i A");

    try {
        $idproofImg = uploadImage("idproof_img", "images/technician_idproof");
        $stmt = $obj->con1->prepare(
            "INSERT INTO `technician`(`name`,`email`,`contact`,`service_center`,`userid`,`password`,`id_proof`,`status`,`date_time`) VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("sssisssss",$name,$email,$contact,$serviceCenterId,$user_id,$pass,$idproofImg,$status,$date_time);
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
        header("location:technician.php");

        if ($Resp) {
            move_uploaded_file(
                $_FILES["idproof_img"]["tmp_name"],
                "images/technician_idproof/" . $idproofImg
            );
            header("location:technician.php");
        } else {
            setcookie("msg", "fail", time() + 3600, "/");
            header("location:technician.php");
        }
    }
}

function uploadImage($inputName, $uploadDirectory)
    {
        $fileName = $_FILES[$inputName]["name"];
        $tmpFilePath = $_FILES[$inputName]["tmp_name"];
        echo $fileName . $tmpFilePath;
        if ($fileName != "") {
            $targetDirectory = $uploadDirectory . "/";

            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            $i = 0;
            $newFileName = $fileName;

            while (file_exists($targetDirectory . $newFileName)) {
                $i++;
                $newFileName = $i . "_" . $fileName;
            }

            $targetFilePath = $targetDirectory . $newFileName;
            return $newFileName;
        }

        return null;
    }
?>


<div class='p-6'>
    <div class="panel border shadow-md shadow-slate-200">
        <div class="mb-5 flex items-center justify-between">
            <h5 class="text-xl text-primary font-semibold dark:text-white-light">Technician -
                <?php echo isset($mode) == 'view' ? 'View' : 'Add' ?></h5>
        </div>

        <form class="space-y-5" method="post" enctype="multipart/form-data">
            <div>
                <label for="groupFname"> Name</label>
                <input id="groupFname" type="text" name="name" placeholder="Enter Name" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['name'] : '' ?>" required
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> />
            </div>
            <div>
                <label for="ctnEmail">Email address</label>
                <input id="ctnEmail" type="email" name="email" placeholder="name@example.com" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['email'] : '' ?>" required
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> />
            </div>
            <div>
                <label for="groupFname">Contact</label>
                <input id="groupFname" type="text" name="contact" placeholder="" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['contact'] : '' ?>" required
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> />
            </div>
            <div>
                <label for="groupFname"> Service Center</label>

                <select class="form-select text-white-dark" name="service_center" required>
                    <option value="<?php echo (isset($mode)) ? $data['service_center'] : '' ?>">Choose...</option>
                    <?php
                            $stmt = $obj->con1->prepare(
                                "SELECT * FROM `service_center` WHERE status='enable'"
                            );
                            $stmt->execute();
                            $Res = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_assoc($Res)) { 
                        ?>
                    <option value="<?php echo $result["id"]; ?>"><?php echo $result["name"]; ?></option>
                    <?php 
                            } 
                        ?>
                </select>
            </div>
            <div>
                <label for="gridUID">Userid</label>
                <input type="text" placeholder="" name="userid" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['userid'] : '' ?>" required
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> />
            </div>
            <div>
                <label for="gridpass">Password</label>
                <input type="password" placeholder="Enter Password" name="password" class="form-input"
                    value="<?php echo (isset($mode)) ? $data['password'] : '' ?>" required
                    <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''?> />
            </div>
            <div>
                <label for="idproof_img"> Id Proof </label>
                <input name="idproof_img" id="idproof_img" type="file"
                    class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-primary/90 ltr:file:mr-5 file:text-white file:hover:bg-primary"
                    required onchange="readURL(this, 'previewModalImage', 'errModalImg')" />

                <img src="<?php echo (isset($mode)) ? 'images/technician_idproof/'.$data['id_proof'] : '' ?>"
                    class="mt-8  w-80 preview-img" alt="" id="previewModalImage" value="  ">
                <h6 id='errModalImg' class='error-elem'></h6>
            </div>
            <div>
                <label for="gridStatus">Status</label>
                <label class="inline-flex mr-3">
                    <input type="radio" name="default_radio" class="form-radio" checked value="enable" required />
                    <span>Enable</span>
                </label>
                <label class="inline-flex mr-3">
                    <input type="radio" name="default_radio" class="form-radio text-danger" value="disable" required />
                    <span>Disable</span>
                </label>
            </div>
            <div class="relative inline-flex align-middle gap-3 mt-4 <?php echo (isset($mode)) ? 'hidden' : '' ?>">
                <button type="submit" class="btn btn-success" name="save" id="save_btn">Save</button>
                <button type="button" class="btn btn-danger" onclick="location.href='technician.php'">Close</button>
            </div>
        </form>
    </div>
</div>


<script>
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
            document.getElementById('save_btn').disabled = false;
        } else {
            document.getElementById(preview).style.display = "none";
            document.getElementById(errElement).innerHTML = "Please Select Image Only";
            document.getElementById('save_btn').disabled = true;
        }
    }
}

const resetForm = (formElement) => {
    formElement.reset();
    let preview = document.querySelectorAll('.preview-img');
    preview.forEach(img => img.style.display = 'none');
}
</script>

<?php
include "footer.php";
?>