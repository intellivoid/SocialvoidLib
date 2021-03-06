<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $Socialvoid = new \SocialvoidLib\SocialvoidLib();
    $User = $Socialvoid->getUserManager()->getUser(\SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername, "netkas");

    $User->disableAllAuthenticationMethods();
    $User->AuthenticationProperties->setPassword("SuperExtreme1Password24...");
    $User->AuthenticationProperties->generateTimeBasedPrivateSignature(true);
    $User->AuthenticationProperties->generateRecoveryCodes(true);
    $User->AuthenticationMethod = \SocialvoidLib\Abstracts\UserAuthenticationMethod::SimpleSecured;
    $User = $Socialvoid->getUserManager()->updateUser($User);

    print(json_encode($User->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));