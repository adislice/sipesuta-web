<?php 

namespace App;

class Staf {
    private \mysqli $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function tampil($id_staf)
    {
        $stmt = $this->conn->prepare("SELECT * FROM staf WHERE id_staf = ? LIMIT 1");
        $stmt->bind_param("i", $id_staf);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function login($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM staf WHERE email = ?");
        $stmt->bind_param("s", $email);
        echo $this->conn->error;
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;

    }
    public function tambah_staf($nama, $nip, $email, $password, $status, $level)
    {
        $level_int = (int) $level;
        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO staf (nama, nip, email, password, status, level) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $nama, $nip, $email, $hashed_passwd, $status, $level_int);
        $result = $stmt->execute();
        return $result;
    }

    public function is_exists($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM staf WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function tampil_semua()
    {
        $stmt = $this->conn->prepare("SELECT * FROM staf");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function hapus($id_staf)
    {
        $stmt = $this->conn->prepare("DELETE from staf where id_staf = ?");
        $stmt->bind_param("i", $id_staf);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    public function ubah($id_staf, $nama, $email, $nip, $status, $level)
    {
        $stmt = $this->conn->prepare("UPDATE staf SET nama = ?, email = ?, nip = ?, status = ?, level = ? where id_staf = ?");
        $stmt->bind_param("ssssii", $nama, $email, $nip, $status, $level, $id_staf);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    public function ubah_password($id_staf, $password)
    {
        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE staf SET  password = ? where id_staf = ?");
        $stmt->bind_param("si", $hashed_passwd, $id_staf);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    

    
}

?>