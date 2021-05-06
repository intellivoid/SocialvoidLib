create table if not exists coa_authentication
(
    id                     int auto_increment comment 'The Unique Internal Database ID for this record',
    account_id             varchar(255) null comment 'The Account ID from the COA service provider',
    user_id                int          null comment 'The user ID that this coa authentication is associated with',
    application_id         varchar(255) null comment 'The Application ID used to authenticate the user',
    status                 varchar(64)  null comment 'The current status of the coa authentication relation',
    last_updated_timestamp int          null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp      int          null comment 'The Unix Timestamp for when this record was created',
    constraint coa_authentication_account_id_uindex
        unique (account_id),
    constraint coa_authentication_id_uindex
        unique (id),
    constraint coa_authentication_user_id_uindex
        unique (user_id),
    constraint coa_authentication_users_id_fk
        foreign key (user_id) references users (id)
)
    comment 'Table for keeping Coa Authentication relations for the users';

alter table coa_authentication
    add primary key (id);

