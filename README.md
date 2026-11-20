# SMS Tools (GatewayAPI) – Komplet Opsætnings- & Kom-Godt-I-Gang Guide

Denne guide samler alle trin til at køre **SMS Tools** i Docker med MariaDB, automatisk synkronisering af database og brugerlogin via `.env` samt automatisk bygning af Docker-image via GitHub Container Registry (`ghcr.io/lassehschmidt/smstools`).

## 1. Forudsætninger

* En Linux-server (f.eks. `vm-server-01` på `192.168.1.4`) med **Docker** og **Docker Compose** installeret.
* En aktiv konto hos **[GatewayAPI](https://gatewayapi.com/)** med tilhørende **API Token** samt godkendt afsendernavn eller telefonnummer.
* Ledig webport på serveren (standarden i `docker-compose.yml` er port **8088**).

---

## 2. Hurtig opstart på server (med færdigbygget image)

Udfør disse kommandoer i SSH-terminalen på din server:

### Trin 1: Opret mappe og hent konfigurationsfiler
```bash
sudo mkdir -p /opt/stacks/sms-tools
sudo chown -R $USER:$USER /opt/stacks/sms-tools
cd /opt/stacks/sms-tools

curl -sO https://raw.githubusercontent.com/LasseHSchmidt/SMSTools/main/docker-compose.yml
curl -sL https://raw.githubusercontent.com/LasseHSchmidt/SMSTools/main/.env.example -o .env
```

### Trin 2: Udfyld `.env` og begræns filrettigheder
```bash
nano .env
chmod 600 .env
```
*(Se tabel i afsnit 3 for beskrivelse af hver variabel.)*

### Trin 3: Start Docker-stakken
```bash
docker compose pull
docker compose up -d
```

Applikationen er nu klar på:
* **`http://<din-servers-ip>:8088`** (f.eks. `http://192.168.1.4:8088`)

---

## 3. Konfiguration af `.env` (Alle variabler)

Alle adgangskoder, API-nøgler og standard-login styres udelukkende fra `.env`-filen:

| Variabel | Beskrivelse | Eksempel |
| :--- | :--- | :--- |
| `DB_HOST` | Hostnavn på database-containeren i Docker-netværket | `sms-tools-db` |
| `DB_NAME` | Navn på MariaDB-databasen | `dk123432_sms` |
| `DB_USER` | Database-brugernavn | `dk123432_sms` |
| `DB_PASS` | Adgangskode til database-brugeren | `hemmelig_kode` |
| `DB_ROOT_PASS` | Root-adgangskode til MariaDB | `hemmelig_root_kode` |
| `API_URL` | GatewayAPI REST endpoint | `https://gatewayapi.com/rest/mtsms` |
| `API_TOKEN` | Dit personlige API Token fra GatewayAPI | `dit_gatewayapi_token` |
| `SMS_GROUP_ID` | Liste-ID i `SMSGroups`-tabellen | `1` |
| `SMS_GROUP_USER` | Brugernavn til web-login | `admin` |
| `SMS_GROUP_PASS` | Adgangskode til web-login | `skift_mig_123` |
| `SMS_GROUP_PHONE` | Afsender-ID (maks. 11 tegn) eller mobilnummer med `45` | `4512345678` |

---

## 4. Databasestruktur & Automatisk Synkronisering

Ved hver opstart af `sms-tools-web` køres `/usr/local/bin/db_init.php` automatisk, før Apache starter. Scriptet:
1. Venter på, at MariaDB (`sms-tools-db`) er klar.
2. Opretter automatisk følgende tre tabeller, hvis de ikke allerede findes:
   * **`SMSGroups`**: Indeholder login og afsenderoplysninger (`ListId`, `Username`, `Password`, `CellPhone`).
   * **`SMSReceivers`**: Indeholder modtagerlister (`ReceiverId`, `ListId`, `FullName`, `ShortName`, `CellPhone`, `Phone`, `Email`, `BirthDay`).
   * **`SMSLog`**: Logger afsendte SMS-beskeder og pris (`SMSId`, `ListId`, `CellPhone`, `Message`, `Status`, `Date`).
3. Synkroniserer værdierne fra `SMS_GROUP_ID`, `SMS_GROUP_USER`, `SMS_GROUP_PASS` og `SMS_GROUP_PHONE` i `.env` ind i `SMSGroups` via `INSERT ... ON DUPLICATE KEY UPDATE`.

---

## 5. Opdatering af login, afsender eller kode

### Opdater login eller afsender-ID (`.env`)
1. Ret værdierne i `/opt/stacks/sms-tools/.env`.
2. Genstart stakken:
   ```bash
   cd /opt/stacks/sms-tools && docker compose up -d
   ```
3. Log ud og ind igen i web-interfacet, så din aktive PHP-session henter det nye afsendernummer/brugernavn.

### Opdater til nyeste version af Docker-imaget
```bash
cd /opt/stacks/sms-tools
docker compose pull
docker compose up -d
```

---

## 6. Fejlfinding & Tips

* **Tjek container-status og opstartslog:**
  ```bash
  cd /opt/stacks/sms-tools
  docker compose ps
  docker logs sms-tools-web --tail 25
  ```
  *Forventet output:* `SMSGroups synced with environment variables for ListId=1 (admin).`

* **Inspicer tabellerne direkte i MariaDB:**
  ```bash
  docker exec sms-tools-db mariadb -u dk123432_sms -p<DB_PASS> dk123432_sms -e "SHOW TABLES; SELECT * FROM SMSGroups;"
  ```

* **Telefonnumre & Landekode:**
  * Når du opretter en modtager med 8 danske cifre (f.eks. `12345678`), sætter `sms2allform.php` automatisk `45` foran (`4512345678`), før kaldet sendes til GatewayAPI.
  * Sørg for at `SMS_GROUP_PHONE` (afsender) er et godkendt alfanumerisk afsendernavn (maks. 11 tegn) eller et gyldigt nummer med landekode på din GatewayAPI-konto.

* **PHP Sessions:**
  * `php.ini` er konfigureret med `session.save_path = "/tmp"`, så PHP-sessions fungerer stabilt inde i Docker-containeren.

---
