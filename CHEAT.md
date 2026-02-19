
php artisan serve --host=0.0.0.0 --port=8000


for ($i = 1; $i -le 100; $i++) { $card = Get-Random -Min 90001 -Max 90011; Write-Host "Request $i - Card: $card"; Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/scanner/race" -Method POST -Headers @{"X-Device-Code"="SCANNER-01";"Content-Type"="application/json"} -Body (@{card_code=$card.ToString()} | ConvertTo-Json); Start-Sleep -Milliseconds 100 }