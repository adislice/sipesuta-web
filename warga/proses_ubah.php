<?php 

require_once '../vendor/autoload.php';

use App\Database;
use App\PengajuanSurat;
use App\Lampiran;
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
$id_pengajuan = $_POST['id_pengajuan'];
$nama = $_POST['nama'];
$nik = $_POST['nik'];
$no_kk = $_POST['no_kk'];
$jenis_kelamin = $_POST['jeniskelamin'];
$tempat_lahir = $_POST['tempat_lahir'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$kewarganegaraan = $_POST['kewarganegaraan'];
$agama = $_POST['agama'];
$pekerjaan = $_POST['pekerjaan'];
$alamat = $_POST['alamat'];
$keterangan = $_POST['ket'];

$pengajuan = new PengajuanSurat($conn);
$res = $pengajuan->update_pengajuan_surat($id_pengajuan,$nama, $nik, $no_kk, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $kewarganegaraan, $agama, $pekerjaan, $alamat, $keterangan);
Utils::show_alert_redirect("Pengajuan Surat #".$id_pengajuan." berhasil diupdate!", 'dashboard.php');

?>