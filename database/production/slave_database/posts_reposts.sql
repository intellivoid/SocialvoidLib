create table posts_reposts
(
    id                     varchar(286) not null comment 'The Unique Internal Database ID',
    user_id                int          null comment 'The User ID that reposted this post',
    post_id                varchar(126) null comment 'The Post ID associated with this repost',
    original_post_id       varchar(126) null comment 'The original Post ID that this repost is referring to',
    reposted               tinyint(1)   null comment 'Indicates the current repost status',
    last_updated_timestamp int          null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int          null comment 'The Unix Timestamp for when this record was created',
    constraint reposts_id_uindex
        unique (id),
    constraint reposts_user_id_original_post_id_uindex
        unique (user_id, original_post_id),
    constraint reposts_user_id_post_id_uindex
        unique (user_id, post_id),
    constraint posts_reposts_posts_public_id_fk
        foreign key (original_post_id) references posts (public_id)
)
    comment 'Table for housing reposts for posts';

create index reposts_liked_index
    on posts_reposts (reposted);

create index reposts_original_post_id_index
    on posts_reposts (original_post_id);

create index reposts_post_id_index
    on posts_reposts (post_id);

create index reposts_user_id_index
    on posts_reposts (user_id);

alter table posts_reposts
    add primary key (id);

