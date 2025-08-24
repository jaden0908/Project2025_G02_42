<?php
/**
 * package.php — Universal Studios Packages (with prices)
 * Generic branding: "Universal Studios" (no city).
 * Four packages with visible "From $" pricing and numeric price passed to addToCart().
 */
session_start();
define('BRAND_NAME', 'Universal Studios');
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
        // Availability for "Give Feedback" in Pages dropdown
        // Only guests and customers should see it; staff/admin should NOT.
        $isGuest      = empty($_SESSION['user']);
        $sessionRole  = $isGuest ? '' : ($_SESSION['user']['role'] ?? '');
        $canFeedback  = $isGuest || $sessionRole === 'customer';
        ?>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav mx-auto py-0">
                <a href="index.php" class="nav-item nav-link">Home</a>
                <a href="about.php" class="nav-item nav-link">About</a>
                <a href="service.php" class="nav-item nav-link">Service</a>
                <a href="blog.php" class="nav-item nav-link">Blog</a>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle active" data-bs-toggle="dropdown">Pages</a>
                    <div class="dropdown-menu m-0">
                        <a href="feature.php" class="dropdown-item">Our Feature</a>
                        <a href="gallery.php" class="dropdown-item">Our Gallery</a>
                        <a href="attraction.php" class="dropdown-item">Attractions</a>
                        <a href="package.php" class="dropdown-item active">Ticket Packages</a>
                        <a href="team.php" class="dropdown-item">Our Team</a>
                        <a href="testimonial.php" class="dropdown-item">Testimonial</a>

                        <?php if ($canFeedback): ?>
                            <!-- Show only to guests and customers -->
                            <a href="feedback.php" class="dropdown-item">Give Feedback</a>
                        <?php endif; ?>

                        <a href="404.php" class="dropdown-item">404 Page</a>
                    </div>
                </div>

                <a href="contact.php" class="nav-item nav-link">Contact</a>

                <?php if (empty($_SESSION['user'])): ?>
                    <!-- Not logged in -->
                    <a href="signup.php" class="nav-item nav-link">Sign Up</a>
                    <a href="login.php" class="nav-item nav-link">Sign In</a>
                <?php else: ?>
                    <!-- Logged in dropdown -->
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
                            <a href="profile.php" class="dropdown-item">Profile</a>
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

                    <!-- (1) Hotel + Park Tickets -->
                    <div class="col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                        <div class="pricing-item bg-dark rounded text-center p-5 h-100 d-flex flex-column">
                            <div class="pb-4 border-bottom">
                                <h2 class="mb-2 text-primary">Hotel + Park Tickets</h2>
                                <p class="mb-2">Bundle an on-site hotel stay with park admission.</p>
                                <h5 class="mb-3 text-white">Save up to $200 • Stay longer, save more</h5>
                                <!-- Price -->
                                <h2 class="mb-0 text-primary">$219 <span class="text-white-50 fs-5 fw-normal">/person (from)</span></h2>
                            </div>
                            <div class="py-4 text-start text-white flex-grow-1">
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Choose 1–3 parks (select Universal Studios parks & water parks)</p>
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Early Park Admission (select hotels/dates)</p>
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>On-site transportation & easy park access</p>
                                <p class="mb-0"><i class="fa fa-check text-primary me-2"></i>Optional: Park-to-Park upgrades</p>
                            </div>
                            <button
                                onclick="addToCart('Hotel + Park Tickets', 219.00, {unit:'per person', note:'From price', currency:'USD'})"
                                class="btn btn-light rounded-pill py-3 px-5 mt-auto">
                                Add to Cart
                            </button>
                        </div>
                    </div>

                    <!-- (2) Dining Card Vacation Package -->
                    <div class="col-md-6 wow fadeInUp" data-wow-delay="0.25s">
                        <div class="pricing-item bg-primary rounded text-center p-5 h-100 d-flex flex-column">
                            <div class="pb-4 border-bottom">
                                <h2 class="text-dark mb-2">Dining Card Vacation Package</h2>
                                <p class="text-dark mb-2">Stay 4–5 nights at official hotels to receive Dining Card credits.</p>
                                <h5 class="text-dark mb-3">Valid on select travel dates</h5>
                                <!-- Price -->
                                <h2 class="text-dark mb-0">$899 <span class="text-white fs-5 fw-normal">/package (from)</span></h2>
                            </div>
                            <div class="py-4 text-start text-white flex-grow-1">
                                <p class="mb-3"><i class="fa fa-check text-dark me-2"></i>$300–$1,000 Dining Card (by hotel tier & nights)</p>
                                <p class="mb-3"><i class="fa fa-check text-dark me-2"></i>Early Park Admission</p>
                                <p class="mb-3"><i class="fa fa-check text-dark me-2"></i>Complimentary resort transportation</p>
                                <p class="mb-0"><i class="fa fa-check text-dark me-2"></i>Merchandise delivery to hotel</p>
                            </div>
                            <button
                                onclick="addToCart('Dining Card Package', 899.00, {unit:'per package', note:'From price; dining credit up to $1,000', currency:'USD'})"
                                class="btn btn-dark rounded-pill py-3 px-5 mt-auto">
                                Add to Cart
                            </button>
                        </div>
                    </div>

                    <!-- (3) Costco Travel Bundle -->
                    <div class="col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="pricing-item bg-light rounded text-center p-5 h-100 d-flex flex-column">
                            <div class="pb-4 border-bottom">
                                <h2 class="mb-2">Costco Travel Bundle</h2>
                                <p class="mb-2">Theme park tickets + Early Park Admission + Costco digital shop card.</p>
                                <h5 class="mb-3">Options include seasonal events & water parks</h5>
                                <!-- Price -->
                                <h2 class="mb-0">$649 <span class="text-body fs-5 fw-normal">/person (from)</span></h2>
                            </div>
                            <div class="py-4 text-start flex-grow-1">
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Hotel & multi-park ticket bundles</p>
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Early Park Admission (select dates)</p>
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Costco digital shop card (value varies)</p>
                                <p class="mb-0 small text-muted">
                                    *Availability varies by destination and travel dates.  
                                    **Seasonal events may require separate admission.
                                </p>
                            </div>
                            <button
                                onclick="addToCart('Costco Travel Bundle', 649.00, {unit:'per person', note:'From price; digital shop card included', currency:'USD'})"
                                class="btn btn-primary rounded-pill py-3 px-5 mt-auto">
                                Add to Cart
                            </button>
                        </div>
                    </div>

                    <!-- (4) Southwest Vacations Bundle -->
                    <div class="col-md-6 wow fadeInUp" data-wow-delay="0.35s">
                        <div class="pricing-item bg-dark rounded text-center p-5 h-100 d-flex flex-column">
                            <div class="pb-4 border-bottom">
                                <h2 class="text-primary mb-2">Southwest Vacations Bundle</h2>
                                <p class="text-white mb-2">Flight + Hotel + Car + Multi-park tickets.</p>
                                <h5 class="text-white mb-3">Save up to 40% (select promos)</h5>
                                <!-- Price -->
                                <h2 class="mb-0 text-primary">$599 <span class="text-white-50 fs-5 fw-normal">/person (from)</span></h2>
                            </div>
                            <div class="py-4 text-start text-white flex-grow-1">
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Bundle & save on complete trips</p>
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Options to include select parks*</p>
                                <p class="mb-3"><i class="fa fa-check text-primary me-2"></i>Flexible length of stay</p>
                                <p class="mb-0 small">
                                    *Access depends on tickets, travel dates, and availability.
                                </p>
                            </div>
                            <button
                                onclick="addToCart('Southwest Vacations Bundle', 599.00, {unit:'per person', note:'From price; up to 40% off promos', currency:'USD'})"
                                class="btn btn-light rounded-pill py-3 px-5 mt-auto">
                                Add to Cart
                            </button>
                        </div>
                    </div>

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
