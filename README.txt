REQUIREMENTS
===================
1. Storytlr

It's a plugin for Storytlr open.  To get it, download and install Storytlr from storytlr.org

2. SimplePie 1.1.3

- Download SimplePie 1.1.3
- Rename simplepie.inc to SimplePie.php
- Drop SimplePie.php into ./protected/library
- Edit ./protected/application/Bootstrap.php
	113:	error_reporting(E_ALL & ~E_STRICT);
- Edit ./protected/config/config.ini
	31:	debug = 0

INSTALLATION
===================
1. rename folder to visualizeus
2. move this folder into to ./protected/application/plugins
3. update storytlr database
   mysql -u [username] -p [storytlr database] < ./protected/application/plugins/visualizeus/DB.sql

KNOWN ISSUES
===================
- SimplePie 1.2 has a flaw that duplicates entries.  feel free to try it out at your own risk, but I'm sticking with 1.1.3