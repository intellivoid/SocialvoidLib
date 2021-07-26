<?php


    namespace SocialvoidLib\Abstracts;

    /**
     * Class RegexPatterns
     * @package SocialvoidLib\Abstracts
     */
    abstract class RegexPatterns
    {
        const SpecialCharacters = '/[\'^£$%&*()}{@#~?><>,|=_+¬-]/';

        const Alphanumeric = '/^[a-zA-Z]+[a-zA-Z0-9._]+$/';
    }