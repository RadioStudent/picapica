# Add write permission to all users for app/cache and app/logs, so that docker can write to them
chmod -R a+w app/cache
chmod -R a+w app/logs
chmod -R a+w web/bundles
