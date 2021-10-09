create table peer_relations
(
    user_id                int    not null comment 'The User ID that is responsible for the initiation',
    target_user_id         int    not null comment 'The target user ID that the user is following',
    state                  int(2) null comment 'The current state of the following condition',
    created_timestamp      int    null comment 'The Unix Timestamp for when this record was created',
    last_updated_timestamp int    null comment 'The Unix Timestamp of when this record was last updated',
    constraint relations_user_id_target_user_id_uindex
        unique (user_id, target_user_id)
)
    comment 'Table for holding relation states between users';

create index peer_relations_created_timestamp_index
    on peer_relations (created_timestamp);

create index peer_relations_target_user_id_state_index
    on peer_relations (target_user_id, state);

create index peer_relations_user_id_state_index
    on peer_relations (user_id, state);

create index peer_relations_user_id_target_user_id_state_index
    on peer_relations (user_id, target_user_id, state);

create index relations_state_index
    on peer_relations (state);

create index relations_target_user_id_index
    on peer_relations (target_user_id);

create index relations_user_id_index
    on peer_relations (user_id);

alter table peer_relations
    add primary key (user_id, target_user_id);

