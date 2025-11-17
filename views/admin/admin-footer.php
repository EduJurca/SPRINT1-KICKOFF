            </div>
        </main>
    </div>

    <script>
        (function (d) {
            var s = d.createElement("script");
            s.setAttribute("data-account", "RrwQjeYdrh");
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
    
    <script>
        // Dashboard tabs functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    
                    // Ocultar todos los tabs
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Desactivar todos los botones
                    tabButtons.forEach(btn => {
                        btn.classList.remove('bg-blue-900', 'text-white');
                        btn.classList.add('text-gray-600');
                    });
                    
                    // Mostrar tab seleccionado
                    const selectedTab = document.getElementById('tab-' + tabName);
                    if (selectedTab) {
                        selectedTab.classList.remove('hidden');
                    }
                    
                    // Activar botón seleccionado
                    this.classList.remove('text-gray-600');
                    this.classList.add('bg-blue-900', 'text-white');
                });
            });
        });
    </script>
    
    <script src="/assets/js/admin.js"></script>
</body>
</html>
