            </div>
        </main>
    </div>

    <script>
        (function (d) {
            var s = d.createElement("script");
            s.setAttribute("data-account","<?php echo getenv('USERWAY_ACCOUNT_ID'); ?>");
            s.src = "https://cdn.userway.org/widget.js";
            (d.body || d.head).appendChild(s);
        })(document);
    </script>
    <style>
        [class*="userway"], [id*="userway"] {
            position: fixed !important;
            bottom: 20px !important;
            right: 20px !important;
            top: auto !important;
            left: auto !important;
            z-index: 99999 !important;
        }
    </style>

    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/accessibility.js"></script>
    
    <script src="/assets/js/admin.js"></script>

</body>
</html>
