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

    $MediaContent = [];
    $MediaContent[] = $NetworkSession->g

    $Post = $NetworkSession->getTimeline()->postToTimeline("Amazing!");
    print(json_encode($Post->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));