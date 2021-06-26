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


    $Post = $NetworkSession->getTimeline()->replyToPost("692034fdfbb2743f001238651742b6891d454c253f6ac27047fb3e0292872e88", "This is a reply");
    print(json_encode($Post->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));