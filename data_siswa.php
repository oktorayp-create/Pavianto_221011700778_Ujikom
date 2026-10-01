<?php
session_start();
if (!isset($_SESSION['level'])) {
    header("location:login.php");
    exit();
}
include "koneksi.php";

$pesan = "";
$error = "";

// 1. Proses Tambah Siswa dengan Validasi Cegah Duplikat NISN
if (isset($_POST['tambah_siswa'])) {
    $nisn         = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $nis          = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama         = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $id_kelas     = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp      = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $id_spp       = mysqli_real_escape_string($koneksi, $_POST['id_spp']);

    // Cek apakah NISN sudah terdaftar di database
    $cek_nisn = mysqli_query($koneksi, "SELECT * FROM tabel_siswa WHERE nisn = '$nisn'");
    
    if (mysqli_num_rows($cek_nisn) > 0) {
        $error = "Gagal menambah data! NISN <b>$nisn</b> sudah terdaftar di sistem. Gunakan NISN lain.";
    } else {
        $query = mysqli_query($koneksi, "INSERT INTO tabel_siswa (nisn, nis, nama, id_kelas, alamat, no_telp, id_spp) 
                  VALUES ('$nisn', '$nis', '$nama', '$id_kelas', '$alamat', '$no_telp', '$id_spp')");
                  
        if ($query) {
            $pesan = "Data siswa baru berhasil ditambahkan!";
        } else {
            $error = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
        }
    }
}

// 2. Proses Edit / Ubah Siswa
if (isset($_POST['edit_siswa'])) {
    $nisn         = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $nis          = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama         = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $id_kelas     = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp      = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $id_spp       = mysqli_real_escape_string($koneksi, $_POST['id_spp']);

    $query = mysqli_query($koneksi, "UPDATE tabel_siswa SET nis = '$nis', nama = '$nama', id_kelas = '$id_kelas', alamat = '$alamat', no_telp = '$no_telp', id_spp = '$id_spp' WHERE nisn = '$nisn'");
    if ($query) {
        $pesan = "Data siswa berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}

// 3. Proses Hapus Siswa
if (isset($_GET['hapus'])) {
    $nisn = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cek apakah siswa sudah pernah melakukan transaksi pembayaran
    $cek_trx = mysqli_query($koneksi, "SELECT * FROM tabel_pembayaran WHERE nisn = '$nisn'");
    if (mysqli_num_rows($cek_trx) > 0) {
        $error = "Data siswa tidak dapat dihapus karena memiliki riwayat transaksi pembayaran!";
    } else {
        $query = mysqli_query($koneksi, "DELETE FROM tabel_siswa WHERE nisn = '$nisn'");
        if ($query) {
            $pesan = "Data siswa berhasil dihapus!";
        } else {
            $error = "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    }
}

// Ambil Data Siswa beserta Relasi Kelas & SPP dengan Fitur Pencarian
$keyword = "";
if (isset($_GET['cari']) && !empty($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $data_siswa = mysqli_query($koneksi, "SELECT tabel_siswa.*, tabel_kelas.nama_kelas, tabel_spp.nominal, tabel_spp.tahun 
        FROM tabel_siswa 
        JOIN tabel_kelas ON tabel_siswa.id_kelas = tabel_kelas.id_kelas 
        JOIN tabel_spp ON tabel_siswa.id_spp = tabel_spp.id_spp 
        WHERE tabel_siswa.nama LIKE '%$keyword%' OR tabel_siswa.nisn LIKE '%$keyword%' 
        ORDER BY tabel_siswa.nama ASC");
} else {
    $data_siswa = mysqli_query($koneksi, "SELECT tabel_siswa.*, tabel_kelas.nama_kelas, tabel_spp.nominal, tabel_spp.tahun 
        FROM tabel_siswa 
        JOIN tabel_kelas ON tabel_siswa.id_kelas = tabel_kelas.id_kelas 
        JOIN tabel_spp ON tabel_siswa.id_spp = tabel_spp.id_spp 
        ORDER BY tabel_siswa.nama ASC");
}

// Ambil Data untuk Dropdown Kelas & SPP pada Modal
$list_kelas = mysqli_query($koneksi, "SELECT * FROM tabel_kelas ORDER BY nama_kelas ASC");
$list_spp   = mysqli_query($koneksi, "SELECT * FROM tabel_spp ORDER BY tahun DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Siswa - MyApps_UJIKOM</title>
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
                    <li class="nav-item mb-2"><a href="data_siswa.php" class="nav-link text-white active bg-primary rounded"><i class="fas fa-users me-2"></i> Data Siswa</a></li>
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
                    <h1>Manajemen Data Siswa</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
                        <i class="fas fa-user-plus me-1"></i> Tambah Siswa Baru
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

                <!-- Form Pencarian -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" name="cari" class="form-control" placeholder="Cari berdasarkan Nama Siswa atau NISN..." value="<?= htmlspecialchars($keyword); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-dark w-100"><i class="fas fa-search me-1"></i> Cari</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Data Siswa -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>NISN</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Nominal SPP</th>
                                        <th>Alamat</th>
                                        <th>No Telp</th>
                                        <th class="text-center" width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($data_siswa && mysqli_num_rows($data_siswa) > 0) : ?>
                                        <?php while ($row = mysqli_fetch_assoc($data_siswa)) : ?>
                                        <tr>
                                            <td><b><?= htmlspecialchars($row['nisn']); ?></b></td>
                                            <td><?= htmlspecialchars($row['nis']); ?></td>
                                            <td><?= htmlspecialchars($row['nama']); ?></td>
                                            <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                                            <td>Rp <?= number_format($row['nominal'], 0, ',', '.'); ?> (<?= $row['tahun']; ?>)</td>
                                            <td><?= htmlspecialchars($row['alamat']); ?></td>
                                            <td><?= htmlspecialchars($row['no_telp']); ?></td>
                                            <td class="text-center">
                                                <!-- Tombol Edit -->
                                                <button type="button" class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['nisn']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Tombol Hapus -->
                                                <a href="data_siswa.php?hapus=<?= $row['nisn']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus siswa <?= $row['nama']; ?>?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Siswa -->
                                        <div class="modal fade" id="modalEdit<?= $row['nisn']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="">
                                                        <div class="modal-header bg-warning text-white">
                                                            <h5 class="modal-title"><i class="fas fa-user-edit me-1"></i> Edit Data Siswa</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="nisn" value="<?= $row['nisn']; ?>">
                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold">NISN</label>
                                                                <input type="text" class="form-control" value="<?= $row['nisn']; ?>" disabled>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold">NIS</label>
                                                                <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($row['nis']); ?>" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold">Nama Siswa</label>
                                                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($row['nama']); ?>" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold">Kelas</label>
                                                                <select name="id_kelas" class="form-control" required>
                                                                    <?php 
                                                                    mysqli_data_seek($list_kelas, 0);
                                                                    while($k = mysqli_fetch_assoc($list_kelas)) : 
                                                                    ?>
                                                                        <option value="<?= $k['id_kelas']; ?>" <?= ($k['id_kelas'] == $row['id_kelas']) ? 'selected' : ''; ?>>
                                                                            <?= $k['nama_kelas']; ?> (<?= $k['kompetensi_keahlian']; ?>)
                                                                        </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold">Tarif SPP</label>
                                                                <select name="id_spp" class="form-control" required>
                                                                    <?php 
                                                                    mysqli_data_seek($list_spp, 0);
                                                                    while($s = mysqli_fetch_assoc($list_spp)) : 
                                                                    ?>
                                                                        <option value="<?= $s['id_spp']; ?>" <?= ($s['id_spp'] == $row['id_spp']) ? 'selected' : ''; ?>>
                                                                            Tahun <?= $s['tahun']; ?> - Rp <?= number_format($s['nominal'], 0, ',', '.'); ?>
                                                                        </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold">Alamat</label>
                                                                <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($row['alamat']); ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">No Telepon</label>
                                                                <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($row['no_telp']); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_siswa" class="btn btn-warning text-white"><i class="fas fa-save me-1"></i> Update Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada data siswa.</td>
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

    <!-- Modal Tambah Siswa Baru -->
    <div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-labelledby="modalTambahSiswaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTambahSiswaLabel"><i class="fas fa-user-plus me-1"></i> Tambah Data Siswa Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label fw-bold">NISN</label>
                            <input type="text" name="nisn" class="form-control" placeholder="Contoh: 0051234567" required>
                            <div class="form-text">Pastikan NISN bersifat unik/belum terdaftar.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">NIS</label>
                            <input type="text" name="nis" class="form-control" placeholder="Contoh: 1001" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Nama Lengkap Siswa</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama siswa..." required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Kelas</label>
                            <select name="id_kelas" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php 
                                mysqli_data_seek($list_kelas, 0);
                                while($k = mysqli_fetch_assoc($list_kelas)) : 
                                ?>
                                    <option value="<?= $k['id_kelas']; ?>"><?= $k['nama_kelas']; ?> (<?= $k['kompetensi_keahlian']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Tarif & Tahun SPP</label>
                            <select name="id_spp" class="form-control" required>
                                <option value="">-- Pilih SPP --</option>
                                <?php 
                                mysqli_data_seek($list_spp, 0);
                                while($s = mysqli_fetch_assoc($list_spp)) : 
                                ?>
                                    <option value="<?= $s['id_spp']; ?>">Tahun <?= $s['tahun']; ?> - Rp <?= number_format($s['nominal'], 0, ',', '.'); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat siswa..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No Telepon</label>
                            <input type="text" name="no_telp" class="form-control" placeholder="Contoh: 081234567890" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_siswa" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>