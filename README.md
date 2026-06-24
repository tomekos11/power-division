# Power Division

API do kredytowania i obciążania kont.

**Wymagania:** Docker. Do `make` potrzebny jest pakiet `make` (WSL/Ubuntu: `sudo apt install make`). Bez make użyj `./dev` — te same komendy.

## Uruchomienie

Pierwszy raz:

```bash
make setup
# albo: ./dev setup
```

To skopiuje `.env` z przykładu (jeśli go nie masz), zbuduje kontenery, odpali migracje i załaduje dane demo (seed).

Potem wystarczy `make up` (albo `./dev up`). API: http://localhost:8080

Przydatne:

```bash
make down    # stop          |  ./dev down
make fresh   # baza + seed   |  ./dev fresh
make test                   |  ./dev test
make docs                   |  ./dev docs
```

Komendy odpalasz na hoście, w katalogu projektu (nie w kontenerze).

Bez `make` — to samo przez skrypt:

```bash
chmod +x dev   # raz, po sklonowaniu
./dev setup
./dev up
```

## Kontenery

`app` (PHP), `web` (Nginx, port 8080), `postgres`, `redis`. Do podglądu bazy i Redisa są jeszcze pgAdmin (5050) i RedisInsight (5540).

## Seedery

`make setup` i `make fresh` ładują trzech użytkowników z kontami i historią transakcji:

- Alice — alice@example.com — saldo 100.00
- Bob — bob@example.com — saldo 500.00
- Charlie — charlie@example.com — saldo 0.00
