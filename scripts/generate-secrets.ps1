$EnvDocker = ".env"
$EnvLocal  = ".env.local"

if ((Test-Path $EnvDocker) -and (Test-Path $EnvLocal)) {
    Write-Host "🔐 .env and .env.local already exist, skipping secrets generation"
    exit 0
}

$ProjectName = Split-Path (Get-Location) -Leaf
$ProjectDomain = "$ProjectName.arxama.local"

$DbName = $ProjectName
$DbUser = $ProjectName
$DbPassword = [Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Maximum 256 }))

function New-Salt {
    return [Convert]::ToBase64String((1..64 | ForEach-Object { Get-Random -Maximum 256 }))
}

Write-Host "🔐 Generating secrets for project: $ProjectName"

@"
PROJECT_NAME=$ProjectName
PROJECT_DOMAIN=$ProjectDomain

DB_NAME=$DbName
DB_USER=$DbUser
DB_PASSWORD=$DbPassword
"@ | Set-Content $EnvDocker -Encoding UTF8

@"
DB_NAME="$DbName"
DB_USER="$DbUser"
DB_PASSWORD="$DbPassword"
DB_HOST="mariadb"

AUTH_KEY="$(New-Salt)"
SECURE_AUTH_KEY="$(New-Salt)"
LOGGED_IN_KEY="$(New-Salt)"
NONCE_KEY="$(New-Salt)"
AUTH_SALT="$(New-Salt)"
SECURE_AUTH_SALT="$(New-Salt)"
LOGGED_IN_SALT="$(New-Salt)"
NONCE_SALT="$(New-Salt)"
"@ | Set-Content $EnvLocal -Encoding UTF8

Write-Host "✅ .env and .env.local generated"
