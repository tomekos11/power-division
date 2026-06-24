# Power Division

API do kredytowania i obciążania kont.

## Uruchomienie

Pierwszy raz:

```bash
make setup
```

To skopiuje `.env` z przykładu (jeśli go nie masz), zbuduje kontenery, odpali migracje i załaduje dane demo (seed).

Potem wystarczy `make up`. API: http://localhost:8080

Przydatne:

```bash
make down    # stop
make fresh   # baza od zera + seed
make test
make docs    # dokumentacja API → /docs
```

Komendy `make` odpalasz na hoście, w katalogu projektu.

## Kontenery

`app` (PHP), `web` (Nginx, port 8080), `postgres`, `redis`. Do podglądu bazy i Redisa są jeszcze pgAdmin (5050) i RedisInsight (5540).

## Seedery

`make setup` i `make fresh` ładują trzech użytkowników z kontami i historią transakcji:

- Alice — alice@example.com — saldo 100.00
- Bob — bob@example.com — saldo 500.00
- Charlie — charlie@example.com — saldo 0.00
