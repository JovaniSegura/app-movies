function createMovieCard(movie) {
    const imageUrl = movie.poster_path?.startsWith('http')
        ? movie.poster_path
        : `https://image.tmdb.org/t/p/w500${movie.poster_path}`;

    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-lg overflow-hidden transition-transform hover:scale-105';
    card.innerHTML = `
        <img
            src="${imageUrl}"
            alt="${movie.title}"
            class="w-full h-96 object-cover"
            onerror="this.src='/placeholder-movie.jpg'"
        />
        <div class="p-4">
            <div class="flex justify-between items-start">
                <h3 class="text-xl font-bold mb-2">${movie.title}</h3>
            </div>
            <p class="text-gray-600 mb-2">
                ${movie.release_date ? new Date(movie.release_date).getFullYear() : 'Año no disponible'}
            </p>
            <p class="text-gray-700 line-clamp-3">${movie.overview}</p>
        </div>
    `;
    return card;
}
