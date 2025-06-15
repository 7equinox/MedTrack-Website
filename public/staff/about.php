<?php
$page_title = 'About Us';
$body_class = 'page-about-us';
$base_path = '../..';
$activePage = 'about'; 
require_once __DIR__ . '/../../templates/partials/staff_header.php';
?>

  <main>
    <h1>About Us</h1>
    <div class="about-content">
      <div class="left-box">
        <p>
          MedTrack is a web-based platform dedicated to simplifying medication management and improving patient well-being. It provides secure storage, tracking, and timely reminders, integrating with healthcare providers to enhance adherence and minimize errors.
        </p>
        <p><strong>Our goals:</strong></p>
        <ul>
          <li>Combat medication non-adherence.</li>
          <li>Prevent dosage errors.</li>
          <li>Provide an intuitive tracking and reminder system.</li>
          <li>Develop a robust prototype.</li>
          <li>Improve public health outcomes globally.</li>
        </ul>
      </div>
      <div class="right-boxes">
        <div class="card">
          <h3>Vision</h3>
          <p>
            Our vision is to be the indispensable global digital partner for medication adherence. We envision a future where individuals effortlessly manage prescriptions, leading to improved health outcomes, minimized errors, and a seamlessly connected healthcare experience worldwide.
          </p>
        </div>
        <div class="card">
          <h3>Mission</h3>
          <p>
            Our mission is to simplify medication management. MedTrack empowers patients and caregivers with a secure digital platform providing precise reminders, tracking, and secure storage. We connect users with pharmacies and healthcare providers to reduce non-adherence, boost safety, and improve quality of life.
          </p>
        </div>
      </div>
    </div>
  </main>

<?php 
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php'; 
?> 