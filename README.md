# Slide Summarizer

_Experiment with the new Google Slides API. It will scan your presentation and create a bookmark to slides where the title has changed._

## Setup

### Installing Composer
The first step to setting up this project is to install the Composer package manager for PHP. This can be done using the following script:
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '55d6ead61b29c7bdee5cccfb50076874187bd9f21f65d8991d46ec5cc90518f447387fb9f76ebae1fbbacf329e583e30') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```
### Get Credentials for OAuth 2.0 Service Account
Use the following [tutorial](https://developers.google.com/api-client-library/php/auth/service-accounts) to enable OAuth 2.0 for server-to-server applications. When following these instructions, remember to give the service account scope to the Google Drive and Google Slides APIs.

The necessary code framework to use the OAuth service account is already included in the program, all that you need to add is the `service_account.json` file obtained by following the instructions in the "Creating a service account" section.

After downloading the `service_account.json` file from the Credentials wizard, move the file into the project in the repository's root directory and rename it `service-account-credentials.json` or properly adjust the file path to it in the [`base.php`](web/public/templates/base.php) file. From here, move the project into Apache's document root.

### Install the Google Client Library
Run the following command to install the library using composer:
```
php composer.phar require google/apiclient-services:dev-master
php composer.phar require google/apiclient:^2.0
```

## Usage

To utilize the summarizer, first change the `folderId` variable in [`index.php`](web/public/index.php) to the folderId of the public Google Drive folder containing the presentations you would like to summarize. Then, turn on your web server and navigate to the webpage in your browser of choice. The URL should be formatted as follows:
```
http://localhost/Slide-Summarizer/
```
This is the URL to [`index.php`](web/public/index.php). It will dynamically produce a list of all Google Slides presentations in the folder specified with `folderId`. Clicking on the link corresponding to one of the presentations will take you to the [`slide_reader.php`](web/public/slide_reader.php) file. The format for the [`slide_reader.php`](web/public/slide_reader.php) URL is as follows:
```
http://localhost/Slide-Summarizer/slide_reader.php?presentationId=<presentationId>
```

Where `<presentationId>` is the id of whichever Google Slides presentation you would like to summarize.

[`slide_reader.php`](web/public/slide_reader.php) will dynamically produce a list of all the unique slide titles in the presentation. Clicking on any of these titles will change the iframe's position to the corresponding slide.
