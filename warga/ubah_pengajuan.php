<?php

require_once '../vendor/autoload.php';

use App\Database;
use App\User;
use App\PengajuanSurat;
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

$pengajuan = new PengajuanSurat($conn);

if (isset($_GET['id'])) {
    $id_pengajuan = $_GET['id'];

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
    <title>Ubah Pengajuan Surat Pengantar | SIPESUTA Kelurahan Kalibaros</title>
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
            <a href="dashboard.php" class="tab h-12 tab-bordered tab-active">
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
    <!-- Konten -->
    <div class="lg:mx-auto lg:w-3/5 m-5 ">

        <div class="border border-slate-200 rounded-xl bg-base-100 mb-2">
            <div class="px-6 py-2 font-bold text-base">
                <div class="my-4 flex items-center">
                        <a href="dashboard.php" class="btn btn-md btn-square btn-ghost text-primary text-2xl">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                    <h2 class="font-bold text-xl ">Ubah Pengajuan Surat </h2>
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
                    <label class="label cursor-pointer flex justify-start">
                            <input name="data_benar" type="checkbox" class="checkbox checkbox-primary mr-2" required />
                            <span class="label-text">Data di atas Saya isi dengan sebenar-benarnya</span>
                        </label>
                    <button type="submit" class="btn btn-primary block mx-auto h-10 min-h-8 py-2 m-3" name="ubah_pengajuan">Simpan</button>
                </div>
            </form>
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