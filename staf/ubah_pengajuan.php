<?php

require_once '../vendor/autoload.php';

use App\User;
use App\Database;
use App\PengajuanSurat;
use App\Lampiran;
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
} else {
    header('location: ../logout.php');
    exit();
}

$level_staf = $detail_user['level'];


if (isset($_GET['id'])) {
    $id_pengajuan = $_GET['id'];
} else {
    header('location: dashboard.php');
}

$lamp = new Lampiran($conn);
$lampiran = $lamp->tampil_by_pengajuan($id_pengajuan);

$pengajuan = new PengajuanSurat($conn);

if (isset($_GET['id'])) {

    $res = $pengajuan->lihat_pengajuan_surat($id_pengajuan);
    if (!$res) {
        Utils::show_alert_redirect("Pengajuan surat tidak ditemukan!.", 'dashboard.php');
        exit();
    }
    $data_pengajuan = $res->fetch_assoc();
    if ($data_pengajuan['status'] == "Selesai" || $data_pengajuan['status'] == "Ditolak") {
        Utils::show_alert_redirect("Pengajuan surat tidak ditemukan!.", 'dashboard.php');
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pengajua Surat #<?= $id_pengajuan ?> | SIPESUTA Kelurahan Kalibaros</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/lightbox.min.css">
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
                    <a class="btn btn-ghost normal-case text-xl">Verifikasi Pengajuan Surat Pengantar</a>
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
            <div class="font-bold text-base">
                <div class="flex items-center">
                        <a href="verifikasi_pengajuan.php?id=<?= $id_pengajuan ?>" class="btn btn-md btn-square btn-ghost text-primary text-lg">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                    <h2 class="font-bold text-md ">Ubah Pengajuan Surat Pengantar</h2>
                </div>
            </div>
            <form action="proses_ubah.php" method="POST" class="px-4 lg:px-20 " enctype="multipart/form-data">
                <div class="form-control w-full">
                    <input type="hidden" name="id_pengajuan" value="<?= $data_pengajuan['id_pengajuan'] ?>" required/>
                    <label class="label">
                        <span class="label-text">Nama</span>
                    </label>
                    <input type="text" name="nama" placeholder="Type here" class="input input-primary w-full h-10" value="<?= $data_pengajuan['nama']?>" required />

                    <label class="label">
                        <span class="label-text">Nomor Induk Keluarga (NIK)</span>
                    </label>
                    <input type="text" name="nik" maxlength="16" pattern="[0-9]+" placeholder="Type here" class="input input-primary w-full h-10" value="<?= $data_pengajuan['nik']?>"/>

                    <label class="label">
                        <span class="label-text">No. Kartu Keluarga (No. KK)</span>
                    </label>
                    <input type="text" name="no_kk" maxlength="16" pattern="[0-9]+" placeholder="Type here" class="input input-primary w-full h-10"  value="<?= $data_pengajuan['no_kk']?>" required />
                    <label class="label">
                        <span class="label-text">Jenis Kelamin</span>
                    </label>
                    <div class="flex flex-row">
                        <input type="radio" name="jeniskelamin" class="radio radio-primary" value="L" <?php echo $data_pengajuan['jenis_kelamin'] == "L" ? "checked" : "" ?> /><span class="ml-2 mr-6">Laki-laki</span>
                        <input type="radio" name="jeniskelamin" class="radio radio-primary" value="P" <?php echo $data_pengajuan['jenis_kelamin'] == "P" ? "checked" : "" ?> /><span class="ml-2 mr-6">Perempuan</span>
                    </div>
                    <div class="flex flex-col lg:flex-row">
                        <span class="grow lg:mr-1">
                            <label class="label">
                                <span class="label-text">Tempat Lahir</span>
                            </label>
                            <input type="text" name="tempat_lahir" placeholder="Type here" class="input input-primary w-full h-10" value="<?= $data_pengajuan['tempat_lahir']?>"  required />
                        </span>
                        <span class="grow lg:ml-1">
                            <label class="label">
                                <span class="label-text">Tanggal Lahir</span>
                            </label>
                            <input type="date" name="tanggal_lahir" placeholder="Pilih Tanggal" class="input input-primary w-full h-10 " value="<?= $data_pengajuan['tanggal_lahir']?>"  required />
                        </span>
                    </div>
                    <label class="label">
                        <span class="label-text">Kewarganegaraan</span>
                    </label>
                    <input type="text" name="kewarganegaraan" placeholder="Type here" class="input input-primary w-full h-10"  value="<?= $data_pengajuan['kewarganegaraan']?>" required />
                    <label class="label">
                        <span class="label-text">Agama</span>
                        
                    </label>
                    <select name="agama" class="select select-primary font-normal min-h-8 h-10" required>
                        <option disabled>Pilih salah satu</option>
                        <option value="Hindu" <?php echo $data_pengajuan['agama'] == "Hindu" ? "selected" : ""; ?> >Hindu</option>
                        <option value="Buddha" <?php echo $data_pengajuan['agama'] == "Buddha" ? "selected" : ""; ?>>Buddha</option>
                        <option value="Islam" <?php echo $data_pengajuan['agama'] == "Islam" ? "selected" : ""; ?>>Islam</option>
                        <option value="Kristen" <?php echo $data_pengajuan['agama'] == "Kristen" ? "selected" : ""; ?>>Kristen</option>
                        <option value="Konghucu" <?php echo $data_pengajuan['agama'] == "Konghucu" ? "selected" : ""; ?>>Konghucu</option>
                    </select>
                    <label class="label">
                        <span class="label-text">Pekerjaan</span>
                    </label>
                    <input type="text" name="pekerjaan" placeholder="Type here" class="input input-primary w-full h-10"  value="<?= $data_pengajuan['pekerjaan']?>" />
                    <label class="label">
                        <span class="label-text">Alamat</span>
                    </label>
                    <textarea name="alamat" class="textarea textarea-primary" placeholder="Masukkan Alamat" value="<?= $data_pengajuan['alamat']?>" required><?= $data_pengajuan['alamat']?></textarea>
                    <label class="label">
                        <span class="label-text">Keterangan lain</span>
                    </label>
                    <textarea name="ket" class="textarea textarea-primary" placeholder="Masukkan keterangan lain" value="<?= $data_pengajuan['keterangan_surat']?>"><?= $data_pengajuan['keterangan_surat']?></textarea>
                    
                    <button type="submit" class="btn btn-primary block mx-auto h-10 min-h-8 py-2 m-3" name="ubah_pengajuan">Simpan</button>
                </div>
            </form>
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
                    <a href="#" class="btn btn-ghost normal-case text-xl text-primary">SIPESUTA</a>
                    </div>
                </div>
                <ul class="menu p-4 overflow-y-auto">
                <li class="mb-2">
                        <a href="dashboard.php" >
                            <i class='bx bx-home-alt text-2xl'></i>Dashboard</a>
                    </li>
                    <li class="mb-2">
                        <a href="kelola_pengajuan.php" class="active">
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
    <script src="../js/jquery-3.6.0.min.js"></script>
    <script src="../js/lightbox.min.js"></script>
</body>
</html>