<?php
session_start();
include 'config/database.php';
include 'includes/functions.php';

$success_message = '';
$error_message = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validate input
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Semua field harus diisi';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid';
    } else {
        // Process the contact form (in a real app, you would send an email or save to database)
        // For demo purposes, we'll just show a success message
        $success_message = 'Terima kasih! Pesan Anda telah dikirim. Kami akan menghubungi Anda segera.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami | DiamondStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kontak</li>
                    </ol>
                </nav>
                
                <h1 class="mb-4">Hubungi Kami</h1>
                
                <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Kirim Pesan</h3>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subjek</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Pesan</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="submit_contact" class="btn btn-primary">Kirim Pesan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Informasi Kontak</h3>
                        <div class="d-flex mb-4">
                            <div class="contact-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="ms-3">
                                <h5>Alamat</h5>
                                <p class="text-muted mb-0">Jl. Merdeka No. 123, Jakarta Pusat, 10110, Indonesia</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="contact-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="ms-3">
                                <h5>Email</h5>
                                <p class="text-muted mb-0">info@diamondstore.com</p>
                                <p class="text-muted mb-0">support@diamondstore.com</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="contact-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="ms-3">
                                <h5>Telepon</h5>
                                <p class="text-muted mb-0">+62 21 1234 5678</p>
                                <p class="text-muted mb-0">+62 812 3456 7890 (WhatsApp)</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="contact-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="ms-3">
                                <h5>Jam Operasional</h5>
                                <p class="text-muted mb-0">Senin - Jumat: 09:00 - 18:00</p>
                                <p class="text-muted mb-0">Sabtu: 09:00 - 15:00</p>
                                <p class="text-muted mb-0">Minggu & Hari Libur: Tutup</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.6664672948363!2d106.82496851476883!3d-6.175392395532956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5d2e764b12d%3A0x3d2ad6e1e0e9bcc8!2sMonumen%20Nasional!5e0!3m2!1sen!2sid!4v1653052622977!5m2!1sen!2sid" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="mb-4 text-center">Ikuti Kami</h3>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="social-icon">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="social-icon">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="social-icon">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-icon">
                                <i class="bi bi-youtube"></i>
                            </a>
                            <a href="#" class="social-icon">
                                <i class="bi bi-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
