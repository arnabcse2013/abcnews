<?php

namespace Drupal\smart_read_more_link\Plugin\Field\FieldFormatter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "smart_read_more_link",
 *   label = @Translation("Smart read more link"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary"
 *   }
 * )
 */
class SmartReadMoreLinkFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Summary Formatter.
   *
   * @var \Drupal\text\Plugin\Field\FieldFormatter\TextSummaryOrTrimmedFormatter
   */
  protected $summaryFormatter;
  /**
   * Default Formatter.
   *
   * @var \Drupal\text\Plugin\Field\FieldFormatter\TextSummaryOrTrimmedFormatter
   */
  protected $defaultFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, PluginManagerInterface $pluginManager, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->summaryFormatter = $pluginManager->createInstance(
      'text_summary_or_trimmed', [
        'field_definition' => $field_definition,
        'settings' => $settings,
        'label' => $label,
        'view_mode' => $view_mode,
        'third_party_settings' => $third_party_settings,
      ]
    );
    $this->defaultFormatter = $pluginManager->createInstance(
      'text_default', [
        'field_definition' => $field_definition,
        'settings' => $settings,
        'label' => $label,
        'view_mode' => $view_mode,
        'third_party_settings' => $third_party_settings,
      ]
    );
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.field.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'trim_length' => '600',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return $this->summaryFormatter->settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->summaryFormatter->settingsSummary();
  }

  /**
   * View elements.
   *
   * @inheritdoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = $this->summaryFormatter->viewElements($items, $langcode);
    $defaults = $this->defaultFormatter->viewElements($items, $langcode);
    $elements_copy = $elements;
    $elementsMarkup = $this->renderer->render($elements_copy);
    $defaultsMarkup = $this->renderer->render($defaults);
    $readMore = (string) $elementsMarkup !== (string) $defaultsMarkup;
    if ($readMore) {
      $entity = $items->getEntity();
      $node_title_stripped = strip_tags($entity->label());
      $links['body-readmore'] = [
        'title' => $this->t('Read more<span class="visually-hidden"> about @title</span>', [
          '@title' => $node_title_stripped,
        ]),
        'url' => $entity->toUrl(),
        'language' => $entity->language(),
        'attributes' => [
          'rel' => 'tag',
          'title' => $node_title_stripped,
        ],
      ];
      $elements[count($elements) - 1]['links'] = [
        '#theme' => 'links__node__node',
        '#links' => $links,
        '#attributes' => ['class' => ['links', 'inline']],
      ];

    }
    return $elements;
  }

}
