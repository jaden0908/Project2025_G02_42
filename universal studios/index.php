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
    <link href="css/style.css" rel="stylesheet">
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

        <?php if (empty($_SESSION['user'])): ?>
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
                            <div class="ticket-form p-5">
                                <h2 class="text-dark text-uppercase mb-4">book your ticket</h2>
                                <form>
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <input type="text" class="form-control border-0 py-2" id="name" placeholder="Your Name">
                                        </div>
                                        <div class="col-12 col-xl-6">
                                            <input type="email" class="form-control border-0 py-2" id="email" placeholder="Your Email">
                                        </div>
                                        <div class="col-12 col-xl-6">
                                            <input type="phone" class="form-control border-0 py-2" id="phone" placeholder="Phone">
                                        </div>
                                        <div class="col-12">
                                            <select class="form-select border-0 py-2" aria-label="Default select example">
                                                <option selected>Select Packages</option>
                                                <option value="1">Family Packages</option>
                                                <option value="2">Basic Packages</option>
                                                <option value="3">Premium Packages</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <input class="form-control border-0 py-2" type="date">
                                        </div>
                                        <div class="col-12">
                                            <input type="number" class="form-control border-0 py-2" id="number" placeholder="Guest">
                                        </div>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary w-100 py-2 px-5">Book Now</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
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
                            <div class="ticket-form p-5">
                                <h2 class="text-dark text-uppercase mb-4">book your ticket</h2>
                                <form>
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <input type="text" class="form-control border-0 py-2" id="name2" placeholder="Your Name">
                                        </div>
                                        <div class="col-12 col-xl-6">
                                            <input type="email" class="form-control border-0 py-2" id="email2" placeholder="Your Email">
                                        </div>
                                        <div class="col-12 col-xl-6">
                                            <input type="phone" class="form-control border-0 py-2" id="phone2" placeholder="Phone">
                                        </div>
                                        <div class="col-12">
                                            <select class="form-select border-0 py-2" aria-label="Default select example">
                                                <option selected>Select Packages</option>
                                                <option value="1">Family Packages</option>
                                                <option value="2">Basic Packages</option>
                                                <option value="3">Premium Packages</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <input class="form-control border-0 py-2" type="date">
                                        </div>
                                        <div class="col-12">
                                            <input type="number" class="form-control border-0 py-2" id="number2" placeholder="Guest">
                                        </div>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary w-100 py-2 px-5">Book Now</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
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
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="feature-item">
                        <img src="img/feature-1.jpg" class="img-fluid rounded w-100" alt="Image">
                        <div class="feature-content p-4">
                            <div class="feature-content-inner">
                                <h4 class="text-white">Movie-Themed Rides</h4>
                                <p class="text-white">Feel the rush on attractions inspired by your favorite films and characters.</p>
                                <a href="#" class="btn btn-primary rounded-pill py-2 px-4">Read More <i class="fa fa-arrow-right ms-1"></i></a>
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
                                <p class="text-white">Stunt shows, parades, and live performances bring stories to life.</p>
                                <a href="#" class="btn btn-primary rounded-pill py-2 px-4">Read More <i class="fa fa-arrow-right ms-1"></i></a>
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
                                <p class="text-white">Behind-the-scenes touches, immersive lands, and photo-worthy sets.</p>
                                <a href="#" class="btn btn-primary rounded-pill py-2 px-4">Read More <i class="fa fa-arrow-right ms-1"></i></a>
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

    <!-- Ticket Packages (synchronized with package.php) Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">

            <div class="row g-5 align-items-center">
                <!-- Intro / Selling points -->
                <div class="col-lg-12 col-xl-4 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="packages-item h-100">
                        <h4 class="text-primary">Ticket Packages</h4>
                        <h1 class="display-5 mb-4">Choose The Best Packages For Your Family</h1>
                        <p class="mb-4">Save with bundles and seasonal offers. Plan ahead to get the most out of your day at <?= BRAND_NAME ?>.</p>
                        <p><i class="fa fa-check text-primary me-2"></i>Great value for families</p>
                        <p><i class="fa fa-check text-primary me-2"></i>Express upgrade options</p>
                        <p><i class="fa fa-check text-primary me-2"></i>Access to special events</p>
                        <p class="mb-4"><i class="fa fa-check text-primary me-2"></i>Win up to 3 free day tickets</p>
                        <a href="package.php" class="btn btn-primary rounded-pill py-3 px-5"> See All Packages</a>
                    </div>
                </div>

                <!-- Package cards (same prices and buttons as package.php) -->
                <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="pricing-item bg-dark rounded text-center p-5 h-100 d-flex flex-column">
                        <div class="pb-4 border-bottom">
                            <h2 class="mb-2 text-primary">Hotel + Park Tickets</h2>
                            <p class="mb-2">Bundle an on-site hotel stay with park admission.</p>
                            <h5 class="mb-3 text-white">Save up to $200 • Stay longer, save more</h5>
                            <h2 class="mb-0 text-primary">$219 <span class="text-white-50 fs-5 fw-normal">/person (from)</span></h2>
                        </div>
                        <div class="py-4 text-start text-white flex-grow-1">
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Choose 1–3 parks</p>
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Early Park Admission</p>
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>On-site transportation</p>
                            <p class="mb-0"><i class="fa fa-check text-primary me-2"></i>Park-to-Park upgrades</p>
                        </div>
                        <button
                            onclick="addToCart('Hotel + Park Tickets', 219.00, {unit:'per person', note:'From price', currency:'USD'})"
                            class="btn btn-light rounded-pill py-3 px-5 mt-auto">
                            Add to Cart
                        </button>
                    </div>
                </div>

                <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.6s">
                    <div class="pricing-item bg-primary rounded text-center p-5 h-100 d-flex flex-column">
                        <div class="pb-4 border-bottom">
                            <h2 class="text-dark mb-2">Dining Card Vacation Package</h2>
                            <p class="text-dark mb-2">Stay 4–5 nights at official hotels to receive Dining Card credits.</p>
                            <h5 class="text-dark mb-3">Valid on select travel dates</h5>
                            <h2 class="text-dark mb-0">$899 <span class="text-white fs-5 fw-normal">/package (from)</span></h2>
                        </div>
                        <div class="py-4 text-start text-white flex-grow-1">
                            <p class="mb-3"><i class="fa fa-check text-dark me-2"></i>$300–$1,000 Dining Card</p>
                            <p class="mb-3"><i class="fa fa-check text-dark me-2"></i>Early Park Admission</p>
                            <p class="mb-3"><i class="fa fa-check text-dark me-2"></i>Resort transportation</p>
                            <p class="mb-0"><i class="fa fa-check text-dark me-2"></i>Merchandise delivery</p>
                        </div>
                        <button
                            onclick="addToCart('Dining Card Package', 899.00, {unit:'per package', note:'From price; dining credit up to $1,000', currency:'USD'})"
                            class="btn btn-dark rounded-pill py-3 px-5 mt-auto">
                            Add to Cart
                        </button>
                    </div>
                </div>

                <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.8s">
                    <div class="pricing-item bg-light rounded text-center p-5 h-100 d-flex flex-column">
                        <div class="pb-4 border-bottom">
                            <h2 class="mb-2">Costco Travel Bundle</h2>
                            <p class="mb-2">Theme park tickets + Early Park Admission + Costco digital shop card.</p>
                            <h5 class="mb-3">Options include seasonal events & water parks</h5>
                            <h2 class="mb-0">$649 <span class="text-body fs-5 fw-normal">/person (from)</span></h2>
                        </div>
                        <div class="py-4 text-start flex-grow-1">
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Hotel & multi-park tickets</p>
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Early Park Admission</p>
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Digital shop card</p>
                            <p class="mb-0 small text-muted">*Availability varies by destination and dates.</p>
                        </div>
                        <button
                            onclick="addToCart('Costco Travel Bundle', 649.00, {unit:'per person', note:'From price; digital shop card included', currency:'USD'})"
                            class="btn btn-primary rounded-pill py-3 px-5 mt-auto">
                            Add to Cart
                        </button>
                    </div>
                </div>

                <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="1.0s">
                    <div class="pricing-item bg-dark rounded text-center p-5 h-100 d-flex flex-column">
                        <div class="pb-4 border-bottom">
                            <h2 class="text-primary mb-2">Southwest Vacations Bundle</h2>
                            <p class="text-white mb-2">Flight + Hotel + Car + Multi-park tickets.</p>
                            <h5 class="text-white mb-3">Save up to 40% (select promos)</h5>
                            <h2 class="mb-0 text-primary">$599 <span class="text-white-50 fs-5 fw-normal">/person (from)</span></h2>
                        </div>
                        <div class="py-4 text-start text-white flex-grow-1">
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Bundle & save</p>
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Options to include select parks*</p>
                            <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Flexible length of stay</p>
                            <p class="mb-0 small">*Access depends on tickets, dates, and availability.</p>
                        </div>
                        <button
                            onclick="addToCart('Southwest Vacations Bundle', 599.00, {unit:'per person', note:'From price; up to 40% off promos', currency:'USD'})"
                            class="btn btn-light rounded-pill py-3 px-5 mt-auto">
                            Add to Cart
                        </button>
                    </div>
                </div>

            </div>

            <!-- Policy note (same tone as package.php) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        Prices shown are starting rates in USD and may vary by date, hotel tier, length of stay, flight origin, and availability. Final pricing will be confirmed at checkout.
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Ticket Packages End -->

    <!-- Attractions Start -->
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
    <!-- Attractions End -->

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
    <div class="container-fluid blog pb-5">
        <div class="container pb-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
                <h4 class="text-primary">Our Blog</h4>
                <h1 class="display-5 mb-4">Latest News & Articles</h1>
                <p class="mb-0">Tips, event highlights, and planning guides for your next trip to <?= BRAND_NAME ?>.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="blog-item">
                        <div class="blog-img">
                            <a href="#">
                                <img src="img/blog-2.jpg" class="img-fluid w-100 rounded-top" alt="Image">
                            </a>
                            <div class="blog-category py-2 px-4">Vacation</div>
                            <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
                        </div>
                        <div class="blog-content p-4">
                            <a href="#" class="h4 d-inline-block mb-4">How To Maximize A One-Day Visit</a>
                            <p class="mb-4">Plan your route, use Express upgrades, and catch the must-see shows without rushing….</p>
                            <a href="#" class="btn btn-primary rounded-pill py-2 px-4">Read More <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="blog-item">
                        <div class="blog-img">
                            <a href="#">
                                <img src="img/blog-3.jpg" class="img-fluid w-100 rounded-top" alt="Image">
                            </a>
                            <div class="blog-category py-2 px-4">Insight</div>
                            <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
                        </div>
                        <div class="blog-content p-4">
                            <a href="#" class="h4 d-inline-block mb-4">Top 5 Family-Friendly Attractions</a>
                            <p class="mb-4">From gentle rides to character meet-ups, here’s where families love to spend time….</p>
                            <a href="#" class="btn btn-primary rounded-pill py-2 px-4">Read More <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.6s">
                    <div class="blog-item">
                        <div class="blog-img">
                            <a href="#">
                                <img src="img/blog-1.jpg" class="img-fluid w-100 rounded-top" alt="Image">
                            </a>
                            <div class="blog-category py-2 px-4">Insight</div>
                            <div class="blog-date"><i class="fas fa-clock me-2"></i>August 19, 2025</div>
                        </div>
                        <div class="blog-content p-4">
                            <a href="#" class="h4 d-inline-block mb-4">Best Times To Visit <?= BRAND_NAME ?></a>
                            <p class="mb-4">Crowd patterns, weather tips, and seasonal events to help you pick your date….</p>
                            <a href="#" class="btn btn-primary rounded-pill py-2 px-4">Read More <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog End -->

    <!-- Team Start -->
    <div class="container-fluid team pb-5">
        <div class="container pb-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
                <h4 class="text-primary">Meet Our Team</h4>
                <h1 class="display-5 mb-4">Our <?= BRAND_NAME ?> Team</h1>
                <p class="mb-0">The people who bring movie magic to life each and every day.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="team-item p-4">
                        <div class="team-content">
                            <div class="d-flex justify-content-between border-bottom pb-4">
                                <div class="text-start">
                                    <h4 class="mb-0">David James</h4>
                                    <p class="mb-0">Operations Lead</p>
                                </div>
                                <div>
                                    <img src="img/team-1.jpg" class="img-fluid rounded" style="width: 100px; height: 100px;" alt="">
                                </div>
                            </div>
                            <div class="team-icon rounded-pill my-4 p-3">
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-0" href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                            <p class="text-center mb-0">Focused on guest experience and operational excellence across the park.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="team-item p-4">
                        <div class="team-content">
                            <div class="d-flex justify-content-between border-bottom pb-4">
                                <div class="text-start">
                                    <h4 class="mb-0">William John</h4>
                                    <p class="mb-0">Show Director</p>
                                </div>
                                <div>
                                    <img src="img/team-2.jpg" class="img-fluid rounded" style="width: 100px; height: 100px;" alt="">
                                </div>
                            </div>
                            <div class="team-icon rounded-pill my-4 p-3">
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-0" href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                            <p class="text-center mb-0">Leads live entertainment with stunts, music, and crowd-pleasing spectacles.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.6s">
                    <div class="team-item p-4">
                        <div class="team-content">
                            <div class="d-flex justify-content-between border-bottom pb-4">
                                <div class="text-start">
                                    <h4 class="mb-0">Michael John</h4>
                                    <p class="mb-0">Attractions Manager</p>
                                </div>
                                <div>
                                    <img src="img/team-3.jpg" class="img-fluid rounded" style="width: 100px; height: 100px;" alt="">
                                </div>
                            </div>
                            <div class="team-icon rounded-pill my-4 p-3">
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-3" href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a class="btn btn-primary btn-sm-square rounded-circle me-0" href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                            <p class="text-center mb-0">Ensures safety and thrills on our headline rides and family favorites.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->

    <!-- Testimonial Start -->
    <div class="container-fluid testimonial py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
                <h4 class="text-primary">Testimonials</h4>
                <h1 class="display-5 text-white mb-4">What Our Guests Say</h1>
                <p class="text-white mb-0">Real stories from visitors who experienced movie magic at <?= BRAND_NAME ?>.</p>
            </div>
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.2s">
                <div class="testimonial-item p-4">
                    <p class="text-white fs-4 mb-4">Amazing rides and shows! The atmosphere feels like stepping into a blockbuster.</p>
                    <div class="testimonial-inner">
                        <div class="testimonial-img">
                            <img src="img/testimonial-1.jpg" class="img-fluid" alt="Image">
                            <div class="testimonial-quote btn-lg-square rounded-circle"><i class="fa fa-quote-right fa-2x"></i></div>
                        </div>
                        <div class="ms-4">
                            <h4>Person Name</h4>
                            <p class="text-start text-white">Visitor</p>
                            <div class="d-flex text-primary"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star text-white"></i></div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-item p-4">
                    <p class="text-white fs-4 mb-4">Perfect for families—lots of kid-friendly fun and great food choices.</p>
                    <div class="testimonial-inner">
                        <div class="testimonial-img">
                            <img src="img/testimonial-2.jpg" class="img-fluid" alt="Image">
                            <div class="testimonial-quote btn-lg-square rounded-circle"><i class="fa fa-quote-right fa-2x"></i></div>
                        </div>
                        <div class="ms-4">
                            <h4>Person Name</h4>
                            <p class="text-start text-white">Visitor</p>
                            <div class="d-flex text-primary"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star text-white"></i></div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-item p-4">
                    <p class="text-white fs-4 mb-4">The stunt show blew our minds—don’t miss it!</p>
                    <div class="testimonial-inner">
                        <div class="testimonial-img">
                            <img src="img/testimonial-3.jpg" class="img-fluid" alt="Image">
                            <div class="testimonial-quote btn-lg-square rounded-circle"><i class="fa fa-quote-right fa-2x"></i></div>
                        </div>
                        <div class="ms-4">
                            <h4>Person Name</h4>
                            <p class="text-start text-white">Visitor</p>
                            <div class="d-flex text-primary"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star text-white"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->

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
</body>
</html>
