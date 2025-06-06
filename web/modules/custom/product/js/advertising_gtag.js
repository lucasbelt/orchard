(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.advertising = {
    attach: function (context, settings) {
      const config = drupalSettings.gtag;
      if (config.tagId.length !== 0) {
        $('.advertising', context).on('click', function () {

          var campana = $(this).data('campana');
          var name = $(this).data('name');
          var localization = $(this).data('localization');

          gtag('event', 'clic_publicidad', {
            'event_category': 'Publicidad',
            'campana': campana,
            'name': name,
            'localization': localization
          });
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
