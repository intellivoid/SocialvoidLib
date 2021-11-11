<?php

    namespace SocialvoidLib\Abstracts\StatusStates;

    abstract class CaptchaState
    {
        /**
         * Indicates the captcha is currently waiting for an answer from the client
         */
        const AwaitingAnswer = 'AWAITING_ANSWER';

        /**
         * Indicates the captcha is currently waiting for an action to be completed by
         * the user, the server is checking for the answer on it's own
         */
        const AwaitingAction = 'AWAITING_ACTION';

        /**
         * Indicates the captcha has expired and can no longer be used
         */
        const Expired = 'EXPIRED';

        /**
         * Indicates the user provided an answer which is incorrect and rendered the
         * captcha blocked, the user must request a new captcha
         */
        const Blocked = 'BLOCKED';

        /**
         * Indicates the user provided an answer which is correct and the captcha ID
         * can be used to execute the method
         */
        const Success = 'SUCCESS';

        /**
         * Indicates the user used the captcha to execute the method and rendered the
         * captcha used, the user must request a new captcha if needed.
         */
        const Used = 'USED';
    }