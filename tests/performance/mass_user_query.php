<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $Socialvoid = new \SocialvoidLib\SocialvoidLib();
    $jobs = [
        \SocialvoidLib\Service\Jobs\UserManager\GetUserJob::fromInput("username_safe", "nektas"),
        \SocialvoidLib\Service\Jobs\UserManager\GetUserJob::fromInput("username_safe", "admin"),
        \SocialvoidLib\Service\Jobs\UserManager\GetUserJob::fromInput("username_safe", "jaytoo"),
    ];
    $res = $Socialvoid->getUserManager()->getMultipleUsers($jobs);
    var_dump($jobs);
    var_dump($res);