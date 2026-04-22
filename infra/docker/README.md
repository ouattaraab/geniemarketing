# Infra — Docker local

Services de développement : MySQL, Redis, MinIO (S3), Mailpit (SMTP), MeiliSearch.

## Démarrage

```bash
cp .env.example .env
docker compose up -d
```

## Accès

| Service | URL/port |
|---|---|
| MySQL | `localhost:3306` · user `gm` / pass `gm` / db `gm` |
| Redis | `localhost:6379` |
| MinIO API | `http://localhost:9000` |
| MinIO Console | `http://localhost:9001` (minio / minio12345) |
| Mailpit SMTP | `localhost:1025` |
| Mailpit UI | `http://localhost:8025` |
| MeiliSearch | `http://localhost:7700` |

## Initialisation MinIO

Après démarrage, créer le bucket `gm-media` via la console ou le CLI `mc`.
