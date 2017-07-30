const vm = new Vue({
    el: '#app',
    data: {
        results: [],
        pageId: 0
    },
    changeActivePage(pageId) {
        document.getElementById("slide_frame").contentWindow.location.replace("https://docs.google.com/presentation/d/<?php echo urlencode($presentationId);?>/embed?start=false&loop=false&delayms=3000&slide=id." + clickedItem);
    },
    mounted() {
        axios.get("https://api.nytimes.com/svc/topstories/v2/home.json?api-key=your_api_key")
            .then(response => { this.results = response.data.results })
    }
});