<?php

/**
 * NUX module services
 * @package modules
 * @subpackage nux
 */

if (!defined('DEBUG_MODE')) { die(); }

/**
 * @subpackage nux/services
 */

Nux_Quick_Services::add('gmail', array(
    'server' => 'imap.gmail.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Gmail',
    'scope' => 'https://www.google.com/m8/feeds https://mail.google.com/',
    'smtp' => array(
        'server' => 'smtp.gmail.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('icloud', array(
    'server' => 'imap.mail.me.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'iCloud',
    'scope' => ' https://icloud.com/',
    'smtp' => array(
        'server' => 'smtp.mail.me.com',
        'port' => 587,
        'tls' => false
    )
));

Nux_Quick_Services::add('outlook', array(
    'server' => 'outlook.office365.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Outlook.com',
    'scope' => 'wl.imap',
    'smtp' => array(
        'server' => 'smtp.office365.com',
        'port' => 587,
        'tls'=> false
    )
));

Nux_Quick_Services::add('office365', array(
    'server' => 'outlook.office365.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Office365',
    'scope' => 'wl.imap',
    'smtp' => array(
        'server' => 'smtp.office365.com',
        'port' => 587,
        'tls'=> false
    )
));

Nux_Quick_Services::add('yahoo', array(
    'server' => 'imap.mail.yahoo.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Yahoo',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.mail.yahoo.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('mailcom', array(
    'server' => 'imap.mail.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Mail.com',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.mail.com',
        'port' => 587,
        'tls' => true
    )
));

Nux_Quick_Services::add('aol', array(
    'server' => 'imap.aol.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'AOL',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.aol.com',
        'port' => 587,
        'tls' => true
    )
));

Nux_Quick_Services::add('gmx', array(
    'server' => 'imap.gmx.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 143,
    'name' => 'GMX',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.gmx.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('zoho', array(
    'server' => 'imap.zoho.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Zoho',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.zoho.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('fastmail', array(
    'server' => 'imap.fastmail.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Fastmail',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.fastmail.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('yandex', array(
    'server' => 'imap.yandex.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Yandex',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.yandex.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('inbox', array(
    'server' => 'imap.inbox.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Inbox.com',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'my.inbox.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('gandi', array(
    'server' => 'mail.gandi.net',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Gandi',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'mail.gandi.net',
        'port' => 465,
        'tls' => true
    ),
    'sieve' => array(
        'port' => 4190,
        'host' => 'mail.gandi.net',
        'tls' => true
    )
));

Nux_Quick_Services::add('kolabnow', array(
    'server' => 'imap.kolabnow.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Kolab Now',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.kolabnow.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('all-inkl', array(
    'server' => '.kasserver.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'All-inkl',
    'auth' => 'login',
    'smtp' => array(
        'server' => '.kasserver.com',
        'port' => 465,
        'tls' => true
    )
));

Nux_Quick_Services::add('migadu', array(
    'server' => 'imap.migadu.com',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Migadu',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.migadu.com',
        'port' => 465,
        'tls' => true
    ),
    'sieve' => array(
        'port' => 4190,
        'host' => 'imap.migadu.com',
        'tls' => true
    )
));

Nux_Quick_Services::add('postale', array(
    'server' => 'mail.postale.io',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Postale',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'mail.postale.io',
        'port' => 465,
        'tls' => true
    ),
    'sieve' => array(
        'port' => 4190,
        'host' => 'mail.postale.io',
        'tls' => true
    )
));

Nux_Quick_Services::add('mailbox', array(
    'server' => 'imap.mailbox.org',
    'type' => 'imap',
    'tls' => true,
    'port' => 993,
    'name' => 'Mailbox',
    'auth' => 'login',
    'smtp' => array(
        'server' => 'smtp.mailbox.org',
        'port' => 465,
        'tls' => true
    ),
));
