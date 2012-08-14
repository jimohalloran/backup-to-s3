Backup To Amazon S3
===================

This script can be used to back up virtually any LAMP application to Amazon S3.  It performs a mysqldump on one or more databases, and backs up files from one or more folders.  It is inspired by the COPIOUS Magento Backup script at https://github.com/copious/magento_backup

Prerequisites
-------------

PHP 5.3 or later is required to run this backup script.  Namespaces are used for the core classes, and composer ships as a Phar Archive.

The prerequisite libraries for this project are installed via composer.  Once you've cloned this git repository, use composer to install them:
    
    $ php bin/composer.phar install


Running Backups
---------------

To use it, drop your authentication details into a `backup.yml` file as follows:

    name: myproj  # Used to name the tarball we upload to S3
    amazon:
      bucket: my_bucket_name   # Must be created manually
      secret_access_key: 'secret key here'
      access_key_id: access_key_here
    database:  # Backup a database with mysqldump
      -        # Repeat this block if you need to back up multiple databases.
        name: main   # Used to identify the database when creating filenames,
        username: mysql_username
        password: 'mysql_password'
        hostname: localhost
        database: name_of_database
        touch: /wwwroot/myproj/maintenance.flag  # (optional) Create a file while the database backup is performed and delete it afterwards.  Can be used to put magento into maintenance mode.
    files: # Backup a foilder including all files and subfolders.
      -    # Repeat this block if you want to back up multiple filder.
        name: assets      # Used to identify this path when creating backjup folder,
        path: /wwwroot/myproj/media

Once it's configured, run the backup:

		$ php backup.php

In the grand Unix tradition, the script will produce no output on success,  If an error occurs, Error messages will be displayed and the script will return a non 0 status.

If multiple .yml configuration files exist, you can select which one to use from the command line:

		$ php backup.php --config otherconfig.yml

The script utilises the multipart upload capabilities of the AWS SDK to overcome the 5Gb POST size limit.

Restoring
---------

To restore from a backup, you'll first need to download from Amazon S3 the backup tarball you intend to restore.  The S3 web client at http://aws.amazon.com/ is the easiest way to do this.  Then you'll need to extract the archive using tar and gzip:

	  $ tar zxvf my-backup.tgz

This will create a directory containing the assets, and database dump as originally backed up..
