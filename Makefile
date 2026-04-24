setup:
	docker compose build --pull --no-cache
	docker compose up --wait
	docker compose exec php composer install
	docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
	powershell.exe -Command 'Start-Process certutil.exe -ArgumentList "-addstore", "ROOT", "C:\Users\Public\root.crt" -Verb RunAs'