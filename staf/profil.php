<?php

require_once '../vendor/autoload.php';

use App\Database;
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

if (isset($_POST['simpan_password'])) {
    $new_password = $_POST['password'];
    $result = $user->ubah_password($detail_user['id_user'], $new_password);
    if ($result > 0) {
        Utils::show_alert_redirect("Password berhasil diubah");
    }
}
$user = new User($conn);

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | SIPESUTA Kelurahan Kalibaros</title>
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
                    <a class="btn btn-ghost normal-case text-xl">Profil</a>
                </div>
                <div class="flex-none">
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
                <div id="profil" class="container flex flex-col items-center mx-auto">
                <div class="avatar">
                    <div class="w-28 rounded-full">
                        <img src="../images/avatar.png" />
                    </div>
                </div>
                <h2 class="text-2xl font-bold px-4 pt-4 pb-2"><?= $detail_user['nama'] ?></h2>
                <p class="pb-4 px-4"> <div class="badge badge-lg"><?php 
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
                            ?></div>.</p>
                    <form action="profil.php" method="POST" class="px-4 lg:px-20 w-full max-w-lg " enctype="multipart/form-data">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Nomor Induk Pegawai</span>
                        </label>
                        <input type="text" disabled class="input input-primary w-full h-10" value="<?= $detail_user['nip'] ?>"/>
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input type="text" disabled class="input input-primary w-full h-10" value="<?= $detail_user['email'] ?>"/>
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <input type="text" disabled class="input input-primary w-full h-10" value="<?= $detail_user['status'] ?>"/>
                        <label class="label">
                            <span class="label-text">Password</span>
                        </label>
                        <input type="password" name="password" placeholder="Masukkan password baru" class="input input-primary w-full h-10" />
                       
                        <button type="submit" class="btn btn-primary block mx-auto h-10 min-h-8 py-2 m-3" name="simpan_password" onclick="return confirm('Anda yakin ingin mengubah password?')">Simpan</button>
                    </div>
               </form>
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
                        <a href="laporan.php">
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
</body>
</html>