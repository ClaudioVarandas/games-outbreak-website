# Game Outbreak - Website

### Tech stack

- Laravel 10
- Admin
    - Filament 2
- Frontend
    - Livewire

## 3rd party dependencies

- IGDB Laravel (fetch the games from IGDB)
    - marcreichel/igdb-laravel


## IGDB Notes

### Auxiliary tables

```
/*
 * Platforms
 *
 * 6 - PC
 * 130 - Switch
 * 48 - PS4
 * 167 - PS5
 * 169 - Series S|X
 * 49 - XBox ONE
 *
 */

/*
 * Category
 *
 * 0 - main_game
 * 1 - dlc_addon
 * 2 - expansion
 * 3 - bundle
 * 4 - standalone_expansion
 * 5 - mod
 * 6 - episode
 * 7 - season
 * 8 - remake
 * 9 - remaster
 * 10 - expanded_game
 * 11 - port
 * 12 - fork
 * 13 - pack
 * 14 - update
 * 
 */
```



## Deploy

**Required**

docker
node 18
npm 8

**Build**

- npm i
- npm run build
- docker compose up -d
- php artisan go:igdb:fetch-upcoming <start_date> <end_date>

