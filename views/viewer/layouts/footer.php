</main>
<footer class="footer mt-auto py-3 bg-dark footer-fixed">
    <div class="container text-center">
        <span class="text-white-50">Manga Asset Stack v2</span>
    </div>
</footer>

<a href="#" id="page-top-btn" class="page-scroll-btn" title="一番上へ">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5z"/>
    </svg>
</a>
<a href="#" id="page-bottom-btn" class="page-scroll-btn" title="一番下へ">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-down-short" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L7.5 10.293V4.5A.5.5 0 0 1 8 4z"/>
    </svg>
</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var topBtn = document.getElementById('page-top-btn');
    var bottomBtn = document.getElementById('page-bottom-btn');

    // 「一番上へ」ボタンの表示/非表示を制御
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 200) { // 200pxスクロールしたら表示
            topBtn.classList.add('show');
        } else {
            topBtn.classList.remove('show');
        }
    });

    // 「一番上へ」ボタンのクリックイベント
    topBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // 「一番下へ」ボタンのクリックイベント
    bottomBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    });
});
</script>
</body>
</html>