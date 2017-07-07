<!DOCTYPE html>
<html>

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
  <link rel="stylesheet" href="https://<?php echo(urlencode($_SERVER['SERVER_NAME']));?>/slide_reader.css">
  <link rel="shortcut icon" type="image/x-icon" href="https://<?php echo(urlencode($_SERVER['SERVER_NAME']));?>/favicon.ico" />
  <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
  <script src=" https://<?php echo(urlencode($_SERVER[ 'SERVER_NAME']));?>/vue.js"></script>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" crossorigin="anonymous"></script>
</head>

<body>
  <div id="app">
    <div id="header">
      <a href="https://<?php echo (urlencode($_SERVER['SERVER_NAME']));?>/cse247">
        <div id="return_link">Return to list of presentations</div>
      </a>
      <h1>{{ title }}</h1>
    </div>
    <div id="main" class="body">
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
        axios.get("https://<?php echo(urlencode($_SERVER['SERVER_NAME']));?>/presentation/<?php echo(urlencode($presentationId));?>.json")
          .then(response => {
            this.bookmarks = response.data.bookmarks;
            this.title = response.data.title;
            document.title = this.title;
          });
      }
    });
    // Find all iframes
    var $iframe = $("#slide_frame");
    // Find &#x26; save the aspect ratio for all iframes
    $iframe.data("ratio", 9 / 16)
      // Remove the hardcoded width &#x26; height attributes
      .removeAttr("width")
      .removeAttr("height");

    function resizeIframe() {
      //   if ($(window).width() > 3 * ($("#bookmark_list").width())) {
      //     var width = $(window).width() - 1.4 * $("#bookmark_list").width();
      //     if (width * $iframe.data("ratio") > 0.94 * ($(window).innerHeight() - $("#header").height())) {
      //       width = 0.95 * (($(window).innerHeight() - $("#header").height()) / $iframe.data("ratio"));
      //     }
      //     // $iframe.parent().width(width).height(width * $iframe.data("ratio"));
      //     // $iframe.parent().css("position", "fixed");
      //     $iframe.parent().css("order", "3");

      //     // $("#bookmark_list").css("padding-top", "7em");
      //   } else {
      //     var width = 0.98 * $(window).width();
      //     // $iframe.parent().width(width).height(width * $iframe.data("ratio"));
      //     // $iframe.parent().css("position", "relative");
      //     $iframe.parent().css("order", "1");

      //     // $("#bookmark_list").css("padding-top", "0");
      //   }
    }
    resizeIframe();
    var $last_header_height = 0;

    // function makeMobileFriendly() {
    //   if ($("#header").height() !== $last_header_height) {
    //     $("#bookmark_list").css("padding-top", $("#header").height());
    //     $last_header_height = $("#header").height();
    //   }
    // }
    // makeMobileFriendly();
    // Resize the iframes when the window is resized
    // $(window).resize(resizeIframe).resize(makeMobileFriendly).resize();
  </script>
</body>

</html>