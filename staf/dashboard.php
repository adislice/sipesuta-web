<?php

require_once '../vendor/autoload.php';

use App\Database;
use App\PengajuanSurat;
use App\User;
use App\Utils;

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
$user = new User($conn);
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
$user = new User($conn);
$pengajuan = new PengajuanSurat($conn);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?= $level_staf == 1 ? "Admin" : "" ?><?= $level_staf == 2 ? "Staf" : "" ?><?= $level_staf == 3 ? "Lurah" : "" ?>   | SIPESUTA Kelurahan Kalibaros</title>
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
                    <a class="btn btn-ghost normal-case text-xl">Dashboard</a>
                </div>
                <div class="flex-none">
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
                </div>
            </nav>
            <div class="content-body p-4">
                <h2 class="text-2xl font-bold px-4 pt-4 pb-2">Halo, <?= $detail_user['nama'] ?> 👋</h2>
                <p class="pb-4 px-4">Anda login sebagai <b>
                    <?php 
                            switch ($level_staf) {
                                case 1:
                                    echo "admin";
                                    break;
                                case 2:
                                    echo "staf";
                                    break;
                                case 3:
                                    echo "lurah";
                                    break;
                                default:
                                    echo "tidak diketahui";
                                    break;
                            }
                            ?></b>.
                </p>
                <div class="grid grid-cols-1 gap-8 p-4 lg:grid-cols-2 xl:grid-cols-4">
                    <div class="flex items-center justify-between p-4 bg-warning rounded-md">
                    <div>
                        <h6 class=" font-medium leading-none tracking-wider text-white uppercase ">
                        Pengajuan Surat Pending
                        </h6>
                        <span class="text-xl text-white font-semibold">
                            <?php 
                            $res_pending = $pengajuan->tampil_pending();
                            echo $res_pending->num_rows;
                            ?>
                        </span>
                    </div>
                    <div>
                        <span>
                        <i class='bx bx-file text-5xl text-base-100' style="color: #ffffff69;"></i>
                        </span>
                    </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-success rounded-md dark:bg-darker">
                    <div>
                        <h6 class="text-sm font-medium leading-none tracking-wider text-white uppercase">
                        Total Pengajuan Surat
                        </h6>
                        <span class="text-xl text-white font-semibold">
                            <?php 
                            $res_total = $pengajuan->lihat_semua_pengajuan_surat();
                            echo $res_total->num_rows;
                            ?>
                        </span>  
                    </div>
                    <div>
                        <span>
                            <i class='bx bx-bar-chart-alt-2 text-5xl text-base-100' style="color: #ffffff69;"></i>
                        </span>
                    </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-info rounded-md dark:bg-darker">
                    <div>
                        <h6 class="text-sm font-medium leading-none tracking-wider text-white uppercase">
                        Warga
                        </h6>
                        <span class="text-xl text-white font-semibold">
                            <?php 
                            $res_warga = $user->tampil_semua_warga();
                            echo $res_warga->num_rows;
                            ?>
                        </span>
                    </div>
                    <div>
                        <span>
                        <svg class="w-12 h-12 " xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #ffffff69;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        </span>
                    </div>
                    </div>
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
                        <a href="dashboard.php" class="active">
                        <i class='bx bx-home-alt text-2xl'></i>Dashboard</a>
                    </li>
                    <li class="mb-2">
                        <a href="kelola_pengajuan.php">
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