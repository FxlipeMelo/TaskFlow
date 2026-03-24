document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');

    if (searchInput) {
        const taskCards = document.querySelectorAll('.col-12.col-md-6.col-lg-4');

        searchInput.addEventListener('input', function () {
            const searchQuery = searchInput.value.toLowerCase();

            taskCards.forEach(function (card) {
                const taskTitle = card.querySelector('.card-title').textContent.toLowerCase();

                if (taskTitle.includes(searchQuery)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
