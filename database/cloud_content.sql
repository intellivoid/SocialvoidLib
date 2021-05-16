/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */


/* THIS IS A TEMPLATE TEST, AND NOT THE FINAL STRUCTURE. */

create table cloud_content
(
    public_id VARCHAR(256) not null comment 'The Public ID of the media content',
    cdn_public_id VARCHAR(255) not null comment 'The Public CDN ID that this record is related to',
    access_type VARCHAR(126) not null comment 'The Access Type of the content, if further checks are required or not.',
    owner_user_id VARCHAR(255) not null comment 'The User ID that owns this content',
    access_roles BLOB null comment 'ZiProto Blob, the access roles of users related to this content',
    properties BLOB null comment 'ZiProto encoded object of properties related to this record',
    last_accessed_timestamp INT(255) null comment 'The Unix Timestamp of when this content was last accessed',
    last_updated_timestamp INT(255) null comment 'The Unix Timestamp for when this record was last updated',
    created_timestamp INT(255) null comment 'The Unix Timestamp for when this record was first created',
    constraint cloud_content_telegram_cdn_public_id_fk
        foreign key (cdn_public_id) references telegram_cdn (public_id),
    constraint cloud_content_users_public_id_fk
        foreign key (owner_user_id) references users (public_id)
)
    comment 'Table of uploaded media content and their permission control access';

create index cloud_content_cdn_public_id_index
    on cloud_content (cdn_public_id);

create index cloud_content_owner_id_index
    on cloud_content (owner_user_id);

create unique index cloud_content_public_id_uindex
    on cloud_content (public_id);

alter table cloud_content
    add constraint cloud_content_pk
        primary key (public_id);

