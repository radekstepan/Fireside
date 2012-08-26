# Fireside (May 2010)

It's a mix of GMail, Google Wave and a chat app

![image](https://github.com/radekstepan/Fireside/raw/master/example.png)

Hate replying to long emails, constantly scrolling back and forth between the mail and a reply? Would you like to version your conversation in a chat and allow it to go offline?

This quick app serves as a messenger program that creates nodes out of text you write. Each node can then be replied to. When you hit reply, your message is mailed through GMail and when the recipient visits your site, she can see the text of her original message and the sender's reply.

## Getting started

The app has been tested to work on PHP 5.4.4 on Mac OS X Lion through [MAMP](http://www.mamp.info/en/index.html).

Point your browser to the directory with the app. You will be asked to authorize.

The authentication and autorization relies on `Fari_AuthenticatorSimple` which uses `sha1()` to match passwords. By default, the default credentials are `admin:admin`. Use a tool such as [this](http://sha1-hash-online.waraxe.us/) to generate your own hash and modify the table `users` in the database file which is `db/database.php` by default and is of type SQLite3.

Edit `config/config.php` with SMTP server credentials to get emails in addition to the messages being exchanged on the site.

## Debugging

Fari Framework automatically understands that you are in development mode, if you call the app from `127.0.0.1`. Do so to see a stacktrace of where an error has happened instead of seeing a placeholder error message.