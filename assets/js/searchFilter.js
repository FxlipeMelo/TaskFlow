document.addEventListener('turbo:load', function() {

    const searchInput = document.getElementById('searchInput');
    const items = document.querySelectorAll('.task-item, tbody tr');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();

            items.forEach(function(item) {

                if (item.querySelector('td[colspan]')) return;
                const content = item.textContent.toLowerCase();

                if (content.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
