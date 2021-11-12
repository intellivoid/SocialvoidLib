<?php

    require('ppm');
    import('net.intellivoid.socialvoidlib');

    $captcha = new \SocialvoidLib\Classes\CaptchaBuilder();
    $captcha->build();

    $captcha->save('out.jpg');