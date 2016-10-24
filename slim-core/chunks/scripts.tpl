<script>
    document.body.classList.add('loading');
    var lz = document.getElementsByClassName("lazy"), lzl = lz.length;
    for(var i=0;i<lzl;i++){
        var img = lz[i].getAttribute("data-src");
        lz[i].setAttribute("src",img);
    }
    var lzbg = document.getElementsByClassName("lazy-bg"),lzbgl = lzbg.length;
    for(var i=0;i<lzbgl;i++){
        var img = lzbg[i].getAttribute("data-src");
        lzbg[i].setAttribute("style","background-image: url(" + img + ");");
    }
    window.onload = function() {
        document.body.classList.remove('loading');
    }
</script>
