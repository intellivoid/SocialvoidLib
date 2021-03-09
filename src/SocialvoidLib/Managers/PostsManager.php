<?php


    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\Levels\PostPriorityLevel;
    use SocialvoidLib\Abstracts\SearchMethods\PostSearchMethod;
    use SocialvoidLib\Classes\PostText\Extractor;
    use SocialvoidLib\Classes\PostText\PostRegex;
    use SocialvoidLib\Classes\Standard\BaseIdentification;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSearchMethodException;
    use SocialvoidLib\Objects\ActiveSession;
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

        public function publishPost(int $user_id, string $source, string $text, array $media_content=[], $priority=PostPriorityLevel::None, $flags=[]): Post
        {
            $timestamp = (int)time();

            // TODO: Add the ability to validate if the text is valid or not
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
                return null;
            }
            else
            {
                throw new DatabaseException("There was an error while trying to create a post",
                    $Query, $this->socialvoidLib->getDatabase()->error, $this->socialvoidLib->getDatabase()
                );
            }
        }

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
            ], $search_method, $value);
            
        }
    }