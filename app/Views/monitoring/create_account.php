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
                    <h5 class="card-title">Create Account</h5>
                    <!-- Small Modal -->
                    <i class="bx bxs-user-plus" style="font-size: 28px;" data-bs-toggle="modal" data-bs-target="#createaccountModal"></i>

                    <div class="modal fade" id="createaccountModal" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Create Account</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form class="row g-3" action="<?= base_url('/' . $role . '/inputuser') ?>" method="post">
                                    <div class="modal-body">
                                        <div class="col-12">
                                            <label for="nama" class="form-label">Nama</label>
                                            <input type="text" class="form-control" name="nama">
                                        </div>
                                        <div class="col-12">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username">
                                        </div>
                                        <div class="col-12">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="text" class="form-control" name="password">
                                        </div>
                                        <div class="col-12">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" name="role" aria-label="Default select example">
                                                <option selected></option>
                                                <option value="gudang">gudang</option>
                                                <option value="monitoring">monitoring</option>
                                                <option value="packing">packing</option>
                                            </select>
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
                    <p></p>
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Bagian</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Username</th>
                                <th scope="col">Password</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($user as $data) : ?>
                                <tr>
                                    <th scope="row"><?= $no; ?></th>
                                    <td><?= $data['role'] ?></td>
                                    <td><?= $data['nama'] ?></td>
                                    <td><?= $data['username'] ?></td>
                                    <td><?= $data['password'] ?></td>
                                    <td><i class="ri-edit-line" data-bs-toggle="modal" data-bs-target="#editModal"></i></td>
                                    <td><i class="bi bi-trash" data-bs-toggle="modal" data-bs-target="#deleteModal"></i></td>
                                </tr>
                            <?php
                                $no++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                    <!-- Small Modal -->
                    <div class="modal fade" id="editModal" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Create Account</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form class="row g-3" action="<?= base_url('/' . $role . '/inputuser') ?>" method="post">
                                    <div class="modal-body">
                                        <div class="col-12">
                                            <label for="nama" class="form-label">Nama</label>
                                            <input type="text" class="form-control" name="nama">
                                        </div>
                                        <div class="col-12">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username">
                                        </div>
                                        <div class="col-12">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="text" class="form-control" name="password">
                                        </div>
                                        <div class="col-12">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" name="role" aria-label="Default select example">
                                                <option selected></option>
                                                <option value="gudang">gudang</option>
                                                <option value="monitoring">monitoring</option>
                                                <option value="packing">packing</option>
                                            </select>
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
                    <!-- Vertically centered Modal -->
                    <div class="modal fade" id="deleteModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Account</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Yakin ingin menghapus akun ini?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="" type="button" class="btn btn-danger">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Vertically centered Modal-->
                </div>
            </div>

        </div>
    </div>
</section>
<?php $this->endSection(); ?>