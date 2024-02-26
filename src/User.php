<?php 

namespace App;

class User {
    private \mysqli $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function tampil($id_user)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE id_user = $id_user LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function login($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = '$email' LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;

    }
    public function tambah($nama, $nip=null, $email, $password, $status=null, $level)
    {
        $level_int = (int) $level;
        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO user (nama, nip, email, password, status, level)
        VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $nama, $nip, $email, $hashed_passwd, $status, $level_int);
        $result = $stmt->execute();
        
        return $result;
    }

    public function is_exists($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = '$email'");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function tampil_semua_staf($search=null)
    {
        $query = "SELECT * FROM user WHERE level IN (1,2,3)";
        if ($search != null) {
            $query .= " AND (nama LIKE '%".$search."%' OR email LIKE '%".$search."%' OR nip LIKE '%".$search."%')";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function tampil_semua_warga($search=null)
    {
        $query = "SELECT * FROM user WHERE level = 4";
        if ($search != null) {
            $query .= " AND (nama LIKE '%".$search."%' OR email LIKE '%".$search."%')";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function hapus($id_user)
    {
        $stmt = $this->conn->prepare("DELETE from user where id_user = $id_user");
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    public function ubah($id_user, $nama, $email, $nip=null, $status=null, $level)
    {
        $stmt = $this->conn->prepare("UPDATE user SET nama = '$nama', email = '$email', nip = '$nip', status = '$status', level = $level WHERE id_user = $id_user");
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }

    public function ubah_password($id_user, $password)
    {
        $hashed_passwd = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE user SET password = '$hashed_passwd' WHERE id_user = '$id_user'");
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();
        return $result;
    }
}

?>