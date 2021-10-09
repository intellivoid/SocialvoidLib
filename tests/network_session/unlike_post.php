<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    function getInput(string $prompt): string
    {
        print($prompt);
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        print("\n");
        return str_ireplace("\n", "", $line);
    }

    $NetworkSessionPath = __DIR__ . DIRECTORY_SEPARATOR . "network.social_session";
    if(file_exists($NetworkSessionPath) == false)
    {
        print("The file '$NetworkSessionPath' does not exist" . PHP_EOL);
        exit(1);
    }

    $Socialvoid = new \SocialvoidLib\SocialvoidLib(); // Create lib instance
    $NetworkSession = \SocialvoidLib\NetworkSession::loadFromSession(
        json_decode(file_get_contents($NetworkSessionPath), true), $Socialvoid); // Load network session


    $NetworkSession->getTimeline()->unlike("364ff7e64cc4fe16952b8d7d5a15c3f4d6c637980950a7a66816753ce653dac4");
