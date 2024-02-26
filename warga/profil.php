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
$level_user = $detail_user['level'];

if (isset($_POST['simpan_warga'])) {
    $new_nama = $_POST['nama'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $msg = "";
    $result = $user->ubah($detail_user['id_user'], $new_nama, $new_email, null, null, 4);
    if ($result > 0) {
        $msg .= "Profil berhasil diubah."; 
    } else {
        $msg .= "";
    }
    if  (!strlen(trim($_POST['password'])) == 0) {
        $result_pwd = $user->ubah_password($detail_user['id_user'], $new_password);
        if ($result_pwd > 0) {
            $msg .= " Password berhasil diubah.";
        } else {
            $msg .= "";
        }
    }
    if (!strlen(trim($msg))==0) {
        Utils::show_alert_redirect($msg);
    }
}
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
            <a href="dashboard.php" class="tab h-12 btn btn-ghost">
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
                <h2 class="font-bold text-xl">Profil</h2>
            </div>
            <div id="profil" class="container flex flex-col items-center mx-auto">
                <div class="avatar">
                    <div class="w-28 rounded-full">
                        <img src="../images/avatar.png" />
                    </div>
                </div>    
                <h2 class="text-2xl font-bold px-4 pt-4 pb-2"><?= $detail_user['nama'] ?></h2>
                <p class="pb-4 px-4"> <div class="badge badge-lg">
                    <?php 
                            switch ($level_user) {
                                case 1:
                                    echo "admin";
                                    break;
                                case 2:
                                    echo "staf";
                                    break;
                                case 3:
                                    echo "lurah";
                                    break;
                                case 4:
                                    echo "warga";
                                    break;
                                default:
                                    echo "tidak diketahui";
                                    break;
                            }
                            ?>
                        </div>.</p>
                    <form action="profil.php" method="POST" class="px-4 lg:px-20 w-full max-w-lg " enctype="multipart/form-data">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Nama</span>
                        </label>
                        <input type="text" name="nama" class="input input-primary w-full h-10" value="<?= $detail_user['nama'] ?>"/>
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input type="email" name="email" class="input input-primary w-full h-10" value="<?= $detail_user['email'] ?>"/>
                        <label class="label">
                            <span class="label-text">Password</span>
                        </label>
                        <input type="password" name="password" placeholder="Masukkan password baru" class="input input-primary w-full h-10" />
                        <button type="submit" class="btn btn-primary block mx-auto h-10 min-h-8 py-2 m-3" name="simpan_warga" onclick="return confirm('Anda yakin ingin menyimpan?')">Simpan</button>
                    </div>
               </form>
            </div>
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