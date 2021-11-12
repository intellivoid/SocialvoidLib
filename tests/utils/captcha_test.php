<?php

    require('ppm');
    import('net.intellivoid.socialvoidlib');

    $captcha = new \SocialvoidLib\Classes\CaptchaBuilder();
    $captcha->setPhrase('12+4');
    $captcha->build(250, 100);

    $captcha->save('out.jpg');