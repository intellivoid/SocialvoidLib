create table peers
(
    id                        int auto_increment comment 'The Unique Internal Database ID for this record',
    public_id                 varchar(256) null comment 'The Unique Public ID for this record',
    username                  varchar(255) null comment 'The username of the user, can also be null',
    username_safe             varchar(255) null comment 'The lowercase variant of the username',
    network                   varchar(255) null comment 'The network name that the user is from',
    status                    varchar(64)  null comment 'The Status of the user',
    status_change_timestamp   int          null comment 'The Unix Timestamp for when the user status is reverted',
    type                      varchar(32)  null comment 'The peer type',
    role                      varchar(32)  null comment 'The peer role',
    properties                blob         null comment 'ZiProto encoded blob of the user properties',
    flags                     tinyblob     null comment 'The current flags set to this user',
    authentication_method     varchar(64)  null comment 'The authentication method used by the user',
    authentication_properties blob         null comment 'ZiProto encoded blob of the Authentication Properties',
    private_access_token      int          null comment 'The private access token of the the user used to access the network without full-auth, can be null if none is set.',
    profile                   blob         null comment 'ZiProto encoded blob of the user profile data',
    settings                  blob         null comment 'ZiProto encoded blob of the settings configuration made by the user',
    privacy_state             varchar(64)  null comment 'The Privacy State of the user account',
    slave_server              varchar(32)  null comment 'The data center the user''s data is stored at (Slave ID)',
    last_activity_timestamp   int          null comment 'The Unix Timestamp of the last activity of the user',
    created_timestamp         int          null comment 'The Unix Timestamp for when this user was created',
    constraint users_id_uindex
        unique (id),
    constraint users_private_access_token_uindex
        unique (private_access_token),
    constraint users_public_id_uindex
        unique (public_id),
    constraint users_username_safe_uindex
        unique (username_safe),
    constraint users_username_username_safe_uindex
        unique (username, username_safe)
)
    comment 'The main table for housing peers and their own data';

create index users_network_name_index
    on peers (network);

create index users_privacy_state_index
    on peers (privacy_state);

create index users_status_index
    on peers (status);

create index users_username_index
    on peers (username);

alter table peers
    add primary key (id);

