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


    $User = $NetworkSession->getUsers()->resolvePeer(getInput("Peer: "));
    print(json_encode(\SocialvoidLib\Objects\Standard\Peer::fromUser($User)->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    print(convert(memory_get_usage(false)) . PHP_EOL);