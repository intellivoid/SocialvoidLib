create table posts_quotes
(
    id                     varchar(286) not null comment 'The Unique Internal Database ID',
    user_id                int          null comment 'The User ID that quoted this post',
    post_id                varchar(126) null comment 'The Post ID associated with this quote',
    original_post_id       varchar(126) null comment 'The original post that the post_id is quoting',
    quoted                 tinyint(1)   null comment 'Indicates the current quote status',
    last_updated_timestamp int          null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int          null comment 'The Unix Timestamp for when this record was created',
    constraint quotes_id_uindex
        unique (id),
    constraint quotes_post_id_original_post_id_uindex
        unique (post_id, original_post_id),
    constraint quotes_user_id_post_id_uindex
        unique (user_id, post_id),
    constraint posts_quotes_posts_public_id_fk
        foreign key (original_post_id) references posts (public_id)
)
    comment 'Table for housing quotes for posts';

create index quotes_liked_index
    on posts_quotes (quoted);

create index quotes_original_post_id_index
    on posts_quotes (original_post_id);

create index quotes_post_id_index
    on posts_quotes (post_id);

create index quotes_user_id_index
    on posts_quotes (user_id);

alter table posts_quotes
    add primary key (id);

