<?php
session_start();
define('BRAND_NAME', 'Universal Studios');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= BRAND_NAME ?> - About Us</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="<?= BRAND_NAME ?> about, theme park, history, team" name="keywords">
    <meta content="Learn more about <?= BRAND_NAME ?>—our story, experiences, and the team that brings movie magic to life." name="description">

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
                  <a href="orders.php" class="dropdown-item<?= $active('orders.php') ?>">
                  <i class="fa fa-shopping-cart me-2"></i> Order
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




<!-- ===================== Navbar & Hero End ================= -->


<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">About Us</h4>
       
    </div>
</div>
<!-- Header End -->

<!-- About Start -->
<div class="container-fluid about py-5">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.2s">
                <div>
                    <h4 class="text-primary">About <?= BRAND_NAME ?></h4>
                    <h1 class="display-5 mb-4">Movie Magic. Real-World Thrills.</h1>
                    <p class="mb-5">
                        From adrenaline-pumping coasters to immersive worlds and award-winning shows,
                        <?= BRAND_NAME ?> brings your favorite stories to life. We’re dedicated to creating
                        unforgettable adventures for guests of every age.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3"><i class="fas fa-theater-masks fa-3x text-primary"></i></div>
                                <div>
                                    <h4>Live Entertainment</h4>
                                    <p>Stunt spectaculars, character meet-ups and dazzling parades daily.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3"><i class="fas fa-ticket-alt fa-3x text-primary"></i></div>
                                <div>
                                    <h4>Attractions For All</h4>
                                    <p>High thrills for adventurers and gentle fun for little explorers.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3"><i class="fas fa-hamburger fa-3x text-primary"></i></div>
                                <div>
                                    <h4>Dining & Snacks</h4>
                                    <p>Themed restaurants and quick bites across the park.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3"><i class="fas fa-shield-alt fa-3x text-primary"></i></div>
                                <div>
                                    <h4>Safe & Clean</h4>
                                    <p>Helpful staff, lockers and guest services for a smooth visit.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.4s">
                <div class="position-relative rounded">
                    <div class="rounded" style="margin-top: 40px;">
                        <div class="row g-0">
                            <div class="col-lg-12">
                                <div class="rounded mb-4">
                                    <img src="img/about.jpg" class="img-fluid rounded w-100" alt="About <?= BRAND_NAME ?>">
                                </div>
                                <div class="row gx-4 gy-0">
                                    <div class="col-6">
                                        <div class="counter-item bg-primary rounded text-center p-4 h-100">
                                            <div class="counter-item-icon mx-auto mb-3">
                                                <i class="fas fa-thumbs-up fa-3x text-white"></i>
                                            </div>
                                            <div class="counter-counting mb-3">
                                                <span class="text-white fs-2 fw-bold" data-toggle="counter-up">150</span>
                                                <span class="h1 fw-bold text-white">K +</span>
                                            </div>
                                            <h5 class="text-white mb-0">Happy Visitors</h5>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="counter-item bg-dark rounded text-center p-4 h-100">
                                            <div class="counter-item-icon mx-auto mb-3">
                                                <i class="fas fa-certificate fa-3x text-white"></i>
                                            </div>
                                            <div class="counter-counting mb-3">
                                                <span class="text-white fs-2 fw-bold" data-toggle="counter-up">122</span>
                                                <span class="h1 fw-bold text-white"> +</span>
                                            </div>
                                            <h5 class="text-white mb-0">Awards & Honors</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded bg-primary p-4 position-absolute d-flex justify-content-center" style="width: 90%; height: 80px; top: -40px; left: 50%; transform: translateX(-50%);">
                        <h3 class="mb-0 text-white">20 Years Experience</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About End -->

<!-- Feature Start -->
<div class="container-fluid feature pb-5">
    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.2s">
                <div class="feature-item">
                    <img src="img/feature-1.jpg" class="img-fluid rounded w-100" alt="Image">
                    <div class="feature-content p-4">
                        <div class="feature-content-inner">
                            <h4 class="text-white">Blockbuster Rides</h4>
                            <p class="text-white">Experience attractions inspired by your favorite movies and characters.</p>
                            <a
  class="btn btn-primary rounded-pill py-2 px-4"
  data-bs-toggle="modal"
  data-bs-target="#rideModal"
>Read More <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.4s">
                <div class="feature-item">
                    <img src="img/feature-2.jpg" class="img-fluid rounded w-100" alt="Image">
                    <div class="feature-content p-4">
                        <div class="feature-content-inner">
                            <h4 class="text-white">Spectacular Shows</h4>
                            <p class="text-white">Stunts, parades and live performances that bring stories to life.</p>
                            <a
  class="btn btn-primary rounded-pill py-2 px-4"
  data-bs-toggle="modal"
  data-bs-target="#showModal"
>Read More <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.6s">
                <div class="feature-item">
                    <img src="img/feature-3.jpg" class="img-fluid rounded w-100" alt="Image">
                    <div class="feature-content p-4">
                        <div class="feature-content-inner">
                            <h4 class="text-white">Studio Experiences</h4>
                            <p class="text-white">Behind-the-scenes details, immersive lands and photo-worthy sets.</p>
                            <a
  class="btn btn-primary rounded-pill py-2 px-4"
  data-bs-toggle="modal"
  data-bs-target="#studioModal"
>Read More <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Feature End -->

<!-- Gallery Start -->
<div class="container-fluid gallery pb-5">
    <div class="container pb-5">
        <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
            <h4 class="text-primary">Our Gallery</h4>
            <h1 class="display-5 mb-4">Captured Moments At <?= BRAND_NAME ?></h1>
            <p class="mb-0">Relive the fun with highlights from rides, shows and special events.</p>
        </div>
        <div class="row g-4">
            <div class="col-6 wow fadeInUp" data-wow-delay="0.2s">
                <div class="gallery-item">
                    <img src="img/gallery-1.jpg" class="img-fluid rounded w-100 h-100" alt="">
                    <div class="search-icon">
                        <a href="img/gallery-1.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-1"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-3 wow fadeInUp" data-wow-delay="0.4s">
                <div class="gallery-item">
                    <img src="img/gallery-2.jpg" class="img-fluid rounded w-100 h-100" alt="">
                    <div class="search-icon">
                        <a href="img/gallery-2.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-2"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-3 wow fadeInUp" data-wow-delay="0.6s">
                <div class="gallery-item">
                    <img src="img/gallery-3.jpg" class="img-fluid rounded w-100 h-100" alt="">
                    <div class="search-icon">
                        <a href="img/gallery-3.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-3"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-3 wow fadeInUp" data-wow-delay="0.2s">
                <div class="gallery-item">
                    <img src="img/gallery-4.jpg" class="img-fluid rounded w-100 h-100" alt="">
                    <div class="search-icon">
                        <a href="img/gallery-4.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-4"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-3 wow fadeInUp" data-wow-delay="0.4s">
                <div class="gallery-item">
                    <img src="img/gallery-5.jpg" class="img-fluid rounded w-100 h-100" alt="">
                    <div class="search-icon">
                        <a href="img/gallery-5.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-5"><i class="fas fa-search-plus"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-6 wow fadeInUp" data-wow-delay="0.6s">
                <div class="gallery-item">
                    <img src="img/gallery-6.jpg" class="img-fluid rounded w-100 h-100" alt="">
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
<!-- ===================== Feature Detail Modals ===================== -->

<!-- Blockbuster Rides -->
<div class="modal fade" id="rideModal" tabindex="-1" aria-labelledby="rideModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="rideModalLabel">Blockbuster Rides</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-6">
            <img src="img/feature-1.jpg" alt="Blockbuster Rides" class="img-fluid rounded">
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              From high-speed coasters to 3D/4D simulator rides, experience attractions
              inspired by blockbuster films and beloved characters.
            </p>
            <ul class="mb-4">
              <li>Headliner coaster with cinematic theming</li>
              <li>Family-friendly motion simulator (height 102cm+)</li>
              <li>Express access available on select rides</li>
              <li>On-ride photo & locker services nearby</li>
            </ul>
            <div class="alert alert-info">
              Tip: Arrive early or use Express upgrades to minimize wait times.
            </div>
          </div>
        </div>
      </div>

 
    </div>
  </div>
</div>

<!-- Spectacular Shows -->
<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="showModalLabel">Spectacular Shows</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-6">
            <img src="img/feature-2.jpg" alt="Spectacular Shows" class="img-fluid rounded">
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              Stunts, parades and live performances that bring stories to life—perfect
              for all ages with several showtimes daily.
            </p>
            <ul class="mb-4">
              <li>Action stunt spectacular (20–25 mins)</li>
              <li>Character parade with photo moments</li>
              <li>Indoor theater shows (air-conditioned)</li>
              <li>Accessibility seating available</li>
            </ul>
            <div class="alert alert-info">
              Tip: Check today’s schedule in the app for the next showtime and venue.
            </div>
          </div>
        </div>
      </div>

     
    </div>
  </div>
</div>

<!-- Studio Experiences -->
<div class="modal fade" id="studioModal" tabindex="-1" aria-labelledby="studioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="studioModalLabel">Studio Experiences</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-6">
            <img src="img/feature-3.jpg" alt="Studio Experiences" class="img-fluid rounded">
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              Go behind the scenes with immersive sets, props, and photo-worthy spots
              that showcase how movie magic is made.
            </p>
            <ul class="mb-4">
              <li>Backlot-style walkthroughs & set pieces</li>
              <li>Interactive prop displays and effects</li>
              <li>Guided experiences at select hours</li>
              <li>Plenty of photo opportunities</li>
            </ul>
            <div class="alert alert-info">
              Tip: Weekday afternoons are usually less crowded—great for photos.
            </div>
          </div>
        </div>
      </div>

    
    </div>
  </div>
</div>
<!-- =================== /Feature Detail Modals =================== -->

</body>
</html>
