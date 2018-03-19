
                            AKKAR 2.4
                  http://akkar.sourceforge.net/

###############################################################################

SYSTEM REQUIREMENTS

PHP 4.3 or newer (http://www.php.net/)
Required extensions: zlib, gd2, mysql
Recommended extensions: exif, mbstring, iconv
Recommended compile-options: --enable-calendar

MySQL 3.23 or newer
http://www.mysql.com/

A webserver
Apache is recommended - http://www.apache.org/

A W3C standards-compliant JavaScript-enabled and cookie-enabled web browser 
Firefox is recommended - http://www.mozilla.com/firefox/

AKKAR has also been found to work with PHP 5.x and MySQL 4.x. MySQL 5.x support
is not perfected yet, so use at your own risk.

###############################################################################

IMPORTANT FOR WINDOWS INSTALLATIONS

- During the installation of PHP on Windows XP using the Apache webserver, the
following php-libraries need to be added:
  extension=php_mbstring.dll
  extension=php_exif.dll
  extension=php_gd2.dll
  extension=php_mysql.dll
It's also important that php_mbstring.dll is loaded before php_exif or you'll
get an error in Apache. They're loaded in a different order in the php.ini that
ships with PHP.

- Additionally, the post at
http://akkar.sourceforge.net/forum/index.php?topic=41.0 should be examined if
you're running PHP on a Windows platform.

###############################################################################

SETTING UP THE DATABASE AND USER

Setting up the database and the user account for AKKAR is pretty straight 
forward, but if you have no experience with MySQL, are managing your own server
(e.g. not using a webhost) and have no graphical tool to help you set up the 
account, this is how you do it.

First of all, you need to start the command-line MySQL client and log in with
an account that has admin privileges - this is usually the user "root", and
by default this user has no password - though you REALLY should change that,
but I won't cover those steps here as that is normally covered in every "how
to install MySQL" guide out there.

So first, log in with the command-line client, as such:
mysql -u root -p
Or, if you don't have a password for root:
mysql -u root

On the "mysql>" prompt, enter the following to create the database (substitute 
"nameofmydatabase" with the actual name you will be using):
CREATE DATABASE nameofmydatabase;

Then, to create the user that AKKAR will access the db as, and give it the
necessary access-rights, enter these commands (substitute "nameofmydatabase"
with the one you used above, and substitute "akkarusername" and "akkarpassword"
with the values you want to use as username and password - yes, I like stating
the obvious :P):

GRANT ALL PRIVILEGES ON nameofmydatabase.* TO 'akkarusername'@'localhost' IDENTIFIED BY 'akkarpassword' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON nameofmydatabase.* TO 'akkarusername'@'%' IDENTIFIED BY 'akkarpassword' WITH GRANT OPTION;

In most cases you can ignore the second of these two lines, but if the 
installation complains about access denied, enter the second one as well.

After having entered either or any of the two lines, enter the following to
activate the changed immediately:
FLUSH PRIVILEGES;

You can then close the MySQL command line client by simply typing "EXIT".

You should now be ready to perform the actual installation of AKKAR.

###############################################################################

INSTALLING AKKAR

1. Unpack the AKKAR archive:
Linux: tar fzxv akkar-x.y.z.tar.gz
Windows: use WinZip or similar

2. Create the directory where you want AKKAR to live.

3. Copy everything from the akkar subdirectory to the directory you created

4. Make sure the following directories are writeable by the webserver:
filesystem/
images/personer/
tmp/
conf/

5. For security reasons, the webserver should disallow read-access for clients
to the following directories:
filesystem/
tmp/
conf/
All the files herein are loaded by PHP scripts, so the users never need direct
access to the files for AKKAR to work. If you're running Apache, access to 
these directories will be handled automatically by supplied .htaccess-files.

6. Open your browser and point it to install.php in the directory where you
installed AKKAR (example http://www.example.com/akkar/install.php). Fill out 
the form and click "Continue".

7. If AKKAR is unable to create the basic configuration-file you'll be notified
and given a chance to download it. You will then have to place it in the conf/
directory in the main AKKAR directory.

8. Delete install.php and upgrade.php from the directory where you installed 
AKKAR.

9. Open your browser and go to where you installed AKKAR. Log in using the
username and password you supplied in the installation.

10. Go to the Configuration-screen under the Admin section and complete the
rest of the AKKAR configuration.

That's it! AKKAR should now be online :)

###############################################################################

UPGRADING FROM AKKAR 2.2.x

Upgrading from a previous 2.2 release is quite trivial. Simply do the 
following:

1. Unpack the AKKAR archive

2. Copy everything from the akkar subdirectory into the directory where your 
installation resides overwriting all existing files.

3. Open your browser and go to where you have AKKAR installed. Click the 
"Upgrade" button.

4. Delete upgrade.php and install.php from the AKKAR directory.

That's all :)

###############################################################################

UPGRADING FROM RASLAV 2.0.x

If you're upgrading from a version of RASLAV (The 2.0 series of AKKAR) the
steps you need to take are as follows:

1. Backup your data. If something goes wrong during the upgrade it might ruin
everything you've stored so far. Since RASLAV only allowed database backups
from within the system you need to back up any files in the images/personer/
and in the filesystem manually.

2. Unpack AKKAR 2.2 and replace all files in the akkar subdirectory with your
current installation. If you want to make sure your new installation is clean,
you can also delete everything in the old installation except the
images/personer/ directory, the conf/ directory and your filesystem directory.
Several files have been moved and/or removed between 2.0 and 2.2, so if you
don't delete anything you'll end up with several redundant files. It will
however not cause any problems other than taking up more diskspace than what's
necessary.

3. Point your browser to the upgrade.php file in the AKKAR installation
(example http://www.example.com/akkar/upgrade.php) and follow the
onscreen instructions.

4. Once you're done, make sure both upgrade.php and install.php are deleted 
from your installation directory. AKKAR will not operate if any of the two 
files exist.

That's it. AKKAR should now be operational :)

###############################################################################
