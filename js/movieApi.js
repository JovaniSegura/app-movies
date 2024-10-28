const API_URL = 'http://localhost/movies-api/api.php';

const movieApi = {
    async getMovies(page = 1) {
        try {
            const response = await fetch(`${API_URL}?action=movies&page=${page}`);
            const data = await response.json();
            return {
                success: true,
                results: data.data || [],
                total_pages: data.total_pages || 1,
                page: page
            };
        } catch (error) {
            console.error('Error fetching movies:', error);
            return {
                success: false,
                results: [],
                total_pages: 1,
                page: page,
                error: 'Error al obtener las películas'
            };
        }
    },

    async addMovie(movie) {
        try {
            const response = await fetch(`${API_URL}?action=add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(movie)
            });
            const data = await response.json();
            return {
                success: true,
                results: data.data || [],
                total_pages: data.total_pages || 1,
                page: 1
            };
        } catch (error) {
            console.error('Error adding movie:', error);
            return {
                success: false,
                results: [],
                total_pages: 1,
                page: 1,
                error: 'Error al agregar la película'
            };
        }
    }
};