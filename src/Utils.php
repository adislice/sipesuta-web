<?php 

namespace App;

class Utils {
    public static function clear_post_state()
    {
        echo "<script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        </script>";
    }
    public static function show_alert_redirect($msg, $redirect=null)
    {
        echo "<script>alert('$msg');window.location.href='$redirect'</script>";
    }

    public static function set_config($nama_config, $value)
    {
        $config = include '../konfigurasi.php';
        $config[$nama_config]= $value;
        file_put_contents('../konfigurasi.php', '$config = ' . var_export($config));
    }
    public static function get_config($nama_config)
    {
        $config = include '../konfigurasi.php';
        return $config[$nama_config];
    }
}
