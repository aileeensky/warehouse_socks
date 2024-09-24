<?php $this->extend($role . '/layout'); ?>
<?php $this->section('content'); ?>
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Data Permintaan</h5>
                    <form action="">
                        <div class="row mb-2">
                            <label for="cari" class="col-sm-2 col-form-label">No Model / Jalur</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="text" name="cari1">
                            </div>
                            <label for="cari2" class="col-sm-1 col-form-label">Tgl Jalan</label>
                            <div class="col-sm-2">
                                <input class="form-control" type="date" name="cari2">
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-info">Search</button>
                            </div>
                        </div>
                    </form>
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Tgl Minta</th>
                                <th scope="col">Tgl Jalan</th>
                                <th scope="col">Packing</th>
                                <th scope="col">Area</th>
                                <th scope="col">Buyer</th>
                                <th scope="col">No Model</th>
                                <th scope="col">In</th>
                                <th scope="col">Style</th>
                                <th scope="col">Qty Minta</th>
                                <th scope="col">Qty Kirim</th>
                                <th scope="col">Tagihan</th>
                                <th scope="col">Edit</th>
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