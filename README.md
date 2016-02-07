# Pastebin

This is a really, really simple PHP pastebin app, which has powered ![Bin 6 It](http://bin6.it/) (IPv6 Only Pastebin) for the past 5 years without issue or spam abuse. 

## Features

- Private pastes
- Paste pruning (1 year since last viewed)
- Custom paste URLs
- Syntax highlighting for hundreds of file types
- Reply to pastes easily
- Functionality to make it hard for spambots to post

## Requirements

- PHP 5.3
- MySQL 5 
- Apache (clean URLs via the .htaccess file)

## Instructions

Load the schema.sql file into your database

    mysql bin6 < schema.sql

Configure database settings accordingly in config.php.sample and rename to config.php

    cp config.php.sample config.php
    vi config.php

Untar the geshi folder, for syntax highighting support. Should create a folder called "geshi" in the same directory as this file.

    tar xJvf geshi.tar.xz


## Credits

- Ryan Rawdon <ryan@u13.net> for ipv6-only pastebin idea and hosting
- Joe Gillotti <joe@u13.net> for coding

(c) 2010 PuttyNuts Web Services
We release this under the terms of the GPL
