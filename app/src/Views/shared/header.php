<?php
$config = require __DIR__ . '/../../../config/config.php';
$fb = $config['firebase'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= htmlspecialchars($title ?? 'Musina Gas') ?></title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <!-- Firebase v10 SDK Initialization (Project: musinagas-42910) -->
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
    import { getAuth } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
    import { getFirestore } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

    const firebaseConfig = {
      apiKey: "<?= htmlspecialchars($fb['api_key']) ?>",
      authDomain: "<?= htmlspecialchars($fb['auth_domain']) ?>",
      projectId: "<?= htmlspecialchars($fb['project_id']) ?>",
      storageBucket: "<?= htmlspecialchars($fb['storage_bucket']) ?>",
      messagingSenderId: "<?= htmlspecialchars($fb['messaging_sender_id']) ?>",
      appId: "<?= htmlspecialchars($fb['app_id']) ?>"
    };

    const app = initializeApp(firebaseConfig);
    window.firebaseApp = app;
    window.firebaseAuth = getAuth(app);
    window.firebaseDb = getFirestore(app);
  </script>
</head>
<body>
