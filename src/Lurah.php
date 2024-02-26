<?php 

namespace App;

class Lurah {
    private \mysqli $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function tampil($id_lurah)
    {
        $stmt = $this->conn->prepare("SELECT * FROM lurah WHERE id_lurah = ? LIMIT 1");
        $stmt->bind_param("i", $id_lurah);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function login($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM lurah WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function tambah_lurah($nama, $nip, $email, $password, $status)
    {
        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO lurah (nama, nip, email, password, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nama, $nip, $email, $hashed_passwd, $status);
        $result = $stmt->execute();
        return $result;
    }

    public function is_exists($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM lurah WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
        
    }

    
}

?>