Write-Host "🗄️  Initialisation de la base de données..."

# Charger les variables d'environnement si .env existe
if (Test-Path .env) {
    Get-Content .env | Where-Object { $_ -notmatch '^#' -and $_ -match '=' } | ForEach-Object {
        $key, $value = $_ -split '=', 2
        [Environment]::SetEnvironmentVariable($key, $value, "Process")
    }
}

# Vérifications
if (-not $Env:DB_NAME) { throw "DB_NAME manquant" }
$DB_HOST = if ($Env:DB_HOST) { $Env:DB_HOST } else { "mariadb" }
$DB_ROOT_PASSWORD = if ($Env:DB_ROOT_PASSWORD) { $Env:DB_ROOT_PASSWORD } else { "root" }

Write-Host "➡️  DB_HOST=$DB_HOST"
Write-Host "➡️  DB_NAME=$Env:DB_NAME"
Write-Host "ℹ️  Utilisation de l'utilisateur root pour la connexion locale"

Write-Host "🐳 Création de la base via Docker..."

# Commande SQL simple pour créer la base
$sqlCommand = "CREATE DATABASE IF NOT EXISTS \`$($Env:DB_NAME)\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Exécution via Docker (équivalent du here-doc bash)
# On utilise ` pour échapper les guillemets dans PowerShell
docker run --rm -i --network backend `
  -e MYSQL_PWD="$DB_ROOT_PASSWORD" `
  -e DB_HOST="$DB_HOST" `
  -e DB_NAME="$Env:DB_NAME" `
  mariadb:10.11 bash -c @"
    echo '⏳ Attente de MariaDB ($DB_HOST)...'
    until mysqladmin ping -h '$DB_HOST' -u root --silent; do
      sleep 1
      echo -n '.'
    done
    echo ''
    echo '✅ MariaDB disponible'

    echo '🛠 Création de la base de données...'
    mysql -h '$DB_HOST' -u root -e '$sqlCommand'

    echo '✅ Base de données créée'
"@

Write-Host "✅ Processus terminé."
