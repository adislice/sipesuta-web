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
    if ($detail_user['level'] == 4) {
        Utils::show_alert_redirect("Anda tidak memiliki akses untuk membuka halaman ini!", "../index.php");
    }
} else {
    header('location: ../logout.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_pengajuan = $_GET['id'];
} else {
    // header('location: dashboard.php');
}

$pengajuan = new PengajuanSurat($conn);

if (isset($_GET['setujui'])) {
    $id_pengajuan = $_GET['setujui'];
    $res_verif = $pengajuan->terima($id_pengajuan, $detail_user['id_user']);
    if ($res_verif > 0) {
        Utils::show_alert_redirect("Data #".$id_pengajuan." berhasil disetujui", 'kelola_pengajuan.php');
    } else {
        Utils::show_alert_redirect("Data #".$id_pengajuan." gagal disetujui", 'kelola_pengajuan.php');
    }
} elseif (isset($_GET['tolak'])){
    $id_pengajuan = $_GET['tolak'];
    $res_verif = $pengajuan->tolak($id_pengajuan, $detail_user['id_user']);
    if ($res_verif > 0) {
        Utils::show_alert_redirect("Data #".$id_pengajuan." berhasil ditolak", 'kelola_pengajuan.php');
    } else {
        Utils::show_alert_redirect("Data #".$id_pengajuan." gagal ditolak", 'kelola_pengajuan.php');
    }
}

?>