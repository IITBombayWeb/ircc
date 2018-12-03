<?php

namespace Drupal\query_parameters_to_url\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class QueryParametersToUrlSettingsForm.
 *
 * @package Drupal\query_parameters_to_url\Form
 */
class QueryParametersToUrlSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'query_parameters_to_url.admin',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('query_parameters_to_url.settings');
    $default_options = array(
      '#states' => array(
        'enabled' => array(
          ':input[name="' . $config->get('query_parameters_to_url_enabled') . '"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['query_parameters_to_url_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable rewriting'),
      '#description' => $this->t('If checked, URLs containing query parameters will be rewritten to Clean URL components.'),
      '#default_value' => $config->get('query_parameters_to_url_enabled'),
    );
    $form['query_parameters_to_url_url_and_query_delimiter'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL and query parameters delimiter'),
      '#description' => $this->t('This string will be placed in the URL, between real URL components and encoded query parameters.<br/><strong>Example:</strong> If the accessed URI is <string>events/all?category=1&page=2</string> the resulting clean URL will be <strong>events/all/p/category/1/page/2</strong>.'),
      '#default_value' => $config->get('query_parameters_to_url_url_and_query_delimiter'),
    ) + $default_options;

    $form['query_parameters_to_url_query_nested_key_delimiter'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Query parameter nested key delimiter'),
      '#description' => $this->t('This string will be used to delimit nested keys for a parameter value.<br/><strong>Example:</strong> If the accessed URI is <strong>events?category_id[0][1][2][3][4]=5</strong> the resulting clearn URL will be <strong>events/all/p/category_id/0__1__2__3__4__5</strong>.'),
      '#default_value' => $config->get('query_parameters_to_url_query_nested_key_delimiter'),
    ) + $default_options;

    $form['query_parameters_to_url_query_nested_value_delimiter'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Query parameter nested values delimiter'),
      '#description' => $this->t('This string will be used to delimit nested parameter values from one another.<br/><strong>Example:</strong> If the accessed URI is <strong>events?category_id[0][1]=2&category_id[3][4]=5</strong> the resulting clearn URL will be <strong>events/all/p/category_id/0__1__2--3__4__5</strong>.'),
      '#default_value' => $config->get('query_parameters_to_url_query_nested_value_delimiter'),
    ) + $default_options;

    $form['query_parameters_to_url_path_reg_exp'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Regular expression to match paths, where rewriting should be enabled'),
      '#description' => $this->t("While you can always reset this configuration and recover without permanent damage to your site, a change to this expression will break old bookmarked URLs. Change only when you know what you're doing.<br>Example: <strong>{(^home|^events|^news/all)}</strong>. To match all paths you can use: <strong>{.+}</strong>. To disable regular expression matching, leave it empty."),
      '#default_value' => $config->get('query_parameters_to_url_path_reg_exp'),
    ) + $default_options;

    $form['advanced_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Advanced settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => FALSE,
    );

    $form['advanced_settings']['hook_hint'] = array(
      '#type' => 'item',
      '#title' => $this->t('Additional paths'),
      '#description' => $this->t('You can enable rewriting for additional paths, by implementing <strong>hook_query_parameters_to_url_rewrite_access()</strong>.'),
    ) + $default_options;

    $form['advanced_settings']['query_parameters_to_url_hooks_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable hook invocation to find additional paths'),
      '#description' => $this->t('If checked, <strong>hook_query_parameters_to_url_rewrite_access()</strong> implementations will be invoked, to allow support for additional rewrite paths.'),
      '#default_value' => $config->get('query_parameters_to_url_hooks_enabled'),
    ) + $default_options;

    $form['advanced_settings']['query_parameters_to_url_rewrite_hooks_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable hook invocation to allow rewriting the final encoded paths'),
      '#description' => $this->t('If checked, <strong>hook_query_parameters_to_url_rewrite_alter()</strong> implementations will be invoked, to allow rewriting the final query parameter encoded URLs. See <em>query_parameters_to_url.api.php</em> for an example and documentation.'),
      '#default_value' => $config->get('query_parameters_to_url_rewrite_hooks_enabled'),
    ) + $default_options;

    $form['advanced_settings']['query_parameters_to_url_redirect_protection_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable protection against redirect loop'),
      '#description' => $this->t('If checked, each redirect in hook_init() is logged, to protect against any possible redirect loops, just like redirect module does it.'),
      '#default_value' => $config->get('query_parameters_to_url_redirect_protection_enabled'),
    ) + $default_options;

    $form['advanced_settings']['query_parameters_to_url_ignore_admin_paths'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Ignore admin paths'),
      '#description' => $this->t('Whether query parameter encoding should be skipped on admin paths.'),
      '#default_value' => $config->get('query_parameters_to_url_ignore_admin_paths'),
    ) + $default_options;

    $form['advanced_settings']['query_parameters_to_url_redirect_protection_enabled'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Redirect loop threshold'),
      '#description' => $this->t('How many redirects are allowed in 15 seconds, before it is considered that a redirect loop has occurred.'),
      '#default_value' => $config->get('query_parameters_to_url_redirect_protection_enabled'),
    ) + $default_options;

    $form['advanced_settings']['query_parameters_to_url_allow_rewritten_menu_item_save'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Allow rewritten menu item save'),
      '#description' => $this->t('If this is checked, you will be able to save an encoded query parameter path as a menu item. Which means you can add a menu item with a path like <em>events/p/page/1</em>. For saving an encoded query parameter path for the front page, make sure you prepend the front page path, like <em>home/p/page/1</em>. <strong>This is an experimental feature.</strong>'),
      '#default_value' => $config->get('query_parameters_to_url_allow_rewritten_menu_item_save'),
    ) + $default_options;

    $form['advanced_settings']['query_parameters_to_url_redirect_status_code'] = array(
      '#type' => 'select',
      '#title' => $this->t('HTTP code to use for redirects'),
      '#description' => $this->t('Choose which HTTP Code should be issued for redirects done by this module.'),
      '#options' => query_parameters_to_url_status_code_options(),
      '#default_value' => $config->get('query_parameters_to_url_redirect_status_code'),
    ) + $default_options;

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
    $config_form->set('query_parameters_to_url_enabled', $form_state->getValue('query_parameters_to_url_enabled'))->save();
    $config_form->set('query_parameters_to_url_url_and_query_delimiter', $form_state->getValue('query_parameters_to_url_url_and_query_delimiter'))->save();
    $config_form->set('query_parameters_to_url_query_nested_key_delimiter', $form_state->getValue('query_parameters_to_url_query_nested_key_delimiter'))->save();
    $config_form->set('query_parameters_to_url_query_nested_value_delimiter', $form_state->getValue('query_parameters_to_url_query_nested_value_delimiter'))->save();
    $config_form->set('query_parameters_to_url_path_reg_exp', $form_state->getValue('query_parameters_to_url_path_reg_exp'))->save();
    $config_form->set('query_parameters_to_url_hooks_enabled', $form_state->getValue('query_parameters_to_url_hooks_enabled'))->save();
    $config_form->set('query_parameters_to_url_rewrite_hooks_enabled', $form_state->getValue('query_parameters_to_url_rewrite_hooks_enabled'))->save();
    $config_form->set('query_parameters_to_url_redirect_protection_enabled', $form_state->getValue('query_parameters_to_url_redirect_protection_enabled'))->save();
    $config_form->set('query_parameters_to_url_ignore_admin_paths', $form_state->getValue('query_parameters_to_url_ignore_admin_paths'))->save();
    $config_form->set('query_parameters_to_url_allow_rewritten_menu_item_save', $form_state->getValue('query_parameters_to_url_allow_rewritten_menu_item_save'))->save();
    $config_form->set('query_parameters_to_url_redirect_status_code', $form_state->getValue('query_parameters_to_url_redirect_status_code'))->save();
  }

}
