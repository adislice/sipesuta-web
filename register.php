<?php

require_once './vendor/autoload.php';

use App\Database;
use App\Warga;
use App\Utils;
use App\User;

session_start();
// cek cookie
$db = new Database();
$conn = $db->getConnection();
$user = new User($conn);

if (isset($_COOKIE['id']) && isset($_COOKIE['azhdaha'])) {
    echo "ada";
    $uid = $_COOKIE['id'];
    $u = $user->tampil($uid);
    if ($u->num_rows === 1) {
        $row = $u->fetch_assoc();
        if ($row['password'] == $_COOKIE['azhdaha']) {
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

if (isset($_SESSION['login'])) {
    if ($_SESSION['login_as'] == "warga") {
        header('location: warga/dashboard.php');
    } elseif ($_SESSION['login_as'] == "staf") {
        header('location: staf/dashboard.php');
    }
}

if (isset($_POST['submit_register'])) {
    $reg_nama = $_POST['nama'];
    $reg_email = $_POST['email'];
    $reg_password = $_POST['password'];

    $db = new Database("root", "");
    $conn = $db->getConnection();
    $warga = new Warga($conn);

    $email_ada = $warga->is_exists($reg_email);

    if ($email_ada) {
        $register_failed = true;
        $register_msg = "Email sudah terdaftar!";
    } else {
        $res = $warga->register($reg_nama, $reg_email, $reg_password);

        if ($res) {
            $register_success = true;
            $register_msg = "Register berhasil! Silahkan login untuk melanjutkan!";
        } else {
            $register_failed = true;
            $register_msg = "Kesalahan! Silahkan periksa kembali data yang Anda masukkan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Sistem Informasi Pelayanan Surat Pengantar Kelurahan Kalibaros</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;700&display=swap');
    </style>
</head>

<body class="bg-blue-100">
    <div class="container min-h-screen flex flex-row items-center content-center justify-center mx-auto">
        <div class="w-96 shadow-lg h-fit p-5  my-8 rounded-lg bg-white">
            <div class="flex items-center">
                <a href="index.php" class="btn btn-md btn-square btn-ghost text-primary text-2xl">
                    <i class='bx bx-arrow-back'></i>
                </a>
            </div>
            <div class="text-xl text-center font-bold">
                Register
            </div>
            <p class="text-center">Lengkapi data di bawah ini untuk membuat akun baru</p>


            <form action="register.php" class="mb-8" method="POST" onsubmit="return validate_form()">
                <div class="form-control w-full max-w-xs mx-auto my-4">
                    <?php
                    if (isset($register_failed)) {
                    ?>
                        <div class="alert alert-error">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><?= $register_msg ?></span>
                            </div>
                        </div>
                    <?php
                    Utils::clear_post_state();
                    } elseif (isset($register_success)) {
                    ?>
                        <div class="alert alert-success shadow-lg">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><?= $register_msg ?></span>
                            </div>
                        </div>
                    <?php
                    Utils::clear_post_state();
                    }
                    ?>
                    <label class="label">
                        <span class="label-text">Nama</span>
                    </label>
                    <input name="nama" type="text" placeholder="Masukkan Nama Anda" required="" class="input input-bordered focus:input-primary w-full max-w-xs h-10 min-h-8" />

                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input name="email" type="email" placeholder="contoh@gmail.com" required="" class="input input-bordered focus:input-primary w-full max-w-xs h-10 min-h-8" />
                    <label class="label">
                        <span class="label-text">Kata Sandi</span>
                    </label>
                    <input name="password" type="password" placeholder="Masukkan Kata Sandi" required="" class="input input-bordered focus:input-primary w-full max-w-xs h-10 min-h-8" id="passwd" oninput="cek_password()" onchange="cek_password()"/>
                    <span class="px-3 my-1 rounded-md bg-red-100 text-red-500 hidden" id="tooltip_passwd"><small>Kata sandi hanya menerima huruf dan angka</small></span>
                    <label class="label">
                        <span class="label-text">Konfirmasi Kata Sandi</span>
                    </label>
                    <input type="password" placeholder="Konfirmasi Kata Sandi" required="" class="input input-bordered focus:input-primary w-full max-w-xs h-10 min-h-8" id="passwd_c" oninput="confirm_password()" onchange="confirm_password()" />
                    <span class="px-3 my-1 rounded-md bg-red-100 text-red-500 hidden" id="tooltip_passwd_c"><small>Kata sandi tidak cocok</small></span>
                    <button type="submit" class="btn btn-primary block ml-auto h-10 min-h-8 py-2 mt-4" name="submit_register">Register</button>
                </div>

            </form>
            <div class="divider">Sudah punya akun?</div>
            <div class="text-center">
                <a href="login.php" class="btn btn-ghost normal-case text-primary h-10 min-h-8">Login</a>
            </div>


        </div>

    </div>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script>

        function cek_password() {
            const allowed = /^[a-z0-9]+$/i;
            var passwd = $("#passwd").val();
            if (!allowed.test(passwd) && passwd.length > 0) {
                $("#tooltip_passwd").slideDown();
                return false;
            } else {
                $("#tooltip_passwd").slideUp();
                return true;
            }
        }
        function confirm_password() {
            var passwd = $("#passwd").val();
            var passwd_c = $("#passwd_c").val();
            if (passwd === passwd_c) {
                $("#tooltip_passwd_c").slideUp();
                return true;
            } else {
                $("#tooltip_passwd_c").slideDown();
                return false;
            }
        }
        function validate_form() {
            var cek = cek_password();
            var confirm = confirm_password();
            if (cek_password() && confirm_password()) {
                return true;
            } else {
                return false;
            }
        }
    </script>
</body>

</html>
