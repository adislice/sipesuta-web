<?php

require_once '../vendor/autoload.php';

use App\Database;
use App\PengajuanSurat;
use App\Utils;
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
    if ($detail_user['level'] != 1 && $detail_user['level'] != 2 && $detail_user['level'] != 3) {
        Utils::show_alert_redirect("Anda tidak memiliki akses untuk membuka halaman ini!", "../index.php");
    }
} else {
    header('location: ../logout.php');
    exit();
}

$level_staf = $detail_user['level'];

if (isset($_GET['aksi'])) {
    if ($_GET['aksi'] == "hapus") {
        $pengajuan = new PengajuanSurat($conn);
        $id_hapus = $_GET['id'];
        $res_hapus = $pengajuan->hapus_jenis_pengajuan($id_hapus);
        if ($res_hapus > 0) {
            Utils::show_alert_redirect("Data berhasil dihapus", 'kelola_jenis_pengajuan.php');
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
    <title>Data Jenis Pengajuan Surat | SIPESUTA Kelurahan Kalibaros</title>
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
                    <a class="btn btn-ghost normal-case text-xl">Data Jenis Pengajuan Surat Pengantar</a>
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
                <div class="flex">
                
                    <?php if($level_staf == 1){ ?>
                        <a href="tambah_ubah_jenis.php?aksi=tambah" class="btn btn-primary h-10 min-h-8 text-white my-2"><i class='bx bx-customize text-xl mr-1'></i>Tambah Jenis Pengajuan</a>
                    <?php } ?>
                    <form action="kelola_jenis_pengajuan.php" method="GET" class="ml-auto">
                        <div class="form-control mb-4">
                            <div class="input-group">
                                <input name="search" type="text" placeholder="Cari berdasarkan nama jenis pengajuan…" class="input input-bordered  h-10 min-h-8 w-80" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>"/>
                                <button class="btn btn-primary h-10 min-h-8">
                                    Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
           
            <?php
            $jenis_p = new PengajuanSurat($conn);
            ?>
                <div class="relative overflow-x-hidden border border-slate-200 sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="p-2">
                                    No.
                                </th>
                                <th scope="col" class="p-2">
                                    Nama Jenis Pengajuan
                                </th>
                                <th scope="col" class="p-2">
                                    Keterangan
                                </th>
                                <th scope="col" class="p-2">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($_GET['search'])) {
                                $result = $jenis_p->lihat_semua_jenis_pengajuan($_GET['search']);
                            } else {
                                $result = $jenis_p->lihat_semua_jenis_pengajuan();
                            }
                            $idx = 1;
                            $modal_html = "";

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                            ?>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <th scope="row" class="text-center p-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                            <?= $idx ?>
                                        </th>
                                        <td class="p-2">
                                            <?= $row['nama_jenis'] ?>
                                        </td>
                                        <td class="p-2">
                                            <?= $row['keterangan'] ?>
                                        </td>
                                        
                                        <td class="p-2 whitespace-nowrap">
                                            <div class="tooltip" data-tip="Lihat data #<?= $row['id_jenis'] ?>">
                                                <label for="lihat-<?= $row['id_jenis'] ?>" class="btn modal-button btn-success h-8 min-h-8 w-8 btn-square my-1 text-white"><i class='bx bx-show font-medium text-lg'></i></label>
                                            </div>
                                            <?php if($level_staf == 1){ ?>
                                            <div class="tooltip" data-tip="Edit data #<?= $row['id_jenis'] ?>">
                                                <a href="tambah_ubah_jenis.php?aksi=ubah&id=<?= $row['id_jenis'] ?>" class="btn btn-info h-8 min-h-8 w-8 btn-square my-1 text-white" >
                                                    <i class='bx bxs-pencil font-medium text-lg'></i>
                                                </a>
                                            </div>
                                            <div class="tooltip" data-tip="Hapus data #<?= $row['id_jenis'] ?>">
                                                <a href="kelola_jenis_pengajuan.php?aksi=hapus&id=<?= $row['id_jenis'] ?>" class="btn btn-error h-8 min-h-8 w-8 btn-square my-1 text-white" onclick="return confirm('Anda yakin akan menghapus data #<?= $row['id_jenis'] ?>')">
                                                    <i class='bx bx-trash font-medium text-lg'></i>
                                                </a>
                                            </div>
                                            <?php } ?>
                                            
                                        </td>
                                        
                                    </tr>
                            <?php

                                $modal_html .= "<input type='checkbox' id='lihat-{$row['id_jenis']}' class='modal-toggle' />
                                <div class='modal'>
                                  <div class='modal-box'>
                                    <h3 class='font-bold text-lg'>{$row['nama_jenis']}</h3>
                                    <hr />
                                    <h5 class='font-semibold pt-4'>Keterangan :</h5>
                                    <p class='pb-2'>".nl2br($row['keterangan'])."</p>
                                    <h5 class='font-semibold'>Persyaratan :</h5>
                                    <p class='pb-1'>".nl2br($row['persyaratan'])."</p>
                                    <div class='modal-action'>
                                      <label for='lihat-{$row['id_jenis']}' class='btn min-h-8 h-10'>Tutup</label>
                                    </div>
                                  </div>
                                </div>";
                                $idx = $idx+1;
                                }
                            }else {
                                echo "<tr><td colspan='5' class='text-center py-2'>Tidak ada data</td></tr>";
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

    

<?= $modal_html ?>


<script src="../js/jquery-3.6.0.min.js"></script>

</body>
</html>