<?php
session_start();
define('BRAND_NAME', 'Universal Studios');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= BRAND_NAME ?> - Blog</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="<?= BRAND_NAME ?> blog, park news, tips" name="keywords">
    <meta content="Latest news and tips from <?= BRAND_NAME ?>." name="description">

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

       <div class="d-flex align-items-center ms-auto">
        <?php if (empty($_SESSION['user'])): ?> 
          <!-- Show single icon for guests -->
           <div class="nav-item dropdown">
             <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
             <i class="fa fa-user"></i></a>
                <div class="dropdown-menu dropdown-menu-end m-0">
                     <a href="signup.php" class="dropdown-item<?= $active('signup.php') ?>">
                     <i class="fa fa-user-plus me-2"></i> Sign Up</a>
                     <a href="login.php" class="dropdown-item<?= $active('login.php') ?>">
                     <i class="fa fa-sign-in-alt me-2"></i> Sign In</a>
                 </div>
            </div>
        <?php else: ?> 
        <!-- Show name & role if logged in -->
          <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
               <i class="fa fa-user-circle me-1"></i>
        <?php 
          echo htmlspecialchars($_SESSION['user']['name']); 
            if (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] !== 'customer') { 
          echo ' (' . ucfirst($_SESSION['user']['role']) . ')'; 
        } ?>
    </a>
    <div class="dropdown-menu dropdown-menu-end m-0">
      <a href="profile.php" class="dropdown-item<?= $active('profile.php') ?>">
        <i class="fa fa-id-badge me-2"></i> Profile
      </a>
      <?php if (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'staff'): ?>
        <a href="staff_dashboard.php" class="dropdown-item"><i class="fa fa-briefcase me-2"></i> Dashboard</a>
      <?php elseif (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?> 
        <a href="admin_dashboard.php" class="dropdown-item"><i class="fa fa-cogs me-2"></i> Dashboard</a>
      <?php endif; ?>
      <a href="logout.php" class="dropdown-item"><i class="fa fa-sign-out-alt me-2"></i> Sign Out</a>
    </div>
    </div>
  </div>
<?php endif; ?>
       <a href="package.php" class="btn btn-primary rounded-pill d-flex align-items-center justify-content-center py-1 px-3 flex-shrink-0">
  Ticket Packages
      </a>
    </div>
  </nav>
</div>
<!-- ===================== Navbar & Hero End ================= -->


<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Our Blog</h4>
    
    </div>
</div>
<!-- Header End -->

<!-- Blog Start -->
<div class="container-fluid blog py-5">
  <div class="container py-5">
    <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
      <h4 class="text-primary">Our Blog</h4>
      <h1 class="display-5 mb-4">Latest News & Articles</h1>
      <p class="mb-0">Tips, event highlights, and planning guides for your next trip to <?= BRAND_NAME ?>.</p>
    </div>
    <div class="row g-4">
  <!-- Card 1 -->
  <div class="col-lg-4 d-flex">
    <div class="blog-item w-100 h-100 d-flex flex-column">
      <div class="blog-img">
        <div class="ratio ratio-16x9 rounded-top overflow-hidden">
          <a href="#" data-bs-toggle="modal" data-bs-target="#blogRideModal">
            <img src="img/blog-1.jpg" class="w-100 h-100" style="object-fit:cover;object-position:center" alt="Top rides at <?= BRAND_NAME ?>">
          </a>
        </div>
        <div class="blog-category py-2 px-4">Vacation</div>
        <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
      </div>

      <div class="blog-content p-4 d-flex flex-column flex-grow-1">
        <a href="#" class="h4 d-inline-block mb-3" data-bs-toggle="modal" data-bs-target="#blogRideModal">
          Top 5 Must-Ride Attractions at <?= BRAND_NAME ?>
        </a>
        <p class="mb-4">
          From roller coasters to 3D simulators, here’s your guide to the rides you can’t miss…
        </p>
        <a href="#" class="btn btn-primary rounded-pill py-2 px-4 mt-auto"
           data-bs-toggle="modal" data-bs-target="#blogRideModal">
          Read More <i class="fas fa-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Card 2 -->
  <div class="col-lg-4 d-flex">
    <div class="blog-item w-100 h-100 d-flex flex-column">
      <div class="blog-img">
        <div class="ratio ratio-16x9 rounded-top overflow-hidden">
          <a href="#" data-bs-toggle="modal" data-bs-target="#blogShowModal">
            <img src="img/blog-2.jpg" class="w-100 h-100" style="object-fit:cover;object-position:center" alt="Shows at <?= BRAND_NAME ?>">
          </a>
        </div>
        <div class="blog-category py-2 px-4">Insight</div>
        <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
      </div>

      <div class="blog-content p-4 d-flex flex-column flex-grow-1">
        <a href="#" class="h4 d-inline-block mb-3" data-bs-toggle="modal" data-bs-target="#blogShowModal">
          How To Plan Your Day for Shows & Parades
        </a>
        <p class="mb-4">
          Make the most of stunt shows, musical performances and the evening parade…
        </p>
        <a href="#" class="btn btn-primary rounded-pill py-2 px-4 mt-auto"
           data-bs-toggle="modal" data-bs-target="#blogShowModal">
          Read More <i class="fas fa-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Card 3 -->
  <div class="col-lg-4 d-flex">
    <div class="blog-item w-100 h-100 d-flex flex-column">
      <div class="blog-img">
        <div class="ratio ratio-16x9 rounded-top overflow-hidden">
          <a href="#" data-bs-toggle="modal" data-bs-target="#blogTipsModal">
            <img src="img/blog-3.jpg" class="w-100 h-100" style="object-fit:cover;object-position:center" alt="Best time to visit <?= BRAND_NAME ?>">
          </a>
        </div>
        <div class="blog-category py-2 px-4">Insight</div>
        <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
      </div>

      <div class="blog-content p-4 d-flex flex-column flex-grow-1">
        <a href="#" class="h4 d-inline-block mb-3" data-bs-toggle="modal" data-bs-target="#blogTipsModal">
          Family Guide: Best Times To Visit <?= BRAND_NAME ?>
        </a>
        <p class="mb-4">
          Crowds, weather and events—here’s how to choose the best day for your visit…
        </p>
        <a href="#" class="btn btn-primary rounded-pill py-2 px-4 mt-auto"
           data-bs-toggle="modal" data-bs-target="#blogTipsModal">
          Read More <i class="fas fa-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </div>
</div>

  </div>
</div>
<!-- Blog End -->


<!-- Footer Start -->
<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-lg-6 col-xl-4">
                <div class="footer-item">
                    <a href="index.php" class="p-0">
                        <h4 class="text-white mb-4"><i class="fas fa-film text-primary me-3"></i><?= BRAND_NAME ?></h4>
                    </a>
                    <p class="mb-2">Dolor amet sit justo amet elitr clita ipsum elitr est. Lorem ipsum dolor sit amet, consectetur adipiscing...</p>
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
                <span class="text-body"><a href="#" class="border-bottom text-white"><i class="fas fa-copyright text-light me-2"></i><?= BRAND_NAME ?></a>, All right reserved.</span>
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
script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/counterup/counterup.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="js/main.js"></script>
<!-- =================== Blog Detail Modals (image left, text right) =================== -->

<!-- Blog Modal 1 -->
<div class="modal fade" id="blogRideModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Top 5 Must-Ride Attractions at <?= BRAND_NAME ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4 align-items-start">
          <!-- Left: image (fixed 16:9) -->
          <div class="col-lg-6">
            <div class="ratio ratio-16x9 rounded overflow-hidden">
              <img src="img/blog-1.jpg" class="w-100 h-100 modal-img-cover" alt="Rides">
            </div>
          </div>

          <!-- Right: text -->
          <div class="col-lg-6">
            <p class="mb-3">
              Our rides range from adrenaline-pumping roller coasters to family-friendly simulators. Highlights include:
            </p>
            <ul class="mb-4">
              <li>The headline coaster with cinematic theming</li>
              <li>3D/4D simulator rides inspired by blockbuster movies</li>
              <li>Family adventure rides with minimum height 102 cm</li>
              <li>Express upgrades to save waiting time</li>
            </ul>
            <div class="alert alert-info mb-0">
              Tip: Arrive early or use Express upgrades to minimize wait times.
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer border-0">
        <button class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
        <a href="package.php" class="btn btn-primary rounded-pill">See Ticket Packages</a>
      </div>
    </div>
  </div>
</div>

<!-- Blog Modal 2 -->
<div class="modal fade" id="blogShowModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">How To Plan Your Day for Shows & Parades</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4 align-items-start">
          <div class="col-lg-6">
            <div class="ratio ratio-16x9 rounded overflow-hidden">
              <img src="img/blog-2.jpg" class="w-100 h-100 modal-img-cover" alt="Shows">
            </div>
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              With multiple shows across the park, timing is everything:
            </p>
            <ul class="mb-4">
              <li>Action stunt spectacular (20–25 mins)</li>
              <li>Character parade with photo moments</li>
              <li>Indoor theater shows (air-conditioned)</li>
              <li>Night parade with fireworks</li>
            </ul>
            <div class="alert alert-info mb-0">
              Tip: Check today’s schedule in the app for the next showtime and venue.
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer border-0">
        <button class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
        <a href="package.php" class="btn btn-primary rounded-pill">Plan Your Trip</a>
      </div>
    </div>
  </div>
</div>

<!-- Blog Modal 3 -->
<div class="modal fade" id="blogTipsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Family Guide: Best Times To Visit <?= BRAND_NAME ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4 align-items-start">
          <div class="col-lg-6">
            <div class="ratio ratio-16x9 rounded overflow-hidden">
              <img src="img/blog-3.jpg" class="w-100 h-100 modal-img-cover" alt="Best Times">
            </div>
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              When you visit can make or break your experience. Here are tips:
            </p>
            <ul class="mb-4">
              <li><b>Weekdays</b>: Less crowded, easier to explore</li>
              <li><b>School holidays</b>: More events, but heavier crowds</li>
              <li><b>Evenings</b>: Cooler weather, perfect for parades</li>
              <li><b>Seasonal events</b>: Halloween Horror Nights & Christmas Lights</li>
            </ul>
            <div class="alert alert-info mb-0">
              Tip: Weekday afternoons are usually less crowded—great for photos.
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer border-0">
        <button class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
        <a href="package.php" class="btn btn-primary rounded-pill">Book Now</a>
      </div>
    </div>
  </div>
</div>
<!-- =================== /Blog Detail Modals =================== -->


</body>
</html>
