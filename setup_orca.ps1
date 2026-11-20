$ErrorActionPreference = 'Stop'
Write-Host "== JuDy Simulator - preparacion de entorno Orca =="

$python = Get-Command python -ErrorAction SilentlyContinue
if (-not $python) {
    $python = Get-Command py -ErrorAction SilentlyContinue
}
if (-not $python) {
    throw "Python 3 no encontrado. Instala Python 3.9+ y vuelve a ejecutar este script."
}

Push-Location $PSScriptRoot
try {
    & $python.Source tools/judy_gem_sync.py
    if ($LASTEXITCODE -ne 0) { throw " judy_gem_sync.py devolvio $LASTEXITCODE" }

    & $python.Source tools/judy_audit.py --strict
    if ($LASTEXITCODE -ne 0) { throw " judy_audit.py encontro hallazgos criticos" }

    Write-Host "`nEntorno listo. Abre esta carpeta en Orca y revisa LEEME_ENTORNO.md."
} finally {
    Pop-Location
}
