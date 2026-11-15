<?php
// FastAid authentication guard
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: /login.php');
    exit;
}
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#dc3545">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="manifest" href="manifest.json">
  <title>FastAid - Emergency Medical Assistance</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/legacy.css">
</head>
<body class="legacy-page legacy-home">
  <nav class="navbar" aria-label="Main navigation">
    <div class="container">
      <a class="navbar-brand" href="/fastaid/index.php"><svg class="legacy-icon"><use href="assets/icons.svg#heart-pulse"></use></svg>FastAid</a>
      <button class="navbar-toggler" type="button" onclick="toggleNav()" aria-label="Toggle navigation" aria-expanded="false">☰</button>
      <div class="navbar-collapse" id="navLinks">
        <ul class="navbar-nav">
          <li><a class="nav-link" href="/caretrack/index.php">CareTrack</a></li><li><a class="nav-link" href="#home">Home</a></li><li><a class="nav-link" href="#about">About</a></li><li><a class="nav-link" href="#how-it-works">How It Works</a></li><li><a class="nav-link" href="#services">Services</a></li><li><a class="nav-link" href="#contact">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="notice-banner">FastAid does not replace official emergency services. Call your local emergency number first for life-threatening emergencies.</div>

  <section id="home" class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container"><div class="row align-items-center min-vh-100">
      <div class="col-lg-6 hero-content">
        <div class="emergency-badge mb-4"><svg class="legacy-inline-icon"><use href="assets/icons.svg#phone"></use></svg>Emergency Assistance</div>
        <h1 class="display-3 fw-bold text-white mb-4">Emergency Medical Assistance <span class="text-warning">On Demand</span></h1>
        <p class="lead text-white-50 mb-4">Request help from available, admin-reviewed volunteers near a location you choose to share. For life-threatening emergencies, call your local emergency number first.</p>
        <div class="d-flex flex-wrap gap-3 mb-4"><a href="register.html?role=patient" class="btn btn-light btn-lg">Get Help Now</a><a href="register.html?role=volunteer" class="btn btn-outline-light btn-lg">Become a Volunteer</a></div>
        <div class="d-flex gap-4 mt-4"><div class="hero-stat"><h3 class="text-white fw-bold">Patient</h3><p class="text-white-50 mb-0">Emergency requests</p></div><div class="hero-stat"><h3 class="text-white fw-bold">Volunteer</h3><p class="text-white-50 mb-0">Case matching</p></div><div class="hero-stat"><h3 class="text-white fw-bold">Case</h3><p class="text-white-50 mb-0">Status updates</p></div></div>
      </div>
      <div class="col-lg-6 text-center position-relative"><div class="hero-illustration"><div class="pulse-circle"></div><svg class="legacy-icon hero-icon"><use href="assets/icons.svg#ambulance"></use></svg></div></div>
    </div></div>
  </section>

  <section id="about" class="about-section py-5">
    <div class="container py-5"><div class="row align-items-center">
      <div class="col-lg-6 mb-4"><div class="about-image"><img src="assets/images/about-care.svg" alt="Emergency Medical" class="img-fluid rounded-4 shadow"><div class="experience-badge"><span class="fw-bold">Care</span><small>Community support</small></div></div></div>
      <div class="col-lg-6"><div class="section-header mb-4"><span class="badge bg-danger px-3 py-2 mb-3">About FastAid</span><h2 class="fw-bold">Connecting Patients with Medical Heroes</h2></div>
        <p class="text-muted mb-4">FastAid helps patients request assistance and lets approved volunteers respond to cases. Volunteer credentials are submitted by users and are not independently verified.</p>
        <p class="text-muted mb-4">Patients can submit emergency requests. Nearby matching depends on volunteers sharing their location and being available; no response time is guaranteed.</p>
        <div class="row g-3"><div class="col-6"><div class="feature-box"><svg class="legacy-icon text-danger"><use href="assets/icons.svg#clock"></use></svg><h6>Case Updates</h6></div></div><div class="col-6"><div class="feature-box"><svg class="legacy-icon text-danger"><use href="assets/icons.svg#shield"></use></svg><h6>Admin Review</h6></div></div><div class="col-6"><div class="feature-box"><svg class="legacy-icon text-danger"><use href="assets/icons.svg#pin"></use></svg><h6>Optional Location</h6></div></div><div class="col-6"><div class="feature-box"><svg class="legacy-icon text-danger"><use href="assets/icons.svg#heart-pulse"></use></svg><h6>Patient Requests</h6></div></div></div>
      </div>
    </div></div>
  </section>

  <section id="how-it-works" class="how-it-works-section py-5 bg-light"><div class="container py-5">
    <div class="text-center mb-5"><span class="badge bg-danger px-3 py-2 mb-3">How It Works</span><h2 class="fw-bold">Simple Steps to Get Help</h2><p class="text-muted">Get emergency assistance in three easy steps</p></div>
    <div class="row g-4"><div class="col-md-4"><div class="step-card text-center p-4"><div class="step-number">1</div><div class="step-icon"><svg class="legacy-icon"><use href="assets/icons.svg#pin"></use></svg></div><h4>Request Help</h4><p class="text-muted mb-0">Enter an address or choose to share your location and describe the emergency.</p></div></div><div class="col-md-4"><div class="step-card text-center p-4"><div class="step-number">2</div><div class="step-icon"><svg class="legacy-icon"><use href="assets/icons.svg#users"></use></svg></div><h4>Volunteer Matching</h4><p class="text-muted mb-0">Available, approved volunteers who shared their location can view nearby requests.</p></div></div><div class="col-md-4"><div class="step-card text-center p-4"><div class="step-number">3</div><div class="step-icon"><svg class="legacy-icon"><use href="assets/icons.svg#medical"></use></svg></div><h4>Follow Case Status</h4><p class="text-muted mb-0">View case status updates. Always follow official emergency service guidance.</p></div></div></div>
  </div></section>

  <section id="services" class="services-section py-5"><div class="container py-5">
    <div class="text-center mb-5"><span class="badge bg-danger px-3 py-2 mb-3">Our Services</span><h2 class="fw-bold">Comprehensive Emergency Support</h2><p class="text-muted">Multiple ways we help during medical emergencies</p></div>
    <div class="row g-4"><div class="col-md-6 col-lg-4"><div class="service-card p-4"><div class="service-icon"><svg class="legacy-icon"><use href="assets/icons.svg#medical"></use></svg></div><h4>First Aid Requests</h4><p class="text-muted">Submit a first aid request and share case details with a nearby responding volunteer.</p></div></div><div class="col-md-6 col-lg-4"><div class="service-card p-4"><div class="service-icon"><svg class="legacy-icon"><use href="assets/icons.svg#heart-pulse"></use></svg></div><h4>CPR Requests</h4><p class="text-muted">Choose CPR as the emergency type and contact official emergency services first when needed.</p></div></div><div class="col-md-6 col-lg-4"><div class="service-card p-4"><div class="service-icon"><svg class="legacy-icon"><use href="assets/icons.svg#ambulance"></use></svg></div><h4>Case Coordination</h4><p class="text-muted">Review assignments and status updates within the Emergency Assistance feature.</p></div></div><div class="col-md-6 col-lg-4"><div class="service-card p-4"><div class="service-icon"><svg class="legacy-icon"><use href="assets/icons.svg#medical"></use></svg></div><h4>Medication Questions</h4><p class="text-muted">Use the medication question category when submitting an assistance request.</p></div></div><div class="col-md-6 col-lg-4"><div class="service-card p-4"><div class="service-icon"><svg class="legacy-icon"><use href="assets/icons.svg#shield"></use></svg></div><h4>Initial Triage</h4><p class="text-muted">Answer the request flow's initial question about consciousness and breathing.</p></div></div><div class="col-md-6 col-lg-4"><div class="service-card p-4"><div class="service-icon"><svg class="legacy-icon"><use href="assets/icons.svg#users"></use></svg></div><h4>Volunteer Matching</h4><p class="text-muted">Approved, available volunteers who choose to share location can view nearby requests.</p></div></div></div>
  </div></section>

  <section class="cta-section py-5"><div class="container py-5"><div class="row align-items-center"><div class="col-lg-8"><h2 class="text-white fw-bold mb-3">Ready to Make a Difference?</h2><p class="text-white-50 mb-0">Join the volunteer network and respond to nearby requests when available.</p></div><div class="col-lg-4 text-lg-end mt-3 mt-lg-0"><a href="register.html?role=volunteer" class="btn btn-light btn-lg">Become a Volunteer</a></div></div></div></section>

  <section id="contact" class="legacy-contact"><div class="container"><div class="legacy-contact-heading"><span class="badge bg-danger px-3 py-2 mb-3">Contact</span><h2>Contact Us</h2></div><div class="row"><div class="col-lg-4"><ul class="contact-info list-unstyled"><li>Contact channel not configured</li><li>For a life-threatening emergency, call your local emergency number.</li></ul></div><div class="col-lg-8"><form class="legacy-contact-form" onsubmit="handleContactSubmit(event)"><div class="row g-3"><div class="col-md-6"><label class="form-label" for="contactName">Name</label><input class="form-control" type="text" id="contactName" name="name" placeholder="Your name" required></div><div class="col-md-6"><label class="form-label" for="contactEmail">Email</label><input class="form-control" type="email" id="contactEmail" name="email" placeholder="you@example.com" required></div><div class="col-12"><label class="form-label" for="contactMsg">Message</label><textarea class="form-control" id="contactMsg" name="message" placeholder="How can we help?" required></textarea></div><div class="col-12"><button type="submit" class="btn btn-danger">Send Message</button></div></div></form></div></div></div></section>

  <footer class="footer-section py-5"><div class="container"><div class="row g-4"><div class="col-lg-4"><h4><svg class="legacy-inline-icon"><use href="assets/icons.svg#heart-pulse"></use></svg>FastAid</h4><p class="text-white-50">Emergency Assistance connects patient requests with available volunteers and case status updates.</p><div class="d-flex gap-3"><span class="social-icon" aria-hidden="true">f</span><span class="social-icon" aria-hidden="true">𝕏</span><span class="social-icon" aria-hidden="true">◎</span><span class="social-icon" aria-hidden="true">in</span></div></div><div class="col-lg-2"><h6>Quick Links</h6><ul class="list-unstyled footer-links"><li><a href="#home">Home</a></li><li><a href="#about">About</a></li><li><a href="#how-it-works">How It Works</a></li><li><a href="#services">Services</a></li></ul></div><div class="col-lg-3"><h6>For Users</h6><ul class="list-unstyled footer-links"><li><a href="request.html">Request Help</a></li><li><a href="register.html?role=volunteer">Become Volunteer</a></li></ul></div><div class="col-lg-3"><h6>Contact Us</h6><ul class="contact-info list-unstyled"><li>Contact channel not configured</li><li>For a life-threatening emergency, call your local emergency number.</li></ul></div></div><hr><div class="legacy-copyright"><span>&copy; 2026 FastAid. All rights reserved.</span><span><a href="privacy.html">Privacy Policy</a> <a href="terms.html">Terms of Service</a></span></div></div></footer>

  <script src="assets/js/main.js"></script><script src="assets/js/api.js"></script>
  <script>
    function toggleNav(){const nav=document.getElementById('navLinks');nav.classList.toggle('open');document.querySelector('.navbar-toggler').setAttribute('aria-expanded',nav.classList.contains('open'));}
    function handleContactSubmit(e){e.preventDefault();showToast('This contact form is demo-only. No message was sent.','error');}
    window.addEventListener('scroll',()=>document.querySelector('.navbar').classList.toggle('scrolled',window.scrollY>50));
    document.querySelectorAll('a[href^="#"]').forEach(anchor=>anchor.addEventListener('click',e=>{const target=document.querySelector(anchor.getAttribute('href'));if(target){e.preventDefault();target.scrollIntoView({behavior:'smooth',block:'start'});}}));
  </script>
  <script>if('serviceWorker' in navigator){navigator.serviceWorker.register('sw.js');}</script>
</body>
</html>