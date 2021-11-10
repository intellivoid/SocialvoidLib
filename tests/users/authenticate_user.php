<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $Socialvoid = new \SocialvoidLib\SocialvoidLib();
    $User = $Socialvoid->getPeerManager()->getUser(\SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername, "netkas");

    function getInput(string $prompt): string
    {
        print($prompt);
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        print("\n");
        return str_ireplace("\n", "", $line);
    }

    $User->simpleAuthentication(
        \SocialvoidLib\Classes\Converter::emptyString(getInput("Password: ")),
        \SocialvoidLib\Classes\Converter::emptyString(getInput("Optional 2FA: "))
    );
    $Socialvoid->getPeerManager()->updateUser($User);

    print("Success!" . PHP_EOL);