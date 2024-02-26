<?php

require_once '../vendor/autoload.php';

use App\User;
use App\Database;
use App\PengajuanSurat;
use App\Utils;
use App\Lampiran;

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
    if ($detail_user['level'] == 4) {
        Utils::show_alert_redirect("Anda tidak memiliki akses untuk membuka halaman ini!", "../index.php");
    }
} else {
    header('location: ../logout.php');
    exit();
}

$level_staf = $detail_user['level'];


if (isset($_GET['aksi'])) {
    if ($_GET['aksi'] == "hapus") {
        $penga = new PengajuanSurat($conn);
        $id_hapus = $_GET['id'];
        $lamp = new Lampiran($conn);
        $lihat_lamp = $lamp->tampil_by_pengajuan($id_hapus);
        
        $res_hapus = $penga->hapus_pengajuan($id_hapus);
        if ($res_hapus > 0) {
            while ($row = $lihat_lamp->fetch_assoc()){
                $file = $row['lokasi_file'];
                unlink("../".$file);
            }
            Utils::show_alert_redirect("Hapus Pengajuan Surat #$id_hapus berhasil!", 'kelola_pengajuan.php');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Surat Warga | SIPESUTA Kelurahan Kalibaros</title>
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
            <nav class="navbar bg-base-100 shadow sticky top-0 z-50">
                <label for="my-drawer-2" class="btn btn-square btn-ghost drawer-button lg:hidden"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current md:h-6 md:w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg></label>
                <div class="flex-1">
                    <a class="btn btn-ghost normal-case text-xl">Pengajuan Surat Warga</a>
                </div>
                <div class="flex flex-none items-center">
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
            <div class="flex">
                <form action="kelola_pengajuan.php" method="GET" class="ml-auto">
                    <div class="form-control mb-4">
                        <div class="input-group">
                            <input name="search" type="text" placeholder="Cari berdasarkan nama…" class="input input-bordered  h-10 min-h-8 w-80" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>"/>
                            <button class="btn btn-primary h-10 min-h-8">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
                
                <?php
                $pengajuan = new PengajuanSurat($conn);
                ?>
                <div class="relative overflow-x-auto border border-slate-200 sm:rounded-lg">
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
                                    Email
                                </th>
                                <th scope="col" class="p-2">
                                    Tanggal
                                </th>
                                <th scope="col" class="p-2">
                                    Jenis Pengajuan
                                </th>
                                <th scope="col" class="p-2">
                                    Status
                                </th>
                                <th scope="col" class="p-2">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($_GET['search'])) {
                                $result = $pengajuan->lihat_semua_pengajuan_surat(null, null, $_GET['search']);
                            } else {
                                $result = $pengajuan->lihat_semua_pengajuan_surat();
                            }
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
                                            <?= $row['email'] ?>
                                        </td>
                                        <td class="p-2 whitespace-nowrap">
                                            <?= strftime("%e %B %Y, %H:%M", strtotime($row['tanggal'])); ?>
                                        </td>
                                        <td class="p-2">
                                            <?= $row['nama_jenis'] ?>
                                        </td>
                                        <td class="p-2">
                                        <?php 
                                        switch ($row['status']) {
                                            case 'Pending':
                                                $bg = "badge-warning";
                                                break;
                                            case 'Selesai':
                                                $bg = "badge-success";
                                                break;
                                            case 'Ditolak': 
                                                $bg = "badge-error";
                                                break;
                                            default:
                                                $bg = "badge-info";
                                                break;
                                        }
                                        echo "<div class='badge $bg text-white' >".$row['status']."</div>";
                                        
                                        ?>
                                        </td>
                                        <td class="p-2 whitespace-nowrap">
                                            <?php
                                            if ($row['status'] == "Pending" || $row['status'] == "Ditolak") {
                                            ?>
                                                <div class="tooltip" data-tip="Lihat data #<?= $row['id_pengajuan'] ?>">
                                                    <a href="verifikasi_pengajuan.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-info h-8 min-h-8 w-8 px-0 text-white">
                                                    <i class='bx bx-show font-medium text-lg'></i>
                                                    </a>
                                                </div>
                                            <?php
                                            } elseif($row['status'] == "Selesai") {
                                            ?>
                                                <div class="tooltip" data-tip="Unduh data #<?= $row['id_pengajuan'] ?> (PDF)">
                                                    <a href="../generate_surat.php?id=<?= $row['id_pengajuan'] ?>" target="_blank" class="btn btn-info h-8 min-h-8 w-8 px-0 text-white">
                                                        <i class='bx bxs-download font-medium text-lg'></i>
                                                    </a>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <div class="tooltip" data-tip="Hapus data #<?= $row['id_pengajuan'] ?>">
                                                <a href="kelola_pengajuan.php?aksi=hapus&id=<?= $row['id_pengajuan'] ?>" class="btn btn-error h-8 min-h-8 w-8 px-0 text-white" onclick="return confirm('Anda yakin akan menghapus data #<?= $row['id_pengajuan'] ?>')">
                                                    <i class='bx bx-trash font-medium text-lg'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                            <?php
                            $idx++;
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-2'>Tidak ada data</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
                            <i class='bx bx-home-alt text-2xl'></i>Dashboard</a>
                    </li>
                    <li class="mb-2">
                        <a href="kelola_pengajuan.php"  class="active">
                            <i class='bx bx-file text-2xl'></i>Pengajuan Surat Warga</a>
                    </li>
                    <?php 
                    // Menu Master Data Admin
                    if ($level_staf == 1) {
                        ?>
                        <div class="divider my-2">Master Data</div>
                        <?php 
                        } 
                        if($level_staf == 1 || $level_staf == 3){
                        ?>
                        <li class="mb-2">
                            <a href="kelola_warga.php">
                            <i class='bx bx-user text-2xl'></i>Data Warga</a>
                        </li>
                        <li class="mb-2">
                            <a href="kelola_staf.php">
                            <i class='bx bx-user-check text-2xl'></i>Data Staf</a>
                        </li>
                        <?php 
                        }
                        ?>
                        <li class="mb-2">
                            <a href="kelola_jenis_pengajuan.php">
                            <i class='bx bx-grid-alt text-2xl'></i>Data Jenis Pengajuan</a>
                        </li>
                        <?php 
                        if ($level_staf == 3) {
                        ?>
                        <li class="mb-2">
                            <a href="laporan.php">
                            <i class='bx bx-line-chart text-2xl'></i>Laporan Pengajuan Surat</a>
                        </li>
                        <?php 
                        }
                        ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>