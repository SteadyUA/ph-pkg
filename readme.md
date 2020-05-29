# Phoenix core package skeleton

To create your package, enter the following command in your console.

```
cd protected/packages/core
composer create-project steady-ua/ph-pkg <package-dir-name>
```

## Local repository
Check, that the local repository, is configured. Run
```
$ composer config -g -l | grep repositories.local
```
You should see the following
```
[repositories.local.type] path
[repositories.local.url] /home/phoenix/htdocs/protected/packages/*/*
```
If empty, not configured, run
```
composer config --global repositories.local path '/home/phoenix/htdocs/protected/packages/*/*'
```
