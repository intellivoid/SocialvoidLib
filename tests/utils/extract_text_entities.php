<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");


    $text = "<b>Foo</b><i>bar</i> <b>Jo<i>hn</i></b> @admin #netkas #cool #new #amazing https://google.com <a href=\"https://google.com/\">Click Here</a>";

    var_dump(\SocialvoidLib\Classes\Utilities::extractTextEntities($text, \SocialvoidLib\Abstracts\Modes\Standard\ParseMode::HTML));
    var_dump(\SocialvoidLib\Classes\Utilities::extractTextWithoutEntities($text, \SocialvoidLib\Abstracts\Modes\Standard\ParseMode::HTML));