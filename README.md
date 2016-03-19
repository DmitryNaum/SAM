# SAM - Simple Asset Manager
Это простой asset manager для управления js и css файлами.

## Возможности
- Объединять разные css и js фпйлы в один
- Сжимать css и js (минифицировать)
- Контролировать кэш браузера

## Использование

В корне проекта должен находится файл `manifest.json` в котором описаны все необходимые для SAM`а параметры
```json
{
    "assetBasePath" : "Базовая папка куда будут сохранены все ассеты. Должна быть доступна из web!",
    "resultMapPath" : "Путь до карты скомпилированных ассетов ",
    "assets" : { 
        "название файла в который будет сохранен скомпилированный ассет (build/asset/app.css)" : [
            "Файл который будет объединен с другими и записан в app.css",
            "Файл который будет объединен с другими и записан в app.css"
        ]
    }
}
```

Пример `manifest.json`
```json
{
    "assetBasePath" : "build/asset/",
    "resultMapPath" : "build/asset/map.json",
    "assets" : {
        "app.css" : [
            "asset/css/first.css",
            "asset/css/second.css"
        ]
    }
    
}
```
## Компиляция
Есть несколько режимов компиляции ассетов.
1. Простая компиляция. SAM просто соберет все asset\`ы
```shell
php bin/build_asset build
```
2. Компиляция с минификацией. Asset\`ы будут собраны и минифицированы
```shell
php bin/build_asset build -c
```
3. Компиляция с заморозкой. Asset\`ы будут собраны и в название результирующих файлов будут добавлены их хэши. Это помогает избежать проблем с кэшом браузера
```shell
php bin/build_asset build -f
```
4. Компиляция с минификацией и заморозкой.
 ```shell
php bin/build_asset build -с -f
```
Также компилятору asset\`ов можно указать путь до `manifest.json`
```shell
php bin/build_asset build my_manifest.json -с -f
```

## Рендеринг
Для того что бы добавить asset в шаблон Вашего сайта, выполните 2 простые команды
```php
// Добавляем asset
Dmitrynaum\SAM\Asset::useJs('app.js');
// Выводим ассет на страницу
echo Dmitrynaum\SAM\Asset::renderJs();
```

# ToDo
* Добавить возжность собирать asset\`ы на лету в development окружении для простой разработки.
* Добавить возможность рендерить теги `javascript` с атрибутами `async` и `defer`