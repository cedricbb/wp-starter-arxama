Write-Host "🗄️  Initialisation de la base de données…"

# Vérifications
if (-not $Env:DB_NAME) { throw "DB_NAME manquant" }
if (-not $Env:DB_USER) { throw "DB_USER manquant" }
if (-not $Env:DB_PASSWORD) { throw "DB_PASSWORD manquant" }
if (-not $Env:DB_ROOT_PASSWORD) { throw "DB_ROOT_PASSWORD manquant" }

$DB_HOST = $Env:DB_HOST
if (-not $DB_HOST) { $DB_HOST = "mariadb" }

Write-Host "➡️  DB_HOST=$DB_HOST"
Write-Host "➡️  DB_NAME=$Env:DB_NAME"
Write-Host "➡️  DB_USER=$Env:DB_USER"

# Attente MariaDB
Write-Host "⏳ Attente de MariaDB…"
do {
    Start-Sleep -Seconds 2
    $ping = & mysqladmin ping `
        -h $DB_HOST `
        -u root `
        -p$Env:DB_ROOT_PASSWORD 2>$null
} until ($ping -match "mysqld is alive")

Write-Host "✅ MariaDB disponible"

# SQL
$sql = @"
CREATE DATABASE IF NOT EXISTS `$($Env:DB_NAME)`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS '$($Env:DB_USER)'@'%'
  IDENTIFIED BY '$($Env:DB_PASSWORD)';

GRANT ALL PRIVILEGES ON `$($Env:DB_NAME)`.* TO '$($Env:DB_USER)'@'%';

FLUSH PRIVILEGES;
"@

# Exécution
$sql | mysql `
  -h $DB_HOST `
  -u root `
  -p$Env:DB_ROOT_PASSWORD

Write-Host "✅ Base de données prête"
