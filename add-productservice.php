<?php
include "header.php";
?>
<div class='p-6' x-data='exportTable'>
    <div class="panel mt-6">
        <div class='flex items-center justify-between mb-3'>
            <h5 class="text-lg font-semibold dark:text-white-light">Product-Service- Add</h5>
        </div>
        <div class="mb-5">
            <form class="space-y-5">
                <div>
                    <label for="groupFname"> Product</label>

                    <select class="form-select text-white-dark">
                        <option>-none-</option>
                        <option>MIRA BHAYANDER</option>
                        <option>N H SERVICE</option>
                        <option>NO SERVICE</option>
                        <option>PALGHAR</option>
                        <option>Test Service center</option>
                        <option>VIRAR NSP VASAI</option>
                    </select>
                </div>
                <div>
                    <label for="groupFname">Service</label>

                    <select class="form-select text-white-dark">
                        <option>-none-</option>
                        <option>MIRA BHAYANDER</option>
                        <option>N H SERVICE</option>
                        <option>NO SERVICE</option>
                        <option>PALGHAR</option>
                        <option>Test Service center</option>
                        <option>VIRAR NSP VASAI</option>
                    </select>
                </div>
                <div>
                    <label for="gridStatus">Status</label>
                    <label class="inline-flex">
                        <input type="radio" name="default_radio" value="enable" class="form-radio" checked required />
                        <span>Enable</span>
                    </label>
                    <label class="inline-flex">
                        <input type="radio" name="default_radio" value="disable" class="form-radio text-danger" required />
                        <span>Disable</span>
                    </label>
                </div>
                    
                    <div class="relative inline-flex align-middle gap-3 mt-4">
                        <button type="button" class="btn btn-primary">Save </button>
                        <button type="button" class="btn  btn-warning ">Close</button>
                    </div>
                </div>
        </div>
        </form>
    </div>
</div>

<?php
include "footer.php";
?>