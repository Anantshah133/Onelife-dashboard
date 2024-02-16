<?php
include "header.php";

setcookie("editId", "", time() - 3600);
setcookie("viewId", "", time() - 3600);

if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    try {
        $stmt_del = $obj->con1->prepare(
            "delete from city where srno='" . $_REQUEST["n_cityid"] . "'"
        );
        $Resp = $stmt_del->execute();
        if (!$Resp) {
            if (
                strtok($obj->con1->error, ":") ==
                "Cannot delete or update a parent row"
            ) {
                throw new Exception("City is already in use!");
            }
        }
        $stmt_del->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data_del", time() + 3600, "/");
    }
    header("location:city.php");
}
?>

<div class='p-6' x-data='exportTable'>
    <div class="panel mt-2">
        <div class='flex items-center justify-between mb-3'>
            <h1 class='text-primary text-2xl font-bold'>City</h1>

            <div class="flex flex-wrap items-center">
                <button type="button" class="p-2 btn btn-primary btn-sm m-1" onclick="location.href='add_city.php'">
                    <i class="ri-add-line mr-1"></i> Add City
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
<!-- script -->
<script>
checkCookies();
function getActions(id, name) {
    return `<ul class="flex items-center gap-4">
        <li>
            <a href="javascript:viewRecord(${id}, 'add_city.php')" class='text-xl' x-tooltip="View">
                <i class="ri-eye-line text-primary"></i>
            </a>
        </li>
        <li>
            <a href="javascript:updateRecord(${id}, 'add_city.php')" class='text-xl' x-tooltip="Edit">
                <i class="ri-pencil-line text text-success"></i>
            </a>
        </li>
        <li>
            <a href="javascript:;" class='text-xl' x-tooltip="Delete" @click="showAlert(${id}, '${name}')">
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
                    headings: ['Sr.No.', 'City Name','State Name', 'Status','Action'],
                    data: [
                        <?php
                        $stmt = $obj->con1->prepare(
                            "SELECT c1.*, s1.name FROM `city` c1, `state` s1 WHERE c1.state_id=s1.id"
                        );
                        $stmt->execute();
                        $Resp = $stmt->get_result();
                        $i = 1;
                        while ($row = mysqli_fetch_array($Resp)) { ?>
                        [
                            <?php echo $i; ?>, 
                            '<?php echo $row["ctnm"]; ?>',
                            '<?php echo $row["name"]; ?>',
                            `<span class="badge badge-outline-<?php echo $row["status"] == "enable" ? 'success' : 'danger'?>">
                                <?php echo ucfirst($row["status"]); ?>
                            </span>`,
                            getActions(<?php echo $row["srno"]; ?>, '<?php echo $row["ctnm"]; ?>')
                        ],
                        <?php $i++;}
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
                filename: 'city',
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

    
async function showAlert(id, name) {
    new window.Swal({
        title: 'Are you sure?',
        text: `You want to delete city :- ${name}`,
        showCancelButton: true,
        confirmButtonText: 'Delete',
        padding: '2em',
    }).then((result) => {
        console.log(result)
        if (result.isConfirmed) {
            var loc = "city.php?flg=del&n_cityid=" + id;
            window.location = loc;
        }
    });
}
    
</script>

<?php
include "footer.php";
?>