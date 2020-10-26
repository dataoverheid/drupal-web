# Indicia Profile
Provides the Indicia profile.

## Commands
##### Drush site installation
Once Composer is finished downloading all the required dependencies, you can
do a full site installation using Drush. It's recommended to execute the 
installation command as the web user to avoid file permission issues. Simply 
execute the following command in your terminal to setup your Drupal instance. 

By default, the indicia profile will be used as the installation profile, and
the development environment will be used as the active config split. 

```bash
sudo -u www-data drush si --db-url=mysql://root@localhost/{database}
```

## Installation
```bash
composer require indicia-drupal/profile
```
