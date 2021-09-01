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

        const SupportedHtmlTags = '#(.*?)(<(\\bu\\b|\\bs\\b|\\ba\\b|\\bb\\b|\\bstrong\\b|\\bblockquote\\b|\\bstrike\\b|\\bdel\\b|\\bem\\b|i|\\bcode\\b|\\bpre\\b)[^>]*>)(.*?)([<]\\s*/\\s*\\3[>])#is';

        const HtmlHrefTag = '#<a\\s*href=("|\')(.+?)("|\')\\s*>#is';

        const HtmlBrTag = '#< *br */? *>#';
    }