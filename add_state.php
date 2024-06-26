<?php
include "header.php";

if (isset($_COOKIE['editId'])) {
    $mode = 'edit';
    $editId = $_COOKIE['editId'];
    $stmt = $obj->con1->prepare("SELECT * FROM state WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['viewId'])) {
    $mode = 'view';
    $viewId = $_COOKIE['viewId'];
    $stmt = $obj->con1->prepare("SELECT * FROM state WHERE id=?");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST['update'])) {
    $editId = $_COOKIE['editId'];
    $name = $_REQUEST["name"];

    $stmt = $obj->con1->prepare("UPDATE state SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $editId);
    $Res = $stmt->execute();
    $stmt->close();

    if ($Res) {
        setcookie("msg", "update", time() + 3600, "/");
        header("location:state.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:state.php");
    }
}

if (isset($_REQUEST["save"])) {
    $name = $_REQUEST["name"];
    $mode = "";
    try {
        $stmt = $obj->con1->prepare("INSERT INTO state(name) VALUES (?)");
        $stmt->bind_param("s", $name);
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
        header("location:state.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:state.php");
    }
    exit();
}
?>

<div class='p-6'>
    <div class="panel mt-2">
        <div class='flex items-center justify-between mb-3'>
            <h5 class="text-2xl text-primary font-semibold dark:text-white-light">State -
                <?php echo isset($mode) ? ($mode == 'edit' ? 'Edit' : 'View') : 'Add' ?>
            </h5>
        </div>
        <div class="mb-5">
            <form class="space-y-5" method="post" id="mainForm">
                <div>
                    <label for="groupFname">Name </label>
                    <input id="groupFname" name="name" type="text" class="form-input"
                        onblur="checkName(this,<?php echo isset($mode) ? $data['id'] : 0 ?>)" pattern="^\s*\S.*$"
                        value="<?php echo isset($mode) ? $data['name'] : '' ?>" required />
                    <p class="mt-3 text-danger text-base font-bold" id="demo"></p>
                    <div class="relative inline-flex align-middle gap-3 mt-4">
                    <!-- Save/Update button -->
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
                        id="save" class="btn btn-success" <?php echo isset($mode) && $mode == 'view' ? 'style="display:none;"' : '' ?>
                        onclick="return localValidate()">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <!-- Close button -->
                    <button type="button" class="btn btn-danger" onclick="window.location='state.php'">
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function localValidate(){
        let form = document.getElementById('mainForm');
        let submitButton = document.getElementById('save');
        let nameEle = document.getElementById('groupFname');
        
        if (form.checkValidity() && checkName(nameEle, <?php echo isset($mode) ? $data['id'] : 0 ?>)) {
            setTimeout(() => {
                submitButton.disabled = true;
            }, 0);
            return true;
        }
    }

    function checkName(c1, id) {
        let n = c1.value;
        const obj = new XMLHttpRequest();
        obj.open("GET", "./ajax/check_state.php?name=" + n + "&stid=" + id, false);
        obj.send();
        if(obj.status == 200){
            let x = obj.responseText;
            console.log(n, id);
            if (x >= 1) {
                c1.value = "";
                c1.focus();
                document.getElementById("demo").innerHTML = "Sorry the name alredy exist!";
                return false;
            }
            else {
                document.getElementById("demo").innerHTML = "";
                return true;
            }
        }
    }

</script>

<?php
include "footer.php";
?>