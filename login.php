<?php 

require_once './vendor/autoload.php';

use App\Utils;
use App\Database;
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

if (isset($_POST['submit_login'])) {
    $login_email = $_POST['email'];
    $login_password = $_POST['password'];


    $result = $user->login($login_email);
    
    if ($result->num_rows === 1) {
        $user_arr = $result->fetch_assoc();

        if (password_verify($login_password, $user_arr['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['saved_login'] = $user_arr;
            $user_level = $user_arr['level'];
            if ($_POST['remember']) {
                setcookie("id", $user_arr['id_user'], strtotime("+1 month"));
                setcookie("azhdaha", $user_arr['password'], strtotime("+1 month"));
            }
            if ($user_level == 4) {
                $_SESSION['login_as'] = "warga";
                header('location: warga/dashboard.php');
            } else {
                $_SESSION['login_as'] ="staf";
                header('location: staf/dashboard.php');   
            }
            exit();
        } else {
            $login_failed = true;
        $login_msg = "Email atau Password salah! Silahkan coba lagi!";
        } 
    } else {
        $login_failed = true;
        $login_msg = "Email atau Password salah! Silahkan coba lagi!";
    }
}
if (isset($_GET['as'])) {
    $user_type = $_GET['as'];
} else {
    $user_type = "warga";
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Informasi Pelayanan Surat Pengantar Kelurahan Kalibaros</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;700&display=swap');
    </style>
</head>
<body class="bg-blue-100">
    <div class="container min-h-screen flex flex-row items-center content-center justify-center mx-auto">
        <div class="w-96 shadow-lg h-fit p-5  my-8 rounded-lg bg-white">
            <div class="flex items-center justify-between">
                <a href="index.php" class="btn btn-md btn-square btn-ghost text-primary text-2xl">
                    <i class='bx bx-arrow-back'></i>
                </a>
            </div>
            <div class="text-xl text-center font-bold">
                Login
            </div>
            <p class="text-center">Masukkan email dan password untuk login</p>
            <form action="login.php" class="mb-8" method="POST">

                <div class="form-control w-full max-w-xs mx-auto my-4">
                <?php
                    if (isset($login_failed)) {
                    ?>
                        <div class="alert alert-error bg-red-200 text-red-800">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><?= $login_msg ?></span>
                            </div>
                        </div>
                    <?php
                    Utils::clear_post_state();
                    } elseif (isset($login_success)) {
                    ?>
                        <div class="alert alert-success shadow-lg">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><?= $login_msg ?></span>
                            </div>
                        </div>
                    <?php
                    Utils::clear_post_state();
                    }
                    ?>
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input name="email" type="email" placeholder="contoh@gmail.com" required="" class="input input-bordered focus:input-primary w-full max-w-xs h-10 min-h-8" />
                    <label class="label">
                        <span class="label-text">Kata Sandi</span>
                    </label>
                    <input name="password" type="password" placeholder="Masukkan Kata Sandi" required="" class="input input-bordered focus:input-primary w-full max-w-xs h-10 min-h-8" />
                    <label class="label cursor-pointer justify-start">
                        <input name="remember" type="checkbox"  class="checkbox checkbox-primary" />
                        <span class="label-text mx-2">Ingat Saya</span>
                    </label>
                    <input type="hidden" name="login_type" value="<?php if (isset($_GET['as'])){ echo $_GET['as'];} else { echo "warga";}  ?>">
                    <button type="submit" class="btn btn-primary block ml-auto h-10 min-h-8 py-2" name="submit_login">Login</button>
                </div>
            </form>
            <div class="divider">Belum punya akun?</div>
            <div class="text-center">
                <a href="register.php" class="btn btn-ghost normal-case text-primary h-10 min-h-8">Buat Akun Baru</a>
            </div>
            <div class="border p-2 rounded">
                <b>Info Login:</b><br />
                Admin: <br />
                 - Email: rusdianto@gmail.com<br>
                Staf: <br>
                 - Email: budisetiadi@gmail.com<br>
                Warga: <br>
                 - Email: riskiawan@gmail.com<br>
                Lurah: <br>
                 - Email: supriyadi@gmail.com<br>
                <b>Password</b>: password

            </div>

            
    </div>
</body>

</html>