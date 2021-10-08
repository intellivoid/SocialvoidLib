create table sessions
(
    id                         varchar(255) not null comment 'The Unique Public ID for this session',
    flags                      blob         null comment 'ZiProto encoded array of flags associated with this session',
    authenticated              tinyint(1)   null comment 'Indicates if this session is currently authenticated or not',
    user_id                    int          null comment 'The User ID that owns this session',
    authentication_method_used varchar(64)  null comment 'The Authentication Method used create this session',
    platform                   varchar(255) null comment 'The platform that created this session',
    client_name                varchar(255) null comment 'The name of the client using this session',
    client_version             varchar(255) null comment 'The version of the client being used',
    ip_address                 varchar(255) null comment 'The last known IP address to use this session',
    data                       blob         null comment 'Session Data, ZiProto encoded',
    security                   blob         null comment 'The security details of the session',
    last_active_timestamp      int          null comment 'The Unix Timestamp for when this session was last active',
    created_timestamp          int          null comment 'The Unix Timestmap for when this session was created',
    expires_timestamp          int          null comment 'The Unix Timestamp for when this session expires',
    constraint sessions_public_id_uindex
        unique (id),
    constraint sessions_public_id_user_id_uindex
        unique (id, user_id)
)
    comment 'Table for housing active sessions for clients to the network';

create index sessions_ip_address_index
    on sessions (ip_address);

create index sessions_user_id_index
    on sessions (user_id);

alter table sessions
    add primary key (id);

