create table socialvoid_slave.posts_likes
(
    id                     varchar(286) not null comment 'The Unique Internal Database ID',
    user_id                int          null comment 'The User ID that liked this post',
    post_id                varchar(64)  null comment 'The Post ID associated with this like',
    liked                  tinyint(1)   null comment 'Indicates the current like status',
    last_updated_timestamp int          null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int          null comment 'The Unix Timestamp for when this record was created',
    constraint likes_id_uindex
        unique (id),
    constraint likes_user_id_post_id_uindex
        unique (user_id, post_id)
)
    comment 'Table for housing likes for posts';

create index likes_liked_index
    on socialvoid_slave.posts_likes (liked);

create index likes_post_id_index
    on socialvoid_slave.posts_likes (post_id);

create index likes_user_id_index
    on socialvoid_slave.posts_likes (user_id);

alter table socialvoid_slave.posts_likes
    add primary key (id);

