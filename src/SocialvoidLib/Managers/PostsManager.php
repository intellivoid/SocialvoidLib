<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\Levels\PostPriorityLevel;
    use SocialvoidLib\Abstracts\SearchMethods\PostSearchMethod;
    use SocialvoidLib\Classes\PostText\Extractor;
    use SocialvoidLib\Classes\PostText\TwitterMethod\Parser;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Exceptions\Standard\Network\PostNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidPostTextException;
    use SocialvoidLib\Objects\Post;
    use SocialvoidLib\SocialvoidLib;
    use ZiProto\ZiProto;

    /**
     * Class PostsManager
     * @package SocialvoidLib\Managers
     */
    class PostsManager
    {

        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * PostsManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Publishes a post to the network
         *
         * @param int $user_id
         * @param string $source
         * @param string $text
         * @param int|null $session_id
         * @param array $media_content
         * @param string $priority
         * @param array $flags
         * @return Post
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostNotFoundException
         * @throws InvalidPostTextException
         */
        public function publishPost(int $user_id, string $source, string $text, int $session_id=null, array $media_content=[], $priority=PostPriorityLevel::None, $flags=[]): Post
        {
            $timestamp = (int)time();

            $textPostParser = new Parser();
            $textPostResults = $textPostParser->parseInput($text);
            if($textPostResults->valid == false)
                throw new InvalidPostTextException("The given post text is invalid", $text);

            $PublicID = BaseIdentification::PostID($user_id, $timestamp, $text);
            $Properties = new Post\Properties();

            // Extract important information from this text
            $Extractor = new Extractor($text);
            $Entities = new Post\Entities();
            $Entities->Hashtags = $Extractor->extractHashtags();
            $Entities->UserMentions = $Extractor->extractMentionedUsernames();
            $Entities->Urls = $Extractor->extractURLs();

            $MediaContentArray = [];
            /** @var Post\MediaContent $value */
            foreach($media_content as $value)
                $MediaContentArray[] = $value->toArray();

            $Query = QueryBuilder::insert_into("posts", [
                "public_id" => $this->socialvoidLib->getDatabase()->real_escape_string($PublicID),
                "text" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($text)),
                "source" => $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($source)),
                "session_id" => ($session_id == null ? null : (int)$session_id),
                "properties" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Properties->toArray())),
                "flags" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($flags)),
                "priority_level" => $this->socialvoidLib->getDatabase()->real_escape_string($priority),
                "entities" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($Entities->toArray())),
                "likes" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "reposts" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                "media_content" => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContentArray)),
                "last_updated_timestamp" => $timestamp,
                "created_timestamp" => $timestamp
            ]);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);
            if($QueryResults)
            {
                return $this->getPost(PostSearchMethod::ByPublicId, $PublicID);
            }
            else
            {
                throw new DatabaseException("There was an error while trying to create a post",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Returns an existing post from the network
         *
         * @param string $search_method
         * @param string $value
         * @return Post
         * @throws DatabaseException
         * @throws InvalidSearchMethodException
         * @throws PostNotFoundException
         */
        public function getPost(string $search_method, string $value): Post
        {
            switch($search_method)
            {
                case PostSearchMethod::ByPublicId:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = $this->socialvoidLib->getDatabase()->real_escape_string($value);
                    break;

                case PostSearchMethod::ById:
                    $search_method = $this->socialvoidLib->getDatabase()->real_escape_string($search_method);
                    $value = (int)$value;
                    break;

                default:
                    throw new InvalidSearchMethodException("The given search method is invalid for getPost()", $search_method, $value);
            }

            $Query = QueryBuilder::select("posts", [
                "id",
                "public_id",
                "text",
                "source",
                "properties",
                "session_id",
                "poster_user_id",
                "reply_to_post_id",
                "reply_to_user_id",
                "quote_original_post_id",
                "quote_original_user_id",
                "repost_original_post_id",
                "repost_original_user_id",
                "flags",
                "priority_level",
                "entities",
                "likes",
                "reposts",
                "media_content",
                "last_updated_timestamp",
                "created_timestamp"
            ], $search_method, $value, null, null, 1);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new PostNotFoundException("The requested post was not found");
                }
                else
                {
                    $Row["properties"] = ($Row["properties"] == null ? null : ZiProto::decode($Row["properties"]));
                    $Row["flags"] = ($Row["flags"] == null ? null : ZiProto::decode($Row["flags"]));
                    $Row["entities"] = ($Row["entities"] == null ? null : ZiProto::decode($Row["entities"]));
                    $Row["likes"] = ($Row["likes"] == null ? null : ZiProto::decode($Row["likes"]));
                    $Row["reposts"] = ($Row["reposts"] == null ? null : ZiProto::decode($Row["reposts"]));
                    $Row["media_content"] = ($Row["media_content"] == null ? null : ZiProto::decode($Row["media_content"]));
                    $Row["text"] = ($Row["text"] == null ? null : urldecode($Row["text"]));
                    $Row["source"] = ($Row["source"] == null ? null : urldecode($Row["source"]));

                    return(Post::fromAlternativeArray($Row));
                }
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying retrieve an existing post from the network",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

        /**
         * Updates an existing post on the network
         *
         * @param Post $post
         * @return Post
         * @throws DatabaseException
         */
        public function updatePost(Post $post): Post
        {
            $MediaContent = null;

            if($post->MediaContent !== null)
            {
                $MediaContent = [];
                foreach($post->MediaContent as $mediaContent)
                    $MediaContent[] = $mediaContent->toArray();
            }

            $post->LastUpdatedTimestamp = (int)time();

            // TODO: Validate text

            // Probably the most CPU intensive update there is here.
            $Query = QueryBuilder::update("posts", [
                "text" => ($post->Text == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($post->Text))),
                "source" => ($post->Source == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(urlencode($post->Source))),
                "properties" => ($post->Properties == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Properties->toArray()))),
                "post_to_user_id" => ($post->PosterUserID == null ? null : (int)$post->PosterUserID),
                "reply_to_post_id" => ($post->Reply == null || $post->Reply->ReplyToPostID == null ? null : (int)$post->Reply->ReplyToPostID),
                "reply_to_user_id" => ($post->Reply == null || $post->Reply->ReplyToUserID == null ? null : (int)$post->Reply->ReplyToUserID),
                "quote_original_post_id" => ($post->Quote == null || $post->Quote->OriginalPostID == null ? null : (int)$post->Quote->OriginalPostID),
                "quote_original_user_id" => ($post->Quote == null || $post->Quote->OriginalUserID == null ? null : (int)$post->Quote->OriginalUserID),
                "repost_original_post_id" => ($post->Repost == null || $post->Repost->OriginalPostID == null ? null : (int)$post->Repost->OriginalPostID),
                "repost_original_user_id" => ($post->Repost == null || $post->Repost->OriginalUserID == null ? null : (int)$post->Repost->OriginalUserID),
                "flags" => ($post->Flags == null ? [] : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Flags))),
                "priority_level" => ($post->PriorityLevel == null ? $this->socialvoidLib->getDatabase()->real_escape_string(PostPriorityLevel::None) : $this->socialvoidLib->getDatabase()->real_escape_string($post->PriorityLevel)),
                "entities" => ($post->Entities == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Entities->toArray()))),
                "likes" => ($post->Likes == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Likes))),
                "reposts" => ($post->Reposts == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($post->Reposts))),
                "media_content" => ($MediaContent == null ? null : $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode($MediaContent))),
                "last_updated_timestamp" => $post->LastUpdatedTimestamp,
            ], "id", (int)$post->ID);
            $QueryResults = $this->socialvoidLib->getDatabase()->query($Query);

            if($QueryResults)
            {
                return $post;
            }
            else
            {
                throw new DatabaseException(
                    "There was an error while trying to update the post",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }
    }