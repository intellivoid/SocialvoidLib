create table captcha
(
    id                varchar(64)  not null comment 'The ID of the captcha solution',
    type              varchar(32)  null comment 'The captcha solution type',
    value             varchar(256) null comment 'The captcha value for the captcha type, eg; if external image then it would be a URL, if it''s a imagecaptcha then it would be "123 456", recaptcha would be it''s internal values',
    answer            varchar(256) null comment 'The answer to the captcha solution',
    state             varchar(32)  not null comment 'The current state of the captcha solution',
    used              tinyint(1)   null comment 'Indicates if the answer of this captcha was used or not',
    ip_address        varchar(128) null comment 'The IP address of the client that created the captcha',
    ip_tied           tinyint(1)   null comment 'Indicates if the captcha is tied to the IP address, which means anyone with a different IP address won''t be able to interact with the captcha',
    expiry_timestamp  int          null comment 'The Unix Timestamp for when the captcha solution expires',
    created_timestamp int          null comment 'The Unix Timestamp for when the captcha record was created',
    constraint captcha_id_uindex
        unique (id)
)
    comment 'Table for housing captcha solutions';

create index captcha_state_index
    on captcha (state);

alter table captcha
    add primary key (id);

