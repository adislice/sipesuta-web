<?php 

namespace App;

class Lampiran {
    private \mysqli $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function tampil($id_lampiran)
    {
        $stmt = $this->conn->prepare("SELECT * FROM lampiran WHERE id_lampiran = ? LIMIT 1");
        $stmt->bind_param("i", $id_lampiran);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function tampil_by_pengajuan($id_pengajuan)
    {
        $stmt = $this->conn->prepare("SELECT * FROM lampiran WHERE id_pengajuan = ?");
        $stmt->bind_param("i", $id_pengajuan);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function tambah_lampiran($id_pengajuan, $nama, $lokasi_file)
    {
        $stmt = $this->conn->prepare("INSERT INTO lampiran (id_pengajuan, nama_lampiran, lokasi_file) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_pengajuan, $nama, $lokasi_file);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
    public function tambah_lampiran_arr($id_pengajuan, $files_arr)
    {
        $stmt = $this->conn->prepare("INSERT INTO lampiran (id_pengajuan, nama_lampiran, lokasi_file) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_pengajuan, $nama_lampiran, $lokasi_file);
        foreach($files_arr as $file) {
            $nama_lampiran = $file['nama_lampiran'];
            $lokasi_file = $file['lokasi_file'];
            $result = $stmt->execute();
        }
        $stmt->close();
        return $result;
    }
}

?>