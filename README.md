# Premium Companies Plugin для Joomla 4/5

[English](#english) | [Русский](#russian)

---

## English

### Description

Premium Companies Plugin is a Joomla 4/5 extension that integrates DJ-Classifieds and J-Business Directory components. When users add orders through DJ-Classifieds forms, the plugin automatically suggests relevant companies from J-Business Directory. Premium companies are displayed first, followed by all other companies. The plugin features optional AI-powered matching using ChatGPT to find the most relevant companies for each order.

### Features

- ✅ **Smart Display**: Shows companies on specific pages (configurable via URL patterns)
- ✅ **Priority Sorting**: Premium companies first, then all others
- ✅ **AI-Powered Matching**: Optional ChatGPT integration for intelligent company recommendations
- ✅ **Category Matching**: Filter companies by classified ad category
- ✅ **Multiple Display Styles**: Cards, List, Compact
- ✅ **Flexible Configuration**: Choose how many companies to display (1-50)
- ✅ **Responsive Design**: Works on mobile and desktop
- ✅ **Rich Display**: Company logos, descriptions, and contact information
- ✅ **Premium Badge**: Visual highlighting for premium companies
- ✅ **Custom Positioning**: Display before or after content
- ✅ **Custom CSS Support**: Full styling customization
- ✅ **Multilingual**: English & Russian support

### Requirements

- **Joomla:** 4.0 or higher (compatible with Joomla 5)
- **PHP:** 7.4 or higher
- **Required Components:**
  - DJ-Classifieds
  - J-Business Directory

### Installation

1. Download the plugin package
2. Navigate to **System → Install → Extensions** in Joomla admin panel
3. Upload the plugin ZIP file
4. Click **Upload & Install**
5. The plugin will be automatically enabled after installation

### Configuration

1. Go to **System → Plugins**
2. Find and open **Content - Premium Companies for DJ-Classifieds**
3. Configure the following settings:

#### Basic Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Target Pages (URLs) | URLs where companies should be displayed (one per line) | Empty |
| Maximum Companies | Number of companies to show (1-50) | 10 |
| Show All Companies | Show all companies (premium first) or only premium | Yes |
| Match by Category | Show only companies matching the classified ad category | Yes |
| Display Position | Where to show companies (Before/After Content) | Before Content |
| Display Style | Visual style (Cards/List/Compact) | Cards |
| Show Company Logo | Display company logos | Yes |
| Custom CSS | Add custom styling | Empty |

#### AI Settings (ChatGPT)

| Setting | Description | Default |
|---------|-------------|---------|
| Enable AI Matching | Use ChatGPT to find most relevant companies | No |
| OpenAI API Key | Your OpenAI API key from platform.openai.com | Empty |
| AI Model | ChatGPT model to use | GPT-4o Mini |
| AI Max Results | Maximum companies AI should return | 5 |

### Usage

Once installed and configured, the plugin works automatically:

1. **Configure Target Pages**: Add URLs where companies should appear (e.g., `/index.php?option=com_djclassifieds&view=additem`)
2. **User Adds Order**: When users add orders in DJ-Classifieds
3. **Plugin Activates**: Plugin queries J-Business Directory for companies
4. **Smart Display**: Premium companies shown first, then others
5. **AI Matching** (Optional): If enabled, ChatGPT analyzes the order and ranks companies by relevance
6. **Users Interact**: Users can click companies to view their profiles

### Database Structure

The plugin expects the following J-Business Directory database tables:

- `#__jbusinessdirectory_companies` - Company information
- `#__jbusinessdirectory_company_package` - Company package assignments
- `#__jbusinessdirectory_packages` - Package definitions (must have `type = 'premium'`)

### Display Styles

#### Cards Style
Displays companies in a grid of attractive cards with logos, descriptions, and contact info.

#### List Style
Shows companies in a detailed list format with all information visible.

#### Compact Style
Minimal display with company name and premium badge, ideal for sidebar placement.

### Customization

You can add custom CSS in the plugin settings or override the default styles by creating:

```
/templates/your-template/css/plg_premiumcompanies_custom.css
```

### Troubleshooting

**Problem:** No companies are displayed
- Ensure Target Pages (URLs) are configured in plugin settings
- Verify you're on one of the target pages
- Check that J-Business Directory has companies
- If "Show All Companies" is off, ensure there are premium companies
- Verify the plugin is enabled
- Check category matching settings

**Problem:** AI matching not working
- Verify OpenAI API key is correct
- Check your OpenAI account has available credits
- Review warning messages in Joomla for API errors
- Try a different AI model (GPT-4o Mini is most cost-effective)

**Problem:** Styles not loading
- Clear Joomla cache
- Check browser console for errors
- Verify CSS file permissions

**Problem:** All companies shown, not just premium
- This is correct behavior when "Show All Companies" is enabled
- Premium companies appear first in the list
- Check the premium badge to identify premium companies

### Support & Development

For issues, feature requests, or contributions, please visit the project repository.

### License

GNU General Public License version 2 or later

---

## Russian

### Описание

Плагин Premium Companies - это расширение для Joomla 4/5, которое интегрирует компоненты DJ-Classifieds и J-Business Directory. Когда пользователи добавляют заказы через формы DJ-Classifieds, плагин автоматически предлагает релевантные компании из J-Business Directory. Премиум компании отображаются первыми, за ними следуют все остальные компании. Плагин имеет опциональную AI-поддержку через ChatGPT для умного подбора наиболее релевантных компаний для каждого заказа.

### Возможности

- ✅ **Умное отображение**: Показывает компании на определенных страницах (настраивается через URL паттерны)
- ✅ **Приоритетная сортировка**: Сначала премиум компании, затем все остальные
- ✅ **ИИ-подбор**: Опциональная интеграция с ChatGPT для интеллектуальных рекомендаций компаний
- ✅ **Сопоставление по категориям**: Фильтрация компаний по категории объявления
- ✅ **Несколько стилей отображения**: Карточки, Список, Компактный
- ✅ **Гибкая настройка**: Выбор количества компаний для показа (1-50)
- ✅ **Адаптивный дизайн**: Работает на мобильных и десктопах
- ✅ **Богатое отображение**: Логотипы, описания и контактная информация компаний
- ✅ **Премиум значок**: Визуальное выделение премиум компаний
- ✅ **Настраиваемая позиция**: Отображение до или после контента
- ✅ **Поддержка пользовательских CSS**: Полная кастомизация стилей
- ✅ **Мультиязычность**: Поддержка английского и русского языков

### Требования

- **Joomla:** 4.0 или выше (совместим с Joomla 5)
- **PHP:** 7.4 или выше
- **Необходимые компоненты:**
  - DJ-Classifieds
  - J-Business Directory

### Установка

1. Скачайте пакет плагина
2. Перейдите в **Система → Установить → Расширения** в панели администратора Joomla
3. Загрузите ZIP файл плагина
4. Нажмите **Загрузить и установить**
5. Плагин будет автоматически включен после установки

### Настройка

1. Перейдите в **Система → Плагины**
2. Найдите и откройте **Контент - Премиум компании для DJ-Classifieds**
3. Настройте следующие параметры:

#### Основные настройки

| Настройка | Описание | По умолчанию |
|-----------|----------|--------------|
| Целевые страницы (URL) | URL страниц, где должны показываться компании (по одному на строку) | Пусто |
| Максимум компаний | Количество компаний для отображения (1-50) | 10 |
| Показать все компании | Показывать все компании (сначала премиум) или только премиум | Да |
| Совпадение по категории | Показывать только компании, соответствующие категории объявления | Да |
| Позиция отображения | Где показывать компании (До/После контента) | Перед контентом |
| Стиль отображения | Визуальный стиль (Карточки/Список/Компактный) | Карточки |
| Показать логотип компании | Отображать логотипы компаний | Да |
| Пользовательский CSS | Добавить пользовательские стили | Пусто |

#### Настройки ИИ (ChatGPT)

| Настройка | Описание | По умолчанию |
|-----------|----------|--------------|
| Включить ИИ подбор | Использовать ChatGPT для поиска релевантных компаний | Нет |
| API ключ OpenAI | Ваш API ключ OpenAI с platform.openai.com | Пусто |
| Модель ИИ | Модель ChatGPT для использования | GPT-4o Mini |
| Макс результатов ИИ | Максимум компаний, которые вернет ИИ | 5 |

### Использование

После установки и настройки плагин работает автоматически:

1. **Настройте целевые страницы**: Добавьте URL, где должны появляться компании (например, `/index.php?option=com_djclassifieds&view=additem`)
2. **Пользователь добавляет заказ**: Когда пользователи добавляют заказы в DJ-Classifieds
3. **Плагин активируется**: Плагин запрашивает компании из J-Business Directory
4. **Умное отображение**: Премиум компании показываются первыми, затем остальные
5. **ИИ подбор** (опционально): Если включен, ChatGPT анализирует заказ и ранжирует компании по релевантности
6. **Пользователи взаимодействуют**: Пользователи могут кликать на компании для просмотра профилей

### Структура базы данных

Плагин ожидает следующие таблицы J-Business Directory:

- `#__jbusinessdirectory_companies` - Информация о компаниях
- `#__jbusinessdirectory_company_package` - Назначения пакетов компаниям
- `#__jbusinessdirectory_packages` - Определения пакетов (должны иметь `type = 'premium'`)

### Стили отображения

#### Стиль Карточки
Отображает компании в виде сетки привлекательных карточек с логотипами, описаниями и контактной информацией.

#### Стиль Список
Показывает компании в детальном формате списка со всей видимой информацией.

#### Компактный стиль
Минимальное отображение с названием компании и премиум значком, идеально для размещения в боковой панели.

### Кастомизация

Вы можете добавить пользовательские CSS в настройках плагина или переопределить стандартные стили, создав:

```
/templates/ваш-шаблон/css/plg_premiumcompanies_custom.css
```

### Решение проблем

**Проблема:** Компании не отображаются
- Убедитесь, что настроены Целевые страницы (URL) в настройках плагина
- Проверьте, что вы находитесь на одной из целевых страниц
- Убедитесь, что в J-Business Directory есть компании
- Если "Показать все компании" выключено, убедитесь что есть премиум компании
- Убедитесь, что плагин включен
- Проверьте настройки сопоставления по категориям

**Проблема:** ИИ подбор не работает
- Проверьте правильность API ключа OpenAI
- Убедитесь, что на аккаунте OpenAI есть доступные кредиты
- Проверьте предупреждения в Joomla на наличие ошибок API
- Попробуйте другую модель ИИ (GPT-4o Mini наиболее экономичная)

**Проблема:** Стили не загружаются
- Очистите кэш Joomla
- Проверьте консоль браузера на наличие ошибок
- Проверьте права доступа к CSS файлам

**Проблема:** Показываются все компании, а не только премиум
- Это правильное поведение при включенной опции "Показать все компании"
- Премиум компании показываются первыми в списке
- Проверьте премиум значок для определения премиум компаний

### Поддержка и разработка

Для сообщений о проблемах, запросов функций или внесения вклада, пожалуйста, посетите репозиторий проекта.

### Лицензия

GNU General Public License version 2 or later

---

## Development & Contribution

### Project Structure

```
plg_djclassifieds_premiumcompanies/
├── premiumcompanies.xml          # Plugin manifest
├── premiumcompanies.php          # Main plugin file
├── script.php                    # Installation script
├── language/
│   ├── en-GB/
│   │   └── plg_djclassifieds_premiumcompanies.ini
│   └── ru-RU/
│       └── plg_djclassifieds_premiumcompanies.ini
└── assets/
    ├── css/
    │   └── style.css
    └── js/
        └── script.js
```

### Building for Distribution

To create a distribution package:

```bash
cd plg_djclassifieds_premiumcompanies
zip -r plg_content_premiumcompanies_v1.0.0.zip . -x "*.git*" "*.DS_Store"
```

### Version History

- **1.0.0** (2026-01) - Initial release
  - Basic integration between DJ-Classifieds and J-Business Directory
  - Multiple display styles
  - Category matching
  - Responsive design
  - Multilingual support

---

**Created with ❤️ for the Joomla Community**
