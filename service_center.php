<?php
include "header.php";
if(isset($_REQUEST["flg"]) && $_REQUEST["flg"]=="del")
{
  try
  {
   
    $stmt_del = $obj->con1->prepare("delete from service_center where id='".$_REQUEST["id"]."'");
    $Resp=$stmt_del->execute();
    if(!$Resp)
    {
      if(strtok($obj->con1-> error,  ':')=="Cannot delete or update a parent row")
      {
        throw new Exception("City is already in use!");
      }
    }
    $stmt_del->close();
  }
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "data_del",time()+3600,"/");
    // echo "this data is deleted";
    // echo "<script type='text/javascript'>coloredToast('success')</script>";
  }
    header("location:service_center.php");
}

?>
<div class='p-6' x-data='exportTable'>
    <div class="panel mt-6">
        <div class='flex items-center justify-between mb-3'>
            <h1 class='text-primary text-2xl font-bold'>Service Center</h1>

            <div class="flex flex-wrap items-center">
                <button type="button" class="p-2 btn btn-primary btn-sm m-1"
                    onclick="location.href='add_service_center.php'">
                    <i class="ri-add-line mr-1"></i> Add
                </button>
                <button type="button" class="p-2 btn btn-primary btn-sm m-1" @click="printTable">
                    <i class="ri-printer-line mr-1"></i> PRINT
                </button>
                <button type="button" class="p-2 btn btn-primary btn-sm m-1" @click="exportTable('csv')">
                    <i class="ri-file-line mr-1"></i> CSV
                </button>
            </div>
        </div>
        <table id="myTable" class="table-hover whitespace-nowrap">

        </table>
    </div>
</div>

<!-- script -->
<script>
function createCookie(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = (name) + "=" + String(value) + expires + ";path=/ ";

}

function readCookie(name) {
    var nameEQ = (name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return (c.substring(nameEQ.length, c.length));
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}
coloredToast = (color) => {
    const toast = window.Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        showCloseButton: true,
        customClass: {
            popup: `color-${color}`
        },
    });
    toast.fire({
        title: 'Record Deleted Successfully.',
        onClose: () => {
            document.cookie = "msg=data_del; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }
    });
};

if (readCookie("msg") == "data_del") {
    coloredToast("success");
    eraseCookie("msg")
}

// let x = document.cookie;
// console.log(x)
// if (x == "msg=data_del") {
//     coloredToast('success')
// }

function getActions(id) {

    return `<ul class="flex items-center justify-center gap-4">
        <li>
            <a href="javascript:;" class='text-xl' x-tooltip="View">
                <i class="ri-eye-line text-primary"></i>
            </a>
        </li>
        <li>
            <a href="javascript:;" class='text-xl' x-tooltip="Edit">
                <i class="ri-pencil-line text text-success"></i>
            </a>
        </li>
        <li>
            <a href="javascript:;" class='text-xl' x-tooltip="Delete"  @click="showAlert(` + id + `)">
                <i class="ri-delete-bin-line text-danger"></i>
            </a>
        </li>
    </ul>`
}


document.addEventListener('alpine:init', () => {
    Alpine.data('exportTable', () => ({
        datatable: null,
        init() {
            console.log('Initalizing datatable')
            this.datatable = new simpleDatatables.DataTable('#myTable', {
                data: {
                    headings: ['Sr.No.', 'Name', '	Email', '	Contact',
                        'Adress', 'Area', 'Status', 'Date Time',
                        'Action'
                    ],
                    data: [
                        <?php
                          
                            $stmt =  $obj->con1->prepare("SELECT sc1.*, sa1.name AS state FROM service_center sc1, service_area sa1 WHERE sc1.area=sa1.id");
                            $stmt->execute();
                            $res_stmt=$stmt->get_result();
                            $stmt->close();
                            
                            $id=1;
                            while($row=mysqli_fetch_array($res_stmt)){
                               
                                           
                        ?>

                        ['<?php echo $id ?>', '<?php echo $row["name"] ?>',
                            '<?php echo $row["email"] ?>',
                            '<?php echo $row["contact"] ?>',
                            '<?php echo $row["address"] ?>',
                            '<?php echo $row["state"] ?>',
                            ' <?php echo $row["status"] ?>',
                            ' <?php echo $row["date_time"] ?>', getActions((
                                '<?php echo $row['id'] ?>'))],
                        <?php 
                        $id++;	
                            }
                        ?>
                    ],
                    // SELECT sc1.*, sa1.name AS state FROM service_center sc1, service_area sa1 WHERE sc1.area=sa1.id.

                },
                perPage: 10,
                perPageSelect: [10, 20, 30, 50, 100],
                columns: [{
                        select: 0,
                        sort: 'asc',
                    },
                    // {
                    //     select: 4,
                    //     render: (data, cell, row) => {
                    //         return this.formatDate(data);
                    //     },
                    // },
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


async function showAlert(id) {
    new window.Swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonText: 'Delete',
        padding: '2em',
    }).then((result) => {
        console.log(result)
        if (result.isConfirmed) {
            var loc = "service_center.php?flg=del&id=" + id;
            window.location = loc;

            // coloredToast('success')
        }
    });
}
</script>

<?php
include "footer.php";
?>