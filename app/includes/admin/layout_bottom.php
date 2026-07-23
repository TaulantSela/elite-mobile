        </main>
    </div>
</div>
<script>
    document.addEventListener('click', function (event) {
        if (document.body.classList.contains('nav-open')
            && !event.target.closest('.admin-sidebar')
            && !event.target.closest('.admin-menu-btn')) {
            document.body.classList.remove('nav-open');
        }
    });
</script>
</body>
</html>
