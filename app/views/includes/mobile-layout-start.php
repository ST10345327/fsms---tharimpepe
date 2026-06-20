<?php
// Mobile layout start wrapper (shell)
// Handles mobile header area + base tags + includes mobile CSS/JS.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#0b5ed7" />
  <link rel="stylesheet" href="/assets/css/mobile-base.css" />
  <link rel="stylesheet" href="/assets/css/mobile-responsive.css" />
  <link rel="stylesheet" href="/assets/css/mobile-animations.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <title><?php echo htmlspecialchars(isset($pageTitle) ? $pageTitle : 'Tharimpepe'); ?></title>
</head>
<body>
  <div class="mobile-safe">
    <?php include __DIR__ . '/mobile-drawer.php'; ?>
    <?php include __DIR__ . '/mobile-nav.php'; ?>

    <header class="mobile-header" role="banner">
      <button class="mobile-hamburger" type="button" aria-label="Open menu" data-mobile-drawer-open>
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
      </button>

      <div class="mobile-header-title">
        <div class="mobile-title">Tharimpepe FSMS</div>
        <div class="mobile-subtitle"><?php echo htmlspecialchars(isset($pageSubtitle) ? $pageSubtitle : ''); ?></div>
      </div>

      <a class="mobile-header-profile" href="/views/users/profile.php" aria-label="Profile">
        <i class="fa-solid fa-user" aria-hidden="true"></i>
      </a>
    </header>

    <main class="mobile-content" role="main">

