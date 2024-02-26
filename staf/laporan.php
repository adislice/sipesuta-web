<?php

require_once '../vendor/autoload.php';

use App\Database;
use App\PengajuanSurat;
use App\User;
use App\Utils;

setlocale(LC_ALL, 'IND');
setlocale(LC_TIME, 'IND');

session_start();

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn);

// cek cookie
if (isset($_COOKIE['id']) && isset($_COOKIE['azhdaha'])) {
    $u = $user->tampil($_COOKIE['id']);
    if ($u->num_rows === 1) {
        $row = $u->fetch_assoc();
        if ($_COOKIE['azhdaha'] == $row['password']) {
            $_SESSION['login'] = true;
            $_SESSION['saved_login'] = $row;
            $user_level = $row['level'];
            if ($user_level == 4) {
                $_SESSION['login_as'] = "warga";
            } else {
                $_SESSION['login_as'] ="staf"; 
            }
        }
    }
}
if (!isset($_SESSION['login'])) {
    header('location: ../index.php');
    exit();
}
if (!$_SESSION['login_as'] == "staf") {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$saved_login = $_SESSION['saved_login'];
$res = $user->tampil($saved_login['id_user']);

if ($res->num_rows > 0) {
    $detail_user = $res->fetch_assoc();
    if ($detail_user['level'] != 3) {
        Utils::show_alert_redirect("Anda tidak memiliki akses untuk membuka halaman ini!", "../index.php");
    }
} else {
    header('location: ../logout.php');
    exit();
}

$level_staf = $detail_user['level'];

$user = new User($conn);
$pengajuan = new PengajuanSurat($conn);

if (isset($_GET['tanggal_awal'])) {
    $tampil_laporan = true;
    $tanggal_awal = $_GET['tanggal_awal'];
    $tanggal_akhir = $_GET['tanggal_akhir'];
    $filter_status = $_GET['filter_status'];
}
$total_pengajuan = 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan | SIPESUTA Kelurahan Kalibaros</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;700&display=swap');
    </style>
</head>
<body>
    <div class="drawer drawer-mobile">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col bg-base-100">
            <!-- Nav -->
            <nav class="navbar bg-base-100 shadow sticky top-0 z-50 print:hidden">
                <label for="my-drawer-2" class="btn btn-square btn-ghost drawer-button lg:hidden  print:hidden"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current md:h-6 md:w-6  print:hidden">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg></label>
                <div class="flex-1 print:hidden">
                    <a class="btn btn-ghost normal-case text-xl print:hidden">Laporan Pengajuan Surat Warga</a>
                </div>
                <div class="flex-none print:hidden">
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                        <div class="bg-base-300 text-base-content rounded-full w-10">
                            <span><?= substr($detail_user['nama'], 0, 1); ?></span>
                        </div>
                        </label>
                        <ul tabindex="0" class="menu menu-compact dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52">
                            <li>
                                <div class="hover:bg-white font-semibold"><?= $detail_user['nama'] ?></div>
                            </li>
                            <li>
                                <a href="profil.php" class="justify-between">
                                    Profil
                                </a>
                            </li>
                            <li><a href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="content-body p-4">
                <div class="hidden print:block text-center font-bold laporan">PEMERINTAH KOTA PEKALONGAN</div>
                <div class="hidden print:block text-center font-bold laporan">KECAMATAN PEKALONGAN TIMUR</div>
                <div class="hidden print:block text-center font-bold laporan">KELIRAHAN KALIBAROS</div>
                <div class="hidden print:block text-center font-bold laporan">Jl. Ir. Sutami No.3, Pekalongan Timur, Pekalongan</div>
                <div class="separator-surat hidden print:block"></div>
                <p class="hidden print:block text-center font-bold laporan underline mb-2"> Laporan Pengajuan Surat Warga</p>
                <p class="hidden print:block laporan my-4">Tanggal <?php echo isset($tampil_laporan) ? strftime("%e %B %Y", strtotime($tanggal_awal)) : "-" ?> sampai <?php echo isset($tampil_laporan) ? $tanggal_akhir : "-" ?></p>
               
                    <div class="flex flex-row align-middle items-center print:hidden">
                    <form action="laporan.php" method="GET" class="print:hidden flex items-center w-full">
                        <input type="date" name="tanggal_awal" id="tanggal_awal" placeholder="Pilih Tanggal Awal" class="input input-primary w-full h-10 mr-2" required value="<?php if(isset($tanggal_awal)){echo $tanggal_awal;} else {echo '';} ?>">
                        <span class="mx-2">sampai</span>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" placeholder="Pilih Tanggal Awal" class="input input-primary w-full h-10 mr-2" required value="<?php if(isset($tanggal_akhir)){echo $tanggal_akhir;} else {echo '';} ?>">
                        <select name="filter_status" class="select select-primary font-normal min-h-8 h-10 mr-2" required>
                            <option disabled>Filter Status</option>
                            <option value="Semua" <?php if(isset($filter_status)) { echo $filter_status == "Semua" ? "selected" : "";} ?> >Semua</option>
                            <option value="Selesai" <?php if(isset($filter_status)) { echo $filter_status == "Selesai" ? "selected" : "";} ?> >Selesai</option>
                            <option value="Ditolak" <?php if(isset($filter_status)) { echo $filter_status == "Ditolak" ? "selected" : "";} ?> >Ditolak</option>
                        </select>
                        <button type="submit" class="btn btn-primary h-10 min-h-8 text-white my-2 mr-2">Lihat</button>
                        </form>
                        <!-- <button type="button" id="cetak_laporan" class="btn btn-success text-white h-10 min-h-8" onclick="window.print();">Cetak</button> -->
                        <form action="cetak_laporan.php" method="get"  target="_blank">
                            <input type="hidden" name="tanggal_awal" value="<?= $tanggal_awal ?>">
                            <input type="hidden" name="tanggal_akhir" value="<?= $tanggal_akhir ?>">
                            <input type="hidden" name="filter_status" value="<?= $filter_status ?>">

                            <button type="submit" class="btn btn-success text-white h-10 min-h-8">Cetak</button>
                        </form>
                    </div>
                
                <div class="relative overflow-x-auto border border-slate-200 sm:rounded-lg">
                <?php
                if (!isset($tampil_laporan)) {
                    $result = $pengajuan->laporan(null, null);
                } else {
                    $result = $pengajuan->laporan($tanggal_awal, $tanggal_akhir, $filter_status);
                }
                ?>
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="p-2">
                                    No.
                                </th>
                                <th scope="col" class="p-2">
                                    Nama
                                </th>
                                <th scope="col" class="p-2">
                                    Tanggal
                                </th>
                                <th scope="col" class="p-2">
                                    Jenis Pengajuan
                                </th>
                                <th scope="col" class="p-2">
                                    Nama Akun
                                </th>
                                <th scope="col" class="p-2">
                                    Email Akun
                                </th>
                                <th scope="col" class="p-2">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $idx = 1;
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {

                            ?>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                            <?= $idx ?>
                                        </th>
                                        <td class="p-2">
                                            <?= $row['nama'] ?>
                                        </td>
                                        <td class="p-2">

                                        <?= strftime("%e %B %Y, %H:%M", strtotime($row['tanggal'])); ?>
                                        </td>
                                        <td class="p-2">
                                            <?= $row['nama_jenis'] ?>
                                        </td>
                                        <td class="p-2">
                                            <?= $row['nama_akun'] ?>
                                        </td>
                                        <td class="p-2">
                                            <?= $row['email'] ?>
                                        </td>
                                        <td class="p-2">
                                            <?php echo strtoupper($row['status']); ?>
                                        </td>
                                        
                                    </tr>
                            <?php
                                    $idx = $idx + 1;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <h5 class=" font-semibold text-right mx-4">
                        Total  : <?php echo $result->num_rows ?>
                        </h5>
                </div>
            </div>
        </div>
        <!-- Sidebar -->
        <div class="drawer-side bg-base-200">
            <label for="my-drawer-2" class="drawer-overlay"></label>
            <div class="w-80 bg-base-200 text-base-content">
                <div class="navbar">
                    <div class="px-2 pt-4 text-xl font-bold mx-auto">
                        <div class="w-8 rounded">
                            <img src="../images/lambang_kota_pekalongan.png" />
                        </div>
                        <a href="dashboard.php" class="btn btn-ghost normal-case text-xl text-primary">SIPESUTA</a>
                    </div>
                </div>
                <ul class="menu p-4 overflow-y-auto">
                    <li class="mb-2">
                        <a href="dashboard.php">
                            <i class='bx bx-home-alt text-2xl'></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="kelola_pengajuan.php">
                            <i class='bx bx-file text-2xl'></i>
                            Pengajuan Surat Warga
                        </a>
                    </li>
                    <?php
                    // Menu Master Data Admin
                    if ($level_staf == 1) {
                    ?>
                        <div class="divider my-2">Master Data</div>
                    <?php
                    }
                    if ($level_staf == 1 || $level_staf == 3) {
                    ?>
                        <li class="mb-2">
                            <a href="kelola_warga.php">
                                <i class='bx bx-user text-2xl'></i>
                                Data Warga
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="kelola_staf.php">
                                <i class='bx bx-user-check text-2xl'></i>
                                Data Staf
                            </a>
                        </li>
                        <?php 
                    }
                    ?>
                    <li class="mb-2">
                        <a href="kelola_jenis_pengajuan.php">
                        <i class='bx bx-grid-alt text-2xl'></i>
                            Data Jenis Pengajuan
                        </a>
                    </li>
                    <?php
                    if ($level_staf == 3) {
                    ?>
                        <li class="mb-2">
                            <a href="laporan.php" class="active">
                                <i class='bx bx-line-chart text-2xl'></i>
                                Laporan Pengajuan Surat
                            </a>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <?php 
    Utils::clear_post_state()
    ?>
</body>
</html>