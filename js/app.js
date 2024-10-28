document.addEventListener('DOMContentLoaded', () => {
    const movieList = document.getElementById('movieList');
    const loading = document.getElementById('loading');
    const addMovieBtn = document.getElementById('addMovieBtn');
    const exportBtn = document.getElementById('exportBtn');
    const modal = new MovieModal();
    
    let page = 1;
    let hasMore = true;
    let isLoading = false;
    let movies = [];

    async function fetchMovies() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        loading.classList.remove('hidden');
        
        try {
            const response = await movieApi.getMovies(page);
            if (response.success) {
                const sortedMovies = response.results.sort(
                    (a, b) => new Date(b.release_date).getTime() - new Date(a.release_date).getTime()
                );
                
                movies = page === 1 ? sortedMovies : [...movies, ...sortedMovies];
                hasMore = page < response.total_pages;
                
                renderMovies();
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            isLoading = false;
            loading.classList.add('hidden');
        }
    }

    function renderMovies() {
        movieList.innerHTML = '';
        movies.forEach(movie => {
            movieList.appendChild(createMovieCard(movie));
        });
    }

    async function handleAddMovie(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const movieData = Object.fromEntries(formData);
        
        try {
            const response = await movieApi.addMovie(movieData);
            if (response.success) {
                page = 1;
                movies = [];
                await fetchMovies();
                modal.close();
            }
        } catch (error) {
            console.error('Error adding movie:', error);
        }
    }

    function handleExportToExcel() {
        const worksheet = XLSX.utils.json_to_sheet(
            movies.map(movie => ({
                Título: movie.title,
                Año: new Date(movie.release_date).getFullYear(),
                Descripción: movie.overview,
            }))
        );
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Películas');
        XLSX.writeFile(workbook, 'peliculas.xlsx');
    }

    // Infinite scroll
    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && hasMore && !isLoading) {
            page++;
            fetchMovies();
        }
    });

    // Event listeners
    addMovieBtn.addEventListener('click', () => modal.open());
    modal.form.addEventListener('submit', handleAddMovie);
    exportBtn.addEventListener('click', handleExportToExcel);

    // Initial load
    fetchMovies();
});