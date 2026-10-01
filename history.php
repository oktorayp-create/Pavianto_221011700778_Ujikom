<?php
session_start();
if (!isset($_SESSION['level'])) {
    header("location:login.php");
    exit();
}
include "koneksi.php";

// Query mengambil data history pembayaran beserta relasi ke siswa, petugas, kelas, dan spp
$query_history = mysqli_query($koneksi, "SELECT * FROM tabel_pembayaran 
    JOIN tabel_petugas ON tabel_pembayaran.id_petugas = tabel_petugas.id_petugas 
    JOIN tabel_siswa ON tabel_pembayaran.nisn = tabel_siswa.nisn 
    JOIN tabel_kelas ON tabel_siswa.id_kelas = tabel_kelas.id_kelas 
    JOIN tabel_spp ON tabel_siswa.id_spp = tabel_spp.id_spp 
    ORDER BY tabel_pembayaran.tgl_bayar DESC, tabel_pembayaran.id_pembayaran DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History & Detail Pembayaran SPP - MyApps_UJIKOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar text-white min-vh-100 p-3">
                <h4 class="text-center py-3 border-bottom">MyApps_UJIKOM</h4>
                <ul class="nav flex-column mt-3">
                    <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a href="data_kelas.php" class="nav-link text-white"><i class="fas fa-school me-2"></i> Data Kelas</a></li>
                    <li class="nav-item mb-2"><a href="data_siswa.php" class="nav-link text-white"><i class="fas fa-users me-2"></i> Data Siswa</a></li>
                    <li class="nav-item mb-2"><a href="cek_pembayaran.php" class="nav-link text-white"><i class="fas fa-search-dollar me-2"></i> Cek Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="pembayaran.php" class="nav-link text-white"><i class="fas fa-money-bill-wave me-2"></i> Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="history.php" class="nav-link text-white active bg-primary rounded"><i class="fas fa-history me-2"></i> History Pembayaran</a></li>
                    <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin') : ?>
                    <li class="nav-item mb-2"><a href="data_petugas.php" class="nav-link text-white"><i class="fas fa-user-shield me-2"></i> Data Petugas</a></li>
                    <?php endif; ?>
                    <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger bg-light rounded text-center fw-bold"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>History & Detail Pembayaran SPP</h1>
                    <span class="badge bg-secondary p-2">Login: <b><?= strtoupper($_SESSION['level'] ?? 'USER'); ?> (<?= htmlspecialchars($_SESSION['nama_petugas'] ?? $_SESSION['username'] ?? 'Admin'); ?>)</b></span>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Pembayaran</th>
                                        <th>Petugas</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Tgl Bayar</th>
                                        <th>Jumlah Bulan</th>
                                        <th>Jumlah Bayar</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($query_history && mysqli_num_rows($query_history) > 0) : ?>
                                        <?php while ($row = mysqli_fetch_assoc($query_history)) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_pembayaran']); ?></td>
                                            <td><?= htmlspecialchars($row['nama_petugas']); ?></td>
                                            <td><?= htmlspecialchars($row['nisn']); ?></td>
                                            <td><?= htmlspecialchars($row['nama']); ?></td>
                                            <td><?= htmlspecialchars($row['tgl_bayar']); ?></td>
                                            <td><?= htmlspecialchars($row['jumlah_bulan']); ?></td>
                                            <td><b class="text-success">Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></b></td>
                                            <td><span class="badge bg-success"><?= htmlspecialchars($row['status']); ?></span></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada history transaksi pembayaran.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>