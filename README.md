# Slide Summarizer

_Experiment with the new Google Slides API. It will scan your presentation and create a bookmark to slides where the title has changed._

## Setup

People wishing to use this project will first need to follow [the instructions](https://developers.google.com/api-client-library/php/auth/web-app#top_of_page) to enable OAuth 2.0 access.

After downloading the client_secret.json file from the Credentials wizard, move the file into the project and rename it client_secret2.json. From here, move the project into Apache or Nginx's document root.

## Usage

To utilize the summarizer, turn on your web server and navigate to the webpage in your browser of choice. The URL should be formatted as follows:

```
http://localhost:8888/Slide-Summarizer/slide_reader.php?<presentationId>
```

Where presentationId is the id of whichever Google Slides presentation you would like to summarize.

The program will dynamically produce a list of all the unique slide titles in the presentation. Clicking on any of these titles will change the iframe's position to the corresponding slide.
