<?php
session_start();
if (!isset($_SESSION['level'])) {
    header("location:login.php");
    exit();
}
include "koneksi.php";

$pesan = "";
$error = "";

// 1. Proses Tambah Kelas
if (isset($_POST['tambah_kelas'])) {
    $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $nama_kelas = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $kompetensi_keahlian = mysqli_real_escape_string($koneksi, $_POST['kompetensi_keahlian']);

    $cek = mysqli_query($koneksi, "SELECT * FROM tabel_kelas WHERE id_kelas = '$id_kelas'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "ID Kelas sudah terdaftar!";
    } else {
        $query = mysqli_query($koneksi, "INSERT INTO tabel_kelas (id_kelas, nama_kelas, kompetensi_keahlian) VALUES ('$id_kelas', '$nama_kelas', '$kompetensi_keahlian')");
        if ($query) {
            $pesan = "Data kelas berhasil ditambahkan!";
        } else {
            $error = "Gagal menambah data: " . mysqli_error($koneksi);
        }
    }
}

// 2. Proses Edit / Ubah Kelas
if (isset($_POST['edit_kelas'])) {
    $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $nama_kelas = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $kompetensi_keahlian = mysqli_real_escape_string($koneksi, $_POST['kompetensi_keahlian']);

    $query = mysqli_query($koneksi, "UPDATE tabel_kelas SET nama_kelas = '$nama_kelas', kompetensi_keahlian = '$kompetensi_keahlian' WHERE id_kelas = '$id_kelas'");
    if ($query) {
        $pesan = "Data kelas berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}

// 3. Proses Hapus Kelas
if (isset($_GET['hapus'])) {
    $id_kelas = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cek apakah kelas masih dipakai oleh siswa
    $cek_siswa = mysqli_query($koneksi, "SELECT * FROM tabel_siswa WHERE id_kelas = '$id_kelas'");
    if (mysqli_num_rows($cek_siswa) > 0) {
        $error = "Data kelas tidak dapat dihapus karena masih digunakan oleh data siswa!";
    } else {
        $query = mysqli_query($koneksi, "DELETE FROM tabel_kelas WHERE id_kelas = '$id_kelas'");
        if ($query) {
            $pesan = "Data kelas berhasil dihapus!";
        } else {
            $error = "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    }
}

// Generate ID Kelas Otomatis
$q_id = mysqli_query($koneksi, "SELECT MAX(id_kelas) as max_id FROM tabel_kelas");
$d_id = mysqli_fetch_assoc($q_id);
$auto_id = "KLS01";
if ($d_id && !empty($d_id['max_id'])) {
    $no_urut = (int) substr($d_id['max_id'], 3, 3) + 1;
    $auto_id = "KLS" . sprintf("%02d", $no_urut);
}

// Ambil Data Kelas
$data_kelas = mysqli_query($koneksi, "SELECT * FROM tabel_kelas ORDER BY id_kelas ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Kelas - MyApps_UJIKOM</title>
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
                    <li class="nav-item mb-2"><a href="data_kelas.php" class="nav-link text-white active bg-primary rounded"><i class="fas fa-school me-2"></i> Data Kelas</a></li>
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
                    <h1>Manajemen Data Kelas</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
                        <i class="fas fa-plus me-1"></i> Tambah Kelas Baru
                    </button>
                </div>

                <?php if (!empty($pesan)) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> <?= $pesan; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> <?= $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Tabel Data Kelas -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="15%">ID Kelas</th>
                                        <th>Nama Kelas</th>
                                        <th>Kompetensi Keahlian</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($data_kelas && mysqli_num_rows($data_kelas) > 0) : ?>
                                        <?php while ($row = mysqli_fetch_assoc($data_kelas)) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_kelas']); ?></td>
                                            <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                                            <td><?= htmlspecialchars($row['kompetensi_keahlian']); ?></td>
                                            <td class="text-center">
                                                <!-- Tombol Edit Trigger Modal -->
                                                <button type="button" class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_kelas']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Tombol Hapus dengan Konfirmasi -->
                                                <a href="data_kelas.php?hapus=<?= $row['id_kelas']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kelas <?= $row['nama_kelas']; ?>?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Kelas untuk setiap baris -->
                                        <div class="modal fade" id="modalEdit<?= $row['id_kelas']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="">
                                                        <div class="modal-header bg-warning text-white">
                                                            <h5 class="modal-title"><i class="fas fa-edit me-1"></i> Edit Data Kelas</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="id_kelas" value="<?= $row['id_kelas']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">ID Kelas</label>
                                                                <input type="text" class="form-control" value="<?= $row['id_kelas']; ?>" disabled>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nama Kelas</label>
                                                                <input type="text" name="nama_kelas" class="form-control" value="<?= htmlspecialchars($row['nama_kelas']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Kompetensi Keahlian</label>
                                                                <input type="text" name="kompetensi_keahlian" class="form-control" value="<?= htmlspecialchars($row['kompetensi_keahlian']); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_kelas" class="btn btn-warning text-white"><i class="fas fa-save me-1"></i> Update Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada data kelas.</td>
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

    <!-- Modal Tambah Kelas -->
    <div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="modalTambahKelasLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTambahKelasLabel"><i class="fas fa-school me-1"></i> Tambah Data Kelas</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ID Kelas</label>
                            <input type="text" name="id_kelas" class="form-control" value="<?= $auto_id; ?>" readonly required>
                            <div class="form-text">ID Kelas digenerate otomatis oleh sistem.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: XII RPL 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kompetensi Keahlian</label>
                            <input type="text" name="kompetensi_keahlian" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_kelas" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>