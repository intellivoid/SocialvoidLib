/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists follower_states
(
    id                     bigint unsigned not null comment 'Unique Internal Database ID',
    user_id                int             null comment 'The User ID that is responsible for the initation',
    target_user_id         int             null comment 'The target user ID that the user is following',
    state                  varchar(32)     null comment 'The current state of the following condition',
    flags                  blob            null comment 'Flags associated with this following state',
    last_updated_timestamp int             null comment 'The Unix Timestamp of when this record was last updated',
    created_timestamp      int             null comment 'The Unix Timestmap for when this record was created',
    constraint follower_states_id_uindex
        unique (id),
    constraint follower_states_user_id_target_user_id_uindex
        unique (user_id, target_user_id),
    constraint follower_states_users_id_fk
        foreign key (user_id) references users (id),
    constraint follower_states_users_id_fk_2
        foreign key (target_user_id) references users (id)
)
    comment 'Table for holding follower states between users';

create index follower_states_state_index
    on follower_states (state);

create index follower_states_target_user_id_index
    on follower_states (target_user_id);

create index follower_states_user_id_index
    on follower_states (user_id);

alter table follower_states
    add primary key (id);

