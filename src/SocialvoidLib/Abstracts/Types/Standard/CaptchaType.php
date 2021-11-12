<?php

    namespace SocialvoidLib\Abstracts\Types\Standard;

    abstract class CaptchaType
    {
        /**
         * Indicates that no answer or action is required to complete the captcha, the captcha
         * is considered completed upon creation.
         */
        const None = 'NONE';

        /**
         * Indicates that the client must display the captcha value to the user and that the user
         * must reproduce an answer from the value (e.g.; the value may contain a math question like "1+1" and the answer
         * from the user would be 2)
         */
        const TextMathChallenge = 'TEXT_MATH_CHALLENGE';

        /**
         * Indicates that the client must display the captcha value to the user and the user must reproduce
         * the answer from the value, e.g. the value may be like "What's the color orange?" and the answer would be
         * "orange".
         */
        const TextQuestionChallenge = 'TEXT_QUESTION_CHALLENGE';

        /**
         * Indicates that the client must display the captcha image to the user from the value, the value
         * being a base64 encoded data uri scheme representation. The user must provide an answer from the
         * text scramble shown in the images. This is easy for a human to do but difficult for a computer.
         */
        const ImageTextScrambleChallenge = 'IMAGE_TEXT_SCRAMBLE_CHALLENGE';

        /**
         * Indicates that the client must display the captcha image to the user from the value, the value
         * being a base64 encoded data uri scheme representation. The user must provide an answer from the
         * math equation that's scrambled shown in the images. This is easy for a human to do but difficult
         * for a computer.
         */
        const ImageTextScrambleMathChallenge = 'IMAGE_TEXT_SCRAMBLE_MATH_CHALLENGE';

        /**
         * Indicates that the client must display the captcha image to the user from a value, the value being
         * a base64 encoded data uri scheme representation. The user must provide an answer from the image shown,
         * the image, eg if the image is a picture of a clown then the answer would be "Clown"
         */
        const ImageObjectIdentificationChallenge = 'IMAGE_OBJECT_IDENTIFICATION_CHALLENGE';

        /**
         * Indicates that the client must display or open the URL from the captcha value and the answer being
         * automatically set once the external web challenge was completed. The answer is automatically provided
         * by the service or server and considered complete once the user complete the web challenge.
         *
         * This can be a "Recaptcha" challenge for example which is handled entirely by the server.
         */
        const ExternalWebChallenge = 'EXTERNAL_WEB_CHALLENGE';
    }