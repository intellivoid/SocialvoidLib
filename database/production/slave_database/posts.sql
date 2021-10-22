create table posts
(
    public_id                    varchar(64)   not null comment 'The Unique Public ID for this record',
    text                         varchar(1526) null comment 'The text content of the post, can be null',
    source                       varchar(256)  null comment 'The source device that was utilized to compose this post',
    properties                   blob          null comment 'ZiProto encoded properties of this post',
    session_id                   varchar(255)  null comment 'The session ID used to create this post if any',
    poster_user_id               int           null comment 'The User ID that made this post',
    reply_to_post_id             varchar(128)  null comment 'The ID of the post if this post is a reply to another post',
    reply_to_user_id             int           null comment 'The user ID of the original post that this post is replying to',
    quote_original_post_id       varchar(128)  null comment 'The original post ID if this post is a quote of another post',
    quote_original_user_id       int           null comment 'The original user of the post of this post is a quote of another post',
    repost_original_post_id      varchar(128)  null comment 'The original post ID of the original post if this post is a repost',
    repost_original_user_id      int           null comment 'The original user ID of the original post if this post is a repost',
    attachments                  blob          null comment 'An array of document objects attached to the post',
    original_thread_post_id      varchar(128)  null comment 'The main thread of the Post where the discussion was created from',
    flags                        tinyblob      null comment 'Flags associated with this post',
    is_deleted                   tinyint(1)    null comment 'Indicates if the post is currently deleted or not',
    priority_level               varchar(32)   null comment 'The priority level of this post',
    text_entities                blob          null comment 'ZiProto encoded extracted named entities from this post',
    like_count                   int default 0 not null comment 'The amount of likes this post has gotten (Query calculated)',
    repost_count                 int default 0 not null comment 'The amount of reposts this post has gotten (Query calculated)',
    quote_count                  int default 0 not null comment 'The amount of quotes this post has gotten (Query calculated)',
    reply_count                  int default 0 not null comment 'The amount of replies that was made to this post (Query Calculated)',
    count_last_updated_timestamp int           null comment 'The Unix Timestamp for when this posts counters was last updated',
    last_updated_timestamp       int           null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp            int           null comment 'The Unix Timestamp for when this record was created',
    constraint posts_public_id_uindex
        unique (public_id)
)
    comment 'Posts made by users on the network';

create index posts_created_timestamp_index
    on posts (created_timestamp);

create index posts_last_updated_timestamp_index
    on posts (last_updated_timestamp);

create index posts_original_thread_post_id_index
    on posts (original_thread_post_id);

create index posts_poster_user_id_index
    on posts (poster_user_id);

create index posts_priority_level_index
    on posts (priority_level);

create index posts_quote_original_post_id_index
    on posts (quote_original_post_id);

create index posts_quote_original_user_id_index
    on posts (quote_original_user_id);

create index posts_reply_to_post
    on posts (reply_to_post_id);

create index posts_reply_to_user_id_index
    on posts (reply_to_user_id);

create index posts_repost_original_post_id_index
    on posts (repost_original_post_id);

create index posts_repost_original_user_id_index
    on posts (repost_original_user_id);

alter table posts
    add primary key (public_id);

