/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

-- Cyclic dependencies found

create table if not exists posts
(
    public_id               varchar(64)   not null comment 'The Unique Public ID for this record',
    text                    varchar(1526) null comment 'The text content of the post, can be null',
    source                  varchar(256)  null comment 'The source device that was utilized to compose this post',
    properties              blob          null comment 'ZiProto encoded properties of this post',
    session_id              varchar(255)  null comment 'The session ID used to create this post if any',
    poster_user_id          int           null comment 'The User ID that made this post',
    reply_to_post_id        varchar(64)   null comment 'The ID of the post if this post is a reply to another post',
    reply_to_user_id        int           null comment 'The user ID of the original post that this post is replying to',
    quote_original_post_id  varchar(64)   null comment 'The original post ID if this post is a quote of another post',
    quote_original_user_id  int           null comment 'The original user of the post of this post is a quote of another post',
    repost_original_post_id varchar(64)   null comment 'The original post ID of the original post if this post is a repost',
    repost_original_user_id int           null comment 'The original user ID of the original post if this post is a repost',
    flags                   tinyblob      null comment 'Flags associated with this post',
    is_deleted              tinyint(1)    null comment 'Indicates if the post is currently deleted or not',
    priority_level          varchar(32)   null comment 'The priority level of this post',
    entities                blob          null comment 'ZiProto encoded extracted named entities from this post',
    likes                   blob          null comment 'ZiProto encoded data of the user IDs that liked this post',
    reposts                 blob          null comment 'ZiProto encoded data of the user IDs that reposted this post',
    quotes                  blob          null comment 'ZiProto encoded array of post IDs that quoted this post',
    replies                 blob          null comment 'ZiProto encoded array of the Post IDs replies made to this post',
    media_content           blob          null comment 'ZiProto encoded data of the media content associated with this post',
    last_updated_timestamp  int           null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp       int           null comment 'The Unix Timestamp for when this record was created',
    constraint posts_public_id_uindex
        unique (public_id),
    constraint posts_posts_public_id_fk
        foreign key (repost_original_post_id) references posts (public_id),
    constraint posts_posts_public_id_fk_2
        foreign key (quote_original_post_id) references posts (public_id),
    constraint posts_posts_public_id_fk_3
        foreign key (reply_to_post_id) references posts (public_id),
    constraint posts_sessions_id_fk
        foreign key (session_id) references sessions (id),
    constraint posts_users_id_fk
        foreign key (poster_user_id) references users (id),
    constraint posts_users_id_fk_2
        foreign key (reply_to_user_id) references users (id),
    constraint posts_users_id_fk_3
        foreign key (quote_original_user_id) references users (id),
    constraint posts_users_id_fk_4
        foreign key (repost_original_user_id) references users (id)
)
    comment 'Posts made by users on the network';

create index posts_created_timestamp_index
    on posts (created_timestamp);

create index posts_is_deleted_index
    on posts (is_deleted);

create index posts_last_updated_timestamp_index
    on posts (last_updated_timestamp);

create index posts_poster_user_id_index
    on posts (poster_user_id);

create index posts_priority_level_index
    on posts (priority_level);

create index posts_quote_original_post_id_index
    on posts (quote_original_post_id);

create index posts_quote_original_user_id_index
    on posts (quote_original_user_id);

create index posts_reply_to_post_id_index
    on posts (reply_to_post_id);

create index posts_reply_to_user_id_index
    on posts (reply_to_user_id);

create index posts_repost_original_post_id_index
    on posts (repost_original_post_id);

create index posts_repost_original_user_id_index
    on posts (repost_original_user_id);

create index posts_session_id_index
    on posts (session_id);

alter table posts
    add primary key (public_id);

