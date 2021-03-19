<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $Socialvoid = new \SocialvoidLib\SocialvoidLib();
    $jobs = [];
    $i = 1;
    while($i < 50)
    {
        $jobs[$i] = \SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ById;
        $i += 1;
    }

    $res = $Socialvoid->getPostsManager()->getMultiplePosts($jobs, false);
    var_dump($jobs);
    var_dump($res);
