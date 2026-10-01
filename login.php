<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $input_id = mysqli_real_escape_string($koneksi, $_POST['input_id']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']); 

    // 1. Cek login untuk Admin / Petugas (Identitas & Password: id_petugas)
    $query_petugas = mysqli_query($koneksi, "SELECT * FROM tabel_petugas WHERE id_petugas='$input_id' OR username='$input_id'");
    
    if (mysqli_num_rows($query_petugas) > 0) {
        $data = mysqli_fetch_assoc($query_petugas);
        // Validasi password disamakan dengan id_petugas atau pencocokan MD5 lama
        if ($password === $data['id_petugas'] || md5($password) === $data['password']) {
            $_SESSION['user_id'] = $data['id_petugas'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama'] = $data['nama_petugas'];
            $_SESSION['level'] = $data['level']; // admin / petugas
            
            header("location:dashboard.php");
            exit();
        }
    } 

    // 2. Cek login untuk Siswa (Identitas & Password: NISN)
    $query_siswa = mysqli_query($koneksi, "SELECT * FROM tabel_siswa WHERE nisn='$input_id'");
    
    if (mysqli_num_rows($query_siswa) > 0) {
        $data = mysqli_fetch_assoc($query_siswa);
        // Validasi password harus sama dengan NISN siswa tersebut
        if ($password === $data['nisn']) {
            $_SESSION['user_id'] = $data['nisn'];
            $_SESSION['username'] = $data['nis'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['level'] = 'siswa';
            
            header("location:dashboard.php");
            exit();
        }
    }

    $error = "ID Pengenal atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyApps_Ujikom</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #653d91 100%);
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow-lg p-5" style="width: 430px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">MyApps_UJIKOM</h3>
            <p class="text-muted small">Silakan login</p>
        </div>
        
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger py-2 text-center small" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold">NISN / ID Petugas</label>
                <input type="text" name="input_id" class="form-control" placeholder="Masukkan NISN atau ID Petugas" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label text-secondary small fw-bold">PASSWORD</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 shadow-sm">MASUK</button>
        </form>
        
        <div class="mt-4 border-top pt-3 text-start">
            <small class="text-muted d-block"><b>Panduan Login:</b></small>
            <small class="text-muted d-block">• Siswa: Masukkan NISN sebagai Username & Password.</small>
            <small class="text-muted d-block">• Petugas/Admin: Masukkan ID Petugas (Cth: PTG01) sebagai Username & Password.</small>
        </div>
    </div>

</body>
</html>