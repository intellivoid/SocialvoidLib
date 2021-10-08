/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists follower_data
(
    user_id                int  not null comment 'The user ID that owns this record',
    followers              int  null comment 'The total amount of users currently following this user',
    followers_ids          blob null comment 'ZiProto encoded data of the User IDs following this user',
    following              int  null comment 'The total amount of users that this user is currently following',
    following_ids          blob null comment 'ZiProto encoded data of the User IDs that this user is currently following',
    last_updated_timestamp int  null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int  null comment 'The Unix Timestamp for when this record was created',
    constraint follower_data_user_id_uindex
        unique (user_id),
    constraint follower_data_users_id_fk
        foreign key (user_id) references users (id)
)
    comment 'The following data for users, intended to use for faster indexing';

alter table follower_data
    add primary key (user_id);

