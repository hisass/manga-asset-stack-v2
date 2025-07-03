</main>

<footer class="site-footer py-3 bg-dark">
    <div class="container text-center">
        <span class="text-white-50">Manga Asset Stack v2</span>
    </div>
</footer>

<a href="#" id="page-top-btn" class="page-scroll-btn" title="一番上へ">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5z"/></svg>
</a>
<a href="#" id="page-bottom-btn" class="page-scroll-btn" title="一番下へ">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-down-short" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L7.5 10.293V4.5A.5.5 0 0 1 8 4z"/></svg>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var topBtn = document.getElementById('page-top-btn');
    var bottomBtn = document.getElementById('page-bottom-btn');
    if(topBtn && bottomBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 200) {
                topBtn.classList.add('show');
            } else {
                topBtn.classList.remove('show');
            }
        });
        topBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        bottomBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        });
    }
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    if (themeToggleBtn) {
        const sunIcon = document.getElementById('theme-icon-sun');
        const moonIcon = document.getElementById('theme-icon-moon');
        const htmlEl = document.documentElement;
        const getCurrentTheme = () => {
            return localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        };
        const applyTheme = (theme) => {
            htmlEl.setAttribute('data-bs-theme', theme);
            if (theme === 'dark') {
                sunIcon.classList.add('d-none');
                moonIcon.classList.remove('d-none');
            } else {
                sunIcon.classList.remove('d-none');
                moonIcon.classList.add('d-none');
            }
        };
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlEl.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });
        applyTheme(getCurrentTheme());
    }
});
</script>

</body>
</html>