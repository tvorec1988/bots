<?php
/**
 * Пример интеграции Premium Companies Plugin В ФОРМУ добавления заказа
 * Режим: INLINE (встроенный) - гармонично отображается внутри формы
 *
 * ВАЖНО: В настройках плагина установите:
 * - Display Style: "Inline (Form Integration)"
 * - Display Position: "Before Content" или "After Content"
 */

defined ('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.tooltip', '.Tips1', array('container' => '#dj-classifieds'));

$app = Factory::getApplication();
$user = Factory::getUser();
$par = $this->par;

$id = $this->item->id;
?>

<div id="dj-classifieds" class="clearfix djcftheme-<?php echo $par->get('theme','default'); ?> <?php echo $par->get('pageclass_sfx'); ?>">
	<?php if($this->page_heading){ ?>
		<h1><?php echo $this->page_heading; ?></h1>
	<?php } ?>

	<?php echo DJClassifiedsTheme::renderModule('djcf-top'); ?>

	<div class="dj-additem<?php echo $id ? ' edit' : ''; ?> clearfix">
		<form action="<?php echo Route::_('index.php'); ?>" method="post" class="form-validate" name="djForm" id="djForm" enctype="multipart/form-data">

			<!-- YooTheme Card Container -->
			<div class="uk-card uk-card-default uk-margin-medium-bottom">
				<div class="uk-card-header">
					<h3 class="uk-card-title">
						<?php echo $id ? Text::_('COM_DJCLASSIFIEDS_EDIT_AD') : Text::_('COM_DJCLASSIFIEDS_NEW_AD'); ?>
					</h3>
				</div>

				<div class="uk-card-body">
					<!-- Название заказа -->
					<?php echo $this->loadTemplate('title'); ?>

					<!-- Категория -->
					<?php echo $this->loadTemplate('category'); ?>

					<!-- Описание заказа -->
					<?php if($par->get('show_introdesc','1')){ ?>
						<?php echo $this->loadTemplate('introdesc'); ?>
					<?php } ?>

					<?php if($par->get('show_description','1')){ ?>
						<?php echo $this->loadTemplate('description'); ?>
					<?php } ?>

					<!-- ============================================================
					     ИНТЕГРАЦИЯ PREMIUM COMPANIES PLUGIN - INLINE MODE
					     Рекомендуемые компании отображаются прямо в форме
					     ============================================================ -->
					<?php
					// Импортируем content плагины
					PluginHelper::importPlugin('content');

					// Создаем объект для передачи в плагин
					$article = new stdClass();
					$article->text = $app->input->get('description', '', 'raw');
					$article->cat_id = $app->input->getInt('cat_id', 0);

					// Параметры
					$params = new \Joomla\Registry\Registry();

					// Триггерим событие onContentBeforeDisplay
					$dispatcher = Factory::getApplication()->getDispatcher();
					$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array(
						'com_djclassifieds.additem',
						&$article,
						&$params,
						0
					));

					// Выводим результаты (плагин вернет красиво оформленный блок)
					if (!empty($results)) {
						foreach ($results as $result) {
							if (!empty($result)) {
								echo $result;
							}
						}
					}
					?>
					<!-- ============================================================ -->

					<!-- Дополнительные поля -->
					<?php echo $this->loadTemplate('categoryfields'); ?>

					<!-- Регион -->
					<?php if($par->get('show_regions','1') && $this->regions){ ?>
						<?php echo $this->loadTemplate('region'); ?>
					<?php } ?>

					<!-- Адрес -->
					<?php if($par->get('show_address','1')){ ?>
						<?php echo $this->loadTemplate('address'); ?>
					<?php } ?>

					<!-- Контакты -->
					<?php if($par->get('show_contact','1')){ ?>
						<?php echo $this->loadTemplate('contact'); ?>
					<?php } ?>

					<!-- Email (для гостей) -->
					<?php if($par->get('email_for_guest','0') && !$user->id){ ?>
						<?php echo $this->loadTemplate('email'); ?>
					<?php } ?>

					<!-- Цена -->
					<?php if($par->get('show_price','1')){ ?>
						<?php echo $this->loadTemplate('price'); ?>
					<?php } ?>
				</div>

				<div class="uk-card-footer">
					<div class="uk-flex uk-flex-between uk-flex-middle">
						<a class="uk-button uk-button-default" href="<?php echo $this->cancel_link; ?>">
							<span uk-icon="icon: close"></span> <?php echo Text::_('COM_DJCLASSIFIEDS_CANCEL'); ?>
						</a>
						<button class="uk-button uk-button-primary djvalidate" type="submit" id="submit_button">
							<span uk-icon="icon: check"></span> <?php echo Text::_('COM_DJCLASSIFIEDS_SAVE'); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Изображения -->
			<?php if($par->get('img_limit','3') > 0){ ?>
				<div class="uk-card uk-card-default uk-margin-medium-bottom">
					<div class="uk-card-header">
						<h3 class="uk-card-title"><?php echo Text::_('COM_DJCLASSIFIEDS_IMAGES'); ?></h3>
					</div>
					<div class="uk-card-body">
						<?php echo $this->loadTemplate('images'); ?>
					</div>
				</div>
			<?php } ?>

			<!-- Продвижение -->
			<?php if($par->get('promotion','1')=='1' && $this->promotions){ ?>
				<div class="uk-card uk-card-default uk-margin-medium-bottom">
					<div class="uk-card-header">
						<h3 class="uk-card-title"><?php echo Text::_('COM_DJCLASSIFIEDS_PROMOTIONS'); ?></h3>
					</div>
					<div class="uk-card-body">
						<?php echo $this->loadTemplate('promotions'); ?>
					</div>
				</div>
			<?php } ?>

			<!-- Условия использования -->
			<?php if($par->get('terms',1)>0 && $this->terms_link && !$id){ ?>
				<div class="uk-margin-medium-bottom">
					<?php echo DJClassifiedsTheme::renderLayout('termsconditions', array('link' => $this->terms_link, 'par' => $par)); ?>
				</div>
			<?php } ?>

			<!-- Капча -->
			<?php if($this->captcha){ ?>
				<div class="uk-margin-medium-bottom">
					<?php echo $this->captcha; ?>
				</div>
			<?php } ?>

			<input type="hidden" name="option" value="com_djclassifieds" />
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
			<input type="hidden" name="view" value="additem" />
			<input type="hidden" name="task" value="save" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>

<style>
/* Дополнительные стили для гармоничной интеграции */
.premium-companies-inline {
	margin: 20px 0;
}

/* Адаптация для мобильных устройств */
@media (max-width: 640px) {
	.premium-companies-inline .uk-grid-small > * {
		width: 100% !important;
	}
}
</style>

<script>
// Опционально: динамическое обновление рекомендаций при изменении описания
document.addEventListener('DOMContentLoaded', function() {
	var descField = document.querySelector('[name="description"]');
	if (descField) {
		// Можно добавить AJAX обновление рекомендаций при изменении текста
		// Пример: debounced AJAX запрос к плагину для обновления списка компаний
	}
});
</script>
