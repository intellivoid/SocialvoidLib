<?php

    ini_set('display_errors', 'Off');
    require("ppm");

    ppm_import("net.intellivoid.kimchi_rpc");
    ppm_import("net.intellivoid.socialvoidlib");

    $socialvoidlib = new \SocialvoidLib\SocialvoidLib();

    // Initialize the server as a handler
    $KimchiRPC = new \KimchiRPC\KimchiRPC("Socialvoid RPC");
    $KimchiRPC->setServerMode(\KimchiRPC\Abstracts\ServerMode::Handler);

    // Enable BackgroundWorker
    $KimchiRPC->enableBackgroundWorker();
    $KimchiRPC->getBackgroundWorker()->getClient()->addServer(
        $socialvoidlib->getRpcServerConfiguration()["GearmanHost"],
        (int)$socialvoidlib->getRpcServerConfiguration()["GearmanPort"]
    );

    // Handle the requests and emits a response.
    $KimchiRPC->handle();
