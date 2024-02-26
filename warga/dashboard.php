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

if (!$_SESSION['login_as'] == "warga") {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$saved_login = $_SESSION['saved_login'];

$res = $user->tampil($saved_login['id_user']);

if ($res->num_rows > 0) {
    $detail_user = $res->fetch_assoc();
    if ($detail_user['level'] != 4) {
        Utils::show_alert_redirect("Anda tidak memiliki akses untuk membuka halaman ini!", "../index.php");
    }
} else {
    header('location: ../logout.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Surat Pengantar Saya | SIPESUTA Kelurahan Kalibaros</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;700&display=swap');
    </style>
</head>

<body class="min-h-screen pb-2 bg-blue-100">
    <div class="sticky top-0 z-50">
    <nav class="navbar bg-base-100 border-b border-b-slate-200">
        <div class="container mx-auto">
            <label for="tab" id="btn_menu" class="btn btn-square btn-ghost drawer-button sm:hidden"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current md:h-6 md:w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg></label>
            <div class="flex-1 flex items-center">
                <div class="w-8 rounded">
                    <img src="../images/lambang_kota_pekalongan.png" />
                </div>
                <a href="#" class="btn btn-ghost normal-case text-xl text-primary">SIPESUTA Kalibaros</a>
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
        </div>
    </nav>
    <div id="tab" class="tabs shadow bg-base-100 hidden sm:block">
        <div class="container mx-auto">
            <a href="dashboard.php" class="tab h-12  tab-bordered tab-active">
                <i class='bx bx-file text-xl mx-2'></i>
                Surat Pengantar Saya
            </a>
            <a href="pengajuan_baru.php" class="tab h-12 btn btn-ghost ">
                <i class='bx bx-plus text-xl mx-2'></i>
                Buat Surat Pengantar
            </a>
        </div>
    </div>
    </div>

    <div class="lg:mx-auto lg:w-4/5 m-5 ">
        <div class="border border-slate-200 rounded-xl bg-base-100 mb-2 p-4 md:p-8">
            <div class="mb-4">
                <h2 class="font-bold text-xl">Surat Pengantar Saya</h2>
            </div>
            <!-- tabel daftar pengajuan -->
            <?php
            $pengajuan = new PengajuanSurat($conn);
            $result = $pengajuan->lihat_semua_pengajuan_surat($detail_user['id_user']);
            if ($result->num_rows === 0) {
            ?>
            <div class="mx-auto">
            <div class="w-96 mx-auto">
            <img src="../images/hero-image.png" alt="Belum Ada Surat Pengantar">
            </div>
            <h3 class="text-center text-lg font-semibold">Belum Ada Surat Pengantar</h3>
            <p class="text-center">Pilih tombol Buat Surat Pengantar untuk membuat pengajuan surat pengantar baru!</p>
            </div>
            <?php
            } else {
            ?>
            <div class="relative overflow-x-auto border border-slate-200 sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Nama
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Tanggal
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Jenis Pengajuan
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                        ?>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        <a href="lihat_pengajuan.php?id=<?= $row['id_pengajuan'] ?>" class="link text-primary link-hover"><?= $row['nama'] ?></a>
                                    </th>
                                    <td class="px-6 py-2">
                                        <?= strftime("%e %B %Y, %H:%M", strtotime($row['tanggal'])) ?>
                                    </td>
                                    <td class="px-6 py-2">
                                        <?= $row['nama_jenis'] ?>
                                    </td>
                                    <td class="px-6 py-2">
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
                                        echo "<div class='badge $bg text-white'>".$row['status']."</div>";
                                    ?>
                                    </td>
                                    <td class="px-6 py-2">
                                        <?php
                                        if ($row['status'] == "Pending") {
                                        ?>
                                            <div class="tooltip" data-tip="Edit data #<?= $row['id_pengajuan'] ?>">
                                                <a href="ubah_pengajuan.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-info btn-sm text-white w-8">
                                                    <i class='bx bxs-pencil font-medium text-lg'></i>
                                                </a>
                                            </div>
                                        <?php
                                        } elseif($row['status'] == "Selesai") {
                                        ?>
                                            <div class="tooltip" data-tip="Unduh data #<?= $row['id_pengajuan'] ?> (PDF)">
                                                <a href="../generate_surat.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-info btn-sm text-white w-8" target="_blank">
                                                    <i class='bx bxs-download font-medium text-lg'></i>
                                                </a>
                                            </div>
                                        <?php
                                        } elseif($row['status'] == "Ditolak") {
                                        ?>
                                            <div class="tooltip" data-tip="Edit data #<?= $row['id_pengajuan'] ?>">
                                                <button class="btn btn-disabled btn-sm text-white w-8">
                                                    <i class='bx bxs-pencil font-medium text-lg'></i>
                                                </button>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                        <div class="tooltip" data-tip="Hapus data #<?= $row['id_pengajuan'] ?>">
                                            <a href="hapus_pengajuan.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-error w-8 btn-sm text-white" onclick="return confirm('Anda yakin akan menghapus data #<?= $row['id_pengajuan'] ?>')">
                                                <i class='bx bx-trash font-medium text-lg'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
    <script src="../js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#btn_menu").on('click', (e) => {
                $("#tab").slideToggle();
            })

            $(window).resize(function() {
                if ($(window).width() > 768) {
                    $("#tab").show();
                } else {
                    $("#tab").hide();
                }
            });
        });
    </script>
</body>
</html>