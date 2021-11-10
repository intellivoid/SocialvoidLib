/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists quotes
(
    id                     varchar(286) not null comment 'The Unique Internal Database ID',
    user_id                int          null comment 'The User ID that quoted this post',
    post_id                varchar(64)  null comment 'The Post ID associated with this quote',
    original_post_id       varchar(64)  null comment 'The original post that the post_id is quoting',
    quoted                 tinyint(1)   null comment 'Indicates the current quote status',
    last_updated_timestamp int          null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int          null comment 'The Unix Timestamp for when this record was created',
    constraint quotes_id_uindex
        unique (id),
    constraint quotes_post_id_original_post_id_uindex
        unique (post_id, original_post_id),
    constraint quotes_user_id_post_id_uindex
        unique (user_id, post_id),
    constraint quotes_posts_public_id_fk
        foreign key (post_id) references posts (public_id),
    constraint quotes_posts_public_id_fk_2
        foreign key (original_post_id) references posts (public_id),
    constraint quotes_users_id_fk
        foreign key (user_id) references peers (id)
)
    comment 'Table for housing quotes for posts';

create index quotes_liked_index
    on quotes (quoted);

create index quotes_original_post_id_index
    on quotes (original_post_id);

create index quotes_post_id_index
    on quotes (post_id);

create index quotes_user_id_index
    on quotes (user_id);

alter table quotes
    add primary key (id);