(function (Drupal, once) {
  Drupal.behaviors.orchardCtaClick = {
    attach(context, settings) {
      once('orchard-cta', '.cta-button', context).forEach((button) => {
        button.addEventListener('click', function () {
          const productId = this.getAttribute('data-product-id');
          fetch(`/orchard-product/click?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                console.log('Click logged successfully');
              } else {
                console.error('Error logging click:', data.message);
              }
            });
        });
      });
    }
  };
})(Drupal, once);
