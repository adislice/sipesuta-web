<?php 
namespace App;

class PengajuanSurat {
    private \mysqli $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function lihat_jenis_pengajuan($id_jenis)
    {
        $stmt = $this->conn->prepare("SELECT * FROM jenis_pengajuan WHERE id_jenis = ?");
        $stmt->bind_param("i", $id_jenis);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function lihat_semua_jenis_pengajuan($search=null)
    {
        $query = "SELECT * FROM jenis_pengajuan";
        if ($search != null) {
            $query .= " WHERE nama_jenis LIKE '%".$search."%'";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }
    public function tambah_jenis_pengajuan($nama_jenis, $keterangan, $persyaratan)
    {
        $stmt = $this->conn->prepare("INSERT INTO jenis_pengajuan (nama_jenis, keterangan, persyaratan) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama_jenis, $keterangan, $persyaratan);
        $result = $stmt->execute();
        return $result;
    }
    public function hapus_jenis_pengajuan($id_jenis)
    {
        $stmt = $this->conn->prepare("DELETE from jenis_pengajuan where id_jenis = ?");
        $stmt->bind_param("i", $id_jenis);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }
    public function ubah_jenis_pengajuan($id_jenis, $nama_jenis, $keterangan, $persyaratan)
    {
        $stmt = $this->conn->prepare("UPDATE jenis_pengajuan SET nama_jenis = ?, keterangan = ?, persyaratan = ? where id_jenis = ?");
        $stmt->bind_param("sssi", $nama_jenis, $keterangan, $persyaratan, $id_jenis);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    public function tambah_pengajuan_surat($id_user, $id_jenis, $nama, $nik, $no_kk, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $kewarganegaraan, $agama, $pekerjaan, $alamat, $keterangan, $nama_lurah, $nip_lurah)
    {
        $tgl_lahir= date("Y-m-d", strtotime($tanggal_lahir));
        $query = "INSERT into pengajuan_surat (id_user, id_jenis, tanggal, nama, nik, no_kk, jenis_kelamin, tempat_lahir, tanggal_lahir, kewarganegaraan, agama, pekerjaan, alamat, keterangan, status, nama_lurah, nip_lurah)
        VALUES                                ($id_user, $id_jenis,  NOW(), '$nama', '$nik', '$no_kk', '$jenis_kelamin', '$tempat_lahir', '$tgl_lahir',  '$kewarganegaraan', '$agama', '$pekerjaan', '$alamat', '$keterangan', 1, '$nama_lurah', '$nip_lurah' )";
        $stmt = $this->conn->prepare($query);
        
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            echo $this->conn->error;
            return false;
        }
    }

    public function lihat_semua_pengajuan_surat($id_user=null, $id_jenis=null, $search=null)
    {
        $query = "SELECT id_pengajuan,tanggal, ps.nama, ps.id_user, email, nama_jenis, ps.status FROM pengajuan_surat AS ps JOIN jenis_pengajuan AS jp ON ps.id_jenis = jp.id_jenis JOIN user as u ON ps.id_user = u.id_user ";
        if ($id_user != null) {
            $query .= " WHERE ps.id_user = $id_user";
        } elseif($id_jenis != null) {
            $query .= " WHERE ps.id_jenis = $id_jenis";
        } elseif($search != null) {
            $query .= " WHERE ps.nama LIKE '%".$search."%'";
        }
        $query .= " ORDER BY tanggal DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }
    public function tampil_pending()
    {
        $query = "SELECT id_pengajuan,tanggal, ps.nama, ps.id_user, email, nama_jenis, ps.status FROM pengajuan_surat AS ps JOIN jenis_pengajuan AS jp ON ps.id_jenis = jp.id_jenis JOIN user as u ON ps.id_user = u.id_user WHERE ps.status = 'Pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function lihat_pengajuan_surat($id_pengajuan)
    {
        $stmt = $this->conn->prepare("SELECT *,ps.keterangan as keterangan_surat FROM pengajuan_surat as ps JOIN jenis_pengajuan as jp 
        ON ps.id_jenis = jp.id_jenis WHERE id_pengajuan = ? ");
        $stmt->bind_param("i", $id_pengajuan);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function update_pengajuan_surat($id_pengajuan, $nama, $nik, $no_kk, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $kewarganegaraan, $agama, $pekerjaan, $alamat, $keterangan)
    {
        $stmt = $this->conn->prepare("UPDATE pengajuan_surat  
        SET nama = '$nama',
        nik = '$nik',
        no_kk = '$no_kk',
        jenis_kelamin = '$jenis_kelamin',
        tempat_lahir = '$tempat_lahir',
        tanggal_lahir = '$tanggal_lahir',
        kewarganegaraan = '$kewarganegaraan',
        agama = '$agama',
        pekerjaan = '$pekerjaan',
        keterangan = '$keterangan',
        alamat = '$alamat' WHERE id_pengajuan = $id_pengajuan");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;

    }

    public function hapus_pengajuan($id_pengajuan)
    {
        $stmt = $this->conn->prepare("DELETE FROM pengajuan_surat WHERE id_pengajuan = ? ");
        $stmt->bind_param("i", $id_pengajuan);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_deleted = $stmt->affected_rows;
        $stmt->close();
        return $num_deleted;
    }

    public function terima($id_pengajuan)
    {
        $stmt = $this->conn->prepare("UPDATE pengajuan_surat  
        SET status = 'Selesai' WHERE id_pengajuan = $id_pengajuan");
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    public function tolak($id_pengajuan, $id_staf)
    {
        $stmt = $this->conn->prepare("UPDATE pengajuan_surat  
        SET status = 'Ditolak' WHERE id_pengajuan = $id_pengajuan");
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    public function laporan($tanggal_awal, $tanggal_akhir, $status="Semua")
    {
        if ($status == "Semua") {
            $filter = " ";
        } else {
            $filter = " AND ps.status = '$status'";
        }
        $query = "SELECT id_pengajuan,tanggal, ps.nama, ps.id_user, email, nama_jenis, ps.status, u.nama as nama_akun FROM pengajuan_surat AS ps JOIN jenis_pengajuan AS jp ON ps.id_jenis = jp.id_jenis JOIN user as u ON ps.id_user = u.id_user ";
        $query .= " WHERE CAST(tanggal as date) BETWEEN '$tanggal_awal' AND '$tanggal_akhir' $filter ORDER BY tanggal ASC ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function ambil_setahun()
    {
        $query = "SELECT MONTH(tanggal) AS bulan, COUNT(*) AS jumlah FROM pengajuan_surat
        WHERE pengajuan_surat.tanggal > (DATE_SUB(CURDATE(), INTERVAL 12 MONTH)) GROUP BY  
        MONTH(tanggal)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;

    }
}

?>