```
Base de datos SQL

-- Crear tabla para películas personalizadas
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    overview TEXT,
    release_date DATE,
    poster_path TEXT,
    is_custom BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla para películas de la API
CREATE TABLE api_movies (
    id INT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    overview TEXT,
    release_date DATE,
    poster_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

[Click aquí para ver el video de la web en funcionamiento](https://drive.google.com/file/d/1R8-SJzu2wIaaAw7BNz8FX1URv7SAbekY/view?usp=sharing)
