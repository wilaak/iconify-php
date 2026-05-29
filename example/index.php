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
    <title>iconify-php example</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 1rem;
            background: #fff;
            color: #000;
        }

        .row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .label {
            width: 200px;
            font-size: 1rem;
            font-family: monospace;
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
    <h1>iconify-php example</h1>

    <div class="row">
        <span class="label">mask mdi:home</span>
        <?php Icon\mask('mdi:home', '32px', '32px') ?>
    </div>

    <div class="row">
        <span class="label">mask mdi:account</span>
        <?php Icon\mask('mdi:account', '32px', '32px') ?>
    </div>

    <div class="row">
        <span class="label">inline mdi:home</span>
        <?php Icon\inline('mdi:home', '32px', '32px') ?>
    </div>

    <div class="row">
        <span class="label">inline mdi:account</span>
        <?php Icon\inline('mdi:account', '32px', '32px') ?>
    </div>

    <div class="row">
        <span class="label">inline lucide:rocket</span>
        <?php Icon\inline('lucide:rocket', '32px', '32px') ?>
    </div>

    <div class="row">
        <span class="label">inline heroicons:bell</span>
        <?php Icon\inline('heroicons:bell', '32px', '32px') ?>
    </div>
</body>

</html>