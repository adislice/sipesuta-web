<?php 

require 'vendor/autoload.php';

use App\Database;
use App\PengajuanSurat;
use App\Utils;
use Dompdf\Dompdf;

$config = include 'konfigurasi.php';
session_start();

setlocale(LC_ALL, 'IND');
setlocale(LC_TIME, 'IND');

if (!isset($_SESSION['login'])) {
    header('location: logout.php');
	exit();
}
$db = new Database();
$conn = $db->getConnection();

if (!$_SESSION['login_as'] == "warga" || !$_SESSION['login_as'] == "staf") {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}
if (!isset($_GET['id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}
$id_pengajuan = $_GET['id'];

$pengajuan = new PengajuanSurat($conn);
$res_cek = $pengajuan->lihat_pengajuan_surat($id_pengajuan);
if ($res_cek->num_rows == 0) {
	Utils::show_alert_redirect("Data pengajuan surat tidak ditemukan!", "dashboard.php");
}

$surat = $res_cek->fetch_assoc();
if ($surat['status'] != "Selesai") {
	Utils::show_alert_redirect("Data pengajuan surat belum disetujui!", 'dashboard.php');
}
$saved_login = $_SESSION['saved_login'];

$kode_kelurahan = $config['kode_kelurahan'];
$kode_surat = $surat['id_pengajuan'];
$kode_jenis = $surat['id_jenis'];
$kode_kecamatan = $config['kode_kecamatan'];
$kode_kota = $config['kode_kota'];
$nama_jenis = $surat['nama_jenis'];
$nomor_surat = substr($kode_kelurahan, -6)."/".$kode_kecamatan."/".$kode_kota."/".$kode_jenis."/".date("y").".".$id_pengajuan;

$nama = $surat['nama'];
$nik = $surat['nik'];
$no_kk = $surat['no_kk'];
$jenis_kelamin = $surat['jenis_kelamin'] == "L" ? "Laki-laki" : "Perempuan";
$tempat_lahir = $surat['tempat_lahir'];
$tanggal_lahir = strftime("%e %B %Y", strtotime($surat['tanggal_lahir']));
$kewarganegaraan = $surat['kewarganegaraan'];
$agama = $surat['agama'];
$pekerjaan = $surat['pekerjaan'];
$alamat = $surat['alamat'];
$keterangan = nl2br($surat['keterangan_surat']);
$tgl_lengkap = strftime("%e %B %Y");
$nama_lurah = $surat['nama_lurah'];
$nip_lurah = $surat['nip_lurah'];
$html = <<<EOD
<html>
<head>
  <meta charset="utf-8">
  <style type="text/css">
    body {
      margin: 50px;
    }
    h3,
    h2 {
      margin: 0;
    }
    .kop-teks,
    .kop-logo,
    .judul {
      text-align: center;
    }
    table {
      width: 100%;
    }
    hr {
      border-top: 3px double #8c8b8b;
    }
    .no-kelurahan {
      margin-bottom: 20px;
    }
    .judul {
      margin-bottom: 20px;
    }
    p {
      margin: 5px 0 10px 0;
    }
    .judul h3 {
      text-decoration: underline;
    }
    .separator {
      height: 2px;
      width: 100%;
      border: 0;
      border-top: 1px solid black;
      border-bottom: 1px solid black;
      margin: 10px 0;
    }
    .info-pribadi {
      margin: 20px;
    }
    .tgl-surat {
      padding-left: 10px;
    }
    .nip-lurah {
      border-top: 1px solid black;
    }
  </style>
</head>
<body>
  <div class="kop-surat">
    <table>
      <tr>
        <td class="kop-logo">
          <img src="images/lambang_kota_pekalongan.png" width="60px">
        </td>
        <td class="kop-teks">
          <h2>PEMERINTAH KOTA PEKALONGAN</h2>
          <h3>KECAMATAN PEKALONGAN TIMUR</h3>
          <h3>KELURAHAN KALIBAROS</h3>
          <span>Jl. Ir. Sutami No.3, Pekalongan Timur, Pekalongan</span>
        </td>
      </tr>
    </table>
  </div>
  <div class="separator"></div>
  <div class="isi-surat">
    <div class="no-kelurahan">
      <div>No. Kode Kelurahan :</div>
      <div>${kode_kelurahan}</div>
    </div>
    <div class="judul">
      <h3>${nama_jenis}</h3>
      <p>Nomor : ${nomor_surat}</p>
    </div>
    <div class="pembuka">
      <p>Yang bertanda tangan di bawah ini menerangkan bahwa : </p>
    </div>
    <table class="info-pribadi">
      <tr>
        <td>Nama</td>
        <td>:</td>
        <td>${nama}</td>
      </tr>
      <tr>
        <td>Nomor Induk Keluarga</td>
        <td>:</td>
        <td>${nik}</td>
      </tr>
      <tr>
        <td>Nomor Kartu Keluarga</td>
        <td>:</td>
        <td>${no_kk}</td>
      </tr>
      <tr>
        <td>Jenis Kelamin</td>
        <td>:</td>
        <td>${jenis_kelamin}</td>
      </tr>
      <tr>
        <td>Tempat & Tanggal Lahir</td>
        <td>:</td>
        <td>${tempat_lahir}, ${tanggal_lahir}</td>
      </tr>
      <tr>
        <td>Kewarganegaraan</td>
        <td>:</td>
        <td>${kewarganegaraan}</td>
      </tr>
      <tr>
        <td>Agama</td>
        <td>:</td>
        <td>${agama}</td>
      </tr>
      <tr>
        <td>Pekerjaan</td>
        <td>:</td>
        <td>${pekerjaan}</td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>${alamat}</td>
      </tr>
      <tr>
        <td>Keterangan</td>
        <td>:</td>
        <td>${keterangan}</td>
      </tr>
    </table>
    <div class="penutup">
      <p>
        Demikian surat ini dibuat dengan sebenar-benarnya bagi yang berkepentingan. Atas perhatiannya, diucapkan terima kasih.
      </p>
    </div>
    <table style="table-layout: fixed ; width: 100%;">
      <tr>
        <td></td>
        <td>
          <div class="tgl-surat">
            <p>Pekalongan, ${tgl_lengkap}</p>
            <p>Mengetahui,</p>
          </div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="" style='text-align:center'>Pemegang</div>
        </td>
        <td>
          <div class="" style='text-align:center'>Lurah</div>
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><br><br><br><br></td>
      </tr>
      <tr>
        <td>
          <div class="" style='text-align:center'>
            ${nama}
          </div>
        </td>
        <td>
          <div class="" style='text-align:center'>
            ${nama_lurah}
          </div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="" style='text-align:center'>
          </div>
        </td>
        <td>
          <div style='text-align:center'>
            <span class="nip-lurah">${nip_lurah}</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

</body>

</html>

EOD;

// inisialisasi Dompdf
$dompdf = new Dompdf();
$dompdf->getOptions()->setChroot(__DIR__);
// load HTML
$dompdf->loadHtml($html);
// render
$dompdf->add_info('Title', 'Surat Pengantar '.$id_pengajuan);
$dompdf->render();
// tampilkan
$dompdf->stream("Surat-Pengantar-$id_pengajuan.pdf", array("Attachment" => false));

?>
