# Data.overheid Drupal
This repository contains the Drupal installation which is used for
[data.overheid.nl](https://data.overheid.nl/). 

## Requirements
- A working LAMP/LEMP stack (Linux, Apache/NGINX, MySQL, PHP)
- Composer (https://getcomposer.org)
- NPM installed globally (https://www.npmjs.com/get-npm)
- Gulp installed globally (https://gulpjs.com)

See https://www.drupal.org/docs/system-requirements for details about Drupal's
requirements and the composer.json file for more specific version requirements
on this project.

## Setting up a new project

### 1. Download all requirements with composer
Download the latest source code and open a terminal in the root directory. Using
the following composer command you can download all the required libraries for
this project.

```bash
composer install
```

### 2. Site installation
Once Composer is finished downloading all the required dependencies, you can
do a full site installation using Drush.

```bash
drush si --db-url=mysql://{db_user}:{db_pass}@localhost/{db_database_name} --account-name={username} --account-pass={user_pass}  --site-name='Data overheid'
```

### 3. Config import
The final step of setting up your Drupal instance requires you to do a
configuration import. The initial configuration import should be run twice to
make sure that the environment specific configuration is also imported.

```bash
drush cim -y
drush cim -y
drush config-set system.site page.front /home
```

## Updating an existing project
To update an existing project to the latest version you should checkout the
latest source code and run the following commands.

```bash
composer install
drush updb -y
drush cim -y
drush cr
```

## Installing the theme

To generate the theme assets. Navigate to the theme folder, install the required
npm modules and run gulp to compile the source files.

```bash
cd drupal/themes/custom/koop_overheid
npm install
gulp build
```
