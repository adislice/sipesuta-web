<?php 

require_once '../vendor/autoload.php';

use App\Database;
use App\Lampiran;
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
$user = new User($conn);
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

if (!isset($_GET['id'])) {
    header('location: dashboard.php');
}

$id_pengajuan = $_GET['id'];

$pengajuan = new PengajuanSurat($conn);
$lamp = new Lampiran($conn);
$lihat_lamp = $lamp->tampil_by_pengajuan($id_pengajuan);

$res = $pengajuan->hapus_pengajuan($id_pengajuan);
if ($res > 0) {
    Utils::show_alert_redirect("Hapus Pengajuan Surat #$id_pengajuan berhasil!", 'dashboard.php');
    while ($row = $lihat_lamp->fetch_assoc()){
        $file = $row['lokasi_file'];
        unlink($file);
    }
}
?>