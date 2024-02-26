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
$cek = $pengajuan->lihat_pengajuan_surat($id_pengajuan);
$cek_res = $cek->fetch_assoc();
$status_surat = $cek_res['status'];
?>


<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPESUTA Kelurahan Kalibaros | Verifikasi Pengajua Surat #<?= $id_pengajuan ?></title>
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
            <div class="content-body p-2">
                <div class="flex items-center">
                    <div class="mb-2 flex items-center">
                        <a href="kelola_pengajuan.php" class="btn btn-md btn-square btn-ghost text-primary text-lg">
                            <i class='bx bx-arrow-back'></i>
                        </a>
                        <h2 class="font-bold text-md">Detail Pengajuan Surat Pengantar</h2>
                    </div>
                    <?php 
                    if ($status_surat == "Pending") {
                    ?>
                    <a href="ubah_pengajuan.php?id=<?= $id_pengajuan ?>" class="btn btn-square btn-ghost mb-2 text-primary ml-auto"><i class='bx bx-edit text-lg'></i></a>
                    <?php 
                    }
                    ?>
                </div>
                
                <!-- tabel infopengajuan -->
                <?php
                
                $result = $pengajuan->lihat_pengajuan_surat($id_pengajuan);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $ada = false;
                    
                ?>
                <div class="relative overflow-x-auto border border-slate-200 sm:rounded-lg p-6">
                    
                    <table class="w-full text-sm text-left text-gray-800 ">
                        <tbody>
                                <tr class="h-8 font-medium align-top">
                                        <td>Jenis Pengajuan Surat</td>
                                        <td>:</td>
                                        <td><?= $row['nama_jenis'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Nama</td>
                                        <td>:</td>
                                        <td><?= $row['nama'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Nomor Induk Kependudukan</td>
                                        <td>:</td>
                                        <td><?= $row['nik'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Nomor Kartu Keluarga</td>
                                        <td>:</td>
                                        <td><?= $row['no_kk'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Jenis Kelamin</td>
                                        <td>:</td>
                                        <td><?php 
                                            echo $row['jenis_kelamin'] == "L" ? "Laki-laki" : "Perempuan";
                                        ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Tempat & Tanggal Lahir</td>
                                        <td>:</td>
                                        <td><?= $row['tempat_lahir'] ?>, <?= $row['tanggal_lahir'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Kewarganegaraan</td>
                                        <td>:</td>
                                        <td><?= $row['kewarganegaraan'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Agama</td>
                                        <td>:</td>
                                        <td><?= $row['agama'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Pekerjaan</td>
                                        <td>:</td>
                                        <td><?= $row['pekerjaan'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Alamat</td>
                                        <td>:</td>
                                        <td><?= $row['alamat'] ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                        <td>Keterangan Lain</td>
                                        <td>:</td>
                                        <td><?php echo nl2br($row['keterangan_surat']) ?></td>
                                    </tr>
                                    <tr class="h-8 font-medium align-top">
                                    <td>Status</td>
                                    <td>:</td>
                                    <td>
                                        <?php 
                                        switch ($row['status']) {
                                            case 'Pending':
                                                $bg = "badge-warning";
                                                $ada = true;
                                                break;
                                            case 'Selesai':
                                                $bg = "badge-success";
                                                break;
                                            case 'Ditolak': 
                                                $bg = "badge-error";
                                                break;
                                            default:
                                                $bg = "bg-info";
                                                break;
                                        }
                                        echo "<div class='badge $bg text-white'>".$row['status']."</div>";
                                        
                                        ?>
                                        
                                    </td>
                                </tr>



                        </tbody>
                    </table>
                    <h4 class="font-semibold">Lampiran :</h4>
                <div class="flex flex-row flex-wrap p-4">
                    
                    <?php 
                    while ($row = $lampiran->fetch_assoc()) {

                    ?>
                    <div class="w-64">
                    <a  href="../<?= $row['lokasi_file'] ?>" data-lightbox="lampiran" data-title="<?= $row['nama_lampiran'] ?>">
                        <img src="../<?= $row['lokasi_file'] ?>" alt="<?= $row['nama_lampiran'] ?>">
                    </a>
                    </div>
                    <?php 
                    }
                    ?>
                </div>
                </div>

                <?php
                if ($ada) {
                ?>
                
                <div id="tombol" class="mx-auto text-center my-8">
                    <a href="aksi_verifkasi.php?setujui=<?= $id_pengajuan ?>" class="btn btn-success text-base-100 w-24 h-10 min-h-8" onclick="return confirm('Anda yakin ingin menyetujui data pengajuan #<?= $id_pengajuan ?>')">Setujui</a>
                    <a href="aksi_verifkasi.php?tolak=<?= $id_pengajuan ?>" class="btn btn-error text-base-100 w-24 h-10 min-h-8" onclick="return confirm('Anda yakin ingin menolak data pengajuan #<?= $id_pengajuan ?>')">Tolak</a>
                </div>
                <?php
                }
                }
                ?>
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
                        <a href="dashboard.php">
                            <i class='bx bx-home-alt text-2xl'></i>Dashboard</a>
                    </li>
                    <li class="mb-2">
                        <a href="kelola_pengajuan.php"   class="active">
                            <i class='bx bx-file text-2xl'></i>Pengajuan Surat Warga
                        </a>
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
    <script src="../js/jquery-3.6.0.min.js"></script>
    <script src="../js/lightbox.min.js"></script>
</body>
</html>