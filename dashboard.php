<?php 

$nik = "";

for ($i=1; $i <= 16; $i++) { 
    $nik .= random_int(0, 9);
}
echo "<input type='text' value='{$nik}'></input>";

?>