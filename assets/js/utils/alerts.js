/**
 * Utility functions for displaying alerts in the Mini-ERP system
 *
 * Provides a clean way to show Bootstrap styled alerts without relying
 * on specific containers to exist in the DOM
 */
(function ($) {
  'use strict';

  /**
   * Display a Bootstrap alert anywhere in the page
   *
   * @param {string} message - The message to display
   * @param {string} type - Alert type: success, danger, warning, info
   * @param {string|null} container - Optional CSS selector for the container
   * @param {Object} options - Additional options
   * @returns {jQuery} The alert element
   */
  window.showAlert = function (message, type = 'danger', container = null, options = {}) {
    const defaults = {
      dismissible: true,      // Whether alert can be dismissed
      autoClose: true,        // Whether to auto-close the alert
      duration: 5000,         // Auto-close duration in milliseconds
      animate: true,          // Whether to use fade animation
      prependInContainer: true // Whether to prepend (true) or append (false)
    };

    const settings = $.extend({}, defaults, options);

    // Create alert element
    const $alert = $('<div>')
      .addClass('alert alert-' + type)
      .html(message);

    // Add animation classes if needed
    if (settings.animate) {
      $alert.addClass('fade show');
    }

    // Add dismiss button if dismissible
    if (settings.dismissible) {
      $alert.addClass('alert-dismissible')
        .append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
    }

    // Find or create container
    let $container;

    if (container && $(container).length) {
      // Use specified container if it exists
      $container = $(container);
    } else if ($('#alert-container').length) {
      // Use existing alert-container if available
      $container = $('#alert-container');
    } else if ($('.content-wrapper').length) {
      // Create container inside content-wrapper if it exists
      $container = $('<div id="temp-alert-container" class="container-fluid mt-3"></div>');
      $('.content-wrapper').prepend($container);
    } else {
      // Fallback: create container at the top of the body
      $container = $('<div id="temp-alert-container" class="container mt-3"></div>');
      $('body').prepend($container);
    }

    // Add alert to container
    if (settings.prependInContainer) {
      $container.prepend($alert);
    } else {
      $container.append($alert);
    }

    // Auto-close if enabled
    if (settings.autoClose) {
      setTimeout(function () {
        $alert.alert('close');
      }, settings.duration);
    }

    return $alert;
  };

  /**
   * Shorthand for success alerts
   */
  window.showSuccess = function (message, container = null, options = {}) {
    return window.showAlert(message, 'success', container, options);
  };

  /**
   * Shorthand for error alerts
   */
  window.showError = function (message, container = null, options = {}) {
    return window.showAlert(message, 'danger', container, options);
  };

  /**
   * Shorthand for warning alerts
   */
  window.showWarning = function (message, container = null, options = {}) {
    return window.showAlert(message, 'warning', container, options);
  };

  /**
   * Shorthand for info alerts
   */
  window.showInfo = function (message, container = null, options = {}) {
    return window.showAlert(message, 'info', container, options);
  };

  /**
   * jQuery extension for showing alerts attached to elements
   *
   * Usage: $('#my-form').showFormAlert('Validation failed', 'danger');
   */
  $.fn.showFormAlert = function (message, type = 'danger', options = {}) {
    const $element = this.first();

    // If no element found, use global alert
    if (!$element.length) {
      return window.showAlert(message, type, null, options);
    }

    // Create a container if it doesn't exist
    let $container = $element.find('.alert-container');
    if (!$container.length) {
      $container = $('<div class="alert-container mb-3"></div>');
      $element.prepend($container);
    }

    // Show alert in this container
    return window.showAlert(message, type, $container, options);
  };

})(jQuery);
