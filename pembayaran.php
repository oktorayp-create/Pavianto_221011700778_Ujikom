<?php
session_start();
if (!isset($_SESSION['level'])) {
    header("location:login.php");
    exit();
}
include "koneksi.php";

$pesan = "";
$error = "";

// PROSES SIMPAN PEMBAYARAN
if (isset($_POST['bayar'])) {
    $id_pembayaran      = mysqli_real_escape_string($koneksi, $_POST['id_pembayaran']);
    $nisn               = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $id_spp             = mysqli_real_escape_string($koneksi, $_POST['id_spp']);
    
    $id_petugas = $_SESSION['id_petugas'] ?? $_SESSION['id'] ?? '';
    if (empty($id_petugas)) {
        $q_ptg = mysqli_query($koneksi, "SELECT id_petugas FROM tabel_petugas LIMIT 1");
        $d_ptg = mysqli_fetch_assoc($q_ptg);
        $id_petugas = $d_ptg['id_petugas'] ?? 'PTG01';
    }

    $status             = "Sudah Lunas";
    $tgl_bayar          = date('Y-m-d');
    $tgl_terakhir_bayar = mysqli_real_escape_string($koneksi, $_POST['tgl_terakhir_bayar']);
    $batas_pembayaran   = mysqli_real_escape_string($koneksi, $_POST['batas_pembayaran']);
    $jumlah_bulan_val   = (int) $_POST['jumlah_bulan'];
    $jumlah_bulan       = $jumlah_bulan_val . " Bulan";

    // Validasi Cek Dobel: Cegah pembayaran di rentang tanggal yang sama persis
    $cek_dobel = mysqli_query($koneksi, "SELECT * FROM tabel_pembayaran WHERE nisn = '$nisn' AND tgl_terakhir_bayar = '$tgl_terakhir_bayar'");
    if (mysqli_num_rows($cek_dobel) > 0) {
        $error = "Pembayaran untuk tanggal tersebut sudah pernah dicatat! Silakan gunakan tanggal jatuh tempo berikutnya.";
    } else {
        $query_insert = mysqli_query($koneksi, "INSERT INTO tabel_pembayaran (id_pembayaran, id_petugas, nisn, id_spp, status, tgl_bayar, tgl_terakhir_bayar, batas_pembayaran, jumlah_bulan) 
            VALUES ('$id_pembayaran', '$id_petugas', '$nisn', '$id_spp', '$status', '$tgl_bayar', '$tgl_terakhir_bayar', '$batas_pembayaran', '$jumlah_bulan')");
        
        if ($query_insert) {
            header("location:pembayaran.php?nisn=$nisn&pesan=sukses");
            exit();
        } else {
            $error = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
        }
    }
}

// Pencarian Data Siswa & SPP Berdasarkan NISN
$siswa = null;
$riwayat = null;
$suggest_tgl_bayar = date('Y-m-d');
$suggest_batas     = date('Y-m-d', strtotime('+30 days'));

if (isset($_GET['nisn']) && !empty($_GET['nisn'])) {
    $nisn_cari = mysqli_real_escape_string($koneksi, $_GET['nisn']);
    
    $q_siswa = mysqli_query($koneksi, "SELECT tabel_siswa.*, tabel_kelas.nama_kelas, tabel_spp.nominal, tabel_spp.tahun 
        FROM tabel_siswa 
        JOIN tabel_kelas ON tabel_siswa.id_kelas = tabel_kelas.id_kelas 
        JOIN tabel_spp ON tabel_siswa.id_spp = tabel_spp.id_spp 
        WHERE tabel_siswa.nisn = '$nisn_cari'");
    
    if ($q_siswa && mysqli_num_rows($q_siswa) > 0) {
        $siswa = mysqli_fetch_assoc($q_siswa);
        
        // Ambil Riwayat Pembayaran Siswa
        $riwayat = mysqli_query($koneksi, "SELECT tabel_pembayaran.*, tabel_petugas.nama_petugas 
            FROM tabel_pembayaran 
            LEFT JOIN tabel_petugas ON tabel_pembayaran.id_petugas = tabel_petugas.id_petugas 
            WHERE tabel_pembayaran.nisn = '$nisn_cari' 
            ORDER BY tabel_pembayaran.tgl_terakhir_bayar DESC");

        // Cek Transaksi Terakhir untuk Otomatis Menggeser Tanggal ke Bulan Berikutnya
        $q_last_trx = mysqli_query($koneksi, "SELECT batas_pembayaran FROM tabel_pembayaran WHERE nisn = '$nisn_cari' ORDER BY tgl_terakhir_bayar DESC LIMIT 1");
        if ($q_last_trx && mysqli_num_rows($q_last_trx) > 0) {
            $d_last = mysqli_fetch_assoc($q_last_trx);
            // Tanggal bayar berikutnya mulai dari batas pembayaran sebelumnya
            $suggest_tgl_bayar = $d_last['batas_pembayaran'];
            // Batas pembayaran berikutnya ditambah 30 hari lagi (1 bulan ke depan)
            $suggest_batas     = date('Y-m-d', strtotime($suggest_tgl_bayar . ' +30 days'));
        }
    }
}

// Generate Auto ID Pembayaran
$q_max = mysqli_query($koneksi, "SELECT MAX(id_pembayaran) as max_id FROM tabel_pembayaran");
$d_max = mysqli_fetch_assoc($q_max);
$auto_id = "BYR01";
if ($d_max && !empty($d_max['max_id'])) {
    $no_urut = (int) substr($d_max['max_id'], 3, 3) + 1;
    $auto_id = "BYR" . sprintf("%02d", $no_urut);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Pembayaran SPP - MyApps_UJIKOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar text-white min-vh-100 p-3">
                <h4 class="text-center py-3 border-bottom">MyApps_UJIKOM</h4>
                <ul class="nav flex-column mt-3">
                    <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a href="data_kelas.php" class="nav-link text-white"><i class="fas fa-school me-2"></i> Data Kelas</a></li>
                    <li class="nav-item mb-2"><a href="data_siswa.php" class="nav-link text-white"><i class="fas fa-users me-2"></i> Data Siswa</a></li>
                    <li class="nav-item mb-2"><a href="cek_pembayaran.php" class="nav-link text-white"><i class="fas fa-search-dollar me-2"></i> Cek Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="pembayaran.php" class="nav-link text-white active bg-primary rounded"><i class="fas fa-money-bill-wave me-2"></i> Pembayaran</a></li>
                    <li class="nav-item mb-2"><a href="history.php" class="nav-link text-white"><i class="fas fa-history me-2"></i> History Pembayaran</a></li>
                    <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin') : ?>
                    <li class="nav-item mb-2"><a href="data_petugas.php" class="nav-link text-white"><i class="fas fa-user-shield me-2"></i> Data Petugas</a></li>
                    <?php endif; ?>
                    <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger bg-light rounded text-center fw-bold"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Transaksi Pembayaran SPP</h1>
                    <span class="badge bg-secondary p-2">Login: <b><?= strtoupper($_SESSION['level'] ?? 'USER'); ?></b></span>
                </div>

                <!-- Notifikasi Berhasil -->
                <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'sukses') : ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-check-circle me-1"></i> Pembayaran berhasil disimpan!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Notifikasi Error -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> <?= $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Form Pencarian Siswa -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="fas fa-search me-1"></i> Cari Data Siswa Berdasarkan NISN</h5>
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-9">
                                <input type="text" name="nisn" class="form-control" placeholder="Masukkan NISN Siswa..." value="<?= isset($_GET['nisn']) ? htmlspecialchars($_GET['nisn']) : ''; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Cari Siswa</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Konten Transaksi -->
                <?php if (isset($_GET['nisn'])) : ?>
                    <?php if ($siswa) : ?>
                        <div class="row">
                            <!-- Kolom Kiri: Form Bayar -->
                            <div class="col-lg-5 mb-4">
                                <div class="card shadow-sm h-100 border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fas fa-user-graduate me-1"></i> Biodata Siswa & Form Bayar</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless mb-3">
                                            <tr>
                                                <th width="35%">NISN</th>
                                                <td>: <?= htmlspecialchars($siswa['nisn']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nama Siswa</th>
                                                <td>: <?= htmlspecialchars($siswa['nama']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Kelas</th>
                                                <td>: <?= htmlspecialchars($siswa['nama_kelas']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nominal SPP</th>
                                                <td>: <b class="text-success">Rp <?= number_format($siswa['nominal'], 0, ',', '.'); ?></b> (Tahun <?= htmlspecialchars($siswa['tahun']); ?>)</td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <form method="POST" action="">
                                            <input type="hidden" name="nisn" value="<?= htmlspecialchars($siswa['nisn']); ?>">
                                            <input type="hidden" name="id_spp" value="<?= htmlspecialchars($siswa['id_spp']); ?>">

                                            <div class="mb-2">
                                                <label class="form-label fw-bold small">ID Pembayaran (Otomatis)</label>
                                                <input type="text" name="id_pembayaran" class="form-control form-control-sm bg-light" value="<?= $auto_id; ?>" readonly required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fw-bold small">Durasi / Jumlah Bulan</label>
                                                <select name="jumlah_bulan" class="form-control form-control-sm" required>
                                                    <option value="1">1 Bulan</option>
                                                    <option value="2">2 Bulan</option>
                                                    <option value="3">3 Bulan</option>
                                                    <option value="6">6 Bulan</option>
                                                    <option value="12">12 Bulan (1 Tahun)</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fw-bold small">Tanggal Mulai Bayar (Periode)</label>
                                                <input type="date" name="tgl_terakhir_bayar" class="form-control form-control-sm" value="<?= $suggest_tgl_bayar; ?>" required>
                                                <div class="form-text text-muted" style="font-size: 11px;">Otomatis melanjutkan dari batas pembayaran sebelumnya.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Batas Pembayaran Berikutnya</label>
                                                <input type="date" name="batas_pembayaran" class="form-control form-control-sm" value="<?= $suggest_batas; ?>" required>
                                            </div>
                                            <button type="submit" name="bayar" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                                <i class="fas fa-money-bill-wave me-1"></i> Simpan Pembayaran
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Riwayat Pembayaran -->
                            <div class="col-lg-7 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-history me-1"></i> Riwayat Pembayaran Siswa</h5>
                                        <span class="badge bg-primary">Realtime Data</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle small">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>ID Bayar</th>
                                                        <th>Tgl Mulai</th>
                                                        <th>Batas Tempo</th>
                                                        <th>Periode</th>
                                                        <th>Status</th>
                                                        <th>Petugas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($riwayat && mysqli_num_rows($riwayat) > 0) : ?>
                                                        <?php while ($r = mysqli_fetch_assoc($riwayat)) : ?>
                                                        <tr>
                                                            <td><b><?= htmlspecialchars($r['id_pembayaran']); ?></b></td>
                                                            <td><?= htmlspecialchars($r['tgl_terakhir_bayar']); ?></td>
                                                            <td><?= htmlspecialchars($r['batas_pembayaran']); ?></td>
                                                            <td><?= htmlspecialchars($r['jumlah_bulan']); ?></td>
                                                            <td><span class="badge bg-success"><?= htmlspecialchars($r['status']); ?></span></td>
                                                            <td><?= htmlspecialchars($r['nama_petugas'] ?? 'Admin'); ?></td>
                                                        </tr>
                                                        <?php endwhile; ?>
                                                    <?php else : ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat pembayaran untuk siswa ini.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-warning shadow-sm">
                            <i class="fas fa-exclamation-circle me-1"></i> Siswa dengan NISN tersebut tidak ditemukan.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>