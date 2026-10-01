<?php
session_start();
if (!isset($_SESSION['level'])) {
    header("location:login.php");
    exit();
}
include "koneksi.php";

// Ambil Statistik Data untuk Card Dashboard
$q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_siswa");
$d_siswa = mysqli_fetch_assoc($q_siswa);

$q_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_kelas");
$d_kelas = mysqli_fetch_assoc($q_kelas);

$q_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_pembayaran");
$d_transaksi = mysqli_fetch_assoc($q_transaksi);

$q_petugas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_petugas");
$d_petugas = mysqli_fetch_assoc($q_petugas);

// Total Pendapatan Keseluruhan
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(tabel_spp.nominal) as total_pendapatan FROM tabel_pembayaran JOIN tabel_siswa ON tabel_pembayaran.nisn = tabel_siswa.nisn JOIN tabel_spp ON tabel_siswa.id_spp = tabel_spp.id_spp");
$d_pendapatan = mysqli_fetch_assoc($q_pendapatan);
$total_pendapatan = $d_pendapatan['total_pendapatan'] ?? 0;

// Ambil 5 Transaksi Pembayaran Terakhir
$q_latest = mysqli_query($koneksi, "SELECT * FROM tabel_pembayaran 
    JOIN tabel_petugas ON tabel_pembayaran.id_petugas = tabel_petugas.id_petugas 
    JOIN tabel_siswa ON tabel_pembayaran.nisn = tabel_siswa.nisn 
    JOIN tabel_spp ON tabel_siswa.id_spp = tabel_spp.id_spp 
    ORDER BY tabel_pembayaran.tgl_bayar DESC, tabel_pembayaran.id_pembayaran DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi SPP</title>
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
                    <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white active bg-primary rounded"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a href="data_kelas.php" class="nav-link text-white"><i class="fas fa-school me-2"></i> Data Kelas</a></li>
                    <li class="nav-item mb-2"><a href="data_siswa.php" class="nav-link text-white"><i class="fas fa-users me-2"></i> Data Siswa</a></li>
                    <li class="nav-item mb-2"><a href="cek_pembayaran.php" class="nav-link text-white"><i class="fas fa-search-dollar me-2"></i> Cek Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="pembayaran.php" class="nav-link text-white"><i class="fas fa-money-bill-wave me-2"></i> Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="history.php" class="nav-link text-white"><i class="fas fa-history me-2"></i> History Pembayaran</a></li>
                    <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin') : ?>
                    <li class="nav-item mb-2"><a href="data_petugas.php" class="nav-link text-white"><i class="fas fa-user-shield me-2"></i> Data Petugas</a></li>
                    <?php endif; ?>
                    <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger bg-light rounded text-center fw-bold"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1 class="h2">Dashboard Informasi</h1>
                        <p class="text-muted mb-0">Selamat datang kembali, silakan pantau aktivitas pembayaran SPP di sini.</p>
                    </div>
                    <span class="badge bg-secondary p-2 shadow-sm">Login: <b><?= strtoupper($_SESSION['level'] ?? 'USER'); ?> (<?= htmlspecialchars($_SESSION['nama_petugas'] ?? $_SESSION['username'] ?? 'Admin'); ?>)</b></span>
                </div>

                <!-- Row 1: Cards Informasi Utama -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-primary shadow-sm h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase fw-bold">Total Siswa</h6>
                                    <h2 class="display-6 fw-bold mb-0"><?= $d_siswa['total']; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                            <div class="card-footer bg-primary border-0 d-flex align-items-center justify-content-between small">
                                <a class="text-white text-decoration-none" href="data_siswa.php">Lihat Detail</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-success shadow-sm h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase fw-bold">Total Kelas</h6>
                                    <h2 class="display-6 fw-bold mb-0"><?= $d_kelas['total']; ?></h2>
                                </div>
                                <i class="fas fa-school fa-3x opacity-50"></i>
                            </div>
                            <div class="card-footer bg-success border-0 d-flex align-items-center justify-content-between small">
                                <a class="text-white text-decoration-none" href="data_kelas.php">Lihat Detail</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-warning shadow-sm h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase fw-bold">Total Transaksi</h6>
                                    <h2 class="display-6 fw-bold mb-0"><?= $d_transaksi['total']; ?></h2>
                                </div>
                                <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                            </div>
                            <div class="card-footer bg-warning border-0 d-flex align-items-center justify-content-between small">
                                <a class="text-dark text-decoration-none fw-bold" href="history.php">Lihat History</a>
                                <i class="fas fa-angle-right text-dark"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card text-white bg-danger shadow-sm h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase fw-bold">Total Petugas</h6>
                                    <h2 class="display-6 fw-bold mb-0"><?= $d_petugas['total']; ?></h2>
                                </div>
                                <i class="fas fa-user-shield fa-3x opacity-50"></i>
                            </div>
                            <div class="card-footer bg-danger border-0 d-flex align-items-center justify-content-between small">
                                <a class="text-white text-decoration-none" href="data_petugas.php">Lihat Detail</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Pendapatan & Info Tambahan -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i> 5 Transaksi Pembayaran Terakhir</h5>
                                <a href="history.php" class="btn btn-sm btn-outline-light">Selengkapnya</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Siswa</th>
                                                <th>Tanggal</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($q_latest) > 0) : ?>
                                                <?php while ($row = mysqli_fetch_assoc($q_latest)) : ?>
                                                <tr>
                                                    <td><b><?= htmlspecialchars($row['id_pembayaran']); ?></b></td>
                                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                                    <td><?= htmlspecialchars($row['tgl_bayar']); ?></td>
                                                    <td><span class="text-success fw-bold">Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></span></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">Belum ada transaksi tercatat.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i> Ringkasan Keuangan</h5>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center text-center">
                                <h6 class="text-muted text-uppercase">Total Pendapatan SPP</h6>
                                <h3 class="text-success fw-bold my-3">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                                <p class="text-muted small">Akumulasi total seluruh pembayaran SPP yang telah berhasil masuk ke sistem.</p>
                                <a href="pembayaran.php" class="btn btn-success mt-2"><i class="fas fa-plus-circle me-1"></i> Entri Pembayaran Baru</a>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>