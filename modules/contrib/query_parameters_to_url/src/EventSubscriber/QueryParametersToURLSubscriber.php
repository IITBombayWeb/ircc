<?php

namespace Drupal\query_parameters_to_url\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Url;

/**
 * Implements EventSubscriberInterface.
 */
class QueryParametersToURLSubscriber implements EventSubscriberInterface {
  protected $message;
  protected $redirect_url;
  protected $shouldSend = FALSE;

  /**
   *
   */
  public function applyRedirection(FilterResponseEvent $event) {
    if ($shouldSend) {
      $event->getResponse()->headers->set('Status', $status_message);
      $event->getResponse()->headers->set('Location', $redirect_url);
    }
  }

  /**
   * Checks the Redirection.
   *
   * There are situations when we can't alter a link, like a submitted Views
   * exposed filter, which sets the query parameters via a form,
   * with the form action set to GET. In those cases we will do a redirect to the
   * Clean URL, only the specified paths.
   */
  public function checkForRedirection(GetResponseEvent $event) {
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('query_parameters_to_url.settings');
    if ($config->get('query_parameters_to_url_enabled')) {
      // Check whether the path was altered in inbound alter function, which means
      // we shouldn't rewrite the URL again.
      $path_altered = &drupal_static('query_parameters_to_url_url_inbound_alter', FALSE);
      if (!$path_altered) {

        // Get current path and query parameters, and prepare to send them to the
        // outbound alter hook, so it tries and rewrites a path with query
        // parameters into a new clean URL with encoded query parameters.
        $original_path = \Drupal::service('path.current')->getPath();

        // Hack support for global redirect module, to make sure that the front
        // page has an empty string for the URL.
        if (
        \Drupal::service('path.matcher')->isFrontPage()
        && \Drupal::moduleHandler()->moduleExists('globalredirect')
        && ($settings = _globalredirect_get_settings())
        && $settings['frontpage_redirect']
          ) {
          $original_path = '';
        }
        $request = \Drupal::request();
        $original_query_parameters = \Drupal::request()->query->all();
        $path = $original_path;
        $options = array(
          'query' => $original_query_parameters,
        );

        // Skip the global redirect check, because we know this is not called
        // from globalredirect_init().
        $options['skip_global_redirect'] = TRUE;

        // Try and rewrite the path.
        \Drupal::service('query_parameters_to_url.path_processor_query_parameters_to_url')->processOutbound($path, $options, Request::create($path));

        // Make sure to remove any left slashes, if the path was empty, because
        // it was the front page path.
        $path = ltrim($path, '/');

        // If the path indeed was changed, we redirect to the new path.
        if ($path != $original_path) {
          $options['absolute'] = TRUE;
          $url = Url::fromUri($request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . $path, $options);
          $url = query_parameters_to_url_replace_url_encoded_delimiters($url->toString());

          $exit_early = query_parameters_to_url_protect_redirect_loop();
          if ($exit_early) {
            $this->shouldSend = FALSE;
            return;
          }

          // Inspired by redirect module, tell the browser where to redirect
          // and why.
          // $event->getResponse()->headers->set('Location', $url);.
          $status_code = $config->get('query_parameters_to_url_redirect_status_code');
          $status_message = query_parameters_to_url_status_code_options($status_code);
          $this->message = $status_message;
          $this->redirect_url = $url;
          $this->shouldSend = TRUE;
          // $event->getResponse()->headers->set('Status', $status_message);
          // Output redirect, and cache it if necessary.
          // query_parameters_to_url_output_and_cache_redirect($url);
        }
        else {
          $this->shouldSend = FALSE;
        }
      }
      else {
        $this->shouldSend = FALSE;
      }
    }
    else {
      $this->shouldSend = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkForRedirection');
    $events[KernelEvents::RESPONSE][] = array('applyRedirection');
    return $events;
  }

}
