<?php

    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Classes\PostText;

    /**
     * Class Extractor
     * @package SocialvoidLib\Classes\PostText
     */
    class Extractor extends PostRegex
    {

        /**
         * Provides fluent method chaining.
         *
         * @param  string  $post The Post to be converted.
         *
         * @return  Extractor
         * @see  __construct()
         * @noinspection PhpMissingReturnTypeInspection
         * @noinspection PhpMissingParamTypeInspection
         */
        public static function create($post) {
            return new self($post);
        }

        /**
         * Reads in a tweet to be parsed and extracts elements from it.
         *
         * Extracts various parts of a tweet including URLs, usernames, hashtags...
         *
         * @param  string  $post The Post to extract.
         * @noinspection PhpMissingParamTypeInspection
         */
        public function __construct($post)
        {
            parent::__construct($post);
        }

        /**
         * Extracts all parts of a tweet and returns an associative array containing
         * the extracted elements.
         *
         * @return  array  The elements in the tweet.
         * @noinspection PhpMissingReturnTypeInspection
         */
        public function extract()
        {
            return array(
                "hashtags" => $this->extractHashtags(),
                "urls"     => $this->extractURLs(),
                "mentions" => $this->extractMentionedUsernames(),
                "replyto"  => $this->extractRepliedUsernames(),
                "hashtags_with_indices" => $this->extractHashtagsWithIndices(),
                "urls_with_indices"     => $this->extractURLsWithIndices(),
                "mentions_with_indices" => $this->extractMentionedUsernamesWithIndices(),
            );
        }

        /**
         * Extracts all the hashtags from the tweet.
         *
         * @return  array  The hashtag elements in the tweet.
         * @noinspection PhpMissingReturnTypeInspection
         */
        public function extractHashtags()
        {
            preg_match_all(self::REGEX_HASHTAG, $this->text, $matches);
            return $matches[3];
        }

        /**
         * Extracts all the URLs from the tweet.
         *
         * @return  array  The URL elements in the tweet.
         * @noinspection PhpMissingReturnTypeInspection
         * @noinspection PhpUnusedLocalVariableInspection
         */
        public function extractURLs() {
            preg_match_all(self::$REGEX_VALID_URL, $this->text, $matches);
            list($all, $before, $url, $protocol, $domain, $path, $query) = array_pad($matches, 7, '');
            $i = count($url)-1;
            for (; $i >= 0; $i--)
            {
                if (!preg_match('!https?://!', $protocol[$i]))
                {
                    # Note: $protocol can contain 'www.' if no protocol exists!
                    if (preg_match(self::REGEX_PROBABLE_TLD, $domain[$i]) || strtolower($protocol[$i]) === 'www.')
                    {
                        $url[$i] = 'http://'.(strtolower($protocol[$i]) === 'www.' ? $protocol[$i] : '').$domain[$i];
                    }
                    else
                    {
                        unset($url[$i]);
                    }
                }
            }
            # Renumber the array:
            return array_values($url);
        }

        /**
         * Extract all the usernames from the tweet.
         *
         * A mention is an occurrence of a username anywhere in a tweet.
         *
         * @return  array  The usernames elements in the tweet.
         * @noinspection PhpMissingReturnTypeInspection
         * @noinspection PhpUnusedLocalVariableInspection
         */
        public function extractMentionedUsernames()
        {
            preg_match_all(self::REGEX_USERNAME_MENTION, $this->text, $matches);
            list($all, $before, $username, $after) = array_pad($matches, 4, '');
            $usernames = array();

            for ($i = 0; $i < count($username); $i ++)
            {
                # If $after is not empty, there is an invalid character.
                if (!empty($after[$i])) continue;
                array_push($usernames, $username[$i]);
            }

            return $usernames;
        }

        /**
         * Extract all the usernames replied to from the tweet.
         *
         * A reply is an occurrence of a username at the beginning of a tweet.
         *
         * @return array|string
         */
        public function extractRepliedUsernames()
        {

            preg_match(self::$REGEX_REPLY_USERNAME, $this->text, $matches);
            return isset($matches[2]) ? $matches[2] : '';
        }

        /**
         * Extracts all the hashtags and the indices they occur at from the tweet.
         *
         * @return  array  The hashtag elements in the tweet.
         * @noinspection PhpMissingReturnTypeInspection
         */
        public function extractHashtagsWithIndices()
        {
            preg_match_all(self::REGEX_HASHTAG, $this->text, $matches, PREG_OFFSET_CAPTURE);
            $m = &$matches[3];

            for ($i = 0; $i < count($m); $i++)
            {
                $m[$i] = array_combine(array('hashtag', 'indices'), $m[$i]);
                # XXX: Fix for PREG_OFFSET_CAPTURE returning byte offsets...
                $start = mb_strlen(substr($this->text, 0, $matches[1][$i][1]));
                $start += mb_strlen($matches[1][$i][0]);
                $length = mb_strlen($m[$i]['hashtag']);
                $m[$i]['indices'] = array($start, $start + $length + 1);
            }

            return $m;
        }

        /**
         * Extracts all the URLs and the indices they occur at from the tweet.
         *
         * @return  array  The URLs elements in the tweet.
         * @noinspection PhpMissingReturnTypeInspection
         */
        public function extractURLsWithIndices()
        {
            preg_match_all(self::$REGEX_VALID_URL, $this->text, $matches, PREG_OFFSET_CAPTURE);
            $m = &$matches[2];

            for ($i = 0; $i < count($m); $i++)
            {
                $m[$i] = array_combine(array('url', 'indices'), $m[$i]);
                # XXX: Fix for PREG_OFFSET_CAPTURE returning byte offsets...
                $start = mb_strlen(substr($this->text, 0, $matches[1][$i][1]));
                $start += mb_strlen($matches[1][$i][0]);
                $length = mb_strlen($m[$i]['url']);
                $m[$i]['indices'] = array($start, $start + $length);
            }

            return $m;
        }

        /**
         * Extracts all the usernames and the indices they occur at from the tweet.
         *
         * @return  array  The username elements in the tweet.
         * @noinspection PhpMissingReturnTypeInspection
         */
        public function extractMentionedUsernamesWithIndices()
        {
            preg_match_all(self::REGEX_USERNAME_MENTION, $this->text, $matches, PREG_OFFSET_CAPTURE);
            $m = &$matches[2];

            for ($i = 0; $i < count($m); $i++)
            {
                $m[$i] = array_combine(array('screen_name', 'indices'), $m[$i]);
                # XXX: Fix for PREG_OFFSET_CAPTURE returning byte offsets...
                $start = mb_strlen(substr($this->text, 0, $matches[1][$i][1]));
                $start += mb_strlen($matches[1][$i][0]);
                $length = mb_strlen($m[$i]['screen_name']);
                $m[$i]['indices'] = array($start, $start + $length + 1);
            }

            return $m;
        }
    }