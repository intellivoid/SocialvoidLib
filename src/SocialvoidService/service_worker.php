<?php

    /** @noinspection PhpUndefinedClassInspection */
    /** @noinspection DuplicatedCode */

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

    $current_directory = getcwd();
    VerboseAdventure::setStdout(true); // Enable stdout
    ErrorHandler::registerHandlers(); // Register error handlers

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

    // Load all required configurations
    SocialvoidService::setLogHandler(new VerboseAdventure("Socialvoid Service"));
    SocialvoidService::setLastWorkerActivity((int)time());
    SocialvoidService::setIsSleeping(false);


    // Start the worker instance
    SocialvoidService::getLogHandler()->log(EventType::INFO, "Starting Service Worker", "Service Worker");
    SocialvoidService::$SocialvoidLib = new SocialvoidLib();
    SocialvoidService::$SocialvoidLib->connectDatabase();

    if(SocialvoidService::$SocialvoidLib->getDatabase()->connect_error)
    {
        SocialvoidService::getLogHandler()->log(EventType::ERROR, "Failed to initialize SocialvoidLib, " . SocialvoidService::$SocialvoidLib->getDatabase()->connect_error, "Service Worker");
        exit(255);
    }

    try
    {
        SocialvoidService::$BackgroundWorker = new BackgroundWorker();
        SocialvoidService::$BackgroundWorker->getWorker()->addServer(
            SocialvoidService::getSocialvoidLib()->getEngineConfiguration()["GearmanHost"],
            (int)SocialvoidService::getSocialvoidLib()->getEngineConfiguration()["GearmanPort"]
        );
    }
    catch(Exception $e)
    {
        SocialvoidService::getLogHandler()->logException($e, "Service Worker");
        exit(255);
    }

    // Define the function "process_batch" to process a batch of Updates from Telegram in the background
    SocialvoidService::getBackgroundWorker()->getWorker()->getGearmanWorker()->addFunction("process_post", function(GearmanJob $job)
    {
        try
        {
            SocialvoidService::setLastWorkerActivity((int)time()); // Set the last activity timestamp
            SocialvoidService::processSleepCycle(); // Wake worker if it's sleeping

            $ServerResponse = new json_decode($job->workload(), true);
            var_dump($ServerResponse);
        }
        catch(Exception $e)
        {
            SocialvoidService::getLogHandler()->logException($e, "Service Worker");
        }

    });

    // Start working
    SocialvoidService::getLogHandler()->log(EventType::INFO, "Worker started successfully", "Service Worker");

    // Set the timeout to 5 seconds
    SocialvoidService::getBackgroundWorker()->getWorker()->getGearmanWorker()->setTimeout(500);

    while(true)
    {
        while(
            @SocialvoidService::getBackgroundWorker()->getWorker()->getGearmanWorker()->work() ||
            SocialvoidService::getBackgroundWorker()->getWorker()->getGearmanWorker()->returnCode() == GEARMAN_TIMEOUT
        )
        {

            if (SocialvoidService::getBackgroundWorker()->getWorker()->getGearmanWorker()->returnCode() == GEARMAN_TIMEOUT)
            {
                SocialvoidService::processSleepCycle(); // Go to sleep if there's no activity
                continue;
            }

            if (SocialvoidService::getBackgroundWorker()->getWorker()->getGearmanWorker()->returnCode() != GEARMAN_SUCCESS)
            {
                SocialvoidService::getLogHandler()->log(EventType::WARNING, "Gearman returned error code " . SocialvoidService::getBackgroundWorker()->getWorker()->getGearmanWorker()->returnCode(), "Service Worker");
                break;
            }
        }
    }


