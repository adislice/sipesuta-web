<?php 

namespace App;

class Warga {
    private \mysqli $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function tampil($id_warga)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE level = 4 AND id_user = ? LIMIT 1");
        $stmt->bind_param("i", $id_warga);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function login($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE level = 4 AND email = '$email'");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function register($nama, $email, $password)
    {
        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO user (nama, email, password, level) VALUES (?, ?, ?, 4)");
        $stmt->bind_param("sss", $nama, $email, $hashed_passwd);
        // $stmt->execute();
        // $result = $stmt->get_result();
        $result = $stmt->execute();
        return $result;
    }

    public function is_exists($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE level = 4 AND email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
        
    }
    public function tampil_semua($search=null)
    {
        $query = "SELECT * FROM user WHERE level = 4";
        if ($search != null) {
            $query .= " AND nama LIKE '%".$search."%'";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function hapus($id_warga)
    {
        $stmt = $this->conn->prepare("DELETE FROM user WHERE level = 4 AND id_user = ?");
        $stmt->bind_param("i", $id_warga);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }
    public function ubah($id_warga, $nama, $email)
    {
        $stmt = $this->conn->prepare("UPDATE user SET nama = ?, email = ? WHERE level = 4 AND id_user = ?");
        $stmt->bind_param("ssi", $nama, $email,  $id_warga);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }
    public function ubah_password($id_warga, $password)
    {
        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE user SET password = ? WHERE level = 4 AND id_user = ?");
        $stmt->bind_param("si", $hashed_passwd, $id_warga);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    
}

?>