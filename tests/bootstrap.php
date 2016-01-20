<?php

session_start();

// Composer autoloader for Charcoal's psr4-compliant Unit Tests
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\Admin\\', __DIR__.'/../src/');
$autoloader->add('Charcoal\\Admin\\Tests\\', __DIR__);
