<?php

    /** @noinspection DuplicatedCode */

    use BackgroundWorker\BackgroundWorker;
    use ppm\ppm;
    use SocialvoidLib\SocialvoidLib;
    use SocialvoidService\SocialvoidService;
    use VerboseAdventure\Abstracts\EventType;
    use VerboseAdventure\Classes\ErrorHandler;
    use VerboseAdventure\VerboseAdventure;

    // Import all required auto loaders
    /** @noinspection PhpIncludeInspection */
    require("ppm");

    /** @noinspection PhpUnhandledExceptionInspection */
    ppm::import("net.intellivoid.acm");
    /** @noinspection PhpUnhandledExceptionInspection */
    ppm::import("net.intellivoid.background_worker");
    /** @noinspection PhpUnhandledExceptionInspection */
    ppm::import("net.intellivoid.socialvoidlib");
    /** @noinspection PhpUnhandledExceptionInspection */
    ppm::import("net.intellivoid.verbose_adventure");
    /** @noinspection PhpUnhandledExceptionInspection */
    ppm::import("net.intellivoid.ziproto");

    VerboseAdventure::setStdout(true); // Enable stdout
    ErrorHandler::registerHandlers(); // Register error handlers

    $current_directory = getcwd();

    if(file_exists($current_directory . DIRECTORY_SEPARATOR . 'SocialvoidService.php'))
    {
        require_once($current_directory . DIRECTORY_SEPARATOR . 'SocialvoidService.php');
    }
    elseif(file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'SocialvoidService.php'))
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'SocialvoidService.php');
    }
    else
    {
        throw new RuntimeException("Cannot locate service class");
    }

    SocialvoidService::setLogHandler(new VerboseAdventure("Socialvoid Service"));
    SocialvoidService::getLogHandler()->log(EventType::INFO, "Starting Service Supervisor", "Main");

    try
    {
        SocialvoidService::$BackgroundWorker = new BackgroundWorker();
        SocialvoidService::$SocialvoidLib = new SocialvoidLib();

        SocialvoidService::getBackgroundWorker()->getClient()->addServer(
            SocialvoidService::getSocialvoidLib()->getEngineConfiguration()["GearmanHost"],
            (int)SocialvoidService::getSocialvoidLib()->getEngineConfiguration()["GearmanPort"]
        );

        // Start service workers
        SocialvoidService::getLogHandler()->log(EventType::INFO, "Starting Service Workers", "Main");
        SocialvoidService::getBackgroundWorker()->getSupervisor()->restartWorkers(
            $current_directory . DIRECTORY_SEPARATOR . "service_worker.php", "SocialvoidService",
            (int)SocialvoidService::getSocialvoidLib()->getEngineConfiguration()["MaxWorkers"]
        );
    }
    catch(Exception $e)
    {
        SocialvoidService::getLogHandler()->logException($e, "Main");
        exit(255);
    }

    SocialvoidService::getLogHandler()->log(EventType::INFO, "Socialvoid Service started successfully", "Main");
    exit(0);