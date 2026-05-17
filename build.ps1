# Handled FreeScout — Deploy to production
# Syncs dev, merges dev into main, pushes main, then syncs dev back to deployed main.
# Railway redeploys the support service.
# Usage: .\build

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Write-Step { param([string]$msg) Write-Host "  $msg" -ForegroundColor Cyan }
function Write-Ok   { param([string]$msg) Write-Host "  ✓ $msg" -ForegroundColor Green }
function Write-Fail { param([string]$msg) Write-Host "  ✗ $msg" -ForegroundColor Red }

Write-Host ""
Write-Host "Handled FreeScout — Deploying to production" -ForegroundColor Magenta
Write-Host ""

if (-not (Test-Path "$PSScriptRoot\.git")) {
    Write-Fail "Not a git repo root. Run this from the project root."
    exit 1
}

Set-Location $PSScriptRoot

$dirty = git status --porcelain 2>&1
if ($dirty) {
    Write-Host ""
    Write-Host "  Uncommitted changes detected:" -ForegroundColor Yellow
    $dirty | ForEach-Object { Write-Host "    $_" -ForegroundColor DarkGray }
    Write-Host ""
    Write-Host "  Commit or stash your work first, then re-run .\build" -ForegroundColor Yellow
    Write-Host "    git add . && git commit -m `"your message`"" -ForegroundColor DarkGray
    Write-Host "    git stash  (if not ready to commit)" -ForegroundColor DarkGray
    Write-Host ""
    exit 1
}

$showDeploy = $false

try {
    Write-Step "Switching to dev..."
    git checkout dev 2>&1 | Out-Null
    Write-Ok "On dev"

    Write-Step "Fetching latest origin refs..."
    Write-Host ""
    git fetch origin
    Write-Host ""
    Write-Ok "origin refs updated"

    Write-Step "Fast-forwarding dev to origin/dev..."
    Write-Host ""
    git merge origin/dev --ff-only
    Write-Host ""
    Write-Ok "dev up to date"

    Write-Step "Switching to main..."
    git checkout main 2>&1 | Out-Null
    Write-Ok "On main"

    Write-Step "Fast-forwarding main to origin/main..."
    Write-Host ""
    git merge origin/main --ff-only
    Write-Host ""
    Write-Ok "main up to date"

    Write-Step "Merging dev..."
    Write-Host ""
    git merge dev --no-edit
    Write-Host ""
    Write-Ok "dev merged into main"

    Write-Step "Pushing to origin/main..."
    Write-Host ""
    $pushOutput = git push origin main 2>&1
    $pushOutput | ForEach-Object { Write-Host $_ }
    Write-Host ""
    if ($pushOutput -match "Everything up-to-date") {
        Write-Ok "Nothing new to push"
    } else {
        Write-Ok "Pushed — Railway is deploying"
        $showDeploy = $true
    }

    Write-Step "Switching back to dev..."
    git checkout dev 2>&1 | Out-Null
    Write-Ok "On dev"

    Write-Step "Syncing dev with deployed main..."
    Write-Host ""
    git merge main --no-edit
    Write-Host ""
    Write-Ok "dev synced to deployed main"

    Write-Step "Pushing origin/dev..."
    Write-Host ""
    $devPushOutput = git push origin dev 2>&1
    $devPushOutput | ForEach-Object { Write-Host $_ }
    Write-Host ""
    if ($devPushOutput -match "Everything up-to-date") {
        Write-Ok "origin/dev already up to date"
    } else {
        Write-Ok "origin/dev updated"
    }

} catch {
    Write-Fail "Deploy failed: $_"
    Write-Host ""
    Write-Host "  Tip: resolve any merge conflicts, then re-run .\build" -ForegroundColor Yellow
    git checkout dev 2>&1 | Out-Null
    exit 1
}

Write-Host ""
if ($showDeploy) {
    Write-Host "Done. Monitor deployments:" -ForegroundColor Green
    Write-Host "  Railway  https://railway.app/dashboard" -ForegroundColor DarkGray
} else {
    Write-Host "Done." -ForegroundColor Green
}
Write-Host ""
