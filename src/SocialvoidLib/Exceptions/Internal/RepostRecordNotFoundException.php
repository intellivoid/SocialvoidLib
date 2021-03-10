<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Exceptions\Internal;


    use Exception;
    use Throwable;

    /**
     * Class RepostRecordNotFoundException
     * @package SocialvoidLib\Exceptions\Internal
     */
    class RepostRecordNotFoundException extends Exception
    {
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }