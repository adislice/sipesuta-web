<?php 

require '../vendor/autoload.php';

use App\Database;
use App\PengajuanSurat;
use App\Utils;
use Dompdf\Dompdf;
use App\User;

$config = include '../konfigurasi.php';
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


$user = new User($conn);
$pengajuan = new PengajuanSurat($conn);

if (isset($_GET['tanggal_awal'])) {
    $tampil_laporan = true;
    $tanggal_awal = $_GET['tanggal_awal'];
    $tanggal_akhir = $_GET['tanggal_akhir'];
    $filter_status = $_GET['filter_status'];
}
$total_pengajuan = 0;

if (!isset($tampil_laporan)) {
    $result = $pengajuan->laporan(null, null);
} else {
    $result = $pengajuan->laporan($tanggal_awal, $tanggal_akhir, $filter_status);
}
$kode_kelurahan = $config['kode_kelurahan'];
$total = $result->num_rows;
$tabel = "";
$idx = 1;
while ($row = $result->fetch_assoc()) {

$tabel .= "<tr>
    <td>".$idx."</td>
    <td>".$row['nama']."</td>
    <td>".strftime("%e %B %Y, %H:%M", strtotime($row['tanggal']))."</td>
    <td>".$row['nama_jenis']."</td>
    <td>".$row['nama_akun']."</td>
    <td>".$row['email']."</td>
    <td>".strtoupper($row['status'])."</td>
</tr>";

$idx++;
}
    
    
$awal = strftime("%e %B %Y", strtotime($tanggal_awal));
$akhir = strftime("%e %B %Y", strtotime($tanggal_akhir));

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
    .tabel td {
      vertical-align: top;
    }
  </style>
</head>
<body>
  <div class="kop-surat">
    <table>
      <tr>
        <td class="kop-logo">
          <img src="./images/lambang_kota_pekalongan.png" width="60px">
        </td>
        <td class="kop-teks">
          <h2>PEMERINTAH KOTA PEKALONGAN</h2>
          <h3>KECAMATAN PEKALONGAN TIMUR</h3>
          <h3>KELURAHAN KALIBAROS</h3>
          <span>Jl. Ir. Sutami No.2, Pekalongan Timur, Pekalongan</span>
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
      <h3>Laporan Pengajuan Surat Warga</h3>
      <p>Tanggal ${awal} - ${akhir}</p>
    </div>
    <table border="1" style="border-collapse: collapse;" class="tabel">
    <tr>
        <th scope="col" class="">
            No.
        </th>
        <th scope="col" class="">
            Nama
        </th>
        <th scope="col" class="">
            Tanggal
        </th>
        <th scope="col" class="">
            Jenis Pengajuan
        </th>
        <th scope="col" class="">
            Nama Akun
        </th>
        <th scope="col" class="">
            Email Akun
        </th>
        <th scope="col" class="">
            Status
        </th>
    </tr>

    ${tabel}
      
    </table>
    <p>Total Pengajuan : ${total}</p>
    
    
  </div>

</body>

</html>

EOD;

//inisialisasi Dompdf
$dompdf = new Dompdf();
$dompdf->getOptions()->setChroot(__DIR__);
// load HTML
$dompdf->loadHtml($html);
// render
$dompdf->add_info('Title', "Laporan Pengajuan Surat Warga $awal - $akhir");
$dompdf->render();
// tampilkan
$dompdf->stream("Laporan $awal - $akhir.pdf", array("Attachment" => false));


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <?php 
     echo $html;
    ?>
    
</body>
</html>