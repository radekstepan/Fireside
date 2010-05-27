# uncomment the next line to protect an SQLite database
# CREATE TABLE '<?php' (id INTEGER PRIMARY KEY)
CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, password TEXT, name TEXT, email TEXT, layout TEXT);
CREATE TABLE topics (id INTEGER PRIMARY KEY, name TEXT, is_archived INTEGER, created_at TEXT, updated_at TEXT, created_by TEXT, updated_by TEXT);
CREATE TABLE messages (id INTEGER PRIMARY KEY, topics_id INTEGER, child_id INTEGER, name TEXT, user TEXT, created_at TEXT, read_by TEXT);
CREATE TABLE nodes (id INTEGER PRIMARY KEY, topics_id INTEGER, messages_id INTEGER, child_id INTEGER, text TEXT, user TEXT);