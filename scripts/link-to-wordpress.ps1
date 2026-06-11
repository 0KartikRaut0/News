# Link wp-content/themes and wp-content/plugins from this repo into a local WordPress site.
# Usage: .\scripts\link-to-wordpress.ps1 "C:\path\to\wordpress\wp-content"

param(
    [Parameter(Mandatory = $true)]
    [string]$WpContentPath
)

$ErrorActionPreference = "Stop"
$RepoRoot = Split-Path $PSScriptRoot -Parent

$Src = Join-Path $RepoRoot "wp-content"
$ThemesSrc = Join-Path $Src "themes"
$PluginsSrc = Join-Path $Src "plugins"

if (-not (Test-Path $ThemesSrc) -or -not (Test-Path $PluginsSrc)) {
    Write-Error "Expected $ThemesSrc and $PluginsSrc"
}

New-Item -ItemType Directory -Force -Path (Join-Path $WpContentPath "themes") | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $WpContentPath "plugins") | Out-Null

Get-ChildItem $ThemesSrc -Directory | ForEach-Object {
    $target = Join-Path $WpContentPath "themes\$($_.Name)"
    if (Test-Path $target) { Remove-Item $target -Recurse -Force }
    New-Item -ItemType Junction -Path $target -Target $_.FullName | Out-Null
    Write-Host "linked theme: $($_.Name)"
}

Get-ChildItem $PluginsSrc -Directory | ForEach-Object {
    $target = Join-Path $WpContentPath "plugins\$($_.Name)"
    if (Test-Path $target) { Remove-Item $target -Recurse -Force }
    New-Item -ItemType Junction -Path $target -Target $_.FullName | Out-Null
    Write-Host "linked plugin: $($_.Name)"
}

Write-Host "Done. git pull in $RepoRoot updates WordPress via junctions."
