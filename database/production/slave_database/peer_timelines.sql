create table peer_timelines
(
    user_id                int        not null comment 'The User ID that owns this timeline',
    post_chunks            mediumblob null comment 'ZiProto encoded data of the Timeline post chunks',
    new_posts              int        null comment 'The number of posts that has been added to this timeline',
    last_updated_timestamp int        null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int        null comment 'The Unix Timestamp for when this record was created',
    constraint user_timelines_user_id_uindex
        unique (user_id)
)
    comment 'Data for housing personalized Timelines for users';

alter table peer_timelines
    add primary key (user_id);

