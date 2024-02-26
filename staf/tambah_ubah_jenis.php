<?php

require_once '../vendor/autoload.php';

use App\Database;
use App\Utils;
use App\PengajuanSurat;
use App\User;

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
    if ($detail_user['level'] != 1) {
        Utils::show_alert_redirect("Anda tidak memiliki akses untuk membuka halaman ini!", "../index.php");
    }
} else {
    header('location: ../logout.php');
    exit();
}
$level_user = $detail_user['level'];
$level_staf = $detail_user['level'];

if (isset($_GET['aksi'])) {
    $aksi = $_GET['aksi'];
    $jenis_p = new PengajuanSurat($conn);

    if ($aksi == "ubah") {
        $id_jenis_p = $_GET['id'];
        $res_jenis_p = $jenis_p->lihat_jenis_pengajuan($id_jenis_p);
        $data_jenis_p = $res_jenis_p->fetch_assoc();
        $get_param = "&id=".$id_jenis_p;
        $judul = "Ubah";
    } else{
        $get_param = "";
        $judul = "Tambah";
    }
    
    if (isset($_POST['simpan_jenis'])) {
        if ($aksi == "tambah") {
            $res = $jenis_p->tambah_jenis_pengajuan($_POST['nama_jenis'], $_POST['keterangan'], $_POST['persyaratan']);
            if ($res) {
                Utils::show_alert_redirect("Data berhasil ditambahkan", 'kelola_jenis_pengajuan.php');
            }
        } elseif($aksi == "ubah"){
            
            if (!isset($_GET['id'])) {
                Utils::show_alert_redirect("Data tidak ditemukan", 'kelola_jenis_pengajuan.php');
            }
            $res = $jenis_p->ubah_jenis_pengajuan($id_jenis_p, $_POST['nama_jenis'], $_POST['keterangan'], $_POST['persyaratan']);
            
                Utils::show_alert_redirect("Data berhasil diubah.", 'kelola_jenis_pengajuan.php');
            
        }
        Utils::clear_post_state();
        
    }
} else {
    Utils::show_alert_redirect("Aksi tidak diketahui", 'kelola_jenis.php');
}

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul ?> Jenis Pengajuan Surat | SIPESUTA Kelurahan Kalibaros</title>
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
                    <a class="btn btn-ghost normal-case text-xl">Data Jenis Pengajuan</a>
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
                <div class="mb-2 flex items-center">
                        <a href="kelola_jenis_pengajuan.php" class="btn btn-md btn-square btn-ghost text-primary text-2xl">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                    <h2 class="font-semibold text-lg "><?= $judul ?> Data Jenis Pengajuan Surat</h2>
                </div>
               <div class="container">
               <form action="tambah_ubah_jenis.php?aksi=<?= $aksi.$get_param ?>" method="POST" class="px-4 lg:px-20 " enctype="multipart/form-data">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text">Nama Jenis Pengajuan Surat</span>
                        </label>
                        <input type="text" name="nama_jenis" placeholder="Masukkan nama jenis pengajuan surat" class="input input-primary w-full h-10" value="<?php echo $aksi == 'ubah' ? $data_jenis_p['nama_jenis'] : '' ?>" required />
                        <label class="label">
                            <span class="label-text">Keterangan</span>
                        </label>
                        <textarea name="keterangan" class="textarea textarea-primary" placeholder="Masukkan keterangan"><?php echo $aksi == 'ubah' ? $data_jenis_p['keterangan'] : '' ?></textarea>
                        <label class="label">
                            <span class="label-text">Persyaratan</span>
                        </label>
                        <textarea name="persyaratan" class="textarea textarea-primary" placeholder="Masukkan persyaratan (cth.: KTP, KK, dll)"><?php echo $aksi == 'ubah' ? $data_jenis_p['persyaratan'] : '' ?></textarea>
                        <input type="hidden" name="hidden_aksi" value="<?= $aksi ?>">
                        <button type="submit" class="btn btn-primary block mx-auto h-10 min-h-8 py-2 m-3" name="simpan_jenis">Simpan</button>
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
                        <a href="dashboard.php"">
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
                        <a href="kelola_jenis_pengajuan.php" class="active">
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