<div align="center">

# VK Messages dumper
![](https://img.shields.io/static/v1.svg?message=5.111&logo=vk&logoColor=white&label=API&labelColor=black&color=4a76a8&style=flat-square)

</div>


## Запуск и настройка

Открыть main.php, ввести [access_token](https://vkhost.github.io/) и id диалога для дампа. Пример

```php
$access_token = "nevaliddad5f4cf6a4nevalid2d4e298c7dfba28eb057nevalid76nevalid4a11c07bf040c9db5nevalid";
$peer_id = "445340";
```

Запуск:
```bash
php main.php
```

Результат сохраняется в этом же каталоге под названием "Messages {peer_id}.html".
[Пример](https://github.com/ParadoxLike/VK-Messages-dumper/blob/master/Messages%20445340.html)

## Авторство
Основа взята от [VK Opt'а](https://github.com/VkOpt/VkOpt) на js, переведена на PHP и поднята версия до 5.111
Сделано для личного пользования, может кому-то и пригодится ^_^

