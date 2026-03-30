param(
    [string]$Version = "2.4.3"
)

$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$distRoot = Join-Path $root "dist"
$releaseDir = Join-Path $root "releases"
$staging = Join-Path $distRoot "syntekpro-animations"
$zipPath = Join-Path $releaseDir ("syntekpro-animations-" + $Version + ".zip")

if (Test-Path $staging) {
    Remove-Item $staging -Recurse -Force
}

if (-not (Test-Path $releaseDir)) {
    New-Item -Path $releaseDir -ItemType Directory | Out-Null
}

New-Item -Path $staging -ItemType Directory | Out-Null

$excludeTop = @('.git', '.github', 'dist', 'releases')
Get-ChildItem -Path $root -Force | Where-Object {
    $excludeTop -notcontains $_.Name
} | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination $staging -Recurse -Force
}

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zipPath -CompressionLevel Optimal
Write-Host "Release package created: $zipPath"
