[Signifyd Extension for Magento 2](../README.md) > Install Troubleshoot

# Install Troubleshoot

## Third-party cache errors

If something does not go as expected, try to clear any additional caches on the environment (e.g. PHP APC or OPCache, Redis, Varnish).

## There is no "SIGNIFYD" session on System > Configuration

Check if extension it is enabled, by running below command line on terminal:

```bash
cd MAGENTO_ROOT
bin/magento module:status Signifyd_Connect
```

If module is disabled, enabled it using below command line:

```bash
cd MAGENTO_ROOT
bin/magento module:enable Signifyd_Connect
```

If module does not exists, check if below directories exists:
- MAGENTO_ROOT/vendor/signifyd/module-connect
- MAGENTO_ROOT/vendor/signifyd/signifyd-php

If the above files are not present, please repeat the installation steps.

## Logs show database related errors

On the MySQL database check for the existence of 'signifyd_connect_case' table using below command:

```
DESC signifyd_connect_case
```

Verify you see the following columns on the table:
- order_increment
- signifyd_status
- code
- score
- guarantee
- entries_text
- created
- updated
- magento_status
- retries

If you find any missing columns or issues with the table, check if the Magento installation scripts has been ran for the latest version.  

First locate etc/module.xml file on one of these locations:

- For composer installations: MAGENTO_ROOT/vendor/signifyd/module-connect/etc/module.xml
- For manual installations: MAGENTO_ROOT/app/code/Signifyd/Connect/etc/module.xml

On file etc/module.xml check for `setup_version` property on `<module>` tag. 

```
<module name="Signifyd_Connect" setup_version="3.1.1">
    <sequence>
        <module name="Magento_Sales" />
        <module name="Magento_Payment" />
        <module name="Magento_Directory" />
        <module name="Magento_Config" />
    </sequence>
</module>
```

Run below SQL command on MySQL:

```
SELECT * FROM setup_module WHERE module='Signifyd_Connect';
```

The results of the above command should match with `setup_version` property from module.xml file. If does not match, run installation steps again and make sure to clean every possible cache on Magento administration and environment.

## Database integrity check

Version 2.4.1+  will check for all database structures needed for the extension to work correctly. This check is performed on the extension configuration section in the Magento admin.

If any database structures are missing, the extension will be disabled.

If there are warnings on Magento admin, on the extension configuration section, about missing database modifications after install/update, follow the instructions below to fix the issue.

This script will create all of the necessary structures. You will need to run it directly on your MySQL database. If there are any 'duplicate column' errors during this script execution, they can be ignored.

```mysql
CREATE TABLE IF NOT EXISTS `signifyd_connect_case1` (
  `order_increment` varchar(255) NOT NULL COMMENT 'Order ID',
  `signifyd_status` varchar(255) NOT NULL DEFAULT 'PENDING' COMMENT 'Signifyd Status',
  `code` varchar(255) NOT NULL COMMENT 'Code',
  `score` float DEFAULT NULL COMMENT 'Score',
  `guarantee` varchar(64) NOT NULL DEFAULT 'N/A' COMMENT 'Guarantee Status',
  `entries_text` text NOT NULL COMMENT 'Entries',
  `created` timestamp NULL DEFAULT NULL COMMENT 'Creation Time',
  `updated` timestamp NULL DEFAULT NULL COMMENT 'Update Time',
  `magento_status` varchar(255) NOT NULL DEFAULT 'waiting_submission' COMMENT 'Magento Status',
  `retries` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of retries for current case magento_status',
  PRIMARY KEY (`order_increment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Signifyd Cases';

CREATE TABLE IF NOT EXISTS `signifyd_connect_retries` (
  `order_increment` varchar(255) NOT NULL COMMENT 'Order ID',
  `created` timestamp NULL DEFAULT NULL COMMENT 'Creation Time',
  `updated` timestamp NULL DEFAULT NULL COMMENT 'Last Attempt',
  PRIMARY KEY (`order_increment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Signifyd Retries';

ALTER TABLE sales_order ADD COLUMN signifyd_score FLOAT DEFAULT NULL;
ALTER TABLE sales_order ADD COLUMN signifyd_guarantee VARCHAR(64) NOT NULL DEFAULT 'N/A';
ALTER TABLE sales_order ADD COLUMN signifyd_code VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE sales_order ADD COLUMN origin_store_code VARCHAR(32) DEFAULT NULL;

ALTER TABLE sales_order_grid ADD COLUMN signifyd_score FLOAT DEFAULT NULL;
ALTER TABLE sales_order_grid ADD COLUMN signifyd_guarantee VARCHAR(64) NOT NULL DEFAULT 'N/A';
ALTER TABLE sales_order_grid ADD COLUMN signifyd_code VARCHAR(255) NOT NULL DEFAULT '';
```

After running the script, run the command below in terminal in order to update caches.

```bash
bin/magento setup:upgrade
```

If there still warnings about missing database modifications, please, contact our support. 

## Purge all Signifyd data

If you are having issues with the install you can remove all Signifyd data on the Magento database for a clean re-install.

**All Signifyd data on Magento database will be lost.**

```mysql
DROP TABLE signifyd_connect_case;

DROP TABLE signifyd_connect_retries;

ALTER TABLE sales_order DROP COLUMN signifyd_score;
ALTER TABLE sales_order DROP COLUMN signifyd_guarantee;
ALTER TABLE sales_order DROP COLUMN signifyd_code;
ALTER TABLE sales_order DROP COLUMN origin_store_code;

ALTER TABLE sales_order_grid DROP COLUMN signifyd_score;
ALTER TABLE sales_order_grid DROP COLUMN signifyd_guarantee;
ALTER TABLE sales_order_grid DROP COLUMN signifyd_code;

DELETE FROM setup_module WHERE module='Signifyd_Connect';
```

## All of the steps were followed but some error prevented the extension from installing succesfully

Check for any log errors on the web server (e.g. Apache, NGINX) and on PHP logs. Also check for errors on MAGENTO_ROOT/var/log on files system.log, exception.log and signifyd_connect.log. If you are still stuck you can [contact our support team](https://community.signifyd.com/support/s/)