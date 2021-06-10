/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists documents
(
    public_id               varchar(256) not null comment 'The Public ID of the media content',
    content_source          varchar(64)  not null comment 'The source of the content',
    cdn_public_id           varchar(255) null comment 'The Public CDN ID that this record is related to',
    third_party_source      blob         null comment 'ZiProto encoded blob which indicates more information about how to obtain the content from a third-party source',
    file_mime               varchar(64)  not null comment 'The content type of the the document',
    file_size               int          null comment 'The size of the file in bytes',
    file_name               varchar(256) null comment 'The original file name, including the optional file extension',
    file_extension          varchar(32)  null comment 'The extension of the file name (Everything after the first prefix)',
    owner_user_id           int          not null comment 'The User ID that owns this content',
    forward_user_id         int          null comment 'The user ID that forwarded this document if any. The Public ID will change but not the references',
    access_type             varchar(126) not null comment 'The Access Type of the content, if further checks are required or not.',
    access_roles            blob         null comment 'ZiProto Blob, the access roles of users related to this content',
    flags                   blob         null comment 'ZiProto Blob flags associated with this document',
    properties              blob         null comment 'ZiProto encoded object of properties related to this record',
    last_updated_timestamp  int          null comment 'The Unix Timestamp for when this record was last updated',
    last_accessed_timestamp int          null comment 'The Unix Timestamp of when this content was last accessed',
    created_timestamp       int          null comment 'The Unix Timestmap for when this record was first created',
    constraint documents_public_id_uindex
        unique (public_id),
    constraint documents_telegram_cdn_public_id_fk
        foreign key (cdn_public_id) references telegram_cdn (public_id),
    constraint documents_users_id_fk
        foreign key (forward_user_id) references users (id),
    constraint documents_users_id_fk_2
        foreign key (owner_user_id) references users (id)
)
    comment 'Table of uploaded media content and their permission control access';

create index cloud_content_cdn_public_id_index
    on documents (cdn_public_id);

create index cloud_content_owner_id_index
    on documents (owner_user_id);

create index documents_forward_user_id_index
    on documents (forward_user_id);

alter table documents
    add primary key (public_id);

