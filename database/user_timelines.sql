/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists user_timelines
(
    id                     int auto_increment comment 'The Unique Internal Database ID',
    user_id                int         null comment 'The User ID that owns this timeline',
    state                  varchar(32) null comment 'The state of the timeline',
    post_chunks            blob        null comment 'ZiProto encoded data of the Timeline post chunks',
    new_posts              int         null comment 'The number of posts that has been added to this timeline',
    last_updated_timestamp int         null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int         null comment 'The Unix Timestamp for when this record was created',
    constraint user_timelines_id_uindex
        unique (id),
    constraint user_timelines_user_id_uindex
        unique (user_id),
    constraint user_timelines_users_id_fk
        foreign key (user_id) references users (id)
)
    comment 'Data for housing personalized Timelines for users';

create index user_timelines_state_index
    on user_timelines (state);

alter table user_timelines
    add primary key (id);

