/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists likes
(
    id                     bigint unsigned not null comment 'The Unique Internal Database ID',
    user_id                int             null comment 'The User ID that liked this post',
    post_id                int             null comment 'The Post ID associated with this like',
    liked                  tinyint(1)      null comment 'Indicates the current like status',
    last_updated_timestamp int             null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int             null comment 'The Unix Timestamp for when this record was created',
    constraint likes_id_uindex
        unique (id),
    constraint likes_user_id_post_id_uindex
        unique (user_id, post_id),
    constraint likes_posts_id_fk
        foreign key (post_id) references posts (id),
    constraint likes_users_id_fk
        foreign key (user_id) references users (id)
)
    comment 'Table for housing likes for posts';

create index likes_liked_index
    on likes (liked);

create index likes_post_id_index
    on likes (post_id);

create index likes_user_id_index
    on likes (user_id);

alter table likes
    add primary key (id);
