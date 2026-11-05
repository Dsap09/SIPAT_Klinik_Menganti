param(
    [switch]$TanpaBrowser,
    [switch]$DenganVite
)

# Launcher pengembangan SIPAT: satu jalan -> server + queue, lalu buka dua tab browser.
# Default memakai aset hasil `npm run build` (tanpa Vite -> tidak ada FOUC/glitch CSS).
# Pakai -DenganVite hanya saat mengedit CSS/JS agar hot reload aktif (FOUC memang biasa terjadi di mode ini).
$root = Split-Path -Parent $PSScriptRoot

# Tutup stack dev yang lama (supervisor artisan dev/serve, child server.php, queue, dan Vite)
# supaya menjalankan dua kali tidak menggandakan proses rebutan port.
Get-CimInstance Win32_Process -Filter "Name='php.exe'" | ForEach-Object {
    $cmd = [string] $_.CommandLine
    if ($cmd -match 'artisan' -or $cmd -match 'server\.php') {
        Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
    }
}
Get-CimInstance Win32_Process -Filter "Name='node.exe'" | Where-Object { $_.CommandLine -match 'vite' } |
    ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }

foreach ($port in 8000,8001,8002,5173) {
    Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue |
        ForEach-Object { Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }
}

# Beri tanda aplikasi pakai aset yang benar: tanpa hot file -> @vite memakai manifest build.
if (-not $DenganVite) {
    Remove-Item "$root\public\hot" -Force -ErrorAction SilentlyContinue
} else {
    $env:SIPAT_DEV_VITE = '1'
}

Start-Process -FilePath "php" -ArgumentList "artisan","dev" -WorkingDirectory $root

$siap = $false
foreach ($urutan in 1..10) {
    Start-Sleep -Seconds 1
    try {
        Invoke-WebRequest -Uri "http://127.0.0.1:8000/" -UseBasicParsing -TimeoutSec 3 | Out-Null
        $siap = $true
        break
    } catch {}
}

if (-not $siap) {
    Write-Warning "Server belum merespons dalam 10 detik; cek jendela artisan dev."
}

if (-not $TanpaBrowser) {
    Start-Process "http://127.0.0.1:8000"
    Start-Process "http://127.0.0.1:8000/panel/login"
}
