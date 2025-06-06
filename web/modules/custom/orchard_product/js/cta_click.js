(function (Drupal, once) {
  Drupal.behaviors.orchardCtaLinkClick = {
    attach(context, settings) {
      once('cta-link', '.cta-link', context).forEach((link) => {
        link.addEventListener('click', function (event) {
          const productId = this.dataset.productId;

          event.preventDefault();

          fetch(`/orchard-product/click?product_id=${productId}`)
            .then(() => {
              window.location.href = this.href;
            })
            .catch(() => {
              window.location.href = this.href;
            });
        });
      });
    }
  };
})(Drupal, once);
