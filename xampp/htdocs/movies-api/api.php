<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

// Función para registrar errores
function logError($message)
{
    error_log(date('Y-m-d H:i:s') . " - Error: " . $message . "\n", 3, "api_errors.log");
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($method) {
        case 'GET':
            if ($action === 'movies') {
                // Verificar si hay películas en la base de datos
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM api_movies");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['count'] == 0) {
                    // Si no hay películas, obtener de TMDB
                    $tmdbMovies = fetchTMDBMovies();
                    if (!$tmdbMovies['success']) {
                        throw new Exception('Error fetching TMDB movies: ' . $tmdbMovies['error']);
                    }
                }

                // Obtener todas las películas
                getMovies();
            }
            break;

        case 'POST':
            if ($action === 'add') {
                addMovie();
            }
            break;
    }
} catch (Exception $e) {
    logError($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function fetchTMDBMovies()
{
    global $pdo;

    try {
        $bearer_token = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJiZjc0NjU5ODQ2OGIzNzE2NjU3ZmU1NDg0MDdhMGU4NiIsIm5iZiI6MTczMDA0MjE5OC4xNzk3MjIsInN1YiI6IjVmOTU1ZmJjMGZiMTdmMDA0YzM3ZWJiZSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.oW8uolgUiZkDkushB7NpDQu2C5nfNihBpDSPNLVW58c';

        $url = "https://api.themoviedb.org/3/movie/popular?language=es-ES&page=1";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $bearer_token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('TMDB API returned status code: ' . $httpCode);
        }

        $data = json_decode($response, true);

        if (!isset($data['results'])) {
            throw new Exception('Invalid TMDB API response structure');
        }

        // Guardar películas en la base de datos
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO api_movies 
            (id, title, overview, release_date, poster_path) 
            VALUES 
            (:id, :title, :overview, :release_date, :poster_path)
        ");

        foreach ($data['results'] as $movie) {
            $stmt->execute([
                ':id' => $movie['id'],
                ':title' => $movie['title'],
                ':overview' => $movie['overview'],
                ':release_date' => $movie['release_date'],
                ':poster_path' => $movie['poster_path']
            ]);
        }

        return [
            'success' => true,
            'data' => $data['results']
        ];
    } catch (Exception $e) {
        logError("Error in fetchTMDBMovies: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function getMovies()
{
    global $pdo;

    try {
        $stmt = $pdo->query("
            SELECT 
                CONCAT(
                    CASE WHEN m.is_custom = 1 THEN 'custom_' ELSE 'api_' END,
                    m.id
                ) as unique_id,
                m.id,
                m.title,
                m.overview,
                m.release_date,
                m.poster_path,
                m.is_custom
            FROM (
                SELECT id, title, overview, release_date, poster_path, 1 as is_custom
                FROM movies
                UNION ALL
                SELECT id, title, overview, release_date, poster_path, 0 as is_custom
                FROM api_movies
            ) as m
            ORDER BY m.release_date DESC  -- Ordenar por fecha de lanzamiento
        ");


        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $movies
        ]);
    } catch (Exception $e) {
        logError("Error in getMovies: " . $e->getMessage());
        throw $e;
    }
}

function addMovie()
{
    global $pdo;

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['title'])) {
            throw new Exception('Title is required');
        }

        $stmt = $pdo->prepare("
            INSERT INTO movies (title, overview, release_date, poster_path)
            VALUES (:title, :overview, :release_date, :poster_path)
        ");

        $stmt->execute([
            ':title' => $data['title'],
            ':overview' => $data['overview'] ?? '',
            ':release_date' => $data['release_date'] ?? null,
            ':poster_path' => $data['poster_path'] ?? ''
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Movie added successfully'
        ]);
    } catch (Exception $e) {
        logError("Error in addMovie: " . $e->getMessage());
        throw $e;
    }
}
