# Руководство по интеграции Premium Companies Plugin в DJ-Classifieds

## 📋 Оглавление
1. [Обзор](#обзор)
2. [Где находится файл шаблона](#где-находится-файл-шаблона)
3. [Способы интеграции](#способы-интеграции)
4. [Настройка плагина](#настройка-плагина)
5. [Тестирование](#тестирование)

---

## 🎯 Обзор

Premium Companies Plugin работает через события Joomla (`onContentBeforeDisplay` / `onContentAfterDisplay`).

DJ-Classifieds использует собственный шаблон, который НЕ вызывает эти события автоматически. Поэтому нужно добавить триггер события вручную.

---

## 📁 Где находится файл шаблона

Шаблон формы добавления объявления обычно находится здесь:

```
/components/com_djclassifieds/views/additem/tmpl/default.php
```

**Или** если используется переопределение шаблона (template override):

```
/templates/ваш_шаблон/html/com_djclassifieds/additem/default.php
```

### Проверка:
1. Перейдите в **Расширения → Шаблоны → Шаблоны**
2. Выберите ваш активный шаблон (например, YooTheme)
3. Нажмите **Создать переопределения**
4. Найдите **com_djclassifieds → additem**

---

## 🔧 Способы интеграции

### Способ 1: Через события Joomla (Рекомендуется) ✅

**Преимущества:**
- ✅ Правильный подход по стандартам Joomla
- ✅ Работает с любыми content плагинами
- ✅ Легко отключить плагин без правки шаблона

**Как реализовать:**

1. **Откройте файл шаблона** (см. выше где он находится)

2. **Найдите место для вставки** - рекомендую после выбора категории:
   ```php
   <?php echo $this->loadTemplate('category'); ?>
   <?php foreach($this->plugin_category as $plugin_category) echo $plugin_category; ?>
   <?php echo $this->loadTemplate('categoryfields'); ?>

   <!-- ВСТАВЬТЕ КОД СЮДА -->
   ```

3. **Вставьте следующий код:**

```php
<?php
// ============================================================
// PREMIUM COMPANIES PLUGIN INTEGRATION
// Shows recommended companies after category selection
// ============================================================
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;

// Import content plugins
PluginHelper::importPlugin('content');

// Create article object for plugin
$premiumArticle = new stdClass();
$premiumArticle->text = '';
$premiumArticle->cat_id = Factory::getApplication()->input->getInt('cat_id', 0);

// Create empty params object
$premiumParams = new \Joomla\Registry\Registry();

// Trigger onContentBeforeDisplay event
$dispatcher = Factory::getApplication()->getDispatcher();
$premiumResults = $dispatcher->triggerEvent('onContentBeforeDisplay', array(
    'com_djclassifieds.additem',
    &$premiumArticle,
    &$premiumParams,
    0
));

// Output results from plugins
if (!empty($premiumResults)) {
    foreach ($premiumResults as $result) {
        if (!empty($result)) {
            echo $result;
        }
    }
}
?>
```

4. **Сохраните файл**

---

### Способ 2: Упрощенная версия (короткий код)

Если код выше не работает или вы используете Joomla 3.x:

```php
<?php
// Показываем рекомендуемые компании
JPluginHelper::importPlugin('content');
$dispatcher = JEventDispatcher::getInstance();
$tempArticle = new stdClass();
$tempArticle->cat_id = JFactory::getApplication()->input->getInt('cat_id', 0);
$tempParams = new JRegistry();
$pluginResults = $dispatcher->trigger('onContentBeforeDisplay', array(
    'com_djclassifieds.additem',
    &$tempArticle,
    &$tempParams,
    0
));
foreach ($pluginResults as $pluginResult) {
    if (!empty($pluginResult)) {
        echo $pluginResult;
    }
}
?>
```

---

### Способ 3: Через AJAX (для динамической загрузки)

Если хотите, чтобы компании загружались динамически при выборе категории:

**1. Добавьте контейнер в шаблон:**

```php
<?php echo $this->loadTemplate('categoryfields'); ?>

<!-- Контейнер для компаний -->
<div id="premium-companies-container"></div>

<?php if($par->get('show_introdesc','1')){ ?>
```

**2. Добавьте JavaScript в конец шаблона (перед `</div>`):**

```php
<script>
(function() {
    'use strict';

    // Функция загрузки компаний
    function loadPremiumCompanies() {
        var catId = document.querySelector('select[name="cat_id"]');
        if (!catId || !catId.value) return;

        var container = document.getElementById('premium-companies-container');
        if (!container) return;

        // Показываем загрузку
        container.innerHTML = '<div class="loading">Загрузка компаний...</div>';

        // AJAX запрос
        fetch('index.php?option=com_ajax&plugin=premiumcompanies&format=json&cat_id=' + catId.value)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    container.innerHTML = data.data;
                } else {
                    container.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error loading companies:', error);
                container.innerHTML = '';
            });
    }

    // Слушаем изменение категории
    document.addEventListener('DOMContentLoaded', function() {
        var catSelect = document.querySelector('select[name="cat_id"]');
        if (catSelect) {
            catSelect.addEventListener('change', loadPremiumCompanies);

            // Загружаем при загрузке страницы если категория выбрана
            if (catSelect.value) {
                loadPremiumCompanies();
            }
        }
    });
})();
</script>
```

**Примечание:** Для AJAX метода нужно добавить метод в плагин для обработки AJAX запросов.

---

## ⚙️ Настройка плагина

После интеграции настройте плагин:

### 1. Включите плагин
- **Расширения → Плагины**
- Найдите **Content - Premium Companies for DJ-Classifieds**
- Убедитесь что он **включен** (зеленая галочка)

### 2. Настройте целевые страницы

**ВАЖНО!** Укажите URL страницы добавления объявления:

```
Система → Плагины → Premium Companies

Target Pages (URLs):
/index.php?option=com_djclassifieds&view=additem
```

Можно добавить несколько URL (по одному на строку):
```
/index.php?option=com_djclassifieds&view=additem
/index.php?option=com_djclassifieds&view=edititem
/add-classified
/add-order
```

### 3. Основные настройки

| Параметр | Рекомендация |
|----------|--------------|
| **Maximum Companies** | 5-10 (чтобы не перегружать форму) |
| **Show All Companies** | Да (показывать все, премиум первыми) |
| **Match by Category** | Да (только компании из той же категории) |
| **Display Position** | Before Content |
| **Display Style** | Cards или Compact (для формы лучше Compact) |
| **Show Company Logo** | Да |

### 4. AI настройки (опционально)

Если хотите умный подбор:
- **Enable AI Matching**: Да
- **OpenAI API Key**: ваш ключ с platform.openai.com
- **AI Model**: GPT-4o Mini (экономичный)
- **AI Max Results**: 3-5

---

## 🧪 Тестирование

### 1. Проверьте что плагин активирован

Перейдите на страницу добавления объявления:
```
/index.php?option=com_djclassifieds&view=additem
```

**Должны увидеть:**
- Блок "Рекомендуемые компании" после выбора категории
- Премиум компании (со значком) отображаются первыми
- Обычные компании идут после премиум

### 2. Проверьте разные категории

- Выберите категорию "Строительство" → должны показаться строительные компании
- Выберите категорию "Услуги" → должны показаться другие компании
- Если категория не совпадает → компании не покажутся (при включенном Match by Category)

### 3. Проверьте AI подбор (если включен)

- Заполните название заказа: "Нужен ремонт квартиры"
- Заполните описание: "Косметический ремонт 2 комнаты"
- Компании должны отсортироваться по релевантности

### 4. Проверьте мобильную версию

- Откройте на мобильном
- Компании должны адаптироваться под экран
- Карточки должны располагаться в колонку

---

## 🐛 Решение проблем

### Компании не отображаются

**Проверьте:**

1. ✅ Плагин включен (Расширения → Плагины)
2. ✅ Указан правильный URL в Target Pages
3. ✅ В J-Business Directory есть компании
4. ✅ У компаний установлена категория
5. ✅ Код интеграции вставлен в правильное место
6. ✅ Очистите кэш Joomla

### Код вызывает ошибку

**Проверьте:**
- Используете ли правильную версию кода (Joomla 3 vs Joomla 4/5)
- Нет ли синтаксических ошибок в PHP
- Включен ли режим отладки (Система → Глобальная конфигурация → Система → Debug System)

### Стили не применяются

**Проверьте:**
- Загружается ли CSS файл плагина (F12 → Network → CSS)
- Нет ли конфликтов с YooTheme стилями
- Попробуйте добавить Custom CSS в настройках плагина

---

## 📚 Дополнительно

### Кастомизация стилей для YooTheme

Добавьте в настройках плагина (Custom CSS):

```css
/* Адаптация под YooTheme */
.premium-companies-container {
    margin: 30px 0 !important;
    background: var(--tm-background-default, #f9f9f9) !important;
}

.premium-companies-title {
    font-family: var(--tm-heading-font-family) !important;
    color: var(--tm-heading-primary-color) !important;
}

.premium-company-card {
    border: 1px solid var(--tm-border-default, #e5e5e5) !important;
}

.uk-button {
    /* Совместимость с UIkit кнопками YooTheme */
}
```

### Альтернативная позиция

Если хотите показывать компании в другом месте, переместите код:

**После описания:**
```php
<?php echo $this->loadTemplate('description'); ?>

<!-- ВСТАВЬТЕ КОД СЮДА -->
```

**Перед кнопками:**
```php
<?php } ?>

<!-- ВСТАВЬТЕ КОД СЮДА -->

<div class="classifieds_buttons">
```

---

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи Joomla (Система → Системная информация → Логи)
2. Включите режим отладки для подробных ошибок
3. Проверьте консоль браузера (F12)

---

**Готово!** 🎉

Теперь при добавлении объявления пользователи будут видеть рекомендуемые компании!
