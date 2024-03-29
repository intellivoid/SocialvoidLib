<?php /** @noinspection ALL */
/** @noinspection ALL */
/** @noinspection ALL */

/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

namespace SocialvoidLib\Classes\PostText;

    /**
     * Class PostRegex
     * @package SocialvoidLib\Classes\PostText
     */
    abstract class PostRegex
    {
        /**
         * Expression to at sign characters
         *
         * @var  string
         */
        const REGEX_AT_SIGNS = '[@＠]';

        /**
         * Expression to match characters that may come before a URL.
         *
         * @var  string
         */
        const REGEX_URL_CHARS_BEFORE = '(?:[^-\\/"\':!=a-z0-9_@＠]|^|\\:)';

        /**
         * Expression to match the domain portion of a URL.
         *
         * @var  string
         */
        const REGEX_URL_DOMAIN = '(?:[^\\p{P}\\p{Lo}\\s][\\.-](?=[^\\p{P}\\p{Lo}\\s])|[^\\p{P}\\p{Lo}\\s])+\\.[a-z]{2,}(?::[0-9]+)?';

        /**
         * Expression to match handful of probable TLDs for protocol-less URLS.
         *
         * @var  string
         */
        const REGEX_PROBABLE_TLD = '/\\.(?:com|net|org|gov|edu)$/iu';

        /**
         * Expression to match characters that may come in the URL path.
         *
         * @var  string
         */
        const REGEX_URL_CHARS_PATH = '(?:(?:\\([a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\))|@[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_,~]+\\/|[\\.\\,]?(?:[a-z0-9!\\*\';:=\\+\\$\\/%#\\[\\]\\-_~]|,(?!\s)))';

        /**
         * Expression to match characters that may come at the end of the URL path.
         *
         * @var  string
         */
        const REGEX_URL_CHARS_PATH_END = '[a-z0-9=#\\/]';

        /**
         * Expression to match characters that may come in the URL query string.
         *
         * @var  string
         */
        const REGEX_URL_CHARS_QUERY = '[a-z0-9!\\*\'\\(\\);:&=\\+\\$\\/%#\\[\\]\\-_\\.,~]';

        /**
         * Expression to match characters that may come at the end of the URL query
         * string.
         *
         * @var  string
         */
        const REGEX_URL_CHARS_QUERY_END = '[a-z0-9_&=#\\/]';

        /**
         * Expression to match a username followed by a list.
         *
         * @var  string
         */
        const REGEX_USERNAME_LIST = '/([^a-z0-9_\/]|^|RT:?)([@＠]+)([a-z0-9_]{1,20})(\/[a-z][-_a-z0-9\x80-\xFF]{0,24})?([@＠\xC0-\xD6\xD8-\xF6\xF8-\xFF]?)/iu';

        /**
         * Expression to match a username mentioned anywhere in a tweet.
         *
         * @var  string
         */
        const REGEX_USERNAME_MENTION = '/(^|[^a-z0-9_])[@＠]([a-z0-9_]{1,20})([@＠\xC0-\xD6\xD8-\xF6\xF8-\xFF]?)/iu';

        /**
         * Expression to match a hashtag.
         *
         * @var  string
         */
        const REGEX_HASHTAG = '/(^|[^0-9A-Z&\/\?]+)([#＃]+)([0-9A-Z_]*[A-Z_]+[a-z0-9_üÀ-ÖØ-öø-ÿ]*)/iu';

        /**
         * Expression to match whitespace.
         *
         * Single byte whitespace characters
         *   0x0009-0x000D White_Space # Cc # <control-0009>..<control-000D>
         *   0x0020        White_Space # Zs # SPACE
         *   0x0085        White_Space # Cc # <control-0085>
         *   0x00A0        White_Space # Zs # NO-BREAK SPACE
         * Multi byte whitespace characters
         *   0x1680        White_Space # Zs # OGHAM SPACE MARK
         *   0x180E        White_Space # Zs # MONGOLIAN VOWEL SEPARATOR
         *   0x2000-0x200A White_Space # Zs # EN QUAD..HAIR SPACE
         *   0x2028        White_Space # Zl # LINE SEPARATOR
         *   0x2029        White_Space # Zp # PARAGRAPH SEPARATOR
         *   0x202F        White_Space # Zs # NARROW NO-BREAK SPACE
         *   0x205F        White_Space # Zs # MEDIUM MATHEMATICAL SPACE
         *   0x3000        White_Space # Zs # IDEOGRAPHIC SPACE
         *
         * @var  string
         */
        const REGEX_WHITESPACE = '[\x09-\x0D\x20\x85\xA0]|\xe1\x9a\x80|\xe1\xa0\x8e|\xe2\x80[\x80-\x8a,\xa8,\xa9,\xaf\xdf]|\xe3\x80\x80';

        /**
         * Contains the complete valid URL pattern string.
         *
         * This should be generated the first time the constructor is called.
         *
         * @var  string  The regex pattern for a valid URL.
         */
        protected static $REGEX_VALID_URL = null;

        /**
         * Contains the reply username pattern string.
         *
         * This should be generated the first time the constructor is called.
         *
         * @var  string  The regex pattern for a reply username.
         */
        protected static $REGEX_REPLY_USERNAME = null;

        /**
         * The tweet to be used in parsing.  This should be populated by the
         * constructor of all subclasses.
         *
         * @var  string
         */
        protected $text = '';

        /**
         * This constructor is used to populate some variables.
         *
         * @param string $post The tweet to parse.
         */
        protected function __construct(string $post)
        {
            if (is_null(self::$REGEX_VALID_URL))
            {
                self::$REGEX_VALID_URL = '/(?:'             # $1 Complete match (preg_match already matches everything.)
                    . '('.self::REGEX_URL_CHARS_BEFORE.')'    # $2 Preceding character
                    . '('                                     # $3 Complete URL
                    . '((?:https?:\\/\\/|www\\.)?)'           # $4 Protocol (or www)
                    . '('.self::REGEX_URL_DOMAIN.')'          # $5 Domain(s) (and port)
                    . '(\\/'.self::REGEX_URL_CHARS_PATH.'*'   # $6 URL Path
                    . self::REGEX_URL_CHARS_PATH_END.'?)?'
                    . '(\\?'.self::REGEX_URL_CHARS_QUERY.'*'  # $7 Query String
                    . self::REGEX_URL_CHARS_QUERY_END.')?'
                    . ')'
                    . ')/iux';
            }

            if (is_null(self::$REGEX_REPLY_USERNAME))
            {
                self::$REGEX_REPLY_USERNAME = '/^('.self::REGEX_WHITESPACE.')*[@＠]([a-zA-Z0-9_]{1,20})/';
            }

            $this->text = $post;
        }
    }