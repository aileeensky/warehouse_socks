<?php $this->extend($role . '/layout'); ?>
<?php $this->section('content'); ?>
<section class="section">
    <div class="row">
        <?php if (session()->getFlashdata('success')) : ?>
            <script>
                $(document).ready(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '<?= session()->getFlashdata('success') ?>',
                    });
                });
            </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <script>
                $(document).ready(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: '<?= session()->getFlashdata('error') ?>',
                    });
                });
            </script>
        <?php endif; ?>
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Schedule Packing</h5>
                    <div style="display: flex; align-items: center;">
                        <div class="col-md-2">
                            <!-- Icon Excel -->
                            <a class="nav-link collapsed" href="">
                                <i class="ri-file-excel-line" style="font-size: 30px;"></i>
                            </a>
                        </div>
                    </div>
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th></th>
                                <th scope="col">No</th>
                                <th scope="col">Tgl Minta</th>
                                <th scope="col">Tgl Jalan</th>
                                <th scope="col">No Model</th>
                                <th scope="col">Area</th>
                                <th scope="col">Inisial</th>
                                <th scope="col">Style</th>
                                <th scope="col">Warna</th>
                                <th scope="col">Delivery</th>
                                <th scope="col">WH</th>
                                <th scope="col">Eff(%)</th>
                                <th scope="col">Direct</th>
                                <th scope="col">Kapasitas</th>
                                <th scope="col">Qty Minta</th>
                                <th scope="col">Ket Packing</th>
                                <th scope="col">Gd Setting</th>
                                <th scope="col">Kirim</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($permintaan as $data) : ?>
                                <tr>
                                    <th><input class="form-check-input" type="checkbox" id="gridCheck1"></th>
                                    <th scope="row"><?= $no ?></th>
                                    <td><?= $data['tgl_minta'] ?></td>
                                    <td><?= $data['tgl_jalan'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['area'] ?></td>
                                    <td><input type="hidden" value="<?= $data['id_anak'] ?>"><?= $data['inisial'] ?></td>
                                    <td><?= $data['style'] ?></td>
                                    <td><?= $data['warna'] ?></td>
                                    <td><?= $data['delivery'] ?></td>
                                    <td><?= $data['wh'] ?></td>
                                    <td><?= $data['eff'] ?></td>
                                    <td><?= $data['direct'] ?></td>
                                    <td><?= $data['kapasitas'] ?></td>
                                    <td><?= $data['qty_minta'] ?></td>
                                    <td><?= $data['ket_packing'] ?></td>
                                    <td><?= $data['gd_setting'] ?></td>
                                    <td>
                                        <form id="kirimForm<?= $data['id_minta'] ?>" action="<?= base_url('/' . $role . '/kirimpermintaan') ?>" method="post">
                                            <input type="hidden" name="id_minta" value="<?= $data['id_minta'] ?>">
                                            <input type="hidden" name="status" value="ON PROCCESS">
                                            <a href="javascript:void(0);" onclick="document.getElementById('kirimForm<?= $data['id_minta'] ?>').submit();" style="cursor: pointer;">
                                                <i class="ri-send-plane-2-line"></i>
                                            </a>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                    <button type="button" class="btn btn-outline-primary">Kirim</button>
                </div>
            </div>

        </div>
    </div>
</section>
<script>
</script>

<?php $this->endSection(); ?>