create table documents
(
    id                      varchar(126)         not null comment 'The ID of the document',
    content_source          varchar(32)          not null comment 'The source of where the content is hosted at',
    content_identifier      varchar(132)         null comment 'The content identifier that points this record to the content source',
    files                   blob                 null comment 'ZiProto encoded array of files associated with this document',
    deleted                 tinyint(1) default 0 not null comment 'Indicates if this document has been deleted or not',
    owner_user_id           int                  null comment 'The user ID that owns this content',
    forward_user_id         int                  null comment 'The User ID that forwarded this document if any, the Public ID will change but the rest of the information will stay the same',
    access_type             varchar(16)          null comment 'The Access Type of the content, if further checks are required or not to determine if the peer can access this document',
    access_roles            blob                 null comment 'ZiProto Blob, the access roles of the users related to this content',
    flags                   blob                 null comment 'ZiProto Blob, flags associated with this document',
    properties              blob                 null comment 'ZiProto encoded object of properties related to this record',
    last_accessed_timestamp int                  null comment 'The Unix Timestamp for when this record was last accessed',
    created_timestamp       int                  null comment 'The Unix Timestamp for when this record was first created',
    constraint documents_id_uindex
        unique (id)
)
    comment 'Table for housing documents uploaded to the network';

create index documents_content_identifier_index
    on documents (content_identifier);

create index documents_content_source_content_identifier_index
    on documents (content_source, content_identifier);

create index documents_forward_user_id_index
    on documents (forward_user_id);

create index documents_owner_user_id_index
    on documents (owner_user_id);

alter table documents
    add primary key (id);

