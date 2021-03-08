<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $Socialvoid = new \SocialvoidLib\SocialvoidLib();
    $Socialvoid->getUserManager()->registerUser("jaytoo", "Jay", "Smith");
    $User = $Socialvoid->getUserManager()->getUser(\SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername, "jaytoo");

    print(json_encode($User->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));