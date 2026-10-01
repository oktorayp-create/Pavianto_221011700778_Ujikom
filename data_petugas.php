<?php
session_start();
// Validasi hak akses (biasanya hanya admin yang boleh akses manajemen petugas)
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("location:dashboard.php");
    exit();
}
include "koneksi.php";

$pesan = "";
$error = "";

// 1. Proses Tambah Petugas
if (isset($_POST['tambah_petugas'])) {
    $id_petugas = mysqli_real_escape_string($koneksi, $_POST['id_petugas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']); // Bisa di-md5($password) jika sistem lama menggunakan MD5
    $nama_petugas = mysqli_real_escape_string($koneksi, $_POST['nama_petugas']);
    $level = mysqli_real_escape_string($koneksi, $_POST['level']);

    $cek = mysqli_query($koneksi, "SELECT * FROM tabel_petugas WHERE id_petugas = '$id_petugas' OR username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "ID Petugas atau Username sudah terdaftar!";
    } else {
        $query = mysqli_query($koneksi, "INSERT INTO tabel_petugas (id_petugas, username, password, nama_petugas, level) VALUES ('$id_petugas', '$username', '$password', '$nama_petugas', '$level')");
        if ($query) {
            $pesan = "Data petugas berhasil ditambahkan!";
        } else {
            $error = "Gagal menambah data: " . mysqli_error($koneksi);
        }
    }
}

// 2. Proses Edit / Ubah Petugas
if (isset($_POST['edit_petugas'])) {
    $id_petugas = mysqli_real_escape_string($koneksi, $_POST['id_petugas']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_petugas = mysqli_real_escape_string($koneksi, $_POST['nama_petugas']);
    $level = mysqli_real_escape_string($koneksi, $_POST['level']);
    $password_baru = $_POST['password'];

    // Jika password diisi, update beserta passwordnya. Jika kosong, biarkan password lama.
    if (!empty($password_baru)) {
        $password = mysqli_real_escape_string($koneksi, $password_baru);
        $query = mysqli_query($koneksi, "UPDATE tabel_petugas SET username = '$username', password = '$password', nama_petugas = '$nama_petugas', level = '$level' WHERE id_petugas = '$id_petugas'");
    } else {
        $query = mysqli_query($koneksi, "UPDATE tabel_petugas SET username = '$username', nama_petugas = '$nama_petugas', level = '$level' WHERE id_petugas = '$id_petugas'");
    }

    if ($query) {
        $pesan = "Data petugas berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}

// 3. Proses Hapus Petugas
if (isset($_GET['hapus'])) {
    $id_petugas = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cegah admin menghapus akunnya sendiri yang sedang aktif login
    if ($id_petugas == ($_SESSION['id_petugas'] ?? '')) {
        $error = "Kamu tidak dapat menghapus akun yang sedang aktif digunakan login saat ini!";
    } else {
        $query = mysqli_query($koneksi, "DELETE FROM tabel_petugas WHERE id_petugas = '$id_petugas'");
        if ($query) {
            $pesan = "Data petugas berhasil dihapus!";
        } else {
            $error = "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    }
}

// Generate ID Petugas Otomatis (Contoh: PTG01, PTG02, dst)
$q_id = mysqli_query($koneksi, "SELECT MAX(id_petugas) as max_id FROM tabel_petugas");
$d_id = mysqli_fetch_assoc($q_id);
$auto_id = "PTG01";
if ($d_id && !empty($d_id['max_id'])) {
    $no_urut = (int) substr($d_id['max_id'], 3, 3) + 1;
    $auto_id = "PTG" . sprintf("%02d", $no_urut);
}

// Ambil Data Petugas
$data_petugas = mysqli_query($koneksi, "SELECT * FROM tabel_petugas ORDER BY id_petugas ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Petugas - MyApps_UJIKOM</title>
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
                    <li class="nav-item mb-2"><a href="history.php" class="nav-link text-white"><i class="fas fa-history me-2"></i> History Pembayaran</a></li>
                    <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin') : ?>
                    <li class="nav-item mb-2"><a href="data_petugas.php" class="nav-link text-white active bg-primary rounded"><i class="fas fa-user-shield me-2"></i> Data Petugas</a></li>
                    <?php endif; ?>
                    <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger bg-light rounded text-center fw-bold"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Manajemen Data Petugas</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPetugas">
                        <i class="fas fa-user-plus me-1"></i> Tambah Petugas Baru
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

                <!-- Tabel Data Petugas -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="12%">ID Petugas</th>
                                        <th>Username</th>
                                        <th>Nama Petugas</th>
                                        <th width="15%">Level</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($data_petugas && mysqli_num_rows($data_petugas) > 0) : ?>
                                        <?php while ($row = mysqli_fetch_assoc($data_petugas)) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id_petugas']); ?></td>
                                            <td><?= htmlspecialchars($row['username']); ?></td>
                                            <td><?= htmlspecialchars($row['nama_petugas']); ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($row['level'] == 'admin') ? 'danger' : 'info'; ?>">
                                                    <?= strtoupper($row['level']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <!-- Tombol Edit Trigger Modal -->
                                                <button type="button" class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_petugas']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Tombol Hapus dengan Konfirmasi -->
                                                <a href="data_petugas.php?hapus=<?= $row['id_petugas']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus petugas <?= $row['nama_petugas']; ?>?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Petugas -->
                                        <div class="modal fade" id="modalEdit<?= $row['id_petugas']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="">
                                                        <div class="modal-header bg-warning text-white">
                                                            <h5 class="modal-title"><i class="fas fa-user-edit me-1"></i> Edit Data Petugas</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="id_petugas" value="<?= $row['id_petugas']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">ID Petugas</label>
                                                                <input type="text" class="form-control" value="<?= $row['id_petugas']; ?>" disabled>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Username</label>
                                                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($row['username']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Password Baru</label>
                                                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nama Petugas</label>
                                                                <input type="text" name="nama_petugas" class="form-control" value="<?= htmlspecialchars($row['nama_petugas']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Level</label>
                                                                <select name="level" class="form-control" required>
                                                                    <option value="admin" <?= ($row['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                                    <option value="petugas" <?= ($row['level'] == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_petugas" class="btn btn-warning text-white"><i class="fas fa-save me-1"></i> Update Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada data petugas.</td>
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

    <!-- Modal Tambah Petugas -->
    <div class="modal fade" id="modalTambahPetugas" tabindex="-1" aria-labelledby="modalTambahPetugasLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTambahPetugasLabel"><i class="fas fa-user-plus me-1"></i> Tambah Petugas Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ID Petugas</label>
                            <input type="text" name="id_petugas" class="form-control" value="<?= $auto_id; ?>" readonly required>
                            <div class="form-text">ID Petugas digenerate otomatis oleh sistem.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Petugas</label>
                            <input type="text" name="nama_petugas" class="form-control" placeholder="Masukkan nama lengkap..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Level</label>
                            <select name="level" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="petugas">Petugas</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_petugas" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Petugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>