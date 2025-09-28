# warmup.ps1

# Clé API
$warmupKey = $env:WARMUP_KEY

# Healthcheck
$health = Invoke-RestMethod -Uri "https://ton-middleware/api/healthcheck"
$lastTime = [int]$health.last_request_time
$now = [int][double]::Parse((Get-Date -UFormat %s))

# Si plus de 5 min sans activité
if (($now - $lastTime) -gt 300) {
    # Appel warmup en arrière-plan pour ne pas bloquer
    Start-Job -ScriptBlock {
        param($key)
        $endpoints = @(
            "https://ton-middleware/api/warmup"
        )
        foreach ($url in $endpoints) {
            try {
                Invoke-RestMethod -Uri $url -Headers @{ "X-API-KEY" = $key } -TimeoutSec 5
            } catch {
                Write-Host "Warmup error: $_"
            }
        }
    } -ArgumentList $warmupKey

    # Log simple
    Add-Content "C:\warmup\warmup.log" "$(Get-Date) - Warmup déclenché"
}
