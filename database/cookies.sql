/*
 * Copyright (c) 2017-2021. Intellivoid Technologies
 *
 * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
 * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
 * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
 * must have a written permission from Intellivoid Technologies to do so.
 */

create table if not exists cookies
(
    id            int auto_increment comment 'Cookie ID',
    name          varchar(255) null comment 'The name of the Cookie (Public)',
    token         varchar(255) null comment 'The public token of the cookie which uniquely identifies it',
    ip_tied       tinyint(1)   null comment 'If the cookie should be strictly tied to the client''s IP Address',
    client_ip     varchar(255) null comment 'The client''s IP Address of the cookie is tied to the IP',
    disposed      tinyint(1)   null comment 'Flag for if the cookie was disposed',
    data          blob         null comment 'ZiProto Encoded Data associated with the cookie',
    expires       int          null comment 'The Unix Timestamp of when the cookie should expire',
    date_creation int          null comment 'The unix timestamp of when the cookie was created',
    constraint cookies_token_uindex
        unique (token),
    constraint sws_id_uindex
        unique (id)
)
    comment 'The main database for Secured Web Sessions library' charset = latin1;

create index cookies_name_index
    on cookies (name);

alter table cookies
    add primary key (id);

