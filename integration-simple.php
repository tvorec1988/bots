<?php
/**
 * ПРОСТАЯ ИНТЕГРАЦИЯ - добавьте этот код в нужное место шаблона
 * Рекомендуется вставить после <?php echo $this->loadTemplate('categoryfields'); ?>
 */

// Показываем рекомендуемые компании
echo JPluginHelper::importPlugin('content');
$dispatcher = JEventDispatcher::getInstance();
$article = new stdClass();
$article->cat_id = JFactory::getApplication()->input->getInt('cat_id', 0);
$params = new JRegistry();
$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_djclassifieds.additem', &$article, &$params, 0));
foreach ($results as $result) {
    if (!empty($result)) {
        echo $result;
    }
}
?>
