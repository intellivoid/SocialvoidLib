<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");


    $Socialvoid = new \SocialvoidLib\SocialvoidLib(); // Create lib instance

    $res = $Socialvoid->getTelegramCdnManager()->uploadContent(__DIR__ . DIRECTORY_SEPARATOR . "d41.jpg");
    var_dump($res);