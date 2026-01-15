# Premium Companies Plugin для Joomla 4/5

[English](#english) | [Русский](#russian)

---

## English

### Description

Premium Companies Plugin is a Joomla 4/5 extension that integrates DJ-Classifieds and J-Business Directory components. When users add or view classified ads in DJ-Classifieds, the plugin automatically suggests relevant premium companies from J-Business Directory that have premium subscription packages.

### Features

- ✅ Automatic display of premium companies when viewing DJ-Classifieds items
- ✅ Category-based matching between classifieds and companies
- ✅ Multiple display styles: Cards, List, Compact
- ✅ Customizable number of companies to display
- ✅ Responsive design for mobile and desktop
- ✅ Company logos and contact information display
- ✅ Premium badge highlighting
- ✅ Configurable display position (before/after content)
- ✅ Custom CSS support
- ✅ Multilingual support (English & Russian)

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

#### Plugin Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Maximum Companies to Display | Number of premium companies to show (1-20) | 5 |
| Match by Category | Show only companies matching the classified ad category | Yes |
| Display Position | Where to show companies (Before/After Content/Sidebar) | Before Content |
| Display Style | Visual style (Cards/List/Compact) | Cards |
| Show Company Logo | Display company logos | Yes |
| Custom CSS | Add custom styling | Empty |

### Usage

Once installed and configured, the plugin works automatically:

1. Users visit a classified ad in DJ-Classifieds
2. The plugin queries J-Business Directory for companies with premium packages
3. Matching companies are displayed based on your settings
4. Users can click on companies to view their profiles

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
- Ensure J-Business Directory has companies with premium packages
- Check that packages have `type = 'premium'` in the database
- Verify the plugin is enabled
- Check category matching settings

**Problem:** Styles not loading
- Clear Joomla cache
- Check browser console for errors
- Verify CSS file permissions

### Support & Development

For issues, feature requests, or contributions, please visit the project repository.

### License

GNU General Public License version 2 or later

---

## Russian

### Описание

Плагин Premium Companies - это расширение для Joomla 4/5, которое интегрирует компоненты DJ-Classifieds и J-Business Directory. Когда пользователи добавляют или просматривают объявления в DJ-Classifieds, плагин автоматически предлагает релевантные премиум компании из J-Business Directory, у которых есть премиум тарифные пакеты.

### Возможности

- ✅ Автоматический показ премиум компаний при просмотре объявлений DJ-Classifieds
- ✅ Сопоставление по категориям между объявлениями и компаниями
- ✅ Несколько стилей отображения: Карточки, Список, Компактный
- ✅ Настраиваемое количество отображаемых компаний
- ✅ Адаптивный дизайн для мобильных устройств и десктопа
- ✅ Отображение логотипов и контактной информации компаний
- ✅ Выделение премиум статуса
- ✅ Настраиваемая позиция отображения (до/после контента)
- ✅ Поддержка пользовательских CSS
- ✅ Мультиязычная поддержка (Английский и Русский)

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

#### Настройки плагина

| Настройка | Описание | По умолчанию |
|-----------|----------|--------------|
| Максимум компаний для показа | Количество премиум компаний для отображения (1-20) | 5 |
| Совпадение по категории | Показывать только компании, соответствующие категории объявления | Да |
| Позиция отображения | Где показывать компании (Перед/После контента/Боковая панель) | Перед контентом |
| Стиль отображения | Визуальный стиль (Карточки/Список/Компактный) | Карточки |
| Показать логотип компании | Отображать логотипы компаний | Да |
| Пользовательский CSS | Добавить пользовательские стили | Пусто |

### Использование

После установки и настройки плагин работает автоматически:

1. Пользователи посещают объявление в DJ-Classifieds
2. Плагин запрашивает компании с премиум пакетами из J-Business Directory
3. Подходящие компании отображаются в соответствии с вашими настройками
4. Пользователи могут кликнуть на компании для просмотра их профилей

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
- Убедитесь, что в J-Business Directory есть компании с премиум пакетами
- Проверьте, что пакеты имеют `type = 'premium'` в базе данных
- Убедитесь, что плагин включен
- Проверьте настройки сопоставления по категориям

**Проблема:** Стили не загружаются
- Очистите кэш Joomla
- Проверьте консоль браузера на наличие ошибок
- Проверьте права доступа к CSS файлам

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
