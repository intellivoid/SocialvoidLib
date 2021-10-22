<?php


    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $names = [
        'test1.test',
        'test file.txt',
        'test',
        'test/file',
        'test\file'
    ];

    foreach($names as $name)
        print($name . ' ' . (\SocialvoidLib\Classes\Validate::fileName($name) == false ? 'INVALID' : 'VALID') . PHP_EOL);