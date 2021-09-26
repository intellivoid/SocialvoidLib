create table replies
(
    id                     varchar(286) not null comment 'The Unique Internal Database ID',
    user_id                int          null comment 'The User ID that replied this post',
    post_id                varchar(64)  null comment 'The Post ID associated with this reply',
    reply_post_id          varchar(64)  null comment 'The Post ID of the reply',
    replied                tinyint(1)   null comment 'Indicates the current reply status',
    last_updated_timestamp int          null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int          null comment 'The Unix Timestamp for when this record was created',
    constraint replies_id_uindex
        unique (id),
    constraint replies_post_id_reply_post_id_uindex
        unique (post_id, reply_post_id),
    constraint replies_user_id_post_id_uindex
        unique (user_id, post_id),
    constraint replies_posts_public_id_fk
        foreign key (reply_post_id) references posts (public_id),
    constraint replies_posts_public_id_fk_2
        foreign key (post_id) references posts (public_id)
)
    comment 'Table for housing replies to posts';

create index replies_liked_index
    on replies (replied);

create index replies_post_id_index
    on replies (post_id);

create index replies_reply_post_id_index
    on replies (reply_post_id);

create index replies_user_id_index
    on replies (user_id);

alter table replies
    add primary key (id);

