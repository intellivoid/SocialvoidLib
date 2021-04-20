<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");


    $Socialvoid = new \SocialvoidLib\SocialvoidLib(); // Create lib instance

    $res = $Socialvoid->getTelegramCdnManager()->getUploadRecord("93e0c0ba73b2e15a75cae710179812affcd68a9c24aa8791f5dbfb0fc85f0356fda632d5bd340aa720732f32c7ff48871a9e0348e05c4a9fcb106f2c57a00d3e");
    var_dump($res);

    $download_file = __DIR__ . DIRECTORY_SEPARATOR . "example.jpg";
    if(file_exists($download_file)) unlink($download_file);
    file_put_contents($download_file, $Socialvoid->getTelegramCdnManager()->downloadFile($res));