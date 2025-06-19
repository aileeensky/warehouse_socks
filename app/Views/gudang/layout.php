<!DOCTYPE html>
<html lang="en">
<?php

?>

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Warehouse Socks</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="<?= base_url('assets/img/favicon.png') ?>" rel="icon">
    <link href="<?= base_url('assets/img/apple-touch-icon.png') ?>" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <script src="<?= base_url('assets/js/jquery/jquery-3.7.1.min.js') ?>" crossorigin="anonymous"></script>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/boxicons/css/boxicons.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/quill/quill.snow.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/quill/quill.bubble.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/remixicon/remixicon.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/simple-datatables/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/node_modules/sweetalert2/dist/sweetalert2.min.css') ?>" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Updated: Aug 30 2023 with Bootstrap v5.3.1
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <img src="<?= base_url('assets/img/Logo_Kahatex.png') ?>" style="width: 40px" alt="">
            <span class="d-none d-lg-block" style="color: #012970;"><b>Warehouse Socks</b></span>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <div class="search-bar">
            <form class="search-form d-flex align-items-center" method="POST" action="#">
                <input type="text" name="query" placeholder="Search" title="Enter search keyword">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </form>
        </div><!-- End Search Bar -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle " href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li><!-- End Search Icon-->

                <li class="nav-item dropdown">

                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-chat-left-text"></i>
                        <span class="badge bg-success badge-number">3</span>
                    </a><!-- End Messages Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
                        <li class="dropdown-header">
                            You have 3 new messages
                            <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="message-item">
                            <a href="#">
                                <img src="<?= base_url('assets/img/messages-1.jpg') ?>" alt="" class="rounded-circle">
                                <div>
                                    <h4>Maria Hudson</h4>
                                    <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                                    <p>4 hrs. ago</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="message-item">
                            <a href="#">
                                <img src="<?= base_url('assets/img/messages-2.jpg') ?>" alt="" class="rounded-circle">
                                <div>
                                    <h4>Anna Nelson</h4>
                                    <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                                    <p>6 hrs. ago</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="message-item">
                            <a href="#">
                                <img src="<?= base_url('assets/img/messages-3.jpg') ?>" alt="" class="rounded-circle">
                                <div>
                                    <h4>David Muldon</h4>
                                    <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                                    <p>8 hrs. ago</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="dropdown-footer">
                            <a href="#">Show all messages</a>
                        </li>

                    </ul><!-- End Messages Dropdown Items -->

                </li><!-- End Messages Nav -->

                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" data-bs-toggle="dropdown">
                        <i class="ri-account-circle-line ri-2x"></i>
                        <span class="d-none d-md-block dropdown-toggle ps-2"><?= session()->get('username') ?></span>
                    </a><!-- End Profile Iamge Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="<?= base_url('/logout') ?>">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item">
                <a class="nav-link collapsed" href="<?= base_url('/' . $role . '/') ?>">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li><!-- End Dashboard Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="<?= base_url('/' . $role . '/inputdatabase') ?>">
                    <i class="ri-database-2-line"></i><span>Input Database</span></i>
                </a>
            </li><!-- End Components Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="<?= base_url('/' . $role . '/stock') ?>">
                    <i class="bi bi-box"></i><span>Stock</span></i>
                </a>
            </li><!-- End Forms Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="<?= base_url('/' . $role . '/dataorder') ?>">
                    <i class="bi bi-box"></i><span>Input Stock Clustering</span></i>
                </a>
            </li><!-- End Forms Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="<?= base_url('/' . $role . '/datapermintaan') ?>">
                    <i class="ri-file-list-3-line"></i><span>Data Permintaan</span>
                </a>
            </li><!-- End Tables Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="<?= base_url('/' . $role . '/dataterkirim') ?>">
                    <i class="ri-send-plane-2-line"></i><span>Data Terkirim</span>
                </a>
            </li><!-- End Charts Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#report-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-file-earmark-arrow-down"></i><span>Report</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="report-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="<?= base_url('/' . $role . '/reportpemasukan') ?>">
                            <i class="bi bi-circle"></i><span>Report Pemasukkan</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('/' . $role . '/reportpengeluaran') ?>">
                            <i class="bi bi-circle"></i><span>Report Pengeluaran</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Icons Nav -->
        </ul>

    </aside><!-- End Sidebar-->

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <?= $this->renderSection('content'); ?>

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>AileeenSky</span></strong>. by RND Team
        </div>
        <div class="credits">
            <!-- All the links in the footer should remain intact. -->
            <!-- You can delete the links only if you purchased the pro version. -->
            <!-- Licensing information: https://bootstrapmade.com/license/ -->
            <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
            Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
        </div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="<?= base_url('assets/vendor/apexcharts/apexcharts.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/chart.js/chart.umd.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/echarts/echarts.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/quill/quill.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/simple-datatables/simple-datatables.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/php-email-form/validate.js') ?>"></script>

    <!-- Template Main JS File -->
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script src="<?= base_url('assets/node_modules/sweetalert2/dist/sweetalert2.min.js') ?>"></script>

</body>

</html>