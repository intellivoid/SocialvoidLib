<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");


    $usernames = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "usernames.json"), true);

    $Socialvoid = new \SocialvoidLib\SocialvoidLib();

    foreach($usernames as $username)
    {
        print("Registering $username" . PHP_EOL);
        $Socialvoid->getUserManager()->registerUser($username, "John", "Smith");

        $User = $Socialvoid->getUserManager()->getUser(\SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername, $username);
        $User->disableAllAuthenticationMethods();
        $User->AuthenticationProperties->setPassword("SuperExtreme1Password24...");
        $User->AuthenticationMethod = \SocialvoidLib\Abstracts\UserAuthenticationMethod::Simple;
        $User = $Socialvoid->getUserManager()->updateUser($User);
    }
