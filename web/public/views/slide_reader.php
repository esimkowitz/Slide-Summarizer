<!DOCTYPE html>
<html>
<?php include 'templates/base.php'; ?>

  <head>
    <title>
      Loading bookmarks
    </title>
    <meta charset="utf-8">
    <meta name="name" content="Slide Summarizer">
    <meta name="author" content="Evan Simkowitz">
    <meta name="keywords" content="Google,Slides,Summarizer,PHP,Slide">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Summarizes long Google Slides presentations into a list of bookmarks." />
    <link rel="stylesheet" href="<?php echo(url());?>/slide_reader.css">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo(url());?>/favicon.ico" />
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <?php if (getenv("BRANCH") === "development"): ?>
      <script src="<?php echo(url());?>/vue.js"></script>
      <?php else: ?>
      <script src="https://unpkg.com/vue"></script>
      <?php endif; ?>
  </head>

  <body>
    <div id="app">
      <div id="header">
        <a href="<?php echo(url());?>">
          <div id="return_link">Return to list of presentations</div>
        </a>
        <h1>{{ title }}</h1>
      </div>
      <div class="body_container">
        <div class="body">
          <div id="bookmarks" class="sidebar">
            <ul id="bookmark_list">
              <li v-for="bookmark in bookmarks" class="box">
                <a href="#" :id="bookmark[1]" @click.prevent="pageId = bookmark[1]">{{ bookmark[0] }}</a>
              </li>
              <div id="sidebar_padding"></div>
            </ul>
          </div>
          <div id="slide_frame_div" class="content">
            <div class="aspect-ratio aspect-ratio-16-9">
              <iframe id="slide_frame" :src="'https://docs.google.com/presentation/d/<?php echo urlencode($presentationId);?>/embed?start=false&loop=false&delayms=3000&slide=id.' + pageId"></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      const vm = new Vue({
        el: '#app',
        data: {
          bookmarks: [],
          pageId: "p",
          title: "Loading bookmarks"
        },
        methods: {
          changeCurrentPage: function(newPageId) {
            document.getElementById("slide_frame").contentWindow.location.replace("https://docs.google.com/presentation/d/<?php echo urlencode($presentationId);?>/embed?start=false&loop=false&delayms=3000&slide=id." + newPageId);
          }
        },
        mounted() {
          axios.get("<?php echo(url());?>/presentation/<?php echo(urlencode($presentationId));?>.json")
            .then(response => {
              this.bookmarks = response.data.bookmarks;
              this.title = response.data.title;
              document.title = this.title;
            });
        }
      });
    </script>
  </body>

</html>