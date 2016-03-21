# SAM - Simple Asset Manager
Это простой asset manager для управления js и css файлами.

[![Build Status](https://travis-ci.org/DmitryNaum/SAM.svg?branch=master)](https://travis-ci.org/DmitryNaum/SAM)
[![codecov.io](https://codecov.io/github/DmitryNaum/SAM/coverage.svg?branch=master)](https://codecov.io/github/DmitryNaum/SAM?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DmitryNaum/SAM/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DmitryNaum/SAM/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dmitrynaum/sam/v/stable)](https://packagist.org/packages/dmitrynaum/sam) 
[![Total Downloads](https://poser.pugx.org/dmitrynaum/sam/downloads)](https://packagist.org/packages/dmitrynaum/sam) 
[![Latest Unstable Version](https://poser.pugx.org/dmitrynaum/sam/v/unstable)](https://packagist.org/packages/dmitrynaum/sam) 
[![License](https://poser.pugx.org/dmitrynaum/sam/license)](https://packagist.org/packages/dmitrynaum/sam)


## Возможности
- Объединять разные css и js файлы в один
- Сжимать css и js (минифицировать)
- Контролировать кэш браузера
- Компилировать asset`ы на лету в Development окружении
- Использовать удаленные js и css (например с CDN)

## Использование

В корне проекта должен находится файл настроек `sam.json` в котором описаны все необходимые для SAM`а параметры
```json
{
    "assetBasePath" : "Базовая папка куда будут сохранены все asset\`ы. Должна быть доступна из web!",
    "resultMapPath" : "Путь до карты скомпилированных asset\`ов ",
    "assets" : { 
        "Название файла в который будет сохранен скомпилированный asset (app.css) является названием asset`а " : [
            "Файл который будет объединен с другими и записан в app.css",
            "Файл который будет объединен с другими и записан в app.css"
        ]
    }
}
```

Пример `sam.json`
```json
{
    "assetBasePath" : "public/build/",
    "resultMapPath" : "asset/map.json",
    "assets" : {
        "app.css" : [
            "https:///maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css",
            "asset/css/first.css",
            "asset/css/second.css"
        ]
    }
    
}
```

Asset файлы сохраняются в папку которая указана в `assetBasePath`. Т.е. asset `app.css` из примера
выше будет сохранен в `public/build/app.css`

## Компиляция
Есть несколько режимов компиляции asset\`ов.
1. Простая компиляция. SAM просто соберет все asset\`ы
```
php vendor/bin/sam build
```
2. Компиляция с минификацией. Asset\`ы будут собраны и минифицированы
```
php vendor/bin/sam build -c
```
3. Компиляция с заморозкой. Asset\`ы будут собраны и в название результирующих файлов будут добавлены их хэши. Это помогает избежать проблем с кэшем браузера
```
php vendor/bin/sam build -f
```
4. Компиляция с минификацией и заморозкой.
 ```
php vendor/bin/sam build -с -f
```
Также компилятору asset\`ов можно указать путь до `sam.json`.
```
php vendor/bin/sam build my_sam.json -с -f
```

## Рендеринг
Для того что бы добавить asset в шаблон Вашего сайта, выполните 2 простые команды
```php
<?php
// Добавляем asset
Dmitrynaum\SAM\Asset::useCss('app.css');
// Выводим asset на страницу
echo Dmitrynaum\SAM\Asset::renderCss();
```

Рендеринг с атрибутами
```php
<?php
// Добавляем asset
Dmitrynaum\SAM\Asset::useJs('app.js');
// Выводим asset на страницу
echo Dmitrynaum\SAM\Asset::renderJs(['defer']);
```

Для использования удаленных js и css файлов Вы можете воспользоваться методами `Dmitrynaum\SAM\Asset::useRemoteJs()` и `Dmitrynaum\SAM\Asset::useRemoteCss()`.
SAM не будет их нигде кэшировать, он просто обернет ссылки на ресурсы в соответствующие HTML теги
```php
<?php
// ...
Dmitrynaum\SAM\Asset::useRemoteJs('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
Dmitrynaum\SAM\Asset::useRemoteCss('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
// ...
echo Dmitrynaum\SAM\Asset::renderCss();
echo Dmitrynaum\SAM\Asset::renderJs();
```

## Компиляция на лету
Для удобной разработки в SAM\`е предусмотрена возможность компилировать asset\`ы на лету без лишних движений.

Для этого Вам необходимо в коде Вашего приложения в Development окружении сообщить SAM\`у что он должен работать в development режиме, и запустить встроенный веб сервер SAM\`а.
```php
<?php
// ...
if (App::isDevelopment()) {
    Dmitrynaum\SAM\Asset::enableDevelopmentMode();
}
// ...
```
```
php vendor/bin/sam start-server
```

После чего все asset\`ы будут доступны по адресу `http://127.0.0.1:8652?asset=asset_name` и каждый раз при запросе необходимого asset\`а они будут компилироваться на лету.
### ВНИМАНИЕ!
Не используйте встроенный сервер SAM\`а в Production окружении. Это не безопасно!

# ToDo
- [x] Добавить возможность собирать asset\`ы на лету в development окружении для простой разработки.
- [x] Добавить возможность рендерить теги `javascript` с атрибутами `async` и `defer`