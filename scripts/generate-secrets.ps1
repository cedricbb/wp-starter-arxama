$EnvDocker = ".env"
$EnvLocal  = ".env.local"

if ((Test-Path $EnvDocker) -and (Test-Path $EnvLocal)) {
    Write-Host "🔐 .env and .env.local already exist, skipping secrets generation"
    exit 0
}

$ProjectName = Split-Path (Get-Location) -Leaf
$ProjectDomain = "$ProjectName.arxama.local"

# Configuration DB - SIMPLIFIÉE POUR LE DÉVELOPPEMENT LOCAL
$DbName = $ProjectName
$DbUser = "root"
$DbPassword = "root"

function New-Salt {
    return [Convert]::ToBase64String((1..64 | ForEach-Object { Get-Random -Maximum 256 }))
}

Write-Host "🔐 Generating secrets for project: $ProjectName"

# .env pour Docker Compose
@"
PROJECT_NAME=$ProjectName
PROJECT_DOMAIN=$ProjectDomain

DB_NAME=$DbName
DB_USER=$DbUser
DB_PASSWORD=$DbPassword
DB_HOST=mariadb
DB_ROOT_PASSWORD=root
"@ | Set-Content $EnvDocker -Encoding UTF8

# .env.local pour Bedrock (PHP)
@"
DB_NAME='$DbName'
DB_USER='$DbUser'
DB_PASSWORD='$DbPassword'
DB_HOST='mariadb'

WP_ENV='development'
WP_HOME='https://$ProjectDomain'
WP_SITEURL='https://$ProjectDomain/wp'

AUTH_KEY='$(New-Salt)'
SECURE_AUTH_KEY='$(New-Salt)'
LOGGED_IN_KEY='$(New-Salt)'
NONCE_KEY='$(New-Salt)'
AUTH_SALT='$(New-Salt)'
SECURE_AUTH_SALT='$(New-Salt)'
LOGGED_IN_SALT='$(New-Salt)'
NONCE_SALT='$(New-Salt)'
"@ | Set-Content $EnvLocal -Encoding UTF8

Write-Host "✅ .env and .env.local generated (using root/root for local dev)"
