<?php
// CareTrack Doctor Workspace authentication guard
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/db.php';

// Start session securely
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Redirect unauthenticated users to the login page
if (!is_logged_in()) {
    header('Location: /login.php');
    exit;
}

// Only care_staff and admin may access the Doctor Workspace
$role = current_role();
if ($role !== 'care_staff' && $role !== 'admin') {
    // Send other roles to their own dashboard
    $destinations = [
        'patient'   => '/fastaid/patient-dashboard.html',
        'volunteer' => '/fastaid/volunteer-dashboard.html',
    ];
    $target = $destinations[$role] ?? '/login.php';
    header('Location: ' . $target);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CareTrack | Doctor Workspace</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <div class="brand"><div class="brand-mark">+</div><div><strong>CareTrack</strong><small>Doctor Workspace</small></div></div>
    <div class="profile">
      <div class="avatar">DR</div>
      <div><strong id="doctorName">Dr. Rahman</strong><small>Medical Officer</small></div>
    </div>
    <nav class="nav-menu" aria-label="Main navigation">
      <span class="nav-caption">WORKSPACE</span>
      <a href="#dashboard" class="nav-link active" data-section="dashboard"><span>▦</span> Overview</a>
      <a href="#patients" class="nav-link" data-section="patients"><span>♙</span> Patients</a>
      <a href="#medical-history" class="nav-link" data-section="medical-history"><span>▤</span> Medical History</a>
      <a href="#medications" class="nav-link" data-section="medications"><span>✚</span> Medications</a>
      <a href="#test-reports" class="nav-link" data-section="test-reports"><span>▧</span> Test Reports</a>
      <a href="#vital-signs" class="nav-link" data-section="vital-signs"><span>⌁</span> Vital Signs</a>
      <a href="#care-instructions" class="nav-link" data-section="care-instructions"><span>☷</span> Care Instructions</a>
      <span class="nav-caption">CRITICAL CARE</span>
      <a href="#emergency" class="nav-link" data-section="emergency"><span>⚠</span> Emergency Alerts <b class="nav-count" id="navAlertCount">0</b></a>
      <a href="#icu" class="nav-link" data-section="icu"><span>♥</span> ICU Command Center</a>
      <a href="/fastaid/index.php" class="nav-link">⚡ FastAid Emergency</a>
    </nav>
    <div class="sidebar-footer">
      <div class="demo-note"><strong>Demo workspace</strong><small>Data is saved in this browser only.</small></div>
      <button class="logout-btn" id="logoutBtn" type="button">↪ Sign out</button>
    </div>
  </aside>

  <main class="main-area">
    <header class="topbar">
      <button class="mobile-menu" id="mobileMenu" aria-label="Toggle navigation">☰</button>
      <div><span class="eyebrow">CARE MANAGEMENT</span><h1 id="pageTitle">Overview</h1></div>
      <div class="top-actions"><span class="today" id="todayLabel"></span><div class="top-avatar">DR</div></div>
    </header>

    <div class="content">
      <!-- DASHBOARD -->
      <section class="content-section active" id="dashboard">
        <div class="welcome-banner">
          <div><span class="eyebrow light">DOCTOR DASHBOARD</span><h2>Good to see you, <span id="welcomeName">Doctor</span>.</h2><p>Here is your patient-care overview for today.</p></div>
          <div class="welcome-symbol">✚</div>
        </div>
        <div class="stats-grid">
          <article class="stat-card"><div class="stat-top"><span>Total patients</span><span class="stat-icon blue">♙</span></div><strong id="totalPatients">0</strong><small>Registered in CareTrack</small></article>
          <article class="stat-card"><div class="stat-top"><span>Active patients</span><span class="stat-icon green">♥</span></div><strong id="activePatients">0</strong><small>Currently under care</small></article>
          <article class="stat-card"><div class="stat-top"><span>In ICU</span><span class="stat-icon purple">✚</span></div><strong id="dashboardICU">0</strong><small>Critical care admissions</small></article>
          <article class="stat-card"><div class="stat-top"><span>Open alerts</span><span class="stat-icon red">⚠</span></div><strong id="dashboardAlerts">0</strong><small>Awaiting resolution</small></article>
        </div>
        <div class="dashboard-grid">
          <article class="panel">
            <div class="panel-heading"><div><h2>Patient overview</h2><p>Current patient status distribution</p></div><a href="#patients" class="text-link" data-go="patients">View patients →</a></div>
            <div class="status-overview"><div class="donut" id="patientDonut"><div><strong id="donutTotal">0</strong><small>Patients</small></div></div><div class="legend"><span><i class="dot green-dot"></i> Active <b id="legendActive">0</b></span><span><i class="dot amber-dot"></i> Monitoring <b id="legendMonitoring">0</b></span><span><i class="dot gray-dot"></i> Recovered <b id="legendRecovered">0</b></span></div></div>
          </article>
          <article class="panel">
            <div class="panel-heading"><div><h2>Priority attention</h2><p>Items that may need your review</p></div></div>
            <div class="priority-list" id="priorityList"><p class="empty-state">No priority items.</p></div>
          </article>
        </div>
        <article class="panel">
          <div class="panel-heading"><div><h2>Recently added patients</h2><p>Latest patient records</p></div><a href="#patients" class="text-link" data-go="patients">Open patient list →</a></div>
          <div class="table-wrap"><table><thead><tr><th>Patient</th><th>Patient ID</th><th>Age</th><th>Condition</th><th>Status</th></tr></thead><tbody id="recentPatientsBody"></tbody></table></div>
        </article>
      </section>

      <!-- PATIENTS -->
      <section class="content-section" id="patients">
        <div class="section-intro"><div><span class="eyebrow">PATIENT DIRECTORY</span><h2>Patients</h2><p>Register patients and review their current status.</p></div><button class="btn btn-primary" id="showPatientForm">＋ Add patient</button></div>
        <article class="panel form-panel" id="patientFormPanel" hidden>
          <div class="panel-heading"><div><h3>Register a patient</h3><p>Enter the patient's basic information.</p></div><button class="icon-btn" id="closePatientForm" aria-label="Close">×</button></div>
          <form id="patientForm" class="form-grid">
            <label>Full name<input id="patientName" required maxlength="80" placeholder="Patient full name"></label>
            <label>Patient ID<input id="patientId" required maxlength="24" placeholder="e.g. PT004"></label>
            <label>Age<input id="patientAge" type="number" min="0" max="120" required></label>
            <label>Gender<select id="patientGender" required><option value="">Select</option><option>Female</option><option>Male</option><option>Other</option></select></label>
            <label class="span-2">Primary condition<input id="patientCondition" required maxlength="120" placeholder="e.g. Hypertension"></label>
            <label>Status<select id="patientStatus"><option>Active</option><option>Monitoring</option><option>Recovered</option></select></label>
            <div class="form-actions span-2"><button class="btn btn-primary" type="submit">Save patient</button><p class="form-message" id="patientMessage"></p></div>
          </form>
        </article>
        <article class="panel">
          <div class="panel-heading"><div><h3>Patient directory</h3><p>Search by name, ID or condition.</p></div><input class="search-input" id="patientSearch" placeholder="Search patients…"></div>
          <div class="table-wrap"><table><thead><tr><th>Patient</th><th>ID</th><th>Age</th><th>Gender</th><th>Condition</th><th>Status</th><th>Action</th></tr></thead><tbody id="patientTableBody"></tbody></table></div>
        </article>
      </section>

      <!-- MEDICAL HISTORY -->
      <section class="content-section" id="medical-history">
        <div class="section-intro"><div><span class="eyebrow">CLINICAL RECORDS</span><h2>Medical History</h2><p>Document diagnoses and relevant clinical notes.</p></div></div>
        <article class="panel">
          <h3>Add medical history</h3><form id="historyForm" class="form-grid">
            <label>Patient<select id="historyPatient" required></select></label><label>Diagnosis<input id="historyDiagnosis" required maxlength="120"></label>
            <label>Date<input id="historyDate" type="date" required></label><label class="span-2">Clinical notes<textarea id="historyNotes" rows="3" placeholder="Relevant history and notes"></textarea></label>
            <div class="form-actions span-2"><button class="btn btn-primary">Save history</button><p class="form-message" id="historyMessage"></p></div>
          </form>
        </article>
        <article class="panel"><div class="panel-heading"><div><h3>Medical history records</h3><p>Previously recorded diagnoses.</p></div></div><div class="table-wrap"><table><thead><tr><th>Patient</th><th>Diagnosis</th><th>Date</th><th>Notes</th></tr></thead><tbody id="historyTableBody"></tbody></table></div></article>
      </section>

      <!-- MEDICATIONS -->
      <section class="content-section" id="medications">
        <div class="section-intro"><div><span class="eyebrow">TREATMENT MANAGEMENT</span><h2>Medications</h2><p>Record medication plans for registered patients.</p></div></div>
        <article class="panel"><h3>Add medication record</h3><form id="medicationForm" class="form-grid">
          <label>Patient<select id="medPatient" required></select></label><label>Medication name<input id="medName" required maxlength="100"></label>
          <label>Dosage<input id="medDosage" required maxlength="60" placeholder="e.g. 500 mg"></label><label>Frequency<input id="medFrequency" required maxlength="80" placeholder="e.g. Twice daily"></label>
          <label>Start date<input id="medStartDate" type="date" required></label>
          <div class="form-actions span-2"><button class="btn btn-primary">Save medication</button><p class="form-message" id="medicationMessage"></p></div>
        </form></article>
        <article class="panel"><h3>Medication records</h3><div class="table-wrap"><table><thead><tr><th>Patient</th><th>Medication</th><th>Dosage</th><th>Frequency</th><th>Start date</th></tr></thead><tbody id="medicationTableBody"></tbody></table></div></article>
      </section>

      <!-- REPORTS -->
      <section class="content-section" id="test-reports">
        <div class="section-intro"><div><span class="eyebrow">DIAGNOSTICS</span><h2>Test Reports</h2><p>Record test names, dates and result summaries.</p></div></div>
        <article class="panel"><h3>Add test report</h3><form id="reportForm" class="form-grid">
          <label>Patient<select id="reportPatient" required></select></label><label>Test name<input id="reportName" required maxlength="100"></label>
          <label>Test date<input id="reportDate" type="date" required></label><label>Result summary<input id="reportResult" required maxlength="240"></label>
          <div class="form-actions span-2"><button class="btn btn-primary">Save report</button><p class="form-message" id="reportMessage"></p></div>
        </form></article>
        <article class="panel"><h3>Test report records</h3><div class="table-wrap"><table><thead><tr><th>Patient</th><th>Test</th><th>Date</th><th>Result</th></tr></thead><tbody id="reportTableBody"></tbody></table></div></article>
      </section>

      <!-- VITALS -->
      <section class="content-section" id="vital-signs">
        <div class="section-intro"><div><span class="eyebrow">PATIENT OBSERVATIONS</span><h2>Vital Signs</h2><p>Enter observations as recorded by the clinical team.</p></div></div>
        <article class="panel"><h3>Record vital signs</h3><form id="vitalForm" class="form-grid">
          <label>Patient<select id="vitalPatient" required></select></label><label>Blood pressure<input id="bloodPressure" required placeholder="e.g. 120/80"></label>
          <label>Heart rate (bpm)<input id="heartRate" type="number" min="1" max="300" required></label><label>Temperature (°C)<input id="temperature" type="number" min="25" max="45" step="0.1" required></label>
          <label>Date and time<input id="vitalDate" type="datetime-local" required></label>
          <div class="form-actions span-2"><button class="btn btn-primary">Save vital signs</button><p class="form-message" id="vitalMessage"></p></div>
        </form></article>
        <article class="panel"><h3>Vital sign records</h3><div class="table-wrap"><table><thead><tr><th>Patient</th><th>Blood pressure</th><th>Heart rate</th><th>Temperature</th><th>Recorded</th></tr></thead><tbody id="vitalTableBody"></tbody></table></div></article>
      </section>

      <!-- CARE -->
      <section class="content-section" id="care-instructions">
        <div class="section-intro"><div><span class="eyebrow">CARE COORDINATION</span><h2>Care Instructions</h2><p>Document care plans and instructions for the care team.</p></div></div>
        <article class="panel"><h3>Add care instruction</h3><form id="careForm" class="form-grid">
          <label>Patient<select id="carePatient" required></select></label><label>Instruction title<input id="careTitle" required maxlength="100"></label>
          <label>Due date<input id="careDate" type="date" required></label><label class="span-2">Description<textarea id="careDescription" rows="3" required maxlength="1000"></textarea></label>
          <div class="form-actions span-2"><button class="btn btn-primary">Save instruction</button><p class="form-message" id="careMessage"></p></div>
        </form></article>
        <article class="panel"><h3>Care instructions</h3><div class="table-wrap"><table><thead><tr><th>Patient</th><th>Instruction</th><th>Description</th><th>Due date</th></tr></thead><tbody id="careTableBody"></tbody></table></div></article>
      </section>

      <!-- EMERGENCY -->
      <section class="content-section" id="emergency">
        <div class="section-intro"><div><span class="eyebrow">CRITICAL RESPONSE</span><h2>Emergency Alert Center</h2><p>Review and respond to emergency alerts submitted by nursing staff.</p></div><span class="status-pill status-info">DEMO • Browser storage</span></div>
        <div class="stats-grid three">
          <article class="stat-card"><div class="stat-top"><span>Open alerts</span><span class="stat-icon amber">!</span></div><strong id="pendingAlertCount">0</strong><small>Not yet resolved</small></article>
          <article class="stat-card"><div class="stat-top"><span>Critical alerts</span><span class="stat-icon red">⚠</span></div><strong id="criticalAlertCount">0</strong><small>Critical priority and open</small></article>
          <article class="stat-card"><div class="stat-top"><span>Resolved alerts</span><span class="stat-icon green">✓</span></div><strong id="resolvedAlertCount">0</strong><small>Marked resolved</small></article>
        </div>
        <article class="panel"><div class="panel-heading"><div><h3>Incoming alerts</h3><p>Alerts shared by other roles in this browser will appear here.</p></div><button class="btn btn-outline" id="demoAlertBtn">＋ Add demo alert</button></div><div class="alert-list" id="emergencyAlertList"><p class="empty-state">No emergency alerts available.</p></div></article>
        <article class="panel"><h3>Doctor response</h3><form id="doctorResponseForm" class="form-grid">
          <label>Emergency alert<select id="responseAlertId" required><option value="">Select an alert</option></select></label>
          <label class="span-2">Response / instruction<textarea id="doctorResponse" rows="3" required maxlength="1000" placeholder="Document the response or instruction…"></textarea></label>
          <div class="form-actions span-2"><button class="btn btn-primary">Save response</button><p class="form-message" id="doctorResponseMessage"></p></div>
        </form></article>
      </section>

      <!-- ICU -->
      <section class="content-section" id="icu">
        <div class="section-intro"><div><span class="eyebrow">CRITICAL CARE UNIT</span><h2>ICU Command Center</h2><p>A focused workspace for ICU admissions, recorded ventilator observations and care coordination.</p></div><span class="status-pill status-purple">ICU WORKSPACE</span></div>
        <div class="stats-grid three">
          <article class="stat-card"><div class="stat-top"><span>ICU patients</span><span class="stat-icon purple">♥</span></div><strong id="icuPatientCount">0</strong><small>Currently listed in ICU</small></article>
          <article class="stat-card"><div class="stat-top"><span>Ventilator support</span><span class="stat-icon blue">⌁</span></div><strong id="ventilatorPatientCount">0</strong><small>Patients marked ventilated</small></article>
          <article class="stat-card"><div class="stat-top"><span>Family drafts</span><span class="stat-icon amber">▤</span></div><strong id="pendingFamilyCount">0</strong><small>Awaiting doctor approval</small></article>
        </div>
        <article class="panel"><h3>ICU patient admission</h3><p class="panel-subtitle">Add an existing patient to the ICU list and assign a bed.</p><form id="icuPatientForm" class="form-grid">
          <label>Patient<select id="icuPatient" required></select></label><label>ICU bed<input id="icuBed" required maxlength="20" placeholder="e.g. ICU-01"></label>
          <label>Admission date<input id="icuAdmissionDate" type="date" required></label><label>Respiratory support<select id="icuSupport"><option>Room Air</option><option>Oxygen</option><option>Non-invasive Ventilation</option><option>Ventilator</option></select></label>
          <div class="form-actions span-2"><button class="btn btn-primary">Add to ICU</button><p class="form-message" id="icuPatientMessage"></p></div>
        </form></article>
        <article class="panel"><h3>Current ICU patients</h3><div class="table-wrap"><table><thead><tr><th>Patient</th><th>ID</th><th>Bed</th><th>Admission</th><th>Support</th><th>Action</th></tr></thead><tbody id="icuPatientTableBody"></tbody></table></div></article>
        <article class="panel"><h3>Ventilator & respiratory observations</h3><p class="panel-subtitle">Manual entry only. These values are not connected to bedside equipment.</p><form id="icuObservationForm" class="form-grid">
          <label>ICU patient<select id="observationPatient" required></select></label><label>Observation time<input id="observationTime" type="datetime-local" required></label>
          <label>SpO₂ (%)<input id="spo2" type="number" min="0" max="100" step="0.1" required></label><label>FiO₂ (%)<input id="fio2" type="number" min="21" max="100" step="1" required></label>
          <label>PEEP (cmH₂O)<input id="peep" type="number" min="0" max="40" step="0.5" required></label><label>Respiratory rate (/min)<input id="respiratoryRate" type="number" min="0" max="100" required></label>
          <label>Tidal volume (mL)<input id="tidalVolume" type="number" min="0" max="2000" required></label>
          <div class="form-actions span-2"><button class="btn btn-primary">Save observation</button><p class="form-message" id="observationMessage"></p></div>
        </form><div class="table-wrap"><table><thead><tr><th>Patient</th><th>SpO₂</th><th>FiO₂</th><th>PEEP</th><th>Resp. rate</th><th>Tidal volume</th><th>Time</th></tr></thead><tbody id="icuObservationTableBody"></tbody></table></div></article>
        <div class="dashboard-grid">
          <article class="panel"><h3>ICU clinical notes</h3><form id="icuNotesForm" class="stack-form"><label>ICU patient<select id="icuNotesPatient" required></select></label><label>Clinical note<textarea id="icuClinicalNotes" rows="4" required maxlength="1500" placeholder="Enter a clinical note…"></textarea></label><button class="btn btn-primary">Save clinical note</button><p class="form-message" id="icuNotesMessage"></p></form><div class="notes-list" id="icuClinicalNotesList"></div></article>
          <article class="panel"><h3>Family communication</h3><p class="panel-subtitle">Draft an update, then approve it before it is marked family-ready.</p><form id="familyUpdateForm" class="stack-form"><label>ICU patient<select id="familyPatient" required></select></label><label>Update<textarea id="familyUpdateText" rows="4" required maxlength="1500" placeholder="Write a clear, approved-for-review update…"></textarea></label><button class="btn btn-primary">Save as draft</button><p class="form-message" id="familyUpdateMessage"></p></form><div class="notes-list" id="familyUpdateList"></div></article>
        </div>
      </section>
      <footer class="page-footer">CareTrack prototype · For demonstration and academic use only</footer>
    </div>
  </main>
</div>
<script src="script.js"></script>
</body>
</html>
