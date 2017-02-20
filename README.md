# Slide Summarizer

_Experiment with the new Google Slides API. It will scan your presentation and create a bookmark to slides where the title has changed._

## Setup

People wishing to use this project will first need to follow the instructions to [get the Google Slides API working with PHP](https://developers.google.com/slides/quickstart/php) and then to [enable OAuth 2.0 access for web apps](https://developers.google.com/api-client-library/php/auth/web-app#top_of_page).

### Installing Composer
The first step to setting up this project is to install the Composer package manager for PHP. This can be done using the following script:
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '55d6ead61b29c7bdee5cccfb50076874187bd9f21f65d8991d46ec5cc90518f447387fb9f76ebae1fbbacf329e583e30') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```
### Get Credentials for OAuth 2.0 Login



After downloading the client_secret.json file from the Credentials wizard, move the file into the project and rename it client_secret2.json. From here, move the project into Apache or Nginx's document root.

## Usage

To utilize the summarizer, turn on your web server and navigate to the webpage in your browser of choice. The URL should be formatted as follows:

```
http://localhost:8888/Slide-Summarizer/slide_reader.php?presentationId=<presentationId>
```

Where presentationId is the id of whichever Google Slides presentation you would like to summarize.

The program will dynamically produce a list of all the unique slide titles in the presentation. Clicking on any of these titles will change the iframe's position to the corresponding slide.
