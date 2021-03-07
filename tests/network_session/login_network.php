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

    $Socialvoid = new \SocialvoidLib\SocialvoidLib(); // Create lib instance
    $NetworkSession = new \SocialvoidLib\NetworkSession($Socialvoid); // Create network session

    // Define the device
    $Client = new \SocialvoidLib\InputTypes\SessionClient("CLI Test", "1.0.0.0");
    $Device = new \SocialvoidLib\InputTypes\SessionDevice("PPM", "Linux", PPM_VERSION);

    // Authenticate the user
    $Username = \SocialvoidLib\Classes\Converter::emptyString(getInput("Username: "));
    $Password = \SocialvoidLib\Classes\Converter::emptyString(getInput("Password: "));
    $TwoFactorCode = \SocialvoidLib\Classes\Converter::emptyString(getInput("2FA Code (Optional): "));

    $User = $Socialvoid->getUserManager()->getUser(\SocialvoidLib\Abstracts\SearchMethods\UserSearchMethod::ByUsername, $Username);
    if($User->simpleAuthentication($Password, $TwoFactorCode))
    {
        print("Authenticated!" . PHP_EOL);
    }

    print("Connecting to network" . PHP_EOL);
    $NetworkSession->authenticateUser(
        $Client, $Device, $User, $User->AuthenticationMethod, "127.0.0.1"
    );

    print("Dumping session file" . PHP_EOL);
    $session_file = __DIR__ . DIRECTORY_SEPARATOR . "network.social_session";
    file_put_contents($session_file, json_encode(
        $NetworkSession->dumpNetworkSession(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );