<?php $this->extend($role . '/layout'); ?>
<?php $this->section('content'); ?>
<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Data Terkirim</h5>
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
                                <th scope="col">Tgl Kirim</th>
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
                                <th scope="col">Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($terkirim as $data) :
                                $maxKirim = $data['qty_minta'] + ($data['qty_minta'] * 0.2);
                            ?>
                                <tr>
                                    <th scope="row"><?= $no ?></th>
                                    <td><?= $data['tgl_minta'] ?></td>
                                    <td><?= $data['tgl_jalan'] ?></td>
                                    <td><?= $data['area_packing'] ?></td>
                                    <td><?= $data['area'] ?></td>
                                    <td><?= $data['kode_buyer'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['inisial'] ?></td>
                                    <td><?= $data['style'] ?></td>
                                    <td><?= $data['qty_minta'] ?></td>
                                    <td><?= $data['qty_keluar'] ?></td>
                                    <td><?= $data['tagihan'] ?></td>
                                    <td><?= $data['qty_keluar'] - $data['qty_minta'] ?></td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->

                </div>
            </div>

        </div>
    </div>
</section>
<?php $this->endSection(); ?>