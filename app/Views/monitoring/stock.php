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
                    <h5 class="card-title">Stock Gudang</h5>
                    <div style="display: flex; align-items: center;">
                        <div class="col-md-1">
                            <!-- Small Modal -->
                            <i class="bx bx-plus-circle" style="font-size: 22px;" data-bs-toggle="modal" data-bs-target="#inputjalurModal"><span>Jalur</span></i>

                            <div class="modal fade" id="inputjalurModal" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i></i>Input Jalur</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form class="row g-3" action="<?= base_url('/' . $role . '/inputjalur') ?>" method="post">
                                            <div class="modal-body">
                                                <div class="col-12">
                                                    <label for="jalur" class="form-label">Jalur</label>
                                                    <input type="text" class="form-control" name="jalur">
                                                </div>
                                                <div class="col-12">
                                                    <label for="kapasitas" class="form-label">Kapasitas Box</label>
                                                    <input type="text" class="form-control" name="kapasitas">
                                                </div>
                                                <div class="col-12">
                                                    <label for="gd_setting" class="form-label">Gd Setting</label>
                                                    <select class="form-select" name="gd_setting" aria-label="Default select example">
                                                        <option selected></option>
                                                        <option value="GD SETTING">GD SETTING</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label for="ket" class="form-label">Keterangan</label>
                                                    <textarea type="text" class="form-control" name="ket"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form><!-- Vertical Form -->
                                    </div>
                                </div>
                            </div><!-- End Small Modal-->
                        </div>
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
                                <th scope="col">No</th>
                                <th scope="col">Jalur</th>
                                <th scope="col">Space</th>
                                <th scope="col">Qty Jalur</th>
                                <th scope="col">Box</th>
                                <th scope="col">No Model</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Tambah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($jalur as $data) : ?>
                                <tr>
                                    <th scope="row"><?= $no ?></th>
                                    <td><?= $data['jalur'] ?></td>
                                    <td><?= $data['space'] ?></td>
                                    <td><?= $data['qty_stock'] ?></td>
                                    <td><?= $data['box_stock'] ?></td>
                                    <td><?= $data['no_model'] ?></td>
                                    <td><?= $data['keterangan'] ?></td>
                                    <td></td>
                                    <td></td>
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