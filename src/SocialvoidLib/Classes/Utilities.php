<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

namespace SocialvoidLib\Classes;

    use DOMDocument;
    use HttpStream\HttpStream;
    use InvalidArgumentException;
    use MarkdownParser\MarkdownParser;
    use SocialvoidLib\Abstracts\JobClass;
    use SocialvoidLib\Abstracts\Modes\Standard\ParseMode;
    use SocialvoidLib\Abstracts\RegexPatterns;
    use SocialvoidLib\Abstracts\Types\JobType;
    use SocialvoidLib\Abstracts\Types\Standard\PostType;
    use SocialvoidLib\Abstracts\Types\Standard\TextEntityType;
    use SocialvoidLib\Classes\PostText\Extractor;
    use SocialvoidLib\Classes\Security\Hashing;
    use SocialvoidLib\Objects\ContentResults;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\Objects\Standard\TextEntity;
    use function html_entity_decode;
    use function htmlentities;
    use function ord;
    use function preg_match_all;
    use function preg_replace;
    use function strlen;
    use function substr;
    use function trim;

    /**
     * Class Utilities
     * @package SocialvoidLib\Classes
     */
    class Utilities
    {
        /**
         * Returns a definition, returns null if failed.
         *
         * @param string $name
         * @return string|null
         */
        public static function getDefinition(string $name): ?string
        {
            if(defined($name))
                return constant($name);

            return null;
        }

        /**
         * Determines the a boolean definition, returns the default value if all else fails
         *
         * @param string $name
         * @param bool $default_value
         * @return bool
         */
        public static function getBoolDefinition(string $name, bool $default_value=false): bool
        {
            if(defined($name))
                return (bool)constant($name);

            return $default_value;
        }

        /**
         * Determines a integer definition, returns the default value if all else fails
         *
         * @param string $name
         * @param int $default_value
         * @return int
         */
        public static function getIntDefinition(string $name, int $default_value=0): int
        {
            if(defined($name))
                return (int)constant($name);

            return $default_value;
        }

        /**
         * Generates a job ID
         *
         * @param array $data
         * @param int $timestamp
         * @return string
         */
        public static function generateJobID(array $data, int $timestamp): string
        {
            // UPDATE: crc32b takes less time and ram to calculate
            $pepper = Hashing::pepper(json_encode($data) . $timestamp);
            return hash("crc32b", $pepper . json_encode($data) . $timestamp);
        }

        /**
         * Splits an array to chunks
         *
         * @param $input
         * @param $size
         * @return array
         */
        public static function splitToChunks($input, $size): array
        {
            return array_chunk($input, $size);
        }

        /**
         * Rebuilds a chunked array into a raw array
         *
         * @param $input
         * @return array
         */
        public static function rebuildFromChunks($input): array
        {
            $results = [];
            foreach($input as $chunk)
            {
                foreach($chunk as $item)
                    $results[] = $item;
            }
            return $results;
        }

        /**
         * Adds an item to a chunked item
         *
         * @param $item
         * @param $chunks
         * @param int $max_size
         * @param int $max_chunks
         * @return array
         */
        public static function addToChunk($item, $chunks, int $max_size=175, int $max_chunks=5): array
        {
            $data = self::rebuildFromChunks($chunks);
            if(in_array($item, $data))
                return $chunks;

            array_unshift($data, $item);

            if(count($data) > $max_size)
                $data = array_pop($data);

            return self::splitToChunks($data, $max_chunks);
        }

        /**
         * Removes an item from the chunk and rebuilds it
         *
         * @param $item
         * @param $chunks
         * @param int $max_chunks
         * @return array
         */
        public static function removeFromChunk($item, $chunks, int $max_chunks=5): array
        {
            $data = self::rebuildFromChunks($chunks);
            if(in_array($item, $data) == false)
                return $chunks;

            $data = array_diff($data, [$item]);

            return self::splitToChunks($data, $max_chunks);
        }

        /**
         * Splits the job weight
         *
         * @param array $data
         * @param int $workers_available
         * @param bool $preserve_keys
         * @param int $utilization
         * @return array
         */
        public static function splitJobWeight(array $data, int $workers_available, bool $preserve_keys=false, int $utilization=50): array
        {
            // Return the same data if the amount of data cannot be split to more than one worker
            if(count($data) == 1)
                return array_chunk($data, 1, $preserve_keys);

            // Auto-correct the utilization value to prevent negative calculations (1-100)
            if($utilization > 100) $utilization = 100;
            if($utilization < 1) $utilization = 1;

            // Determines the amount of workers to be used by the utilization percentage
            $workers_available = (int)($workers_available * $utilization) / 100;

            $chunks_count = (int)round(count($data) / $workers_available);
            if($chunks_count == 0) $chunks_count = 1;
            return array_chunk($data, $chunks_count, $preserve_keys);
        }

        /**
         * Determines what class the job goes to
         *
         * @param string $job_type
         * @return string
         */
        public static function determineJobClass(string $job_type): string
        {
            switch($job_type)
            {
                case JobType::ResolveUsers:
                case JobType::ResolvePosts:
                    return JobClass::QueryClass;

                case JobType::DistributeTimelinePost:
                case JobType::RemoveTimelinePosts:
                    return JobClass::UpdateClass;

                default:
                    throw new InvalidArgumentException("The given job type does not belong to a worker class");
            }
        }

        /**
         * Determines the post type which indicates how the post is supposed
         * to be rendered for better fit the UI, this uses a standard logic
         * that is expected for libraries to use.
         *
         * @param Post $post
         * @return string
         */
        public static function determinePostType(Post $post): string
        {
            // A repost will not contain text or media, it's just a repost.
            if($post->Repost !== null && $post->Repost->OriginalPostID !== null)
                return PostType::Repost;

            // A quoted post may contain media or text and media.
            if($post->Quote !== null && $post->Quote->OriginalPostID !== null)
            {
                return PostType::Quote;
            }

            // A reply post may contain media or text and media
            if($post->Reply !== null && $post->Reply->ReplyToPostID !== null)
            {
                return PostType::Reply;
            }

            // If all checks fail, it's safe to assume this is just a text post
            if($post->Text !== null)
                return PostType::Post;

            // This post may be included in a new update and the library does not
            // yet have the logic to identify the post
            return PostType::Unknown;
        }

        /**
         * Generates a Telegram CDN ID for uploaded media
         *
         * @param string $file_contents
         * @return string
         */
        public static function generateTelegramCdnId(string $file_contents): string
        {
            return Hashing::pepper($file_contents . time());
        }

        /**
         * Sets the content headers for the content results
         *
         * @param ContentResults $contentResults
         * @param bool $contentLength
         */
        public static function setContentHeaders(ContentResults $contentResults, bool $contentLength=true, bool $omit_http_code=false)
        {
            if($omit_http_code == false)
                http_response_code(200);
            header('Content-Type: ' . $contentResults->FileMime);
            header('Content-Disposition: attachment; filename="' . $contentResults->FileName . '"');
            if($contentLength)
                header('Content-Length: ' . $contentResults->FileSize);
        }

        /**
         * Converts a transparent PNG file to a JPEG file
         *
         * @param string $path
         */
        public static function convertPngToJpeg(string $path)
        {
            $image = imagecreatefromstring(file_get_contents($path));
            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, TRUE);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);
            imagejpeg($bg, $path, 100);
            imagedestroy($bg);
        }

        /**
         * Resizes an image while keeping the aspect ratio, the left-over content is blurred out.
         *
         * @param string $path
         * @param int $width
         * @param int $height
         * @param int $blur_strength
         */
        public static function resizeImage(string $path, int $width, int $height, int $blur_strength=120)
        {
            $image = imagecreatefromstring(file_get_contents($path));
            $wor = imagesx($image);
            $hor = imagesy($image);
            $back = imagecreatetruecolor($width, $height);

            $max_fact = max($width/$wor, $height/$hor);
            $new_w = $wor*$max_fact;
            $new_h = $hor*$max_fact;
            imagecopyresampled($back, $image, -(($new_w-$width)/2), -(($new_h-$height)/2), 0, 0, $new_w, $new_h, $wor, $hor);

            // Blur Image
            for ($x=1; $x <=$blur_strength; $x++)
            {
                imagefilter($back, IMG_FILTER_GAUSSIAN_BLUR, 999);
            }
            imagefilter($back, IMG_FILTER_SMOOTH,90);
            imagefilter($back, IMG_FILTER_BRIGHTNESS, 10);

            $min_fact = min($width/$wor, $height/$hor);
            $new_w = $wor*$min_fact;
            $new_h = $hor*$min_fact;

            $front = imagecreatetruecolor($new_w, $new_h);
            imagecopyresampled($front, $image, 0, 0, 0, 0, $new_w, $new_h, $wor, $hor);

            imagecopymerge($back, $front,-(($new_w-$width)/2), -(($new_h-$height)/2), 0, 0, $new_w, $new_h, 100);

            // Create the new file
            imagejpeg($back, $path,100);
            imagedestroy($back);
            imagedestroy($front);
        }

        /**
         * Extracts only the supported HTML tags and fixes broken tags
         *
         * @param string $input
         * @return string
         */
        public static function fixHtmlTags(string $input): string
        {
            $diff = 0;
            preg_match_all(RegexPatterns::SupportedHtmlTags, $input, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            if ($matches) {
                foreach ($matches as $match)
                {
                    if (trim($match[1][0]) != '')
                    {
                        $mod = htmlentities($match[1][0]);
                        $temp = substr($input, 0, $match[1][1] + $diff);
                        $temp .= $mod;
                        $temp .= substr($input, $match[1][1] + $diff + strlen($match[1][0]));
                        $diff += strlen($mod) - strlen($match[1][0]);
                        $input = $temp;
                    }

                    $mod = htmlentities($match[4][0]);
                    $temp = substr($input, 0, $match[4][1] + $diff);
                    $temp .= $mod;
                    $temp .= substr($input, $match[4][1] + $diff + strlen($match[4][0]));
                    $diff += strlen($mod) - strlen($match[4][0]);
                    $input = $temp;
                }

                $diff = 0;
                preg_match_all(RegexPatterns::HtmlHrefTag, $input, $matches, PREG_OFFSET_CAPTURE);

                foreach ($matches[2] as $match)
                {
                    $mod = htmlentities($match[0]);
                    $temp = substr($input, 0, $match[1] + $diff);
                    $temp .= $mod;
                    $temp .= substr($input, $match[1] + $diff + strlen($match[0]));
                    $diff += strlen($mod) - strlen($match[0]);
                    $input = $temp;
                }

                return $input;
            }

            return htmlentities($input);
        }

        /**
         * Decodes an HTML Entity
         *
         * @param string $input
         * @return string
         */
        public static function decodeHtmlEntity(string $input): string
        {
            return html_entity_decode(preg_replace('#< *br */? *>#', "\n", $input));
        }

        /**
         * Gets the string length while accounting for UTF-8 Characters
         *
         * @param string $input
         * @return int
         */
        public static function mbStringLength(string $input): int
        {
            $length = 0;
            $text_length = strlen($input);

            for ($x = 0; $x < $text_length; $x++)
            {
                $char = ord($input[$x]);
                if (($char & 0xc0) != 0x80)
                    $length += 1 + ($char >= 0xf0);
            }

            return $length;
        }

        /**
         * Parses a HTML AST Node.
         *
         * @param $node
         * @param $entities
         * @param $offset
         */
        private static function parseAstNode($node, &$entities, &$offset)
        {
            switch ($node->nodeName)
            {
                case 'br':
                    $offset++;
                    break;
                case 's':
                case 'strike':
                case 'del':
                    $text = self::decodeHtmlEntity($node->textContent);
                    $length = self::mbStringLength($text);
                    $entities[] = TextEntity::fromArray([
                        'type' => TextEntityType::Strike,
                        'offset' => $offset,
                        'length' => $length,
                        'value' => null
                    ]);
                    $offset += $length;
                    break;
                case 'u':
                    $text = self::decodeHtmlEntity($node->textContent);
                    $length = self::mbStringLength($text);
                    $entities[] = TextEntity::fromArray([
                        'type' => TextEntityType::Underline,
                        'offset' => $offset,
                        'length' => $length,
                        'value' => null
                    ]);
                    $offset += $length;
                    break;
                case 'code':
                case 'blockquote':
                case 'pre':
                    $text = self::decodeHtmlEntity($node->textContent);
                    $length = self::mbStringLength($text);
                    $entities[] = TextEntity::fromArray([
                        'type' => TextEntityType::Code,
                        'offset' => $offset,
                        'length' => $length,
                        'value' => null
                    ]);
                    $offset += $length;
                    break;
                case 'b':
                case 'strong':
                    $text = self::decodeHtmlEntity($node->textContent);
                    $length = self::mbStringLength($text);
                    $entities[] = TextEntity::fromArray([
                        'type' => TextEntityType::Bold,
                        'offset' => $offset,
                        'length' => $length,
                        'value' => null
                    ]);
                    $offset += $length;
                    break;
                case 'i':
                case 'em':
                    $text = self::decodeHtmlEntity($node->textContent);
                    $length = self::mbStringLength($text);
                    $entities[] = TextEntity::fromArray([
                        'type' => TextEntityType::Italic,
                        'offset' => $offset,
                        'length' => $length,
                        'value' => null
                    ]);
                    $offset += $length;
                    break;
                case 'p':
                    foreach ($node->childNodes as $node)
                        self::parseAstNode($node, $entities, $offset);
                    break;
                case 'a':
                    $text = self::decodeHtmlEntity($node->textContent);
                    $length = self::mbStringLength($text);
                    $entities[] = TextEntity::fromArray([
                        'type' => TextEntityType::Url,
                        'offset' => $offset,
                        'length' => $length,
                        'value' => $node->getAttribute('href')
                    ]);
                    $offset += $length;
                    break;
                default:
                    $text = self::decodeHtmlEntity($node->textContent);
                    $length = self::mbStringLength($text);
                    $offset += $length;
                    break;
            }
        }


        /**
         * Parses an HTML AST Node to a text entity
         *
         * @param $node
         * @param $new_message
         * @param $offset
         */
        private static function parseAstNodeToText($node, &$new_message, &$offset)
        {
            switch ($node->nodeName)
            {
                case 'br':
                    $new_message .= "\n";
                    $offset++;
                    break;
                case 'p':
                    foreach ($node->childNodes as $node)
                        self::parseAstNodeToText($node, $new_message, $offset);
                    break;
                default:
                    $text = self::decodeHtmlEntity($node->textContent);
                    $new_message .= $text;
                    $offset += self::mbStringLength($text);
                    break;
            }
        }

        /**
         * Extracts a stylized text without the styling entities
         *
         * @param string $input
         * @param string $parse_mode
         * @return string
         */
        public static function extractTextWithoutEntities(string $input, string $parse_mode=ParseMode::Markdown): string
        {
            $new_message = (string)null;

            if($parse_mode == ParseMode::Markdown)
            {
                $markdown_parser = new MarkdownParser();
                $input = $markdown_parser->line($input);
                $parse_mode = ParseMode::HTML;
            }

            // Parse the stylized entities
            if($parse_mode == ParseMode::HTML)
            {
                $input = self::fixHtmlTags($input);
                $offset = 0;

                $document = new DOMDocument();
                $document->loadHTML($input);

                foreach($document->getElementsByTagName('body')->item(0)->childNodes as $node)
                    self::parseAstNodeToText($node, $new_message, $offset);
            }

            return $new_message;
        }

        /**
         * Extracts the stylized entities from the input
         *
         * @param string $input
         * @param string $parse_mode
         * @return array
         */
        public static function extractStylizedEntities(string $input, string $parse_mode=ParseMode::Markdown): array
        {
            $results = [];

            if($parse_mode == ParseMode::Markdown)
            {
                $markdown_parser = new MarkdownParser();
                $input = $markdown_parser->line($input);
                $parse_mode = ParseMode::HTML;
            }

            // Parse the stylized entities
            if($parse_mode == ParseMode::HTML)
            {
                $input = self::fixHtmlTags($input);
                $offset = 0;
                $document = new DOMDocument();
                $document->loadHTML($input);

                foreach($document->getElementsByTagName('body')->item(0)->childNodes as $node)
                    self::parseAstNode($node, $results, $offset);
            }

            return $results;
        }

        /**
         * Sorts the text entities by offset order
         *
         * @param TextEntity[] $input
         * @return TextEntity[]
         */
        private static function sortTextEntities(array $input): array
        {
            $results = [];
            $offsets = [];

            // Extract the offsets
            foreach($input as $textEntity)
            {
                if(in_array($textEntity->Offset, $offsets) == false)
                    $offsets[] = $textEntity->Offset;
            }

            asort($offsets);

            foreach($offsets as $offset)
            {
                foreach($input as $textEntity)
                {
                    if($textEntity->Offset == $offset)
                        $results[] = $textEntity;
                }
            }

            return $results;
        }

        /**
         * Extracts entities from the given text
         *
         * @param string $input
         * @param string $parse_mode
         * @return array
         */
        public static function extractTextEntities(string $input, string $parse_mode=ParseMode::Markdown): array
        {
            // Extract stylized entities
            /** @var TextEntity[] $results */
            $results = self::extractStylizedEntities($input, $parse_mode);

            // Extract other entities such as mentions and URLs
            $parsed_text = self::extractTextWithoutEntities($input, $parse_mode);
            $extractor = new Extractor($parsed_text);

            // Hashtags
            $offset = null;
            foreach($extractor->extractHashtags() as $hashtag)
            {
                $detected_offset = strpos($parsed_text, $hashtag, $offset) -1;
                $length = self::mbStringLength($hashtag) +1;

                $results[] = TextEntity::fromArray([
                    'type' => TextEntityType::Hashtag,
                    'offset' => $detected_offset,
                    'length' => $length,
                    'value' => $hashtag
                ]);

                $offset = $detected_offset + $length;
            }

            // Mentions
            $offset = null;
            foreach($extractor->extractMentionedUsernames() as $mention)
            {
                $detected_offset = strpos($parsed_text, $mention, $offset) -1;
                $length = self::mbStringLength($mention) +1;

                $results[] = TextEntity::fromArray([
                    'type' => TextEntityType::Mention,
                    'offset' => $detected_offset,
                    'length' => $length,
                    'value' => $mention
                ]);

                $offset = $detected_offset + $length;
            }

            // URLs
            $offset = null;
            foreach($extractor->extractURLs() as $url)
            {
                $detected_offset = strpos($parsed_text, $url, $offset);
                $length = self::mbStringLength($url);

                $skip = false;
                foreach($results as $entity)
                {
                    if($entity->Type == TextEntityType::Url && $entity->Value == $url)
                        $skip = true;
                }

                if($skip == false)
                {
                    $results[] = TextEntity::fromArray([
                        'type' => TextEntityType::Url,
                        'offset' => $detected_offset,
                        'length' => $length,
                        'value' => $url
                    ]);
                }

                $offset = $detected_offset + $length;
            }

            return self::sortTextEntities($results);
        }

        /**
         * Returns the slave hash from an identifier
         *
         * @param string $input
         * @return string|null
         */
        public static function getSlaveHash(string $input): ?string
        {
            $exploded = explode('-', $input);

            if(count($exploded) > 1)
                return $exploded[0];

            return null;
        }

        /**
         * Removes the slave hash from the ID
         *
         * @param string $input
         * @return string
         */
        public static function removeSlaveHash(string $input): string
        {
            $exploded = explode('-', $input);

            if(count($exploded) > 1)
            {
                array_shift($exploded);
            }

            return implode('-', $exploded);
        }
    }