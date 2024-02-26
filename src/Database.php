<?php 
namespace App;

mysqli_report(MYSQLI_REPORT_STRICT);

class Database {
    private $db_name = "db_kelkalibaros";
    private $db_username = "root";
    private $db_password = "";
    private $db_host = "localhost";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
		try {
			$this->conn = new \mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_name);
		} catch (\Exception $e) {
			die("Koneksi ke database gagal!");
		}
		return $this->conn;
    }
}


?>