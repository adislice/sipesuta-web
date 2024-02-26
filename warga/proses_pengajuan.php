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

if (!isset($_FILES['lampiran_file'])) {
    Utils::show_alert_redirect("Lampiran tidak boleh kosong", "pengajuan_baru.php");
    exit();
}

$target_dir = "uploads/";
$len = count($_FILES['lampiran_file']['name']);

$uploaded_files = $_FILES["lampiran_file"];
$files_arr = array();

foreach ($uploaded_files['name'] as $key => $value) {
    $original = explode('.', $uploaded_files["name"][$key]);
    $extension = array_pop($original);
    $file_item = array();
    $file_name_new = $_POST['nik'] . "_" . $key . "_" . date('dmy-siH') . "." . $extension;
    $target_simpan = $target_dir . $file_name_new;
    $nama_lampiran = $_POST['lampiran_nama'][$key];
    $file_item['nama_lampiran'] = $nama_lampiran;
    $file_item['lokasi_file'] = $target_simpan;
    move_uploaded_file($uploaded_files["tmp_name"][$key], "../" . $target_simpan);
    array_push($files_arr, $file_item);    
}

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
$id_user = $detail_user['id_user'];
$id_jenis = $_POST['jenis'];
$nama_lurah = Utils::get_config("nama_lurah");
$nip_lurah = Utils::get_config("nip_lurah");

$db = new Database();
$conn = $db->getConnection();
$pengajuan = new PengajuanSurat($conn);

$res_id = $pengajuan->tambah_pengajuan_surat($id_user, $id_jenis,  $nama, $nik, $no_kk, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $kewarganegaraan, $agama, $pekerjaan, $alamat, $keterangan, $nama_lurah, $nip_lurah);

if ($res) {
    $lamp = new Lampiran($conn);

    $res_insert = $lamp->tambah_lampiran_arr($res_id, $files_arr);
    if ($res_insert) {
        Utils::show_alert_redirect("Pengajuan berhasil dikirim. Silahkan tunggu hingga status pengajuan selesai.",'dashboard.php');
        Utils::clear_post_state();
    } else {
        Utils::show_alert_redirect("Pengajuan gagal dikirim. Silahkan ulangi lagi.",'dashboard.php');
        Utils::clear_post_state();
    }
}
?>