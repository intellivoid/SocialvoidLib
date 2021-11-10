<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $Socialvoid = new \SocialvoidLib\SocialvoidLib(); // Create lib instance


    // Define the device
    $Client = new \SocialvoidLib\InputTypes\SessionClient("CLI Test", "1.0.0.0");
    $Device = new \SocialvoidLib\InputTypes\SessionDevice("PPM", "Linux", PPM_VERSION);

    $usernames = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "usernames.json"), true);
    foreach($usernames as $username)
    {
        print("Creating session for $username" . PHP_EOL);
        $User = $Socialvoid->getPeerManager()->getUser(\SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername, $username);

        $NetworkSession = new \SocialvoidLib\NetworkSession($Socialvoid); // Create network session
        $NetworkSession->authenticateUser(
            $Client, $Device, $User, $User->AuthenticationMethod, "127.0.0.1"
        );

        print("Following @netkas" . PHP_EOL);
        $NetworkSession->getUsers()->followPeer("@netkas");
    }
