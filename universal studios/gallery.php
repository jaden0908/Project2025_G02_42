<?php
session_start();
define('BRAND_NAME', 'Universal Studios');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= BRAND_NAME ?> - Gallery</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="<?= BRAND_NAME ?> gallery, rides, shows, attractions, photos" name="keywords">
    <meta content="Explore photo highlights from <?= BRAND_NAME ?> — blockbuster rides, spectacular shows, and immersive lands." name="description">

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

   <script src="https://kit.fontawesome.com/351048854e.js" crossorigin="anonymous"></script>

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
      <h1 class="display-6 text-dark"><i class="fa-solid fa-globe"></i><?= BRAND_NAME ?></h1>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
      <span class="fa fa-bars"></span>
    </button>

    <?php
    // Current page helper
    $current = basename($_SERVER['PHP_SELF']);

    // “Pages” group: list all files that live under the dropdown
    $pagesGroup = [
      'feature.php','gallery.php','attraction.php','package.php',
      'team.php','testimonial.php','404.php','feedback.php'
    ];
    $isOnPages = in_array($current, $pagesGroup, true);

    // Show "Give Feedback" to guests & customers only
    $isGuest     = empty($_SESSION['user']);
    $sessionRole = $isGuest ? '' : ($_SESSION['user']['role'] ?? '');
    $canFeedback = $isGuest || $sessionRole === 'customer';

    // Helper for active class
    $active = fn($file) => $current === $file ? ' active' : '';
    ?>

    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav mx-auto py-0">
        <a href="index.php"   class="nav-item nav-link<?= $active('index.php') ?>">Home</a>
        <a href="about.php"   class="nav-item nav-link<?= $active('about.php') ?>">About</a>
        <a href="service.php" class="nav-item nav-link<?= $active('service.php') ?>">Service</a>
        <a href="blog.php"    class="nav-item nav-link<?= $active('blog.php') ?>">Blog</a>

        <div class="nav-item dropdown">
          <!-- Only add 'active' when the current file belongs to Pages group -->
          <a href="#" class="nav-link dropdown-toggle<?= $isOnPages ? ' active' : '' ?>" data-bs-toggle="dropdown">Pages</a>
          <div class="dropdown-menu m-0">
            <a href="feature.php"      class="dropdown-item<?= $active('feature.php') ?>">Our Feature</a>
            <a href="gallery.php"      class="dropdown-item<?= $active('gallery.php') ?>">Our Gallery</a>
            <a href="package.php"      class="dropdown-item<?= $active('package.php') ?>">Ticket Packages</a>
            <?php if ($canFeedback): ?>
              <a href="feedback.php"   class="dropdown-item<?= $active('feedback.php') ?>">Give Feedback</a>
            <?php endif; ?>
          </div>
        </div>

        <a href="contact.php" class="nav-item nav-link<?= $active('contact.php') ?>">Contact</a>
      </div>

      <!-- 右侧：用户下拉 + Ticket Packages 按钮 -->
      <div class="d-flex align-items-center ms-auto gap-2 flex-nowrap">

        <?php if ($isGuest): ?> 
          <!-- Guest -->
          <div class="nav-item dropdown me-2 flex-shrink-0">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <i class="fa fa-user"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end m-0">
              <a href="signup.php" class="dropdown-item<?= $active('signup.php') ?>">
                <i class="fa fa-user-plus me-2"></i> Sign Up
              </a>
              <a href="login.php" class="dropdown-item<?= $active('login.php') ?>">
                <i class="fa fa-sign-in-alt me-2"></i> Sign In
              </a>
            </div>
          </div>

        <?php else: ?> 
          <!-- Logged in -->
          <div class="nav-item dropdown me-2 flex-shrink-0">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <i class="fa fa-user-circle me-1"></i>
              <?php 
                echo htmlspecialchars($_SESSION['user']['name']); 
                if (!empty($sessionRole) && $sessionRole !== 'customer') { 
                  echo ' (' . ucfirst($sessionRole) . ')'; 
                }
              ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end m-0">
              <a href="profile.php" class="dropdown-item<?= $active('profile.php') ?>">
                <i class="fa fa-id-badge me-2"></i> Profile
              </a>
              <?php if ($sessionRole === 'customer'): ?>
                <a href="cart.php" class="dropdown-item<?= $active('cart.php') ?>">
                  <i class="fa fa-shopping-cart me-2"></i> Cart
                </a>
              <?php elseif ($sessionRole === 'staff'): ?>
                <a href="staff_dashboard.php" class="dropdown-item"><i class="fa fa-briefcase me-2"></i> Dashboard</a>
              <?php elseif ($sessionRole === 'admin'): ?> 
                <a href="admin_dashboard.php" class="dropdown-item"><i class="fa fa-cogs me-2"></i> Dashboard</a>
              <?php endif; ?>
              <a href="logout.php" class="dropdown-item"><i class="fa fa-sign-out-alt me-2"></i> Sign Out</a>
            </div>
          </div>
        <?php endif; ?>

        <!-- Ticket Packages 按钮 -->
        <a href="package.php" 
           class="btn btn-primary rounded-pill d-flex align-items-center justify-content-center py-1 px-3 flex-shrink-0">
          Ticket Packages
        </a>
      </div>
    </div>
  </nav>
</div>



<!--
      <div class="team-icon d-none d-xl-flex justify-content-center me-3">
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-twitter"></i></a>
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-instagram"></i></a>
        <a class="btn btn-square btn-light rounded-circle mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
      </div>
      <a href="package.php" class="btn btn-primary rounded-pill py-2 px-4 flex-shrink-0">Ticket Packages</a>
    </div>
  </nav>
</div> -->
<!-- ===================== Navbar & Hero End ================= -->


<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Our Gallery</h4>
        <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pages</a></li>
            <li class="breadcrumb-item active text-primary">Gallery</li>
        </ol>    
    </div>
</div>
<!-- Header End -->

<!-- Gallery Start -->
<div class="container-fluid gallery py-5">
    <div class="container py-5">
        <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            <h4 class="text-primary">Our Gallery</h4>
            <h1 class="display-5 mb-4">Captured Moments at <?= BRAND_NAME ?></h1>
            <p class="mb-0">From high-thrill coaster drops to jaw-dropping stunt shows, here are snapshots of the movie-magic you’ll find across the park.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                <div class="gallery-item">
                    <img src="img/gallery-1.jpg" class="img-fluid rounded w-100 h-100" alt="Gallery image 1">
                    <div class="search-icon">
                        <a href="img/gallery-1.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-1"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 wow fadeInUp" data-wow-delay="0.4s">
                <div class="gallery-item">
                    <img src="img/gallery-2.jpg" class="img-fluid rounded w-100 h-100" alt="Gallery image 2">
                    <div class="search-icon">
                        <a href="img/gallery-2.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-2"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 wow fadeInUp" data-wow-delay="0.6s">
                <div class="gallery-item">
                    <img src="img/gallery-3.jpg" class="img-fluid rounded w-100 h-100" alt="Gallery image 3">
                    <div class="search-icon">
                        <a href="img/gallery-3.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-3"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 wow fadeInUp" data-wow-delay="0.2s">
                <div class="gallery-item">
                    <img src="img/gallery-4.jpg" class="img-fluid rounded w-100 h-100" alt="Gallery image 4">
                    <div class="search-icon">
                        <a href="img/gallery-4.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-4"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 wow fadeInUp" data-wow-delay="0.4s">
                <div class="gallery-item">
                    <img src="img/gallery-5.jpg" class="img-fluid rounded w-100 h-100" alt="Gallery image 5">
                    <div class="search-icon">
                        <a href="img/gallery-5.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-5"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 wow fadeInUp" data-wow-delay="0.6s">
                <div class="gallery-item">
                    <img src="img/gallery-6.jpg" class="img-fluid rounded w-100 h-100" alt="Gallery image 6">
                    <div class="search-icon">
                        <a href="img/gallery-6.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-6"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Gallery End -->

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
                    <a href="about.php"><i class="fas fa-angle-right me-2"></i> About Us</a>
                    <a href="feature.php"><i class="fas fa-angle-right me-2"></i> Feature</a>
                    <a href="attraction.php"><i class="fas fa-angle-right me-2"></i> Attractions</a>
                    <a href="package.php"><i class="fas fa-angle-right me-2"></i> Tickets</a>
                    <a href="blog.php"><i class="fas fa-angle-right me-2"></i> Blog</a>
                    <a href="contact.php"><i class="fas fa-angle-right me-2"></i> Contact us</a>
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
                        <img src="img/payment.png" class="img-fluid" alt="Payment methods">
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
