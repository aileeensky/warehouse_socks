<?php $this->extend($role . '/layout'); ?>
<?php $this->section('content'); ?>
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Input Database</h5>
                    <form action="">
                        <div class="row mb-3">
                            <label for="formFile" class="col-sm-2 col-form-label">File Upload</label>
                            <div class="col-sm-3">
                                <input class="form-control" type="file" name="formFile">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-info">Save</button>
                            </div>
                        </div>
                    </form>
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">No Order</th>
                                <th scope="col">Area</th>
                                <th scope="col">No Model</th>
                                <th scope="col">In</th>
                                <th scope="col">Style</th>
                                <th scope="col">Color</th>
                                <th scope="col">Delivery</th>
                                <th scope="col">Qty PO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">1</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->

                </div>
            </div>

        </div>
    </div>
</section>
<?php $this->endSection(); ?>