<?php

namespace Drupal\query_parameters_to_url\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class QueryParametersToUrlExamplesForm.
 *
 * @package Drupal\query_parameters_to_url\Form
 */
class QueryParametersToUrlExamplesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'query_parameters_to_url.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_examples_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $text_field_size = 180;

    $form['example_urls'] = array(
      '#type' => 'item',
      '#markup' => $this->t('<h2>Below you can find example URLs and the new encoded paths.</h2>'),
    );

    $example = 'events?field_event_category_target_id[0]=1&field_event_category_target_id[1]=2&og_group_ref_target_id[0]=100';
    list($encoded, $options) = query_parameters_to_url_parse_uri($example);
    query_parameters_to_url_encode_url($encoded, $options, $example);
    $percentage_saved = query_parameters_to_url_compute_saved_char_percentage($encoded, $example);

    $form['example_1'] = array(
      '#type' => 'textfield',
      '#title' => 'URL Example 1',
      '#default_value' => $example,
      '#size' => $text_field_size,
    );

    $form['encoded_1'] = array(
      '#type' => 'textfield',
      '#title' => 'Encoded URL Example 1',
      '#default_value' => $encoded,
      '#size' => $text_field_size,
    );

    $form['characters_saved_1'] = array(
      '#type' => 'item',
      '#markup' => $this->t('%count% characters saved with the new encoded path.', array('%count' => $percentage_saved)),
    );

    $example = 'search/node?keys=Hello+WOrld';
    list($encoded, $options) = query_parameters_to_url_parse_uri($example);
    query_parameters_to_url_encode_url($encoded, $options, $example);
    $percentage_saved = query_parameters_to_url_compute_saved_char_percentage($encoded, $example);

    $form['example_2'] = array(
      '#type' => 'textfield',
      '#title' => 'URL Example 2',
      '#default_value' => $example,
      '#size' => $text_field_size,
    );

    $form['encoded_2'] = array(
      '#type' => 'textfield',
      '#title' => 'Encoded URL Example 2',
      '#default_value' => $encoded,
      '#size' => $text_field_size,
    );

    $form['characters_saved_2'] = array(
      '#type' => 'item',
      '#markup' => $this->t('%count% characters saved with the new encoded path.', array('%count' => $percentage_saved)),
    );

    $example = 'download-files?file[0]=a&file[]=b&file[]=c&file[]=d&file[]=e';
    list($encoded, $options) = query_parameters_to_url_parse_uri($example);
    query_parameters_to_url_encode_url($encoded, $options, $example);
    $percentage_saved = query_parameters_to_url_compute_saved_char_percentage($encoded, $example);

    $form['example_3'] = array(
      '#type' => 'textfield',
      '#title' => 'URL Example 3',
      '#default_value' => $example,
      '#size' => $text_field_size,
    );

    $form['encoded_3'] = array(
      '#type' => 'textfield',
      '#title' => 'Encoded URL Example 3',
      '#default_value' => $encoded,
      '#size' => $text_field_size,
    );

    $form['characters_saved_3'] = array(
      '#type' => 'item',
      '#markup' => $this->t('%count% characters saved with the new encoded path.', array('%count' => $percentage_saved)),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config_form = \Drupal::getContainer()->get('config.factory')->getEditable('query_parameters_to_url.settings');
    $config_form->set('custom_elements', $form_state->getValue('custom_elements'))->save();
    $config_form->set('buttons_styles', $form_state->getValue('buttons_styles'))->save();
    $config_form->set('social_buttons', $form_state->getValue('social_buttons'))->save();
  }

}
