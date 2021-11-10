<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $Socialvoid = new \SocialvoidLib\SocialvoidLib();
    $jobs = [
        "netkas" => \SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername,
        "admin" => \SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername,
        "jaytoo" => \SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername,
        "jaytoo2" => \SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername
    ];
    $res = $Socialvoid->getPeerManager()->getMultipleUsers($jobs, false);
    var_dump($jobs);
    var_dump($res);