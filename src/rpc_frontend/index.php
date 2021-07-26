<?php

    ini_set('display_errors', '1');
    require("ppm");

    ppm_import("net.intellivoid.kimchi_rpc");

    // Initialize the server as a handler
    $KimchiRPC = new \KimchiRPC\KimchiRPC("Socialvoid RPC");
    $KimchiRPC->setServerMode(\KimchiRPC\Abstracts\ServerMode::Handler);

    // Enable BackgroundWorker
    $KimchiRPC->enableBackgroundWorker();
    $KimchiRPC->getBackgroundWorker()->getClient()->addServer();

    // Handle the requests and emits a response.
    $KimchiRPC->handle();
