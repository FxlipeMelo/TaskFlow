document.addEventListener('turbo:load', function() {

    const searchInput = document.getElementById('searchInput');
    const taskItems = document.querySelectorAll('.task-item'); // Lembre-se que você colocou essa classe na div da coluna!

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();

            taskItems.forEach(function(item) {
                const title = item.querySelector('.card-title').textContent.toLowerCase();
                const category = item.querySelector('.card-subtitle').textContent.toLowerCase();

                if (title.includes(searchTerm) || category.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
