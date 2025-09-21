<?php
/**
 * package.php — Universal Studios Packages (with prices)
 * Generic branding: "Universal Studios" (no city).
 * Four packages with visible "From $" pricing and numeric price passed to addToCart().
 */
session_start();
define('BRAND_NAME', 'Universal Studios');
?>
<?php
require __DIR__ . '/database.php'; // connect to database

// helper function for HTML escaping
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// fetch active packages from database
$packages = [];
$stmt = $conn->prepare(
  "SELECT id,title,short_desc,price_usd,status,image_path
   FROM packages
   WHERE status='active'
   ORDER BY created_at DESC"
);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) { $packages[] = $row; }
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= BRAND_NAME ?> - Ticket & Vacation Packages</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <!-- SEO (generic, no city) -->
    <meta name="keywords" content="Universal Studios packages, hotel and tickets, dining package, Costco Travel, Southwest Vacations, theme park, express pass">
    <meta name="description" content="Explore Universal Studios vacation packages: Hotel + Park Tickets, Dining Card offers, Costco Travel bundles, and Southwest Vacations (flight+hotel+car). Early Park Admission and seasonal perks available.">

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

    <script src="https://kit.fontawesome.com/351048854e.js" crossorigin="anonymous"></script>
</head>

<body>
<!-- ===================== Spinner Start ===================== -->
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<!-- ===================== Spinner End ======================= -->

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


<!-- ===================== Header Start ===================== -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Universal Studios Packages</h4>
        <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pages</a></li>
            <li class="breadcrumb-item active text-primary">Packages</li>
        </ol>
    </div>
</div>
<!-- ===================== Header End ======================= -->

<!-- ===================== Ticket/Vacation Packages Start === -->
<div class="container-fluid py-5">
    <div class="container py-5">

        <div class="row g-5 align-items-stretch">
            <!-- Intro / Selling points -->
            <div class="col-lg-12 col-xl-3 wow fadeInUp" data-wow-delay="0.15s">
                <div class="packages-item h-100">
                    <h4 class="text-primary">Official Offers</h4>
                    <h1 class="display-6 mb-3">Build Your Perfect Trip</h1>
                    <p class="mb-4">
                        Mix-and-match hotel stays, park tickets, seasonal add-ons, and more. Enjoy perks like
                        <strong>Early Park Admission</strong> and bundle savings.
                    </p>
                    <p><i class="fa fa-check text-primary me-2"></i>Hotel + Tickets savings</p>
                    <p><i class="fa fa-check text-primary me-2"></i>Dining Card credits (select dates)</p>
                    <p><i class="fa fa-check text-primary me-2"></i>Third-party bundles & shop cards</p>
                    <p class="mb-4"><i class="fa fa-check text-primary me-2"></i>Flight + Hotel + Car options</p>
                    <div class="alert alert-warning small mb-4">
                        * Offers and inclusions vary by date, hotel tier, and availability.
                    </div>
                    <a href="#uor-packages" class="btn btn-primary rounded-pill py-3 px-5">See Packages</a>
                </div>
            </div>

         <!-- Packages Grid -->
<div id="uor-packages" class="col-lg-12 col-xl-9">
  <div class="row g-4">

    <?php if (empty($packages)): ?>
      <div class="col-12">
        <div class="alert alert-secondary mb-0">
          No active packages available right now.
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($packages as $i => $p): ?>
        <?php $dark = ($i % 2) === 0; // alternate background style ?>
        <div class="col-md-6 wow fadeInUp" data-wow-delay="<?= 0.2 + 0.05 * ($i % 6) ?>s">
          <div class="pricing-item <?= $dark ? 'bg-dark text-white' : 'bg-light' ?> rounded text-center p-5 h-100 d-flex flex-column">

            <!-- Title and short description -->
            <div class="pb-4 border-bottom">
              <h2 class="<?= $dark ? 'text-primary' : '' ?> mb-2"><?= e($p['title']) ?></h2>
              <?php if (!empty($p['short_desc'])): ?>
                <p class="<?= $dark ? 'text-white' : 'text-body' ?> mb-2"><?= e($p['short_desc']) ?></p>
              <?php endif; ?>
              <h2 class="mb-0 <?= $dark ? 'text-primary' : '' ?>">
                $<?= number_format((float)$p['price_usd'], 2) ?>
                <span class="<?= $dark ? 'text-white-50' : 'text-muted' ?> fs-5 fw-normal">/from</span>
              </h2>
            </div>

            <!-- Optional image -->
            <?php if (!empty($p['image_path'])): ?>
              <div class="py-3">
                <img src="<?= e($p['image_path']) ?>" alt="<?= e($p['title']) ?>" class="img-fluid rounded">
              </div>
            <?php endif; ?>

           

            <!-- Add to Cart button -->
            <button
              onclick="addToCart('<?= e($p['title']) ?>', <?= json_encode((float)$p['price_usd']) ?>, {unit:'from', currency:'USD', package_id: <?= (int)$p['id'] ?>})"
              class="btn <?= $dark ? 'btn-light' : 'btn-primary' ?> rounded-pill py-3 px-5 mt-auto">
              Add to Cart
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>

        </div>

        <!-- Policy note -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    Prices shown are starting rates in USD and may vary by date, hotel tier, length of stay, flight origin, and availability. Final pricing will be confirmed at checkout.
                </div>
            </div>
        </div>

    </div>
</div>
<!-- ===================== Ticket/Vacation Packages End ===== -->

<!-- ===================== Footer Start ===================== -->
<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-lg-6 col-xl-4">
                <div class="footer-item">
                    <a href="index.php" class="p-0">
                        <h4 class="text-white mb-4"><i class="fas fa-film text-primary me-3"></i><?= BRAND_NAME ?></h4>
                    </a>
                    <p class="mb-2">Stay on-site, play more, and enjoy bundled savings and perks.</p>
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
                    <a href="package.php"><i class="fas fa-angle-right me-2"></i> Packages</a>
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
                            <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i> 11:00 AM - 4:00 PM</p>
                        </div>
                        <div class="opening-clock flex-shrink-0">
                            <h6 class="text-white mb-0 me-auto">Saturday - Sunday:</h6>
                            <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i> 09:00 AM - 5:00 PM</p>
                        </div>
                        <div class="opening-clock flex-shrink-0">
                            <h6 class="text-white mb-0 me-auto">Holiday:</h6>
                            <p class="mb-0"><i class="fas fa-clock text-primary me-2"></i> 09:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-white mb-2">Payment Accepted</p>
                        <img src="img/payment.png" class="img-fluid" alt="Payment Methods">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ===================== Footer End ======================= -->

<!-- ===================== Copyright Start ================= -->
<div class="container-fluid copyright py-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6 text-center text-md-start mb-md-0">
                <span class="text-body"><a href="#" class="border-bottom text-white"><i class="fas fa-film text-light me-2"></i><?= BRAND_NAME ?></a>, All rights reserved.</span>
            </div>
            <div class="col-md-6 text-center text-md-end text-body">
                Designed By <a class="border-bottom text-white" href="https://htmlcodex.com">HTML Codex</a>
            </div>
        </div>
    </div>
</div>
<!-- ===================== Copyright End =================== -->

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

<!-- ===================== Cart Helper =====================
     - Accepts numeric price OR a string label like "Price varies by date"
     - Now we pass numeric "from" prices so cart shows actual numbers.
=========================================================== -->
<script>
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
        label: label,   // user-friendly label
        meta: meta || {},
        qty: 1,
        addedAt: new Date().toISOString()
    };

    const cart = JSON.parse(localStorage.getItem('cart') || '[]');

    // Merge identical items (same name+label+meta) by increasing qty
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
