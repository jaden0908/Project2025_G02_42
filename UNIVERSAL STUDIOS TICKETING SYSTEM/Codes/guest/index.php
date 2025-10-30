<?php
session_start();
define('BRAND_NAME', 'Universal Studios');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= BRAND_NAME ?> - Theme Park Website Template</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="<?= BRAND_NAME ?> theme park, attractions, rides" name="keywords">
    <meta content="Official template demo for <?= BRAND_NAME ?> with rides, shows, tickets and more." name="description">

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



    <!-- Carousel Start -->
    <div class="header-carousel owl-carousel">
        <div class="header-carousel-item">
            <img src="img/carousel-1.jpg" class="img-fluid w-100" alt="Image">
            <div class="carousel-caption">
                <div class="container align-items-center py-4">
                    <div class="row g-5 align-items-center">
                        <div class="col-xl-7 fadeInLeft animated" data-animation="fadeInLeft" data-delay="1s" style="animation-delay: 1s;">
                            <div class="text-start">
                                <h4 class="text-primary text-uppercase fw-bold mb-4">Welcome To <?= BRAND_NAME ?></h4>
                                <h1 class="display-4 text-uppercase text-white mb-4">Blockbuster Rides & Movie Magic</h1>
                                <p class="mb-4 fs-5">Step into worlds from your favorite films and shows—thrilling coasters, immersive lands, spectacular entertainment, all in one place.</p>
                                <div class="d-flex flex-shrink-0">
                                    <a class="btn btn-primary rounded-pill text-white py-3 px-5" href="package.php">Our Packages</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5 fadeInRight animated" data-animation="fadeInRight" data-delay="1s" style="animation-delay: 1s;">
                           
                        </div> 
                    </div>
                </div>
            </div>
        </div>
        <div class="header-carousel-item">
            <img src="img/carousel-2.jpg" class="img-fluid w-100" alt="Image">
            <div class="carousel-caption">
                <div class="container py-4">
                    <div class="row g-5 align-items-center">
                        <div class="col-xl-7 fadeInLeft animated" data-animation="fadeInLeft" data-delay="1s" style="animation-delay: 1s;">
                            <div class="text-start">
                                <h4 class="text-primary text-uppercase fw-bold mb-4">Welcome To <?= BRAND_NAME ?></h4>
                                <h1 class="display-4 text-uppercase text-white mb-4">The Ultimate Movie-Themed Park</h1>
                                <p class="mb-4 fs-5">From high-speed adventures to family-friendly fun, discover entertainment inspired by global blockbusters.</p>
                                <div class="d-flex flex-shrink-0">
                                    <a class="btn btn-primary rounded-pill text-white py-3 px-5" href="package.php">Our Packages</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5 fadeInRight animated" data-animation="fadeInRight" data-delay="1s" style="animation-delay: 1s;">
                          
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    
    <!-- Feature Start -->
<div class="container-fluid feature py-5">
  <div class="container py-5">
    <div class="row g-4">
      <!-- Card 1 -->
      <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.2s">
        <div class="feature-item">
          <img src="img/feature-1.jpg" class="img-fluid rounded w-100" alt="Movie-Themed Rides">
          <div class="feature-content p-4">
            <div class="feature-content-inner">
              <h4 class="text-white">Movie-Themed Rides</h4>
              <p class="text-white">Feel the rush on attractions inspired by your favorite films and characters.</p>
              <a class="btn btn-primary rounded-pill py-2 px-4"
                 data-bs-toggle="modal"
                 data-bs-target="#featRideModal">
                Read More <i class="fa fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.4s">
        <div class="feature-item">
          <img src="img/feature-2.jpg" class="img-fluid rounded w-100" alt="Spectacular Shows">
          <div class="feature-content p-4">
            <div class="feature-content-inner">
              <h4 class="text-white">Spectacular Shows</h4>
              <p class="text-white">Stunt shows, parades, and live performances bring stories to life.</p>
              <a class="btn btn-primary rounded-pill py-2 px-4"
                 data-bs-toggle="modal"
                 data-bs-target="#featShowModal">
                Read More <i class="fa fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.6s">
        <div class="feature-item">
          <img src="img/feature-3.jpg" class="img-fluid rounded w-100" alt="Studio Experiences">
          <div class="feature-content p-4">
            <div class="feature-content-inner">
              <h4 class="text-white">Studio Experiences</h4>
              <p class="text-white">Behind-the-scenes touches, immersive lands, and photo-worthy sets.</p>
              <a class="btn btn-primary rounded-pill py-2 px-4"
                 data-bs-toggle="modal"
                 data-bs-target="#featStudioModal">
                Read More <i class="fa fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>


    <!-- Feature End -->

    <!-- About Start -->
    <div class="container-fluid about pb-5">
        <div class="container pb-5">
            <div class="row g-5">
                <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div>
                        <h4 class="text-primary">About <?= BRAND_NAME ?></h4>
                        <h1 class="display-5 mb-4">Blockbuster Fun For The Whole Family</h1>
                        <p class="mb-5">From high-octane coasters to immersive lands and family attractions, <?= BRAND_NAME ?> brings movie magic to life with world-class entertainment, dining and experiences.</p>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="me-3"><i class="fas fa-theater-masks fa-3x text-primary"></i></div>
                                    <div>
                                        <h4>Live Entertainment</h4>
                                        <p>Parades, stunt shows, meet & greets and more throughout the day.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="me-3"><i class="fas fa-ticket-alt fa-3x text-primary"></i></div>
                                    <div>
                                        <h4>Attractions For Everyone</h4>
                                        <p>Thrill rides for adventurers and gentle fun for little ones.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="me-3"><i class="fas fa-hamburger fa-3x text-primary"></i></div>
                                    <div>
                                        <h4>Dining & Snacks</h4>
                                        <p>From quick bites to themed restaurants across the park.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="me-3"><i class="fas fa-shield-alt fa-3x text-primary"></i></div>
                                    <div>
                                        <h4>Safe & Clean</h4>
                                        <p>Friendly staff, lockers, and guest services for a smooth visit.</p>
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

    <!-- Service Start -->
    <div class="container-fluid service py-5">
        <div class="container service-section py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
                <h4 class="text-primary">Our Service</h4>
                <h1 class="display-5 text-white mb-4">Explore <?= BRAND_NAME ?> Services</h1>
                <p class="mb-0 text-white">Plan your perfect day with convenient hours, dining options, lockers, rentals, and more guest services across the park.</p>
            </div>
            <div class="row g-4">
                <div class="col-0 col-md-1 col-lg-2 col-xl-2"></div>
                <div class="col-md-10 col-lg-8 col-xl-8 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="service-days p-4">
                        <div class="py-2 border-bottom border-top d-flex align-items-center justify-content-between flex-wrap"><h4 class="mb-0 pb-2 pb-sm-0">Monday - Friday</h4> <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i>11:00 AM - 16:00 PM</p></div>
                        <div class="py-2 border-bottom d-flex align-items-center justify-content-between flex-shrink-1 flex-wrap"><h4 class="mb-0 pb-2 pb-sm-0">Saturday - Sunday</h4> <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i>09:00 AM - 17:00 PM</p></div>
                        <div class="py-2 border-bottom d-flex align-items-center justify-content-between flex-shrink-1 flex-wrap"><h4 class="mb-0">Holiday</h4> <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i>09:00 AM - 17:00 PM</p></div>
                    </div>
                </div>
                <div class="col-0 col-md-1 col-lg-2 col-xl-2"></div>

                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="service-item p-4">
                        <div class="service-content">
                            <div class="mb-4">
                                <i class="fas fa-home fa-4x"></i>
                            </div>
                            <a href="#" class="h4 d-inline-block mb-3">Private Lounges</a>
                            <p class="mb-0">Reserve cozy spaces to relax between rides and shows.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="service-item p-4">
                        <div class="service-content">
                            <div class="mb-4">
                                <i class="fas fa-utensils fa-4x"></i>
                            </div>
                            <a href="#" class="h4 d-inline-block mb-3">Dining & Snacks</a>
                            <p class="mb-0">Tasty bites and themed restaurants throughout the park.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.6s">
                    <div class="service-item p-4">
                        <div class="service-content">
                            <div class="mb-4">
                                <i class="fas fa-door-closed fa-4x"></i>
                            </div>
                            <a href="#" class="h4 d-inline-block mb-3">Lockers</a>
                            <p class="mb-0">Secure storage for your belongings near major attractions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.8s">
                    <div class="service-item p-4">
                        <div class="service-content">
                            <div class="mb-4">
                                <i class="fas fa-ticket-alt fa-4x"></i>
                            </div>
                            <a href="#" class="h4 d-inline-block mb-3">Express Access</a>
                            <p class="mb-0">Upgrade options to reduce wait times at select rides.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Service End -->

    

    <!-- Attractions Start 
    <div class="container-fluid attractions py-5">
        <div class="container attractions-section py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
                <h4 class="text-primary">Attractions</h4>
                <h1 class="display-5 text-white mb-4">Explore <?= BRAND_NAME ?> Attractions</h1>
                <p class="text-white mb-0">High-speed coasters, immersive lands, and family favorites—all inspired by hit movies and TV series.</p>
            </div>
            <div class="owl-carousel attractions-carousel wow fadeInUp" data-wow-delay="0.1s">
                <div class="attractions-item wow fadeInUp" data-wow-delay="0.2s">
                    <img src="img/attraction-1.jpg" class="img-fluid rounded w-100" alt="">
                    <a href="#" class="attractions-name">Blockbuster Coaster</a>
                </div>
                <div class="attractions-item wow fadeInUp" data-wow-delay="0.4s">
                    <img src="img/attraction-2.jpg" class="img-fluid rounded w-100" alt="">
                    <a href="#" class="attractions-name">Carousel</a>
                </div>
                <div class="attractions-item wow fadeInUp" data-wow-delay="0.6s">
                    <img src="img/attraction-3.jpg" class="img-fluid rounded w-100" alt="">
                    <a href="#" class="attractions-name">Arcade Zone</a>
                </div>
                <div class="attractions-item wow fadeInUp" data-wow-delay="0.8s">
                    <img src="img/attraction-4.jpg" class="img-fluid rounded w-100" alt="">
                    <a href="#" class="attractions-name">Sky Carousel</a>
                </div>
                <div class="attractions-item wow fadeInUp" data-wow-delay="1s">
                    <img src="img/attraction-2.jpg" class="img-fluid rounded w-100" alt="">
                    <a href="#" class="attractions-name">Family Carousel</a>
                </div>
            </div>
        </div>
    </div>
    Attractions End -->

    <!-- Gallery Start -->
    <div class="container-fluid gallery pb-5">
        <div class="container pb-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
                <h4 class="text-primary">Our Gallery</h4>
                <h1 class="display-5 mb-4">Captured Moments At <?= BRAND_NAME ?></h1>
                <p class="mb-0">Relive the fun with highlights from rides, shows, parades and special events.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="gallery-item">
                        <img src="img/gallery-1.jpg" class="img-fluid rounded w-100 h-100" alt="">
                        <div class="search-icon">
                            <a href="img/gallery-1.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-1"><i class="fas fa-search-plus"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="gallery-item">
                        <img src="img/gallery-2.jpg" class="img-fluid rounded w-100 h-100" alt="">
                        <div class="search-icon">
                            <a href="img/gallery-2.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-2"><i class="fas fa-search-plus"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 wow fadeInUp" data-wow-delay="0.6s">
                    <div class="gallery-item">
                        <img src="img/gallery-3.jpg" class="img-fluid rounded w-100 h-100" alt="">
                        <div class="search-icon">
                            <a href="img/gallery-3.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-3"><i class="fas fa-search-plus"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="gallery-item">
                        <img src="img/gallery-4.jpg" class="img-fluid rounded w-100 h-100" alt="">
                        <div class="search-icon">
                            <a href="img/gallery-4.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-4"><i class="fas fa-search-plus"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="gallery-item">
                        <img src="img/gallery-5.jpg" class="img-fluid rounded w-100 h-100" alt="">
                        <div class="search-icon">
                            <a href="img/gallery-5.jpg" class="btn btn-light btn-lg-square rounded-circle" data-lightbox="Gallery-5"><i class="fas fa-search-plus"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 wow fadeInUp" data-wow-delay="0.6s">
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

  <!-- Blog Start -->
<style>
  /* Equal-height blog cards */
  .blog .blog-item{display:flex;flex-direction:column;height:100%;border-radius:10px;overflow:hidden;}
  .blog .blog-img{height:230px;overflow:hidden;}
  .blog .blog-img img{width:100%;height:100%;object-fit:cover;display:block;}
  .blog .blog-content{display:flex;flex-direction:column;flex:1 1 auto;background:var(--bs-light);}
  .blog .blog-content .btn{margin-top:auto;align-self:flex-start;}
</style>

<div class="container-fluid blog pb-5">
  <div class="container pb-5">
    <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
      <h4 class="text-primary">Our Blog</h4>
      <h1 class="display-5 mb-4">Latest News & Articles</h1>
      <p class="mb-0">Tips, event highlights, and planning guides for your next trip to <?= BRAND_NAME ?>.</p>
    </div>

    <div class="row g-4">
  <!-- Card 1: Rides -->
  <div class="col-lg-4 wow fadeInUp h-100" data-wow-delay="0.2s">
    <div class="blog-item h-100">
      <div class="blog-img">
        <a href="#" data-bs-toggle="modal" data-bs-target="#blogRideModal">
          <img src="img/blog-1.jpg" alt="Top rides at <?= BRAND_NAME ?>">
        </a>
        <div class="blog-category py-2 px-4">Vacation</div>
        <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
      </div>
      <div class="blog-content p-4">
        <a href="#" class="h4 d-inline-block mb-4" data-bs-toggle="modal" data-bs-target="#blogRideModal">
          Top 5 Must-Ride Attractions at <?= BRAND_NAME ?>
        </a>
        <p class="mb-4">From roller coasters to 3D simulators, here’s your guide to the rides you can’t miss…</p>
        <a href="#" class="btn btn-primary rounded-pill py-2 px-4 mt-auto"
           data-bs-toggle="modal" data-bs-target="#blogRideModal">
          Read More <i class="fas fa-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Card 2: Shows -->
  <div class="col-lg-4 wow fadeInUp h-100" data-wow-delay="0.4s">
    <div class="blog-item h-100">
      <div class="blog-img">
        <a href="#" data-bs-toggle="modal" data-bs-target="#blogShowModal">
          <img src="img/blog-2.jpg" alt="Shows at <?= BRAND_NAME ?>">
        </a>
        <div class="blog-category py-2 px-4">Insight</div>
        <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
      </div>
      <div class="blog-content p-4">
        <a href="#" class="h4 d-inline-block mb-4" data-bs-toggle="modal" data-bs-target="#blogShowModal">
          How To Plan Your Day for Shows & Parades
        </a>
        <p class="mb-4">Make the most of stunt shows, musical performances and the evening parade…</p>
        <a href="#" class="btn btn-primary rounded-pill py-2 px-4 mt-auto"
           data-bs-toggle="modal" data-bs-target="#blogShowModal">
          Read More <i class="fas fa-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Card 3: Best Time -->
  <div class="col-lg-4 wow fadeInUp h-100" data-wow-delay="0.6s">
    <div class="blog-item h-100">
      <div class="blog-img">
        <a href="#" data-bs-toggle="modal" data-bs-target="#blogTipsModal">
          <img src="img/blog-3.jpg" alt="Best time to visit <?= BRAND_NAME ?>">
        </a>
        <div class="blog-category py-2 px-4">Insight</div>
        <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
      </div>
      <div class="blog-content p-4">
        <a href="#" class="h4 d-inline-block mb-4" data-bs-toggle="modal" data-bs-target="#blogTipsModal">
          Family Guide: Best Times To Visit <?= BRAND_NAME ?>
        </a>
        <p class="mb-4">Crowd patterns, weather tips, and seasonal events to help you pick your date…</p>
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
                        <p class="mb-2">Experience movie magic with thrilling rides, spectacular shows, and immersive lands for all ages.</p>
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

    <!-- Cart Helper (exactly the same as in package.php) -->
    <script>
    // Adds a package to a simple localStorage cart
    function addToCart(name, priceOrLabel, meta = {}) {
        let price = null;
        let label = '';
        if (typeof priceOrLabel === 'number') {
            price = priceOrLabel;
            label = '$' + price.toFixed(2);
        } else if (typeof priceOrLabel === 'string') {
            label = priceOrLabel;
        } else {
            label = 'TBD';
        }

        const item = {
            name: String(name || 'Package'),
            price: price,   // numeric when provided
            label: label,   // user-friendly price label
            meta: meta || {},
            qty: 1,
            addedAt: new Date().toISOString()
        };

        const cart = JSON.parse(localStorage.getItem('cart') || '[]');

        // Merge identical items (same name + label + meta) by increasing qty
        const sameIndex = cart.findIndex(i =>
            i.name === item.name &&
            i.label === item.label &&
            JSON.stringify(i.meta) === JSON.stringify(item.meta)
        );

        if (sameIndex > -1) {
            cart[sameIndex].qty += 1;
        } else {
            cart.push(item);
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        alert(item.name + ' added to cart! ' + (item.price !== null ? '(' + item.label + ')' : '(Pricing will be confirmed)'));
    }
    </script>
    <!-- ===================== Feature Detail Modals (INDEX) ===================== -->

<!-- Movie-Themed Rides -->
<div class="modal fade" id="featRideModal" tabindex="-1" aria-labelledby="featRideLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="featRideLabel">Movie-Themed Rides</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-6">
            <img src="img/feature-1.jpg" class="img-fluid rounded" alt="Movie-Themed Rides">
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              From high-speed coasters to 3D/4D simulators, dive into rides
              inspired by blockbuster films and iconic characters.
            </p>
            <ul class="mb-4">
              <li>Headline coaster with cinematic theming</li>
              <li>Family motion simulator (min height 102&nbsp;cm)</li>
              <li>Express access available on select rides</li>
              <li>On-ride photos & nearby locker services</li>
            </ul>
            <div class="alert alert-info">Tip: Arrive early or upgrade to Express to minimize wait times.</div>
          </div>
        </div>
      </div>
     
    </div>
  </div>
</div>

<!-- Spectacular Shows -->
<div class="modal fade" id="featShowModal" tabindex="-1" aria-labelledby="featShowLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="featShowLabel">Spectacular Shows</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-6">
            <img src="img/feature-2.jpg" class="img-fluid rounded" alt="Spectacular Shows">
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              Stunts, parades and live entertainment that bring stories to life,
              with multiple showtimes daily.
            </p>
            <ul class="mb-4">
              <li>High-energy stunt spectacular (20–25 mins)</li>
              <li>Character parade with photo moments</li>
              <li>Indoor theater shows (air-conditioned)</li>
              <li>Accessible seating available</li>
            </ul>
            <div class="alert alert-info">Tip: Check today’s schedule in the app for the next showtime.</div>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</div>

<!-- Studio Experiences -->
<div class="modal fade" id="featStudioModal" tabindex="-1" aria-labelledby="featStudioLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="featStudioLabel">Studio Experiences</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-6">
            <img src="img/feature-3.jpg" class="img-fluid rounded" alt="Studio Experiences">
          </div>
          <div class="col-lg-6">
            <p class="mb-3">
              Go behind the scenes with immersive sets, props and photo-worthy
              spaces that reveal how movie magic is made.
            </p>
            <ul class="mb-4">
              <li>Backlot-style walkthroughs & set pieces</li>
              <li>Interactive prop displays and effects</li>
              <li>Guided experiences at select hours</li>
              <li>Plenty of photo opportunities</li>
            </ul>
            <div class="alert alert-info">Tip: Weekday afternoons are usually less crowded—perfect for photos.</div>
          </div>
        </div>
      </div>
     
    </div>
  </div>
</div>
<!-- =================== /Feature Detail Modals (INDEX) =================== -->



<!-- =================== Blog Detail Modals (INDEX) =================== -->

<!-- Blog Modal 1: Rides -->
<div class="modal fade" id="blogRideModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <!-- ===== Modal Header ===== -->
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Top 5 Must-Ride Attractions at <?= BRAND_NAME ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- ===== Modal Body (Two Columns) ===== -->
      <div class="modal-body">
        <div class="row g-4">
          <!-- Left column: Responsive 16:9 image -->
          <div class="col-lg-6">
            <div class="ratio ratio-16x9 rounded overflow-hidden">
              <img src="img/blog-1.jpg" class="w-100 h-100" style="object-fit:cover;object-position:center" alt="Rides">
            </div>
          </div>

          <!-- Right column: Text content with highlights -->
          <div class="col-lg-6">
            <p class="mb-3">
              Our rides range from adrenaline-pumping roller coasters to family-friendly simulators. Highlights include:
            </p>
            <ul class="mb-4">
              <li>The headline coaster with cinematic theming</li>
              <li>3D/4D simulator rides inspired by blockbuster movies</li>
              <li>Family adventure rides with minimum height 102&nbsp;cm</li>
              <li>Express upgrades to save waiting time</li>
            </ul>
            <!-- Tip box for better UX -->
            <div class="alert alert-info mb-0">
              Tip: Arrive early or upgrade to Express to minimize wait times.
            </div>
          </div>
        </div>
      </div>

      <!-- ===== Modal Footer ===== -->
      <div class="modal-footer border-0">
        <button class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
        <a href="package.php" class="btn btn-primary rounded-pill">See Ticket Packages</a>
      </div>
    </div>
  </div>
</div>

<!-- Blog Modal 2: Shows -->
<div class="modal fade" id="blogShowModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">How To Plan Your Day for Shows & Parades</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <div class="row g-4">
          <!-- Left: Image -->
          <div class="col-lg-6">
            <div class="ratio ratio-16x9 rounded overflow-hidden">
              <img src="img/blog-2.jpg" class="w-100 h-100" style="object-fit:cover;object-position:center" alt="Shows">
            </div>
          </div>

          <!-- Right: Text -->
          <div class="col-lg-6">
            <p class="mb-3">With multiple shows across the park, timing is everything:</p>
            <ul class="mb-4">
              <li>Action stunt spectacular (20–25 mins)</li>
              <li>Character parade with photo moments</li>
              <li>Indoor theater shows (air-conditioned)</li>
              <li>Night parade with fireworks</li>
            </ul>
            <div class="alert alert-info mb-0">
              Tip: Check today’s schedule in the app for the next showtime.
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0">
        <button class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
        <a href="package.php" class="btn btn-primary rounded-pill">Plan Your Trip</a>
      </div>
    </div>
  </div>
</div>

<!-- Blog Modal 3: Best Time -->
<div class="modal fade" id="blogTipsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Family Guide: Best Times To Visit <?= BRAND_NAME ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <div class="row g-4">
          <!-- Left: Image -->
          <div class="col-lg-6">
            <div class="ratio ratio-16x9 rounded overflow-hidden">
              <img src="img/blog-3.jpg" class="w-100 h-100" style="object-fit:cover;object-position:center" alt="Tips">
            </div>
          </div>

          <!-- Right: Text -->
          <div class="col-lg-6">
            <p class="mb-3">When you visit can make or break your experience. Here are tips:</p>
            <ul class="mb-4">
              <li><b>Weekdays</b>: Less crowded, easier to explore</li>
              <li><b>School holidays</b>: More events, but heavier crowds</li>
              <li><b>Evenings</b>: Cooler weather, perfect for parades</li>
              <li><b>Seasonal events</b>: Halloween Horror Nights & Christmas Lights</li>
            </ul>
            <div class="alert alert-info mb-0">
              Tip: Evening visits are cooler and perfect for enjoying parades.
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0">
        <button class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
        <a href="package.php" class="btn btn-primary rounded-pill">Book Now</a>
      </div>
    </div>
  </div>
</div>
<!-- =================== /Blog Detail Modals (INDEX) =================== -->



</body>
</html>
    