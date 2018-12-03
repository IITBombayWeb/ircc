<?php

namespace Drupal\query_parameters_to_url\PathProcessor;

use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\PathProcessor\InBoundPathProcessorInterface;
use Drupal\Core\Url;

/**
 * Provides a null implementation of the path processor manager.
 *
 * This can be used for example in really early installer phases.
 */
class QueryParametersToUrlPathProcessorManager implements OutboundPathProcessorInterface, InBoundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {

    $config = \Drupal::getContainer()->get('config.factory')->getEditable('query_parameters_to_url.settings');

    if ($config->get('query_parameters_to_url_enabled') && isset($request)) {
      $original_path = $request->getRequestUri();
      // Check if this is a path we should rewrite.
      $rewrite = query_parameters_to_url_path_should_be_rewritten($path, $options, $request);
      if ($rewrite) {
        // If asked to skip the check, by hook_init(), skip it.
        $skip_global_redirect_state =
        isset($options['skip_global_redirect']) &&
        $options['skip_global_redirect'] == TRUE;
        // Don't encode query parameters if called from globalredirect_init().
        // The hook_outbound_url_alter() will be called twice from inside
        // globalredirect_init().
        if (!$skip_global_redirect_state && query_parameters_to_url_is_called_from_global_redirect_init()) {
          return $path;
        }

        // Check if link has any query parameters set.
        if (isset($options['query']) && !empty($options['query'])) {

          $query_parameters_components = '';

          // The new path parts array, containing the the query parameters, will
          // be delimited with special character, so we know where the real URL
          // components start, and where the encoded query parameters start.
          $url_and_query_delimiter = $config->get('query_parameters_to_url_url_and_query_delimiter');

          // Make sure to replace <front> with front page url.
          if ($path == '<front>') {
            //$path = variable_get('site_frontpage', 'node');
          }
          // Make sure to trim the rightmost slashes, so double slashes don't
          // occur in the path.
          $new_path_no_delimiter = rtrim($path, '/');
          $new_path = $new_path_no_delimiter . '/' . $url_and_query_delimiter;

          if ($config->get('query_parameters_to_url_rewrite_hooks_enabled')) {
            // Allow altering the final outbound url, before encoding.
            $context = array(
              'direction' => 'outbound',
              'options' => &$options,
              'original_path' => $original_path,
              'new_path_without_parameters' => &$new_path,
            );
            \Drupal::moduleHandler()->alter('query_parameters_to_url_rewrite', $path, $context);
          }
          // drupal_set_message($path,"error");.
          foreach ($options['query'] as $key => $values) {
            // Encode the query parameter values to clean url component.
            $encoded_query_parameter_values = query_parameters_to_url_encode_query_parameter_values($values);

            // Add the encoded query parameter values to the end of path, only
            // if it actually has any values.
            $is_blank = empty($encoded_query_parameter_values) && !is_numeric($encoded_query_parameter_values);
            if (!$is_blank) {
              $query_parameters_components .= '/' . $key . '/' . $encoded_query_parameter_values;
            }
            // Unset the query parameter encoded in the URL, so it's not
            // included in the generated link.
            unset($options['query'][$key]);
          }

          // If there were any query parameters to encode, replace the original
          // path with a new path that contains the encoded query parameters.
          if (!empty($query_parameters_components)) {
            $path = $new_path . $query_parameters_components;
          }
        }
      }
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $original_path = $request->getRequestUri();
    $path_language = $request->getPreferredLanguage();

    $config = \Drupal::getContainer()->get('config.factory')->getEditable('query_parameters_to_url.settings');
    if ($config->get('query_parameters_to_url_enabled')) {

      /*
       * Don't rewrite URL, if trying to save a menu item with a rewritten URL.
       * URLs with aliases or query parameters cannot be saved as a menu item.
       * By not calling url_inbound_alter when saving the menu item path, we
       * allow the rewritten URL to be saved to the database, which overcomes
       * this limitation. This is a hack, and should not be used if possible.
       * @see https://www.drupal.org/node/118072#comment-9492455
       */
      if (query_parameters_to_url_is_called_from_menu_item_edit()) {
        return;
      }

      $parts = explode('/', $path);
      $url_and_query_delimiter = $config->get('query_parameters_to_url_url_and_query_delimiter');

      // Check if this is a path we should rewrite.
      $rewrite = query_parameters_to_url_path_should_be_rewritten($path, array(), $request);

      if ($rewrite && ($p_key = array_search($url_and_query_delimiter, $parts)) !== FALSE) {
        $last_part_key = count($parts);

        // Extract all the path parts that are actually encoded query
        // parameters.
        $extracted_query_parameters = array();
        for ($i = $p_key + 1; $i < $last_part_key; $i += 2) {
          $query_key = $parts[$i];

          // Validation that there are actually pairs of key-value.
          if (!isset($parts[$i + 1])) {
            continue;
          }
          $query_values = $parts[$i + 1];
          // var_dump($query_values);
          // Decode the query parameters values string.
          $query_parameter_values_array = query_parameters_to_url_decode_query_parameter_values($query_values);
          $extracted_query_parameters[$query_key] = $query_parameter_values_array;
        }

        if (!empty($extracted_query_parameters)) {

          // Save the initial inbound path to be used in the cache cid alter.
          $inbound_path = &drupal_static(__FUNCTION__ . '_inbound_path', '');
          $inbound_path = $path;

          // Remove the encoded query parameters in the path, and set it as the
          // inbound path.
          $path = implode('/', array_slice($parts, 0, $p_key));

          // Make a copy of the path for the request_uri, before the front page
          // check, so that the request URI has the empty string path, to make
          // sure global redirect module doesn't cause endless redirect loops,
          // by constantly comparing the request uri to the 'q' parameter, and
          // always seeing that request uri isn't empty, when it actually
          // expects it to be empty for the front page.
          $request_uri_path = $path;

          // Make sure to prepend the base path to the request URI, in case the
          // Drupal environment is not a virtual host, but rather a sub-folder
          // of the main domain. This also fixes endless redirect loops, when
          // Global Redirect is enabled.
          $base_path = base_path();
          if (!empty($base_path)) {
            $request_uri_path = $base_path . $request_uri_path;
          }
          //var_dump($GLOBALS['base_path']);
          // If path is empty, make sure to set it to the actual front page
          // path because otherwise Drupal can't find the proper menu item,
          // and returns page not found.
          if (empty($path)) {
            $path = $GLOBALS['base_path']. "/";
          }

          // Mark that the inbound URL was altered, so that hook_init doesn't do
          // an endless redirect loop.
          $path_altered = &drupal_static(__FUNCTION__, FALSE);
          $path_altered = TRUE;

          if ($config->get('query_parameters_to_url_rewrite_hooks_enabled')) {
            // Allow altering the final inbound url.
            $context = array(
              'direction' => 'inbound',
              'original_path' => $original_path,
              'path_language' => $path_language,
              'extracted_query_parameters' => &$extracted_query_parameters,
              'request_uri_path' => &$request_uri_path,
              'initial_url_components' => $parts,
            );

            \Drupal::moduleHandler()->alter('query_parameters_to_url_rewrite', $path, $context);
          }

          // If there were any query parameters imploded in the URL, put them
          // into $_GET, as if they were originally there.
          // $_GET += $extracted_query_parameters;.
          foreach ($extracted_query_parameters as $key => $value) {
            //var_dump($value);
            $request->query->set($key, $value);
          }
          // Get a copy of all query parameters except for q.
          $query_parameters_without_q = $request->query->all();

          // Build new REQUEST_URI, because certain modules extract the query
          // parameters from inside it, instead of $_GET. One example is Better
          // Exposed filters.
          // So the new REQUEST_URI will contain the new inbound path,
          // concatenated with all the query parameters present.
          // @ignore security_12
          if (isset($_SERVER['REQUEST_URI'])) {
            $query_string = http_build_query($query_parameters_without_q, '');
            $new_request_uri = $request_uri_path . '?' . $query_string;
            // var_dump($query_string);
            // @TODO Is this really a sec hole? If so what can we do about it?
            // @ignore security_12
            $_SERVER['REQUEST_URI'] = $new_request_uri;

            if (isset($_SERVER['QUERY_STRING'])) {
              $_SERVER['QUERY_STRING'] = $query_string;
            }

            if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
              $_SERVER['REDIRECT_QUERY_STRING'] = $query_string;
            }
            //var_dump($new_request_uri);
            //return $new_request_uri;
          }
        }
      }
    }
    
    return $path;
  }

}
