<?php

    use KimchiRPC\Abstracts\ServerMode;
    use KimchiRPC\KimchiRPC;
    use SocialvoidLib\SocialvoidLib;
    use SocialvoidRPC\SocialvoidRPC;
    use VerboseAdventure\Classes\ErrorHandler;
    use VerboseAdventure\VerboseAdventure;

    ini_set('display_errors', 'Off');
    require("ppm");

    ppm_import("net.intellivoid.socialvoid_rpc");
    ppm_import("net.intellivoid.socialvoidlib");

    VerboseAdventure::setStdout(false); // Enable stdout
    ErrorHandler::registerHandlers(); // Register error handlers

    try
    {
        // Prepare the service
        SocialvoidRPC::setLogHandler(new VerboseAdventure("Socialvoid RPC"));
        SocialvoidRPC::$SocialvoidLib = new SocialvoidLib();
        SocialvoidRPC::$RpcServer = new KimchiRPC("Socialvoid RPC");
        SocialvoidRPC::$RpcServer->setServerMode(ServerMode::Handler);
        SocialvoidRPC::registerMethods();
        SocialvoidRPC::$RpcServer->handle();
    }
    catch(Exception $e)
    {
        SocialvoidRPC::getLogHandler()->logException($e, "Main");
    }