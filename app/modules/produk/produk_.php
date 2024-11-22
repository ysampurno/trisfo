<?php
$koneksi 	= mysqli_connect($database['host'], $database['username'], $database['password'], $database['database']);

$sql = 'SELECT * FROM produk';
$koneksi    = mysqli_connect($database['host'], $database['username'], $database['password'], $database['database']);
$query      = mysqli_query($koneksi, $sql);
$result     = mysqli_fetch_all($query, MYSQLI_ASSOC);

include 'app/themes/modern/header.php';
?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Produk</th>
            <th>Deskripsi Produk</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;
    foreach ($result as $val) {
        echo '<tr>
                <td>' . $no . '</td>
                <td>' . $val['nama_produk'] . '</td>
                <td>' . $val['deskripsi_produk'] . '</td>
            </tr>';
        $no++;
    }
    ?>
    </tbody>
</table>