<?php
session_start();
if (!isset($_SESSION['level'])) {
    header("location:login.php");
    exit();
}
include "koneksi.php";

$keyword = "";
$hasil_pencarian = null;
$riwayat_bayar = null;

// Logika ketika tombol "Cek Pembayaran" diklik
if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    
    // Cari data siswa berdasarkan NISN atau Nama
    $query_siswa = mysqli_query($koneksi, "SELECT * FROM tabel_siswa JOIN tabel_kelas ON tabel_siswa.id_kelas = tabel_kelas.id_kelas WHERE tabel_siswa.nisn LIKE '%$keyword%' OR tabel_siswa.nama LIKE '%$keyword%'");
    
    if (mysqli_num_rows($query_siswa) > 0) {
        $hasil_pencarian = mysqli_fetch_assoc($query_siswa);
        $nisn_siswa = $hasil_pencarian['nisn'];
        
        // Ambil data riwayat pembayaran siswa tersebut
        $riwayat_bayar = mysqli_query($koneksi, "SELECT * FROM tabel_pembayaran JOIN tabel_petugas ON tabel_pembayaran.id_petugas = tabel_petugas.id_petugas WHERE tabel_pembayaran.nisn = '$nisn_siswa'");
    }
}

// Query untuk menampilkan seluruh daftar siswa beserta status pembayarannya (Lunas / Belum Lunas)
$query_semua_status = mysqli_query($koneksi, "
    SELECT tabel_siswa.*, tabel_kelas.nama_kelas, 
    (SELECT COUNT(*) FROM tabel_pembayaran WHERE tabel_pembayaran.nisn = tabel_siswa.nisn) as jumlah_bayar 
    FROM tabel_siswa 
    JOIN tabel_kelas ON tabel_siswa.id_kelas = tabel_kelas.id_kelas
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Pembayaran - Aplikasi SPP</title>
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
                    <li class="nav-item mb-2"><a href="cek_pembayaran.php" class="nav-link text-white active bg-primary rounded"><i class="fas fa-search-dollar me-2"></i> Cek Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="pembayaran.php" class="nav-link text-white"><i class="fas fa-money-bill-wave me-2"></i> Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="history.php" class="nav-link text-white"><i class="fas fa-history me-2"></i> History Pembayaran</a></li>
                    <?php if ($_SESSION['level'] == 'admin') : ?>
                    <li class="nav-item mb-2"><a href="data_petugas.php" class="nav-link text-white"><i class="fas fa-user-shield me-2"></i> Data Petugas</a></li>
                    <?php endif; ?>
                    <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger bg-light rounded text-center fw-bold"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Cek Status Pembayaran Siswa</h1>
                    <span class="badge bg-secondary p-2">Login: <b><?= strtoupper($_SESSION['level']); ?></b> (<?= $_SESSION['nama']; ?>)</span>
                </div>

                <!-- Form Searching NISN / Nama -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-9 mb-2">
                                    <label class="form-label fw-bold">Cari Berdasarkan NISN atau Nama Siswa:</label>
                                    <input type="text" name="keyword" class="form-control" placeholder="Contoh: 0051234561 atau Andi" value="<?= htmlspecialchars($keyword); ?>" required>
                                </div>
                                <div class="col-md-3 mb-2 d-flex align-items-end">
                                    <button type="submit" name="cari" class="btn btn-primary w-100 me-1">
                                        <i class="fas fa-search me-1"></i> Cek Pembayaran
                                    </button>
                                    <?php if (!empty($keyword)) : ?>
                                        <a href="cek_pembayaran.php" class="btn btn-outline-secondary">Reset</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kolom Hasil Pencarian Spesifik (Jika tombol cari diklik) -->
                <?php if (isset($_GET['cari'])) : ?>
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas id-card me-2"></i> Hasil Pencarian Siswa & Riwayat Transaksi</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($hasil_pencarian) : ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td width="35%"><strong>NISN</strong></td>
                                                <td>: <?= $hasil_pencarian['nisn']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nama Siswa</strong></td>
                                                <td>: <?= $hasil_pencarian['nama']; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless mb-0">
                                            <tr>
                                                <td width="35%"><strong>Kelas</strong></td>
                                                <td>: <?= $hasil_pencarian['nama_kelas']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status Umum</strong></td>
                                                <td>: 
                                                    <?php 
                                                        $cek_status_temp = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_pembayaran WHERE nisn = '".$hasil_pencarian['nisn']."'");
                                                        $r_temp = mysqli_fetch_assoc($cek_status_temp);
                                                        if ($r_temp['total'] > 0) {
                                                            echo '<span class="badge bg-success">Sudah Lunas (Ada Pembayaran)</span>';
                                                        } else {
                                                            echo '<span class="badge bg-danger">Belum Lunas</span>';
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <h6 class="fw-bold border-bottom pb-2">Detail Riwayat Pembayaran:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>ID Pembayaran</th>
                                                <th>Petugas</th>
                                                <th>Tgl Bayar</th>
                                                <th>Bulan & Tahun</th>
                                                <th>Jumlah Bayar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($riwayat_bayar) > 0) : ?>
                                                <?php while ($r = mysqli_fetch_assoc($riwayat_bayar)) : ?>
                                                <tr>
                                                    <td><?= $r['id_pembayaran']; ?></td>
                                                    <td><?= $r['nama_petugas']; ?></td>
                                                    <td><?= $r['tgl_bayar']; ?></td>
                                                    <td>
                                                        <?php 
                                                            $bln = isset($r['bulan_dibayar']) ? $r['bulan_dibayar'] : (isset($r['bulan']) ? $r['bulan'] : '-');
                                                            $thn = isset($r['tahun_dibayar']) ? $r['tahun_dibayar'] : (isset($r['tahun']) ? $r['tahun'] : '-');
                                                            echo $bln . ' ' . $thn;
                                                        ?>
                                                    </td>
                                                    <td>Rp <?= number_format($r['jumlah_bayar'], 0, ',', '.'); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">Siswa ini belum memiliki riwayat transaksi pembayaran.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else : ?>
                                <div class="alert alert-warning mb-0 text-center">
                                    Data siswa dengan kata kunci <b>"<?= htmlspecialchars($keyword); ?>"</b> tidak ditemukan.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tabel Data Status Keseluruhan Siswa (Lunas / Belum Lunas) -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Rekapitulasi Status Pembayaran Semua Siswa</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Status Pembayaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($query_semua_status)) : ?>
                                    <tr>
                                        <td><?= $row['nisn']; ?></td>
                                        <td><?= $row['nama']; ?></td>
                                        <td><?= $row['nama_kelas']; ?></td>
                                        <td>
                                            <?php if ($row['jumlah_bayar'] > 0) : ?>
                                                <span class="badge bg-success">Sudah Lunas (<?= $row['jumlah_bayar']; ?>x Transaksi)</span>
                                            <?php else : ?>
                                                <span class="badge bg-danger">Belum Lunas</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</body>
</html>