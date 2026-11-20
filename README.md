# SMS Tools (GatewayAPI) – Complete Setup & Getting Started Guide

This guide covers all steps required to run **SMS Tools** in Docker with MariaDB, automatic database schema and user login synchronization via `.env`, and automated Docker image builds via GitHub Container Registry (`ghcr.io/lassehschmidt/smstools`).

## 1. Prerequisites

* A Linux server (e.g., `vm-server-01` at `192.168.1.4`) with **Docker** and **Docker Compose** installed.
* An active account with **[GatewayAPI](https://gatewayapi.com/)**, including an **API Token** and an approved sender ID or phone number.
* An available web port on the server (the default in `docker-compose.yml` is port **8088**).

---

## 2. Quick Start on Server (Using Pre-Built Image)

Run these commands in the SSH terminal on your server:

### Step 1: Create Directory and Download Configuration Files
```bash
sudo mkdir -p /opt/stacks/sms-tools
sudo chown -R $USER:$USER /opt/stacks/sms-tools
cd /opt/stacks/sms-tools

curl -sO https://raw.githubusercontent.com/LasseHSchmidt/SMSTools/main/docker-compose.yml
curl -sL https://raw.githubusercontent.com/LasseHSchmidt/SMSTools/main/.env.example -o .env
```

### Step 2: Configure `.env` and Restrict File Permissions
```bash
nano .env
chmod 600 .env
```
*(See the table in Section 3 for a description of each variable.)*

### Step 3: Start the Docker Stack
```bash
docker compose pull
docker compose up -d
```

The application is now ready at:
* **`http://<your-server-ip>:8088`** (e.g., `http://192.168.1.4:8088`)

---

## 3. Configuring `.env` (All Variables)

All passwords, API keys, and default login credentials are managed exclusively from the `.env` file:

| Variable | Description | Example |
| :--- | :--- | :--- |
| `DB_HOST` | Hostname of the database container within the Docker network | `sms-tools-db` |
| `DB_NAME` | Name of the MariaDB database | `dk123432_sms` |
| `DB_USER` | Database username | `dk123432_sms` |
| `DB_PASS` | Password for the database user | `secret_password` |
| `DB_ROOT_PASS` | Root password for MariaDB | `secret_root_password` |
| `API_URL` | GatewayAPI REST endpoint | `https://gatewayapi.com/rest/mtsms` |
| `API_TOKEN` | Your personal API Token from GatewayAPI | `your_gatewayapi_token` |
| `SMS_GROUP_ID` | List ID in the `SMSGroups` table | `1` |
| `SMS_GROUP_USER` | Username for web login | `admin` |
| `SMS_GROUP_PASS` | Password for web login | `change_me_123` |
| `SMS_GROUP_PHONE` | Sender ID (max. 11 characters) or mobile number with `45` | `4512345678` |

---

## 4. Database Structure & Automatic Synchronization

Every time `sms-tools-web` starts, `/usr/local/bin/db_init.php` runs automatically before Apache launches. The script:
1. Waits for MariaDB (`sms-tools-db`) to become ready.
2. Automatically creates the following three tables if they do not already exist:
   * **`SMSGroups`**: Stores login credentials and sender details (`ListId`, `Username`, `Password`, `CellPhone`).
   * **`SMSReceivers`**: Stores recipient lists (`ReceiverId`, `ListId`, `FullName`, `ShortName`, `CellPhone`, `Phone`, `Email`, `BirthDay`).
   * **`SMSLog`**: Logs sent SMS messages and costs (`SMSId`, `ListId`, `CellPhone`, `Message`, `Status`, `Date`).
3. Synchronizes the values from `SMS_GROUP_ID`, `SMS_GROUP_USER`, `SMS_GROUP_PASS`, and `SMS_GROUP_PHONE` in `.env` into `SMSGroups` using `INSERT ... ON DUPLICATE KEY UPDATE`.

---

## 5. Updating Login, Sender, or Application Code

### Update Login or Sender ID (`.env`)
1. Edit the values in `/opt/stacks/sms-tools/.env`.
2. Restart the stack:
   ```bash
   cd /opt/stacks/sms-tools && docker compose up -d
   ```
3. Log out and log back in via the web interface so your active PHP session picks up the updated sender number/username.

### Update to the Latest Version of the Docker Image
```bash
cd /opt/stacks/sms-tools
docker compose pull
docker compose up -d
```

---

## 6. Troubleshooting & Tips

* **Check Container Status and Startup Logs:**
  ```bash
  cd /opt/stacks/sms-tools
  docker compose ps
  docker logs sms-tools-web --tail 25
  ```
  *Expected output:* `SMSGroups synced with environment variables for ListId=1 (admin).`

* **Inspect Tables Directly in MariaDB:**
  ```bash
  docker exec sms-tools-db mariadb -u dk123432_sms -p<DB_PASS> dk123432_sms -e "SHOW TABLES; SELECT * FROM SMSGroups;"
  ```

* **Phone Numbers & Country Code:**
  * When adding a recipient with an 8-digit Danish number (e.g., `12345678`), `sms2allform.php` automatically prepends `45` (`4512345678`) before sending the request to GatewayAPI.
  * Make sure `SMS_GROUP_PHONE` (sender) is an approved alphanumeric sender ID (max. 11 characters) or a valid phone number with country code on your GatewayAPI account.

* **PHP Sessions:**
  * `php.ini` is configured with `session.save_path = "/tmp"` so PHP sessions work reliably inside the Docker container.