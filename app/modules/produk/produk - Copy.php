<?php
$koneksi 	= mysqli_connect($database['host'], $database['username'], $database['password'], $database['database']);

switch ($_GET['action']) 
{
    default: 
        action_notfound();
    
    	// INDEX 
    case 'index':
        
        $message = [];
        if (!empty($_POST['delete'])) {
            $delete = $db->delete('produk', ['id_produk' => $_POST['id']]);
            if ($delete) {
                $message['status'] = 'ok';
                $message['message'] = 'Data berhasil dihapus';
            } else {
                $message['status'] = 'error';
                $message['message'] = 'Data gagal dihapus';
            }
        }
        $sql = 'SELECT * FROM produk';
        $query 		= mysqli_query($koneksi, $sql);
        $result 	= mysqli_fetch_all($query, MYSQLI_ASSOC);

        $data['hasil'] = $result;
        $data['message'] = $message;

        if (!$data['hasil'])
            data_notfound($data);

        load_view('views/result.php', $data);
    
    case 'add':
        $data['title'] = 'Tambah data';

    case 'edit':

        if (empty($_GET['id']))
            data_notfound();

        $message = [];
        if (!empty($_POST['submit'])) {
            $error = validate_form();
            if ($error) {
                $message['status'] = 'error';
                $message['message'] = $error;
            } else {
                $data_db['nama_produk'] = $_POST['nama_produk'];
                $data_db['deskripsi_produk'] = $_POST['deskripsi_produk'];
                $query = $db->update('produk', $data_db, ['id_produk1' => $_POST['id']]);
                if ($query) {
                    $message['status'] = 'ok';
                    $message['message'] = 'Data berhasil disimpan';
                } else {
                    $message['status'] = 'error';
                    $message['message'] = 'Data gagal disimpan';
                }
            }
        }

        $sql = 'SELECT * FROM produk WHERE id_produk = ?';
        $produk = $db->query($sql, $_GET['id'])->getRowArray();

        if (!$produk)
            data_notfound();

        $data['title'] = 'Edit Data Produk';
        $data['produk'] = $produk;
        $data['message'] = $message;
        load_view('views/form.php', $data);
}

function validate_form() {
    $error = false;
    if (empty(trim($_POST['nama_produk']))) {
        $error[] = 'Nama Produk harus diisi';
    }

    if (empty(trim($_POST['deskripsi_produk']))) {
        $error[] = 'Deskripsi Produk harus diisi';
    }

    return $error;
}
