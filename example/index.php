<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Wilaak\Iconify as Icon;

Icon\init_remote(
    storePath: __DIR__ . '/icons',
    servePath: '/icons',
);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>iconify-php</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 3rem;
            background: white;
            color: black;
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
        }

        .item span {
            font-size: 0.7rem;
            font-family: monospace;
            color: black;
        }

        .iconify-mask {
            background: currentColor;
            mask-position: center center;
            mask-size: 100% auto;
            mask-repeat: no-repeat;
        }
    </style>
</head>

<body>

    <div class="grid">
        <div class="item">
            <?php Icon\mask('ri:php-line', '32px', '32px') ?>
            <span>ri:php-line</span>
        </div>
        <div class="item">
            <?php Icon\mask('simple-icons:php', '32px', '32px') ?>
            <span>simple-icons:php</span>
        </div>
        <div class="item">
            <?php Icon\inline('devicon:php', '32px', '32px') ?>
            <span>devicon:php</span>
        </div>
    </div>

    <div class="grid" style="align-items: flex-end;">
        <div class="item">
            <?php Icon\mask('ri:php-line', '16px', '16px') ?>
            <span>16px</span>
        </div>
        <div class="item">
            <?php Icon\mask('ri:php-line', '24px', '24px') ?>
            <span>24px</span>
        </div>
        <div class="item">
            <?php Icon\mask('ri:php-line', '32px', '32px') ?>
            <span>32px</span>
        </div>
        <div class="item">
            <?php Icon\mask('ri:php-line', '48px', '48px') ?>
            <span>48px</span>
        </div>
        <div class="item">
            <?php Icon\mask('ri:php-line', '64px', '64px') ?>
            <span>64px</span>
        </div>
    </div>

    <div class="grid" style="align-items: flex-end;">
        <div class="item">
            <?php Icon\inline('devicon:php', '16px', '16px') ?>
            <span>16px</span>
        </div>
        <div class="item">
            <?php Icon\inline('devicon:php', '24px', '24px') ?>
            <span>24px</span>
        </div>
        <div class="item">
            <?php Icon\inline('devicon:php', '32px', '32px') ?>
            <span>32px</span>
        </div>
        <div class="item">
            <?php Icon\inline('devicon:php', '48px', '48px') ?>
            <span>48px</span>
        </div>
        <div class="item">
            <?php Icon\inline('devicon:php', '64px', '64px') ?>
            <span>64px</span>
        </div>
    </div>

</body>

</html>