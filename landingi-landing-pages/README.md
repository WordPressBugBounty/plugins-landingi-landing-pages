## Wordpress plugin for displaying Landing Pages as a Wordpress post
Branches:

- `master` - only for compatibility with monorepo structure, should always be up-to-date with `2.x`
- `2.x` - main branch for Landingi-branded version of the plugin, and a source of releases - **main branch of the repository**
- `1.x` - legacy branch for Landingi-branded version of the plugin, no longer in development
- `ub-2.x` - main branch for unbranded version of the plugin, and a source of releases
- `1.x` - legacy branch for unbranded version of the plugin, no longer in development

### Development

- uncomment the `wordpress` container in the Monorepo's `docker-compose.yml`
- `make up`
- `docker compose exec wordpress bash`
- `cd wp-content/plugins/landingi-landing-pages`
- `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`
- `php composer-setup.php --install-dir=/bin --filename=composer --version=1.10.26`
- `php -r "unlink('composer-setup.php');"`
- `composer install`
- go to URL `wordpress.landingi.it`
- go through default Wordpress installation process
- activate landingi plugin
- change the `landingi_api_url` config option in [landingi-plugin.php](landingi-plugin.php) to `http://api.landingi.it/`
- change the `landingi_export_url` config option in [landingi-plugin.php](landingi-plugin.php) to `http://lp.landingi.it/`
- modify the [ApiClientService](src/LandingiPlugin/Service/ApiClientService.php), adding the `'proxy' => 'http://application:80'` option to the Guzzle client
- modify the [LandendApiClientService](src/LandingiPlugin/Service/LandendApiClientService.php), adding the `'proxy' => 'http://application:80'` option to the Guzzle client
- profit

### Deployment:

- make your changes
- revert any local-specific changes (like the Guzzle proxy modifications and config options)
- update the plugin version in [landingi-plugin.php](landingi-plugin.php)
- run `composer update --no-dev`
- commit the changes to the Git repository
- once merged, tag the squashed commit with the semantic version number and push the tag to the Git repository
- copy the merged changes to your SVN trunk (without the development-specific files, like this one or docker-compose.yml) and commit them to the WordPress SVN repository
- check [the plugin page](https://wordpress.org/plugins/landing-pages-app/) in 10-15 minutes to see the plugin version updated
- profit
