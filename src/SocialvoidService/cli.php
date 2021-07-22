<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

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
    ppm::import("net.intellivoid.socialvoid_service");

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
            SocialvoidService::getSocialvoidLib()->getServiceEngineConfiguration()["GearmanHost"],
            (int)SocialvoidService::getSocialvoidLib()->getServiceEngineConfiguration()["GearmanPort"]
        );

        // Start query workers
        SocialvoidService::getLogHandler()->log(EventType::INFO, "Starting Service Query Workers", "Main");
        SocialvoidService::$BackgroundWorker->getSupervisor()->setDisplayOutput(
            "SocialvoidQueryService", (bool)SocialvoidService::getSocialvoidLib()->getServiceEngineConfiguration()["DisplayOutput"]);
        SocialvoidService::getBackgroundWorker()->getSupervisor()->startWorkers(
            $current_directory . DIRECTORY_SEPARATOR . "query_worker.php", "SocialvoidQueryService",
            (int)SocialvoidService::getSocialvoidLib()->getServiceEngineConfiguration()["QueryWorkers"]
        );

        // Start update workers
        SocialvoidService::getLogHandler()->log(EventType::INFO, "Starting Service Update Workers", "Main");
        SocialvoidService::$BackgroundWorker->getSupervisor()->setDisplayOutput(
            "SocialvoidUpdateService", (bool)SocialvoidService::getSocialvoidLib()->getServiceEngineConfiguration()["DisplayOutput"]);
        SocialvoidService::getBackgroundWorker()->getSupervisor()->startWorkers(
            $current_directory . DIRECTORY_SEPARATOR . "update_worker.php", "SocialvoidUpdateService",
            (int)SocialvoidService::getSocialvoidLib()->getServiceEngineConfiguration()["UpdateWorkers"]
        );
    }
    catch(Exception $e)
    {
        SocialvoidService::getLogHandler()->logException($e, "Main");
        exit(255);
    }

    SocialvoidService::getLogHandler()->log(EventType::INFO, "Socialvoid Service started successfully", "Main");

    while (true)
    {
        SocialvoidService::getBackgroundWorker()->getSupervisor()->monitor("SocialvoidQueryService");
        SocialvoidService::getBackgroundWorker()->getSupervisor()->monitor("SocialvoidUpdateService");
    }