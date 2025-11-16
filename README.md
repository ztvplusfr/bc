# Backend API Server

Serveur PHP simple pour gérer les films/mangas avec API REST.

## Structure

```
backend/
├── api/
│   └── movies.php          # API CRUD pour les films
├── data/
│   └── movies/
│       └── 1.json          # Données du film
├── index.php               # Router principal
├── .htaccess              # Configuration Apache
└── README.md
```

## Endpoints API

### Films

- `GET /api/movies` - Liste tous les films
- `GET /api/movies?id=1` - Récupère un film spécifique
- `POST /api/movies` - Crée un nouveau film
- `PUT /api/movies?id=1` - Met à jour un film
- `DELETE /api/movies?id=1` - Supprime un film

## Configuration

### Apache
Le fichier `.htaccess` configure :
- CORS pour les requêtes cross-origin
- Rewriting d'URL
- Gestion des requêtes OPTIONS

### PHP
- PHP 8.0+ recommandé
- Extensions : `json`, `mbstring`

## Utilisation avec Next.js

```javascript
// Récupérer tous les films
const response = await fetch('http://localhost/backend/api/movies');
const movies = await response.json();

// Récupérer un film spécifique
const response = await fetch('http://localhost/backend/api/movies?id=1');
const movie = await response.json();
```

## Données du film

Chaque film contient :
- Informations de base (titre, description, année)
- Médias (poster, backdrop, trailer)
- Métadonnées (rating, genre, durée)
- Casting et reviews
- Statistiques (vues, popularité)
# bc
