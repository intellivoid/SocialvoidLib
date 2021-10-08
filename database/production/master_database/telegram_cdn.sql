/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists telegram_cdn
(
    public_id                   varchar(255) not null comment 'The Unique Public ID for this record',
    file_id                     varchar(255) null comment 'Identifier for this file, which can be used to download or reuse the file (Telegram)',
    file_unique_id              varchar(255) null comment 'Unique identifier for this file, which is supposed to be the same over time and for different bots. Can''t be used to download or reuse the file. (Telegram)',
    mime_type                   varchar(32)  null comment 'The MimeType of the file that has been uploaded',
    cdn_file_size               int          null comment 'The file size of the file that is hosted on the CDN',
    cdn_file_hash               varchar(255) null comment 'The hash of the file that is hosted on the CDN',
    original_file_size          int          null comment 'The original file size that is calculated on the server',
    original_file_hash          varchar(255) null comment 'The original file hash that is calculated on the server',
    encryption_key              blob         null comment 'The encryption key used to decrypt the file.',
    access_url                  varchar(256) null comment 'The Access URL used to download the content from the CDN (Temporary)',
    access_url_expiry_timestamp int          null comment 'The Unix Timestamp that indicates when the Access URL has expired',
    created_timestamp           int          null comment 'The Unix Timestamp for when this record was created',
    constraint telegram_cdn_file_unique_id_uindex
        unique (file_unique_id),
    constraint telegram_cdn_public_id_uindex
        unique (public_id)
)
    comment 'Table for housing CDN uploads to Telegram';

create index telegram_cdn_cdn_file_hash_index
    on telegram_cdn (cdn_file_hash);

create index telegram_cdn_mime_type_index
    on telegram_cdn (mime_type);

create index telegram_cdn_original_file_hash_index
    on telegram_cdn (original_file_hash);

alter table telegram_cdn
    add primary key (public_id);

