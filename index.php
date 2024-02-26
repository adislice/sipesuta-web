<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Pelayanan Surat Pengantar Kelurahan Kalibaros</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;700&display=swap');

    </style>
</head>

<body>
    <nav id="landing_nav" class="navbar py-5 fixed top-0 z-50 transition-all ease duration-500">
        <div class="container m-auto">
            <div class="flex-1">
                <div class="dropdown">
                    <label tabindex="0" class="btn btn-ghost lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="menu menu-compact dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a href="">Login</a></li>
                        <li><a href="">Register</a></li>
                    </ul>
                </div>
                <a class="btn btn-ghost normal-case text-xl text-white" id="brandText">SIPESUTA</a>
            </div>
            <div class="hidden lg:flex flex-none" id="navButton">
                <a href="login.php" class="btn mx-2 btn-primary rounded-full min-h-8 h-10">Login</a>
            </div>
        </div>
    </nav>
    <section id="gambar">
        <div class="hero min-h-screen" style="background-image: url('images/IMG_20220117_104041.jpg'); ">
            <div class="hero-overlay bg-opacity-60"></div>
            <div class="hero-content text-center text-neutral-content py-28 md:py-12">
                <div class="max-w-lg lg:max-w-xl">
                    <div class="w-20 rounded mx-auto mb-2">
                        <img src="images/lambang_kota_pekalongan.png" />
                    </div>
                    <h1 class="mb-5 text-2xl md:text-4xl font-bold">Sistem Informasi Pelayanan Surat Pengantar Kelurahan Kalibaros</h1>
                    <p class="mb-5">Jalan Ir. Sutami No. 3, Kalibaros, Kota Pekalongan</p>
                    <div>
                        <a href="#hero">
                        <i class='bx bx-down-arrow-alt text-4xl'></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="hero" class="hero min-h-max bg-base-100 lg:px-24 lg:py-24 pt-14">
        <div class="hero-content flex-col lg:flex-row-reverse text-center lg:text-left">
            <img src="./images/hero-image.png" class="max-w-sm lg:max-w-lg" />
            <div>
                <h1 class="text-xl lg:text-3xl font-bold">Bikin Surat Pengantar Kini Semakin Mudah</h1>
                <p class="py-6 text-lg lg:text-xl">Buat surat pengantar atau surat keterangan di Kelurahan Kalibaros semakin mudah secara online yang dapat diakses kapan saja dan di mana saja</p>
                <a href="login.php" class="btn btn-primary min-h-8 h-10">Login</a> atau <a href="register.php" class="link link-primary">Register</a>
                
            </div>
        </div>
    </section>

    

    <script src="https://unpkg.com/boxicons@2.1.2/dist/boxicons.js"></script>
    <script src="./js/main.js"></script>
</body>

</html>