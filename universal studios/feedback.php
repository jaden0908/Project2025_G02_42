<?php
// feedback.php
session_start();
define('BRAND_NAME', 'Universal Studios');

require __DIR__ . '/database.php';

/* -------- Access control: only guest & customer -------- */
$isGuest = empty($_SESSION['user']);
$role    = $isGuest ? 'guest' : ($_SESSION['user']['role'] ?? 'customer');
if (!$isGuest && $role !== 'customer') {
    header('Location: index.php');
    exit;
}

/* -------- Navbar helpers (active states) -------- */
$current = basename($_SERVER['PHP_SELF']);
$pagesGroup = [
  'feature.php','gallery.php','attraction.php','package.php',
  'team.php','testimonial.php','404.php','feedback.php'
];
$isOnPages   = in_array($current, $pagesGroup, true);
$sessionRole = $isGuest ? '' : ($_SESSION['user']['role'] ?? '');
$canFeedback = $isGuest || $sessionRole === 'customer';
$active = fn($file) => $current === $file ? ' active' : '';

/* -------- Handle submit -------- */
$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_name = trim($_POST['package_name'] ?? '');
    $rating       = (int)($_POST['rating'] ?? 0);
    $message      = trim($_POST['message'] ?? '');
    $guest_name   = $isGuest ? trim($_POST['name'] ?? '')  : null;
    $guest_email  = $isGuest ? trim($_POST['email'] ?? '') : null;

    if ($rating < 1 || $rating > 5) {
        $error = 'Please choose a rating between 1 and 5.';
    } elseif ($message === '') {
        $error = 'Please enter your feedback message.';
    } elseif ($isGuest && ($guest_name === '' || $guest_email === '')) {
        $error = 'Name and email are required for guest feedback.';
    } else {
        $user_id = $isGuest ? null : (int)($_SESSION['user']['id'] ?? 0);
        $role_snapshot = $isGuest ? 'guest' : 'customer';
        if ($package_name === '') $package_name = null;

        $sql = "INSERT INTO feedbacks
                (user_id, role_snapshot, package_name, rating, message, guest_name, guest_email)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'ississs',
                $user_id,
                $role_snapshot,
                $package_name,
                $rating,
                $message,
                $guest_name,
                $guest_email
            );
            $stmt->execute();
            $success = $stmt->affected_rows > 0;
            $stmt->close();
            // Clear POST values after success so the form resets visually
            if ($success) $_POST = [];
        } catch (mysqli_sql_exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= BRAND_NAME ?> - Give Feedback</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="<?= BRAND_NAME ?> feedback, review, rating" name="keywords">
    <meta content="Share your experience at <?= BRAND_NAME ?> — we value your feedback to keep improving." name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wdth,wght@0,75..100,300..800;1,75..100,300..800&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css?v=999" rel="stylesheet">

</head>
<body>

<!-- Spinner Start -->
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
  <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
    <span class="sr-only">Loading...</span>
  </div>
</div>
<!-- Spinner End -->

<!-- ===================== Navbar & Hero Start =============== -->
<div class="container-fluid nav-bar sticky-top px-4 py-2 py-lg-0">
  <nav class="navbar navbar-expand-lg navbar-light">
    <a href="index.php" class="navbar-brand p-0">
      <h1 class="display-6 text-dark"><i class="fas fa-film text-primary me-3"></i><?= BRAND_NAME ?></h1>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
      <span class="fa fa-bars"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav mx-auto py-0">
        <a href="index.php"   class="nav-item nav-link<?= $active('index.php') ?>">Home</a>
        <a href="about.php"   class="nav-item nav-link<?= $active('about.php') ?>">About</a>
        <a href="service.php" class="nav-item nav-link<?= $active('service.php') ?>">Service</a>
        <a href="blog.php"    class="nav-item nav-link<?= $active('blog.php') ?>">Blog</a>

        <div class="nav-item dropdown">
          <a href="#" class="nav-link dropdown-toggle<?= $isOnPages ? ' active' : '' ?>" data-bs-toggle="dropdown">Pages</a>
          <div class="dropdown-menu m-0">
            <a href="feature.php"      class="dropdown-item<?= $active('feature.php') ?>">Our Feature</a>
            <a href="gallery.php"      class="dropdown-item<?= $active('gallery.php') ?>">Our Gallery</a>
            <a href="attraction.php"   class="dropdown-item<?= $active('attraction.php') ?>">Attractions</a>
            <a href="package.php"      class="dropdown-item<?= $active('package.php') ?>">Ticket Packages</a>
            <a href="team.php"         class="dropdown-item<?= $active('team.php') ?>">Our Team</a>
            <a href="testimonial.php"  class="dropdown-item<?= $active('testimonial.php') ?>">Testimonial</a>
            <?php if ($canFeedback): ?>
              <a href="feedback.php"   class="dropdown-item<?= $active('feedback.php') ?>">Give Feedback</a>
            <?php endif; ?>
            <a href="404.php"          class="dropdown-item<?= $active('404.php') ?>">404 Page</a>
          </div>
        </div>

        <a href="contact.php" class="nav-item nav-link<?= $active('contact.php') ?>">Contact</a>

        <?php if ($isGuest): ?>
          <a href="signup.php" class="nav-item nav-link<?= $active('signup.php') ?>">Sign Up</a>
          <a href="login.php"  class="nav-item nav-link<?= $active('login.php') ?>">Sign In</a>
        <?php else: ?>
          <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <?php
                echo htmlspecialchars($_SESSION['user']['name']);
                if (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] !== 'customer') {
                  echo ' (' . ucfirst($_SESSION['user']['role']) . ')';
                }
              ?>
            </a>
            <div class="dropdown-menu m-0">
              <a href="profile.php" class="dropdown-item<?= $active('profile.php') ?>">Profile</a>
              <?php if (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'staff'): ?>
                <a href="staff_dashboard.php" class="dropdown-item">Dashboard</a>
              <?php elseif (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="dropdown-item">Dashboard</a>
              <?php endif; ?>
              <a href="logout.php" class="dropdown-item">Sign Out</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div class="team-icon d-none d-xl-flex justify-content-center me-3">
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-twitter"></i></a>
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-instagram"></i></a>
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
      </div>
      <a href="package.php" class="btn btn-primary rounded-pill py-2 px-4 flex-shrink-0">Ticket Packages</a>
    </div>
  </nav>
</div>
<!-- ===================== Navbar & Hero End ================= -->

<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
  <div class="container text-center py-5" style="max-width: 900px;">
    <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Give Feedback</h4>
  </div>
</div>
<!-- Header End -->

<!-- Feedback Start -->
<div class="container-fluid service py-5"><!-- reuse same dark section background class -->
  <div class="container py-5">
    <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
      <h1 class="display-5 text-white mb-4">We Value Your Experience</h1>
      <p class="mb-0 text-white">Tell us about your visit to <?= BRAND_NAME ?>. Your input helps us make the park even better.</p>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.3s">
        <?php if ($success): ?>
          <div class="alert alert-success">Thank you! Your feedback has been submitted.</div>
        <?php elseif ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="service-item p-4">
          <div class="service-content">
            <form method="post" novalidate>
              <?php if ($isGuest): ?>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                  </div>
                </div>
              <?php else: ?>
                <div class="mb-3">
                  <label class="form-label">You are</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['name'] ?? 'Customer') ?> (Customer)" readonly>
                </div>
              <?php endif; ?>

              <div class="row g-3 mt-1">
                <div class="col-md-8">
                  <label class="form-label">Package (optional)</label>
                  <input type="text" name="package_name" class="form-control" placeholder="e.g., Hotel + Park Tickets"
                         value="<?= htmlspecialchars($_POST['package_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Rating *</label>
                  <select name="rating" class="form-select" required>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                      <option value="<?= $i ?>" <?= (isset($_POST['rating']) && (int)$_POST['rating'] === $i) ? 'selected' : '' ?>>
                        <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                      </option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>

              <div class="mt-3">
                <label class="form-label">Feedback *</label>
                <textarea name="message" class="form-control" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary rounded-pill py-2 px-4">Submit Feedback</button>
                <a href="package.php" class="btn btn-outline-secondary rounded-pill py-2 px-4">Back to Packages</a>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<!-- Feedback End -->

<!-- Footer Start -->
<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
  <div class="container py-5">
    <div class="row g-5">
      <div class="col-md-6 col-lg-6 col-xl-4">
        <div class="footer-item">
          <a href="index.php" class="p-0">
            <h4 class="text-white mb-4"><i class="fas fa-film text-primary me-3"></i><?= BRAND_NAME ?></h4>
          </a>
          <p class="mb-2">Experience movie magic with thrilling rides, spectacular shows and immersive lands.</p>
          <div class="d-flex align-items-center">
            <i class="fas fa-map-marker-alt text-primary me-3"></i>
            <p class="text-white mb-0">123 Studio Drive, City, Country</p>
          </div>
          <div class="d-flex align-items-center">
            <i class="fas fa-envelope text-primary me-3"></i>
            <p class="text-white mb-0">info@universalstudios.com</p>
          </div>
          <div class="d-flex align-items-center">
            <i class="fa fa-phone-alt text-primary me-3"></i>
            <p class="text-white mb-0">(+012) 3456 7890</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-6 col-xl-2">
        <div class="footer-item">
          <h4 class="text-white mb-4">Quick Links</h4>
          <a href="#"><i class="fas fa-angle-right me-2"></i> About Us</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Feature</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Attractions</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Tickets</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Blog</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Contact us</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-6 col-xl-2">
        <div class="footer-item">
          <h4 class="text-white mb-4">Support</h4>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Privacy Policy</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Terms & Conditions</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Disclaimer</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Support</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> FAQ</a>
          <a href="#"><i class="fas fa-angle-right me-2"></i> Help</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-6 col-xl-4">
        <div class="footer-item">
          <h4 class="text-white mb-4">Opening Hours</h4>
          <div class="opening-date mb-3 pb-3">
            <div class="opening-clock flex-shrink-0">
              <h6 class="text-white mb-0 me-auto">Monday - Friday:</h6>
              <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i> 11:00 AM - 16:00 PM</p>
            </div>
            <div class="opening-clock flex-shrink-0">
              <h6 class="text-white mb-0 me-auto">Satur - Sunday:</h6>
              <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i> 09:00 AM - 17:00 PM</p>
            </div>
            <div class="opening-clock flex-shrink-0">
              <h6 class="text-white mb-0 me-auto">Holiday:</h6>
              <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i> 09:00 AM - 17:00 PM</p>
            </div>
          </div>
          <div>
            <p class="text-white mb-2">Payment Accepted</p>
            <img src="img/payment.png" class="img-fluid" alt="Image">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Footer End -->

<!-- Copyright Start -->
<div class="container-fluid copyright py-4">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-md-6 text-center text-md-start mb-md-0">
        <span class="text-body"><a href="#" class="border-bottom text-white"><i class="fas fa-copyright text-light me-2"></i><?= BRAND_NAME ?></a>, All rights reserved.</span>
      </div>
      <div class="col-md-6 text-center text-md-end text-body">
        Designed By <a class="border-bottom text-white" href="https://htmlcodex.com">HTML Codex</a>
      </div>
    </div>
  </div>
</div>
<!-- Copyright End -->

<!-- Back to Top -->
<a href="#" class="btn btn-primary btn-lg-square rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>   

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/counterup/counterup.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="js/main.js"></script>
</body>
</html>
