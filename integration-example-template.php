<?php
/**
 * Модифицированный шаблон DJ-Classifieds с интеграцией Premium Companies Plugin
 * Добавлен триггер события для показа компаний
 */

defined ('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.tooltip', '.Tips1', array('container' => '#dj-classifieds'));
HTMLHelper::_('bootstrap.tooltip', '.Tips2', array('container' => '#dj-classifieds'));

$app = Factory::getApplication();
$user = Factory::getUser();
$par = $this->par;

$id = $this->item->id;
$copy = $app->input->getInt('copy', 0);
$token = $app->input->getCMD('token', '');

?>

<div id="dj-classifieds" class="clearfix djcftheme-<?php echo $par->get('theme','default'); ?> <?php echo $par->get('pageclass_sfx'); ?>">
	<?php if($this->page_heading){ ?>
		<h1><?php echo $this->page_heading; ?></h1>
	<?php } ?>
	<?php echo DJClassifiedsTheme::renderModule('djcf-top'); ?>
	<?php echo DJClassifiedsTheme::renderModule('djcf-additem-top'); ?>
	<div class="dj-additem<?php echo $id || $token ? ' edit' : ''; ?> clearfix">
		<form action="<?php echo Route::_('index.php'); ?>" method="post" class="form-validate" name="djForm" id="djForm" enctype="multipart/form-data">
			<div class="additem_djform">
				<div class="title_top"><?php
					if($copy){
						echo Text::_('COM_DJCLASSIFIEDS_COPY_OF_AD');
					}else if($id || $token){
						echo Text::_('COM_DJCLASSIFIEDS_EDIT_AD');
					}else{
						echo Text::_('COM_DJCLASSIFIEDS_NEW_AD');
					}
				?></div>
				<div class="additem_djform_in">
					<?php foreach($this->plugin_title as $plugin_title) echo $plugin_title; ?>
					<?php if($par->get('points', 0) && (!$user->id || !$this->points_used_by_user)){ ?>
						<div class="djform_row points_info_row">
							<div class="points_info_row_in">
								<?php echo Text::sprintf('COM_DJCLASSIFIEDS_POINTS_PACKAGE_INFO_IN_NEW_ADVERT_N', '<a href="'.DJClassifiedsSEO::getViewUri('points').'">'.Text::_('COM_DJCLASSIFIEDS_POINTS_PACKAGES').'</a>'); ?>
							</div>
						</div>
					<?php } ?>
					<?php echo $this->loadTemplate('title'); ?>
					<?php echo $this->loadTemplate('category'); ?>
					<?php foreach($this->plugin_category as $plugin_category) echo $plugin_category; ?>
					<?php echo $this->loadTemplate('categoryfields'); ?>

					<?php
					// ============================================================
					// ИНТЕГРАЦИЯ PREMIUM COMPANIES PLUGIN
					// Показываем рекомендуемые компании после выбора категории
					// ============================================================
					use Joomla\CMS\Plugin\PluginHelper;

					// Импортируем content плагины
					PluginHelper::importPlugin('content');

					// Создаем фиктивный объект для передачи в плагин
					$article = new stdClass();
					$article->text = '';
					$article->cat_id = $app->input->getInt('cat_id', 0);

					// Создаем пустой объект параметров
					$params = new \Joomla\Registry\Registry();

					// Триггерим событие onContentBeforeDisplay
					$dispatcher = Factory::getApplication()->getDispatcher();
					$results = $dispatcher->triggerEvent('onContentBeforeDisplay', array(
						'com_djclassifieds.additem',
						&$article,
						&$params,
						0
					));

					// Выводим результаты от плагинов
					if (!empty($results)) {
						foreach ($results as $result) {
							if (!empty($result)) {
								echo $result;
							}
						}
					}
					?>

					<?php if($par->get('show_introdesc','1')){ ?>
						<?php echo $this->loadTemplate('introdesc'); ?>
					<?php } ?>
					<?php if($par->get('show_description','1')){ ?>
						<?php echo $this->loadTemplate('description'); ?>
					<?php } ?>
					<?php if($par->get('seo_metadesc_user_edit','0')){ ?>
						<?php echo $this->loadTemplate('metadesc'); ?>
					<?php } ?>
					<?php if($par->get('seo_keywords_user_edit','0')){ ?>
						<?php echo $this->loadTemplate('metakey'); ?>
					<?php } ?>
					<?php if($par->get('show_types','0')){ ?>
						<?php echo $this->loadTemplate('type'); ?>
					<?php } ?>
					<?php if($par->get('show_regions','1') && $this->regions){ ?>
						<?php echo $this->loadTemplate('region'); ?>
						<?php foreach($this->plugin_region as $plugin_region) echo $plugin_region; ?>
					<?php } ?>
					<?php if($par->get('show_address','1')){ ?>
						<?php echo $this->loadTemplate('address'); ?>
					<?php } ?>
					<?php if($par->get('show_postcode','0')){ ?>
						<?php echo $this->loadTemplate('postcode'); ?>
					<?php } ?>
					<?php if($par->get('allow_user_lat_lng','0')){ ?>
						<?php echo $this->loadTemplate('latlng'); ?>
					<?php } ?>
					<?php if($par->get('durations_list','1') && $this->is_new){ ?>
						<?php echo $this->loadTemplate('duration'); ?>
					<?php } ?>
					<?php if($par->get('show_contact','1')){ ?>
						<?php echo $this->loadTemplate('contact'); ?>
					<?php } ?>
					<?php echo $this->loadTemplate('contactfields'); ?>
					<?php if($par->get('email_for_guest','0') && !$user->id){ ?>
						<?php echo $this->loadTemplate('email'); ?>
					<?php } ?>
					<?php if($par->get('show_website','0')){ ?>
						<?php echo $this->loadTemplate('website'); ?>
					<?php } ?>
					<?php if($par->get('show_video','0')){ ?>
						<?php echo $this->loadTemplate('video'); ?>
					<?php } ?>
					<?php if($par->get('buynow','0')){ ?>
						<?php echo $this->loadTemplate('buynow'); ?>
					<?php } ?>
					<?php if($par->get('show_price','1')){ ?>
						<?php echo $this->loadTemplate('price'); ?>
					<?php } ?>
					<?php if($par->get('auctions','0')){ ?>
						<?php echo $this->loadTemplate('auctions'); ?>
					<?php } ?>
					<?php foreach($this->plugin_rows as $plugin_row) echo $plugin_row; ?>
				</div>
			</div>
			<?php if($par->get('img_limit','3') > 0){ ?>
				<?php echo $this->loadTemplate('images'); ?>
			<?php } ?>
			<?php foreach($this->plugin_sections as $plugin_section) echo $plugin_section; ?>
			<?php if($par->get('promotion','1')=='1' && $this->promotions){ ?>
				<?php echo $this->loadTemplate('promotions'); ?>
			<?php } ?>

			<?php if($par->get('terms',1)>0 && $par->get('terms_article_id',0)>0 && $this->terms_link && !$id && !$token){ ?>
				<?php echo DJClassifiedsTheme::renderLayout('termsconditions', array('link' => $this->terms_link, 'par' => $par)); ?>
			<?php } ?>
			<?php if($par->get('privacy_policy',0)>0 && $par->get('privacy_policy_article_id',0)>0 && $this->privacy_policy_link && !$id && !$token && $user->id==0){ ?>
				<?php echo DJClassifiedsTheme::renderLayout('termsprivacy', array('link' => $this->privacy_policy_link, 'par' => $par)); ?>
			<?php } ?>
			<?php if($par->get('gdpr_agreement',1)>0 && !$id && !$token && $user->id==0){ ?>
				<?php echo DJClassifiedsTheme::renderLayout('terms', array('name' => 'gdpr_agreement', 'info' => $par->get('gdpr_agreement_info', 'COM_DJCLASSIFIEDS_GDPR_AGREEMENT_LABEL'))); ?>
			<?php } ?>

			<?php if($this->captcha){ ?>
				<?php echo $this->captcha; ?>
			<?php } ?>

			<div class="classifieds_buttons">
				<a class="uk-button uk-button-small btn btn-secondary cancel" href="<?php echo $this->cancel_link; ?>"><?php echo Text::_('COM_DJCLASSIFIEDS_CANCEL'); ?></a>
				<button class="uk-button uk-button-small btn btn-primary djvalidate" type="submit" id="submit_button"><?php echo Text::_('COM_DJCLASSIFIEDS_SAVE'); ?></button>
				<?php if($par->get('ad_preview','0')){ ?>
					<button type="button" class="uk-button uk-button-small btn btn-primary" id="preview_button"><?php echo Text::_('COM_DJCLASSIFIEDS_PREVIEW'); ?></button>
				<?php } ?>
			</div>
			<input type="hidden" name="option" value="com_djclassifieds" />
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
			<input type="hidden" name="token" value="<?php echo $token; ?>" />
			<input type="hidden" name="view" value="additem" />
			<input type="hidden" name="task" value="save" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
